<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    // Step 1: Check if email exists
    $stmt = $conn->prepare("SELECT id FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo "<div class='alert alert-danger text-center'>This email is not registered.</div>";
    } else {
        // Step 2: Generate token
        $token = bin2hex(random_bytes(16));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conn->prepare("UPDATE accounts SET token = ?, token_expiry = ? WHERE email = ?");

        #$update = $conn->prepare("UPDATE accounts SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expiry, $email);
        $update->execute();

        // Step 3: Create reset link
        $resetLink = "https://paysmallsmall.com/lander.php?token=" . $token;

        // Step 4: Send email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'mail.paysmallsmall.org';
            $mail->SMTPAuth = true;
            $mail->Username = 'info@paysmallsmall.org';
            $mail->Password = 'YOUR_EMAIL_PASSWORD';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('info@paysmallsmall.org', 'PaySmallSmall');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Reset your PaySmallSmall password';
            $mail->Body = "
                <p>Hello,</p>
                <p>You requested to reset your PaySmallSmall account password.</p>
                <p>Click the link below to set a new password:</p>
                <p><a href='$resetLink' target='_blank' 
                style='background:#2c3e50;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;'>
                Reset Password</a></p>
                <p>If you did not request this, please ignore this email.</p>
                <p>This link expires in 1 hour.</p>
                <hr>
                <small>PaySmallSmall Team</small>
            ";

            $mail->send();
            echo "<div class='alert alert-success text-center'>A reset link has been sent to your email address.</div>";

        } catch (Exception $e) {
            echo "<div class='alert alert-danger text-center'>Email could not be sent. Error: {$mail->ErrorInfo}</div>";
        }
    }
}
?>
