<?php
// ---- DATABASE CONNECTION ----
$conn = new mysqli("localhost", "paysmall_user", ")1+TuLiz!uae", "paysmall_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ---- GET VALUES FROM LINK ----
$order_id = trim($_GET['order_id'] ?? '');
$email    = trim($_GET['email'] ?? '');
$token    = trim($_GET['token'] ?? '');

if (empty($order_id) || empty($email) || empty($token)) {
    die("<h3 style='color:red;'>Invalid confirmation link. Missing parameters.</h3>");
}

// ---- VERIFY PAYMENT RECORD (case-insensitive email) ----
/*$stmt = $conn->prepare("
    SELECT product_name, price, first_installment, plan_type, duration, status, admin_token 
    FROM payments 
    WHERE order_id = ? AND LOWER(email) = LOWER(?)
    LIMIT 1
");*/
$stmt = $conn->prepare("
    SELECT product_name, price, installment_amount, first_installment, plan_type, duration, status, admin_token 
    FROM payments 
    WHERE order_id = ? AND LOWER(email) = LOWER(?)
    LIMIT 1
");

$stmt->bind_param("ss", $order_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<h3 style='color:red;'>❌ Payment not found. Invalid Order ID or email.<br>
        Debug: Order ID='$order_id', Email='$email'</h3>");
}

$row = $result->fetch_assoc();

// Extract payment fields
$product  = $row["product_name"];
$price    = $row["price"];
$installment = $row["installment_amount"];
$installment_without_shipping = $installment;
$first    = $row["first_installment"];
$plan     = $row["plan_type"];
$duration = $row["duration"];

// ---- CHECK TOKEN MATCH ----
if ($token !== $row['admin_token']) {
    die("<h3 style='color:red;'>❌ Invalid or expired confirmation token.</h3>");
}

// ---- CHECK IF ALREADY CONFIRMED ----
if ($row['status'] === 'confirmed') {
    die("<h3 style='color:green;'>This payment has already been confirmed earlier.</h3>");
}

// ---- UPDATE PAYMENT STATUS ----
$update = $conn->prepare("
    UPDATE payments 
    SET status = 'confirmed' 
    WHERE order_id = ? AND LOWER(email) = LOWER(?)
");
$update->bind_param("ss", $order_id, $email);
$update->execute();
$update->close();
// ----------------------------------
// SEND CONFIRMATION EMAIL TO CUSTOMER
// ----------------------------------

$subject = "Payment Confirmed – Order ID: $order_id";

$message = "
<html><body>
<h2 style='color:#28a745;'>Your Payment Has Been Confirmed</h2>

<p><strong>Order ID:</strong> $order_id</p>
<p><strong>Product:</strong> $product</p>
<p><strong>Total Price:</strong> ₦" . number_format($price, 2) . "</p>
<p><strong>Installment Amount (without shipping):</strong> ₦" . number_format($installment, 2) . "</p>
<p><strong>First Installment (with shipping):</strong> ₦" . number_format($first, 2) . "</p>
<p><strong>Plan:</strong> $plan</p>
<p><strong>Duration:</strong> $duration Weeks</p>

<p style='margin-top:20px;'>Your payment has been successfully verified by our team.</p>
<p>You may continue with the next installment when due.</p>

</body></html>
";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type:text/html; charset=UTF-8\r\n";
$headers .= "From: Paysmallsmall <no-reply@paysmallsmall.org>\r\n";

mail($email, $subject, $message, $headers);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Confirmed</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f2f2f2; text-align:center; padding-top:40px; }
        .box { background:#fff; padding:30px; border-radius:10px; width:450px; margin:auto; 
               box-shadow:0px 0px 10px rgba(0,0,0,0.2); }
        h2 { color:#28a745; }
        p { font-size:16px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Payment Confirmed</h2>

        <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order_id); ?></p>
        <p><strong>Product:</strong> <?php echo htmlspecialchars($product); ?></p>
        <p><strong>Total Price:</strong> ₦<?php echo number_format($price, 2); ?></p>
        <p><strong>Installment Amount (without shipping):</strong> ₦<?php echo number_format($installment_without_shipping, 2); ?></p>

        <p><strong>First Installment (with 5% shipping):</strong> ₦<?php echo number_format($first, 2); ?></p>
        <!--       <p><strong>Plan:</strong><?php #echo htmlspecialchars($plan); ?></p>-->
        <p><strong>Duration:</strong> <?php echo htmlspecialchars($duration); ?>Weeks</p>

        <br>
        <p style="color:green;">This payment has been successfully confirmed.</p>
    </div>
</body>
</html>
