<?php
include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $product = $_POST['product_name'];
    $price = floatval($_POST['price']);
    $plan = $_POST['plan_type'];
    $duration = $_POST['duration'];
    $payment_option = $_POST['payment_option'];
    $amountPaid = floatval($_POST['amount_paid']);

    // Generate token for admin confirmation link
    $admin_token = bin2hex(random_bytes(16));

    // Store as pending
    $stmt = $conn->prepare("INSERT INTO payments (account_id, product_name, total_amount, amount_paid, balance, last_payment, status, admin_token) VALUES (NULL, ?, ?, ?, ?, ?, 'pending', ?)");
    $balance = $price; // Deduction happens only after admin confirms
    $stmt->bind_param("sdddss", $product, $price, $amountPaid, $balance, $amountPaid, $admin_token);
    $stmt->execute();

    // Send mail to admin
    $subject = "Payment Confirmation Needed - $product";
    $confirm_link = "https://paysmallsmall.org/confirm_payment.php?token=$admin_token";
    $message = "
      <html>
      <body>
        <h2>Payment Pending Approval</h2>
        <p><strong>Customer Email:</strong> $email</p>
        <p><strong>Product:</strong> $product</p>
        <p><strong>Amount Paid:</strong> ₦" . number_format($amountPaid, 2) . "</p>
        <p><a href='$confirm_link' style='background:#28a745; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Confirm Payment</a></p>
      </body>
      </html>
    ";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Paysmallsmall <no-reply@paysmallsmall.org>\r\n";

    mail("taiwoomosehin6@gmail.com", $subject, $message, $headers);

    echo "<h3 style='text-align:center;color:green;'>✅ Payment submitted successfully! Awaiting admin confirmation.</h3>";
}
?>
