<?php
// confirm_payment.php
// Confirm a payment using order_id, email and admin token.
// Expects: ?order_id=...&email=...&token=...

// Use your existing DB connection file (must set $conn mysqli object)
include 'db_connect.php';

// Helper: show an error and stop
function fail($msg) {
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Payment Confirmation</title></head><body style="font-family:Arial,Helvetica,sans-serif;margin:40px;">';
    echo "<h3 style='color:red;'>$msg</h3>";
    echo '</body></html>';
    exit;
}

// Read and trim GET params
$order_id = isset($_GET['order_id']) ? trim($_GET['order_id']) : '';
$email    = isset($_GET['email'])    ? trim($_GET['email'])    : '';
$token    = isset($_GET['token'])    ? trim($_GET['token'])    : '';

if ($order_id === '' || $email === '' || $token === '') {
    fail('Invalid confirmation link. Missing parameters.');
}

// Basic sanity checks (lengths)
if (strlen($order_id) > 100 || strlen($email) > 255 || strlen($token) > 128) {
    fail('Invalid confirmation parameters.');
}

// Prepare and execute SELECT — token validated in SQL
$sql = "
    SELECT id,
           order_id,
           product_name,
           price,
           installment_amount,
           first_installment,
           plan_type,
           duration,
           status,
           admin_token
    FROM payments
    WHERE order_id = ? 
      AND LOWER(email) = LOWER(?) 
      AND admin_token = ?
    LIMIT 1
";

if (! $stmt = $conn->prepare($sql)) {
    fail('Database error (prepare).');
}

$stmt->bind_param('sss', $order_id, $email, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // No row matched — either wrong order_id/email/token or already modified
    fail('❌ Payment not found or token mismatch. Please check the link or the order details.');
}

$row = $result->fetch_assoc();
$stmt->close();

// Extract values
$id                 = $row['id'];
$db_order_id        = $row['order_id'];
$product            = $row['product_name'];
$price              = (float)$row['price'];
$installmentAmount  = isset($row['installment_amount']) ? (float)$row['installment_amount'] : null;
$firstInstallment   = isset($row['first_installment']) ? (float)$row['first_installment'] : null;
$plan               = $row['plan_type'];
$duration           = $row['duration'];
$status             = $row['status'];

// If already confirmed
if (strtolower($status) === 'confirmed') {
    // Show the record but note it was already confirmed
    $alreadyConfirmed = true;
} else {
    $alreadyConfirmed = false;

    // Update status to 'confirmed' (use id to be precise)
    $u = $conn->prepare("UPDATE payments SET status = 'confirmed' WHERE id = ? AND admin_token = ?");
    if (! $u) {
        fail('Database error (prepare update).');
    }
    $u->bind_param('is', $id, $token);
    $u->execute();
    $u->close();
}

// Output friendly confirmation page
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Payment Confirmed</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body { font-family: Arial, Helvetica, sans-serif; background:#f6f7f9; color:#222; }
    .wrap { max-width:680px; margin:48px auto; background:#fff; padding:28px; border-radius:10px; box-shadow:0 6px 30px rgba(0,0,0,0.08); }
    h1 { color:#28a745; margin:0 0 14px; font-size:22px; }
    p { margin:8px 0; line-height:1.45; }
    .label { color:#555; width:220px; display:inline-block; font-weight:600; }
    .value { color:#111; }
    .note { margin-top:18px; color:#2a7; font-weight:600; }
  </style>
</head>
<body>
  <div class="wrap">
    <h1>Payment Confirmed</h1>

    <p><span class="label">Order ID:</span> <span class="value"><?php echo htmlspecialchars($db_order_id); ?></span></p>
    <p><span class="label">Product:</span> <span class="value"><?php echo htmlspecialchars($product); ?></span></p>
    <p><span class="label">Total Price:</span> <span class="value">₦<?php echo number_format($price, 2); ?></span></p>

    <?php if ($installmentAmount !== null): ?>
      <p><span class="label">Installment Amount (No Shipping):</span> <span class="value">₦<?php echo number_format($installmentAmount, 2); ?></span></p>
    <?php endif; ?>

    <?php if ($firstInstallment !== null): ?>
      <p><span class="label">First Installment (with 5% shipping):</span> <span class="value">₦<?php echo number_format($firstInstallment, 2); ?></span></p>
    <?php endif; ?>

    <p><span class="label">Plan:</span> <span class="value"><?php echo htmlspecialchars($plan ?: 'N/A'); ?></span></p>
    <p><span class="label">Duration:</span> <span class="value"><?php echo htmlspecialchars($duration ?: 'N/A'); ?></span></p>

    <?php if ($alreadyConfirmed): ?>
      <p class="note">This payment was already confirmed earlier.</p>
    <?php else: ?>
      <p class="note">This payment has been successfully confirmed. Thank you.</p>
    <?php endif; ?>

  </div>
</body>
</html>
