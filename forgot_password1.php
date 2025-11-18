<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure PHPMailer is installed via Composer
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    // Step 1: Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        echo "<div class='alert alert-danger text-center'>This email is not registered.</div>";
    } else {
        // Step 2: Generate token
        $token = bin2hex(random_bytes(16));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

        $update = $conn->prepare("UPDATE users SET reset_token = ?, token_expiry = ? WHERE email = ?");
        $update->bind_param("sss", $token, $expiry, $email);
        $update->execute();

        // Step 3: Create reset link
        $resetLink = "https://paysmallsmall.com/lander.php?token=" . $token;

        // Step 4: Send email
        $mail = new PHPMailer(true);
        try {
            // SMTP Settings (replace with your correct email SMTP info)
            $mail->isSMTP();
            $mail->Host = 'mail.paysmallsmall.org'; // Or your hosting mail server
            $mail->SMTPAuth = true;
            $mail->Username = 'info@paysmallsmall.org'; // Sender email
            $mail->Password = 'YOUR_EMAIL_PASSWORD'; // Replace with actual password or use app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email Content
            $mail->setFrom('info@paysmallsmall.org', 'PaySmallSmall');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Reset your PaySmallSmall password';
            $mail->Body = "
                <p>Hello,</p>
                <p>You requested to reset your PaySmallSmall account password.</p>
                <p>Click the link below to set a new password:</p>
                <p><a href='$resetLink' target='_blank' style='background:#2c3e50;color:white;padding:10px 15px;text-decoration:none;border-radius:5px;'>Reset Password</a></p>
                <p>If you did not request this, please ignore this email.</p>
                <p>Link expires in 1 hour.</p>
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

<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password | PaySmallSmall</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .reset-box { max-width: 450px; margin: 100px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .btn-primary { background-color: #2c3e50; border: none; }
  </style>
</head>
<body>
  <div class="reset-box">
    <h3 class="text-center" style="color:#2c3e50;">Forgot Password</h3>
    <p class="text-center">Enter your email to receive a password reset link.</p>
    <form method="POST">
      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" class="form-control" required placeholder="Enter your registered email">
      </div>
      <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
    </form>
  </div>
</body>
</html>
