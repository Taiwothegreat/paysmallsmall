<?php
// ---- DATABASE CONNECTION ----
$conn = new mysqli("localhost", "paysmall_user", ")1+TuLiz!uae", "paysmallsmall_db");
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

// ---- VERIFY PAYMENT RECORD ----
$stmt = $conn->prepare("
    SELECT id, status, admin_token 
    FROM payments 
    WHERE order_id = ? AND email = ? 
    LIMIT 1
");
$stmt->bind_param("ss", $order_id, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<h3 style='color:red;'>❌ Payment not found. Invalid Order ID or email.</h3>");
}

$row = $result->fetch_assoc();

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
    WHERE order_id = ? AND email = ?
");
$update->bind_param("ss", $order_id, $email);
$update->execute();
$update->close();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Confirmed</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f2f2f2; text-align:center; padding-top:80px; }
        .box { background:#fff; padding:40px; border-radius:10px; width:400px; margin:auto; box-shadow:0px 0px 10px rgba(0,0,0,0.2); }
        h2 { color:#28a745; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Payment Confirmed</h2>
        <p>The payment for Order ID:</p>
        <h3><?php echo htmlspecialchars($order_id); ?></h3>
        <p>has been successfully confirmed.</p>
    </div>
</body>
</html>
