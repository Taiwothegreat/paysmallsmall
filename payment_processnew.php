<?php
// payment_process.php
include 'db_connect.php';  // make sure this connects using $conn

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $product = $_POST['product_name'];
    $price = $_POST['price'];
    $plan = $_POST['plan_type'];
    $duration = $_POST['duration'];
    $payment_option = $_POST['payment_option'];

    // Generate unique admin token
    $token = bin2hex(random_bytes(16));

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO payments (email, product_name, price, plan_type, duration, payment_option, status, admin_token) 
                            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)");
    $stmt->bind_param("ssdssss", $email, $product, $price, $plan, $duration, $payment_option, $token);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Send email to admin with confirmation link
    $adminEmail = "info@paysmallsmall.org";
    $subject = "Confirm Payment for Order #$order_id";
    $confirmLink = "https://www.paysmallsmall.org/confirm_payment.php?order_id=$order_id&email=" . urlencode($email) . "&token=$token";
    $message = "
        <html>
        <body>
        <h2>New Payment Confirmation Request</h2>
        <p><strong>Customer:</strong> $email</p>
        <p><strong>Product:</strong> $product</p>
        <p><strong>Amount:</strong> ₦$price</p>
        <p><strong>Plan:</strong> $plan</p>
        <p><strong>Duration:</strong> $duration</p>
        <p><strong>Payment Option:</strong> $payment_option</p>
        <p>Click below to confirm this payment:</p>
        <p><a href='$confirmLink' style='background:#28a745;color:#fff;padding:10px 15px;text-decoration:none;border-radius:5px;'>Confirm Payment</a></p>
        </body>
        </html>
    ";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Paysmallsmall <no-reply@paysmallsmall.org>\r\n";

    mail($adminEmail, $subject, $message, $headers);

    echo "<script>alert('Payment submitted successfully! Admin will confirm shortly.'); window.location.href='thank_you.html';</script>";
}
?>
