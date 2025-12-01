<?php
include 'db_connect.php';  

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email      = $_POST['email'];
    $product    = $_POST['product_name'];
    $price      = $_POST['price'];
    $plan       = $_POST['plan_type'];
    $duration   = $_POST['duration'];
    $orderID    = $_POST['order_id'];
    $payment_option = $_POST['payment_option'];

    // Admin token for confirmation
    $token = bin2hex(random_bytes(16));

    // Insert into database
    $stmt = $conn->prepare("
        INSERT INTO payments 
        (email, product_name, price, plan_type, duration, payment_option, order_id_user, status, admin_token) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)
    ");

    $stmt->bind_param("ssdsssss", $email, $product, $price, $plan, $duration, $payment_option, $orderID, $token);
    $stmt->execute();
    $stmt->close();

    // Confirmation link
    $confirmLink = "https://www.paysmallsmall.org/confirm_payment.php?order_id=$orderID&email=" . urlencode($email) . "&token=$token";

    /*
    ============================
    SEND ADMIN EMAIL
    ============================
    */
    $adminEmail = "info@paysmallsmall.org";
    $subject = "Payment Confirmation Required – Order ID: $orderID";

    $message = "
    <html>
    <body>
    <h2>New Payment Confirmation Request</h2>

    <p><strong>Order ID:</strong> $orderID</p>
    <p><strong>Customer:</strong> $email</p>
    <p><strong>Product:</strong> $product</p>
    <p><strong>Amount:</strong> ₦$price</p>
    <p><strong>Plan:</strong> $plan</p>
    <p><strong>Duration:</strong> $duration</p>
    <p><strong>Payment Option:</strong> $payment_option</p>

    <br>
    <p>Click below to confirm this payment:</p>

    <p>
      <a href='$confirmLink' 
         style='background:#28a745;color:#fff;padding:10px 15px;text-decoration:none;border-radius:5px;'>
         CONFIRM PAYMENT
      </a>
    </p>

    </body>
    </html>
    ";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Paysmallsmall <no-reply@paysmallsmall.org>\r\n";

    mail($adminEmail, $subject, $message, $headers);


    /*
    ============================
    SEND CUSTOMER EMAIL
    ============================
    */
    $customerSubject = "Your Payment Submission – Order ID: $orderID";

    $customerMessage = "
    <html>
    <body>
    <h2>Your Payment Has Been Submitted</h2>

    <p>Thank you for your payment request!</p>

    <p><strong>Order ID:</strong> $orderID</p>
    <p><strong>Product:</strong> $product</p>
    <p><strong>Amount:</strong> ₦$price</p>
    <p><strong>Plan:</strong> $plan</p>
    <p><strong>Duration:</strong> $duration</p>

    <p>We will notify you once your payment is confirmed by our team.</p>

    </body>
    </html>
    ";

    $customerHeaders  = "MIME-Version: 1.0\r\n";
    $customerHeaders .= "Content-type:text/html;charset=UTF-8\r\n";
    $customerHeaders .= "From: Paysmallsmall <no-reply@paysmallsmall.org>\r\n";

    mail($email, $customerSubject, $customerMessage, $customerHeaders);

    echo "<script>alert('Payment submitted successfully! Check your email for your Order ID.'); 
    window.location.href='thank_you.html';</script>";
}
?>
