<?php
date_default_timezone_set('Africa/Lagos');
include 'db_connect.php';

if (isset($_GET['order_id']) && isset($_GET['email']) && isset($_GET['token'])) {
    $order_id = intval($_GET['order_id']);
    $email = $_GET['email'];
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ? AND email = ? AND admin_token = ?");
    $stmt->bind_param("iss", $order_id, $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $update = $conn->prepare("UPDATE payments SET status = 'confirmed' WHERE id = ?");
        $update->bind_param("i", $order_id);
        $update->execute();
        $update->close();

        // Send confirmation email to user
        $payment = $result->fetch_assoc();
        $to = $payment['email'];
        $subject = "Payment Confirmation Received - Paysmallsmall";
        $message = "
            <html><body>
            <h2>Payment Confirmed</h2>
            <p>Dear Customer,</p>
            <p>Your payment for <strong>{$payment['product_name']}</strong> (₦{$payment['price']}) has been successfully confirmed.</p>
            <p>Thank you for your trust in Paysmallsmall.</p>
            </body></html>
        ";
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: Paysmallsmall <info@paysmallsmall.org>\r\n";

        if (mail($to, $subject, $message, $headers)) {
            echo "<h3 style='text-align:center;color:green;margin-top:50px;'>✅ Payment confirmed and email sent to $to</h3>";
        } else {
            echo "<h3 style='text-align:center;color:red;margin-top:50px;'>⚠️ Payment confirmed, but email could not be sent. Check mail() configuration.</h3>";
        }

        $stmt->close();
    } else {
        echo "<h3 style='text-align:center;color:red;margin-top:50px;'>❌ No matching payment record found.</h3>";
    }
} else {
    echo "<h3 style='text-align:center;color:red;margin-top:50px;'>⚠️ Invalid request.</h3>";
}
?>
