<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $sql = "SELECT * FROM users WHERE email = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $token = bin2hex(random_bytes(16));

    // Save token and expiry (1 hour)
    $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
    $update = "UPDATE users SET token = ?, token_expiry = ? WHERE email = ?";
    $stmt2 = $conn->prepare($update);
    $stmt2->bind_param("sss", $token, $expiry, $email);
    $stmt2->execute();

    // Send email (HTML)
    $reset_link = "https://yourdomain.com/reset_password.php?token=" . $token;
    $subject = "Password Reset - Paysmallsmall";
    $message = "
    <html>
    <body style='font-family:Arial,sans-serif;'>
      <div style='max-width:500px;margin:auto;border:1px solid #eee;padding:20px;border-radius:6px;'>
        <div style='text-align:center;'>
          <img src='https://yourdomain.com/logo.png' alt='Paysmallsmall' style='width:120px;margin-bottom:10px;'>
        </div>
        <h3 style='color:#007bff;'>Password Reset Request</h3>
        <p>Hello,</p>
        <p>We received a request to reset your password. Click the button below to reset it:</p>
        <p style='text-align:center;'>
          <a href='$reset_link' style='background:#007bff;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;'>Reset Password</a>
        </p>
        <p>This link will expire in 1 hour. If you didn’t request this, you can ignore this email.</p>
        <p>Best regards,<br>Paysmallsmall Team</p>
      </div>
    </body>
    </html>";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Paysmallsmall <info@paysmallsmall.org>" . "\r\n";

    mail($email, $subject, $message, $headers);

    echo "<h3 style='text-align:center;margin-top:80px;color:green;'>A reset link has been sent to your email.</h3>";
  } else {
    echo "<h3 style='text-align:center;margin-top:80px;color:red;'>Email not found.</h3>";
  }
}
?>
