<?php
include 'db_connect.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $product_name = $_POST['product_name'];
    $total_price = floatval($_POST['total_price']);
    $installment_amount = floatval($_POST['installment_amount']);
    $plan_type = $_POST['plan_type'];
    $duration = ($plan_type == "Weekly") ? "4 weeks" : (($plan_type == "Monthly") ? "3 months" : "Full Payment");

    // Get user info
    $stmt = $conn->prepare("SELECT id, balance FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $user_id = $user['id'];
        $new_balance = $user['balance'] - $installment_amount;

        // Update balance
        $update = $conn->prepare("UPDATE accounts SET balance = ? WHERE id = ?");
        $update->bind_param("di", $new_balance, $user_id);
        $update->execute();

        // Record payment in order_history
        $date = date("Y-m-d H:i:s");
        $insert = $conn->prepare("INSERT INTO order_history (user_id, product_name, price, plan_type, duration, date)
                                  VALUES (?, ?, ?, ?, ?, ?)");
        $insert->bind_param("isdsss", $user_id, $product_name, $installment_amount, $plan_type, $duration, $date);
        $insert->execute();

        // Send thank-you email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'mail.paysmallsmall.org';
            $mail->SMTPAuth = true;
            $mail->Username = 'info@paysmallsmall.org';
            $mail->Password = 'YOUR_EMAIL_PASSWORD';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('info@paysmallsmall.org', 'Paysmallsmall');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = "Payment Successful - Paysmallsmall";
            $mail->Body = "
                <h3>Payment Confirmation</h3>
                <p>Dear Customer,</p>
                <p>Thank you for your payment for <b>$product_name</b>.</p>
                <p>Plan: <b>$plan_type</b><br>
                Amount Paid: ₦" . number_format($installment_amount) . "<br>
                Total Price: ₦" . number_format($total_price) . "</p>
                <p>Your payment was successful.</p>
                <p><b>Bank:</b> Paysmallsmall Microfinance Bank<br>
                <b>Account Name:</b> Paysmallsmall Ltd<br>
                <b>Account Number:</b> 1234567890</p>
                <br><p>We appreciate your business!</p>
                <p>– Paysmallsmall Team</p>
            ";
            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }

        echo "<script>alert('Payment Successful! A confirmation email has been sent.'); window.location='product.html';</script>";
    } else {
        echo "<script>alert('No account found for this email.'); window.location='payment.html';</script>";
    }
}
?>
