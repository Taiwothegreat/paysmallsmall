<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect POST safely
    $email    = trim($_POST['email'] ?? '');
    $product  = trim($_POST['product_name'] ?? '');
    $price    = floatval($_POST['price'] ?? 0);
    $plan     = trim($_POST['plan_type'] ?? '');
    $duration = trim($_POST['duration'] ?? '');
    $orderID  = trim($_POST['order_id'] ?? '');  // CORRECT VARIABLE
    $payment_option = trim($_POST['payment_option'] ?? 'Bank Transfer');

    // Generate admin token
    $token = bin2hex(random_bytes(16));

    /*
    INSERT INTO DATABASE
    */
    $stmt = $conn->prepare("
        INSERT INTO payments 
        (email, product_name, price, plan_type, duration, payment_option, order_id, status, admin_token) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)
    ");

    $stmt->bind_param(
        "ssdsssss",
        $email,
        $product,
        $price,
        $plan,
        $duration,
        $payment_option,
        $orderID,   // CORRECT
        $token
    );

    $stmt->execute();
    $stmt->close();

    /*
    ADMIN CONFIRMATION LINK
    */
    $confirmLink =
        "https://www.paysmallsmall.org/confirm_payment.php?order_id=$orderID&email="
        . urlencode($email)
        . "&token=$token";

    /*
    SEND ADMIN EMAIL
    */
    $adminEmail = "info@paysmallsmall.org";
    $subject = "Payment Confirmation Required – Order ID: $orderID"; // FIXED

    $message = "
    <html><body>
    <h2>New Payment Confirmation Request</h2>

    <p><strong>Order ID:</strong> $orderID</p>
    <p><strong>Customer Email:</strong> $email</p>
    <p><strong>Product:</strong> $product</p>
    <p><strong>Amount:</strong> ₦" . number_format($price, 2) . "</p>
    <p><strong>Plan:</strong> $plan</p>
    <p><strong>Duration:</strong> $duration</p>
    <p><strong>Payment Option:</strong> $payment_option</p>

    <br><br>
    <a href='$confirmLink' 
       style='background:#28a745;color:#fff;padding:12px 20px;text-decoration:none;border-radius:5px;font-size:16px;'>
       CONFIRM PAYMENT
    </a>
    </body></html>
    ";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html; charset=UTF-8\r\n";
    $headers .= "From: Paysmallsmall <no-reply@paysmallsmall.org>\r\n";

    mail($adminEmail, $subject, $message, $headers);

    /*
    SEND CUSTOMER EMAIL
    */
    $customerSubject = "Your Payment Submission – Order ID: $orderID";

    $customerMessage = "
    <html><body>
    <h2>Your Payment Has Been Submitted</h2>

    <p><strong>Order ID:</strong> $orderID</p>
    <p><strong>Product:</strong> $product</p>
    <p><strong>Amount:</strong> ₦" . number_format($price, 2) . "</p>
    <p><strong>Plan:</strong> $plan</p>
    <p><strong>Duration:</strong> $duration</p>

    <p>You will be notified once our team confirms your payment.</p>
    </body></html>
    ";

    $customerHeaders  = "MIME-Version: 1.0\r\n";
    $customerHeaders .= "Content-type:text/html; charset=UTF-8\r\n";
    $customerHeaders .= "From: Paysmallsmall <no-reply@paysmallsmall.org>\r\n";

    mail($email, $customerSubject, $customerMessage, $customerHeaders);

    /*
    FINAL REDIRECT
    */
    echo "<script>
        alert('Payment submitted successfully! Check your email for your Order ID.');
        window.location.href='thank_you.html';
    </script>";
}
?>
