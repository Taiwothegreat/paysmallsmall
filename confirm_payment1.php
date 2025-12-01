<?php
date_default_timezone_set('Africa/Lagos');
include 'db_connect.php';

// Include PHPMailer classes at the top
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

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

        // Send confirmation email
        $payment = $result->fetch_assoc();
        $to = $payment['email'];

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'mail.paysmallsmall.org';  // often mail.yourdomain.com
            $mail->SMTPAuth = true;
            $mail->Username = 'info@paysmallsmall.org';
            #$mail->Password = 'YOUR_EMAIL_PASSWORD'; // Replace this with actual email password
            $mail->Password = '0I@Aj}.P-h]t'; // Replace this with actual email password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('info@paysmallsmall.org', 'Paysmallsmall');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = 'Payment Confirmation Received - Paysmallsmall';
            $mail->Body = "
                <h2>Payment Confirmed</h2>
                <p>Dear Customer,</p>
                <p>Your payment for <strong>{$payment['product_name']}</strong> (₦{$payment['price']}) has been successfully confirmed.</p>
                <p>Thank you for your trust in Paysmallsmall.</p>
            ";
#$mail->SMTPDebug = 2; // or 3 for more detail
#$mail->Debugoutput = 'html';

            $mail->send();
            echo "<h3 style='text-align:center;color:green;margin-top:50px;'>✅ Payment confirmed and email sent to $to</h3>";
        } catch (Exception $e) {
            echo "<h3 style='text-align:center;color:red;margin-top:50px;'>⚠️ Payment confirmed, but email could not be sent. Error: {$mail->ErrorInfo}</h3>";
        }
    } else {
        echo "<h3 style='text-align:center;color:red;margin-top:50px;'>❌ No matching payment record found.</h3>";
    }
}
?>
