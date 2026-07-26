<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect POST safely
    $email    = trim($_POST['email'] ?? '');
    $product  = trim($_POST['product_name'] ?? '');
    $price    = floatval($_POST['price'] ?? 0);
    $plan     = trim($_POST['plan_type'] ?? '');
    $duration = intval($_POST['duration'] ?? 0);
    $orderID  = trim($_POST['order_id'] ?? '');
    $payment_option = trim($_POST['payment_option'] ?? 'Bank Transfer');
    $shippingFee = floatval($_POST['shipping_fee'] ?? 0);
    $customerLGA = trim($_POST['customer_lga'] ?? '');
    $orderType = trim($_POST['order_type'] ?? 'machine');

    // ==============================
   


    // Base installment (no shipping)
    if ($plan === 'Weekly' || $plan === 'Monthly') {
        $installmentAmount = ceil($price / $duration);  // e.g., 4000 / 10 = 400
        $firstInstallment = $installmentAmount + $shippingFee; // e.g., 400 + 200 = 600
    } else {
        // Buy Once plan
        $installmentAmount = $price;
        $firstInstallment = $price + $shippingFee;
    }

    // Generate admin token
    $token = bin2hex(random_bytes(16));

   // INSERT INTO DB
$stmt = $conn->prepare("
INSERT INTO payments
(order_id, email, product_name, price, customer_lga, installment_amount,
 first_installment, plan_type, duration, payment_option, status, admin_token)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)
");

$stmt->bind_param(
    "sssdsddsiss",
    $orderID,
    $email,
    $product,
    $price,
    $customerLGA,
    $installmentAmount,
    $firstInstallment,
    $plan,
    $duration,
    $payment_option,
    $token
);

$stmt->execute();
$stmt->close();


    // ==============================
    // ADMIN EMAIL
    // ==============================

    $adminEmail = "info@paysmallsmall.org";
    $subject = "Payment Confirmation Required – Order ID: $orderID";

    $confirmLink = "https://www.paysmallsmall.org/confirm_payment.php?order_id=$orderID&email=" . urlencode($email) . "&token=$token";


    if ($orderType === "accessory") {
    $productLabel = "Accessories Purchased";
} else {
    $productLabel = "Product";
}
    $message = "
    <html><body>
    
    <h2>New Payment Confirmation Request</h2>

    <p><strong>Order ID:</strong> $orderID</p>
    <p><strong>Customer Email:</strong> $email</p>
<p><strong>$productLabel:</strong><br>$product</p>
    <p><strong>Total Price:</strong> ₦" . number_format($price, 2) . "</p>
    <p><strong>Installment Amount (No Shipping):</strong> ₦" . number_format($installmentAmount, 2) . "</p>

<p><strong>LGA/LCDA:</strong> $customerLGA</p>

<p><strong>Shipping Fee:</strong>
₦" . number_format($shippingFee,2) . "</p>

<p><strong>First Installment:</strong>
₦" . number_format($firstInstallment,2) . "</p>
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

    // ==============================
    // CUSTOMER EMAIL
    // ==============================
    $shippingFee = floatval($_POST['shipping_fee'] ?? 0);

    $customerSubject = "Your Payment Submission – Order ID: $orderID";

    $customerMessage = "
    <html><body>
    <h2>Your Payment Has Been Submitted</h2>

    <p><strong>Order ID:</strong> $orderID</p>
    <p><strong>Product:</strong> $product</p>

    <p><strong>Total Price:</strong> ₦" . number_format($price, 2) . "</p>

<p><strong>LGA/LCDA:</strong> $customerLGA</p>

<p><strong>LGA/LCDA Charge:</strong> ₦" . number_format($shippingFee, 2) . "</p>

<p><strong>First Installment (Including LGA/LCDA Charge):</strong> ₦" . number_format($firstInstallment, 2) . "</p>

<p><strong>Regular Installment:</strong> ₦" . number_format($installmentAmount, 2) . "</p>

<p><strong>Plan:</strong> $plan</p>

<p><strong>Duration:</strong> $duration</p>
    <p>You will be notified once our team confirms your payment.</p>
    </body></html>
    ";

    $customerHeaders  = "MIME-Version: 1.0\r\n";
    $customerHeaders .= "Content-type:text/html; charset=UTF-8\r\n";
    $customerHeaders .= "From: Paysmallsmall <no-reply@paysmallsmall.org>\r\n";

    mail($email, $customerSubject, $customerMessage, $customerHeaders);

    // REDIRECT
    echo "<script>
        alert('Payment submitted successfully! Check your email for your Order ID.');
        window.location.href='thank_you.html';
    </script>";
}
?>
