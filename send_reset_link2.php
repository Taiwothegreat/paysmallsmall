<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $email = trim($_POST['email']);

  // Check email in 'accounts'
  $sql = "SELECT * FROM accounts WHERE email = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {

    $token = bin2hex(random_bytes(16));
    $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Save token + expiry
    $update = "UPDATE accounts SET token = ?, token_expiry = ? WHERE email = ?";
    $stmt2 = $conn->prepare($update);
    $stmt2->bind_param("sss", $token, $expiry, $email);
    $stmt2->execute();

    // Reset link
    $reset_link = "https://paysmallsmall.com/reset_password.php?token=" . $token;

    $subject = "Password Reset - Paysmallsmall";
    $message = "
    <html>
    <body style='font-family:Arial,sans-serif;'>
      <div style='max-width:500px;margin:auto;border:1px solid #eee;padding:20px;border-radius:6px;'>
        <h3 style='color:#007bff;'>Password Reset Request</h3>
        <p>Hello,</p>
        <p>Click the button below to reset your password:</p>
        <p style='text-align:center;'>
          <a href='$reset_link' style='background:#007bff;color:white;padding:10px 20px;border-radius:5px;text-decoration:none;'>
            Reset Password
          </a>
        </p>
        <p>This link will expire in 1 hour.</p>
        <p>Best regards,<br>Paysmallsmall Team</p>
      </div>
    </body>
    </html>";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Paysmallsmall <info@paysmallsmall.org>\r\n";

    mail($email, $subject, $message, $headers);

    echo "<h3 style='text-align:center;margin-top:80px;color:green;'>A reset link has been sent to your email.</h3>";

  } else {
    echo "<h3 style='text-align:center;margin-top:80px;color:red;'>Email not found.</h3>";
  }
}
?>
