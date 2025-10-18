<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $fullname = $_POST['fullname'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $state = $_POST['state'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $token = bin2hex(random_bytes(16)); // unique verification token

  // Insert as unverified
  $sql = "INSERT INTO users (fullname, email, phone, state, password, verified, token) VALUES (?, ?, ?, ?, ?, 0, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssss", $fullname, $email, $phone, $state, $password, $token);

  if ($stmt->execute()) {
    // Prepare verification email
    $subject = "Verify Your PaySmallSmall Account";
    $from = "info@paysmallsmall.org";
    $headers  = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: PaySmallSmall <$from>" . "\r\n";

    // Verification link (update domain to yours)
    $verifyLink = "https://www.paysmallsmall.org/verify.php?token=" . $token;

    // HTML email body
    $message = '
    <html>
    <head>
      <title>Verify Your Account</title>
      <style>
        body { font-family: Arial, sans-serif; background-color: #f6f6f6; margin: 0; padding: 0; }
        .container { background-color: #ffffff; width: 90%; max-width: 600px; margin: 30px auto; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { background-color: #007bff; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .header img { width: 120px; }
        .content { padding: 25px; color: #333333; }
        .footer { text-align: center; padding: 15px; font-size: 13px; color: #777; }
        .btn { background-color: #007bff; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 5px; display: inline-block; margin-top: 10px; }
      </style>
    </head>
    <body>
      <div class="container">
        <div class="header">
          <img src="https://paysmallsmall.org/assets/logo.png" alt="PaySmallSmall Logo">
        </div>
        <div class="content">
          <h2>Welcome, ' . htmlspecialchars($fullname) . '!</h2>
          <p>Thank you for signing up with <strong>PaySmallSmall</strong>.</p>
          <p>To complete your registration, please verify your email address by clicking the button below:</p>
          <p><a href="' . $verifyLink . '" class="btn">Verify My Account</a></p>
          <p>If the button doesn’t work, copy and paste this link into your browser:</p>
          <p><a href="' . $verifyLink . '">' . $verifyLink . '</a></p>
        </div>
        <div class="footer">
          <p>&copy; ' . date("Y") . ' PaySmallSmall. All rights reserved.</p>
        </div>
      </div>
    </body>
    </html>';

    // Send verification email
    mail($email, $subject, $message, $headers);

    echo "<script>alert('Registration successful! Please check your email to verify your account.');window.location.href='index.html';</script>";
  } else {
    echo "Error: " . $stmt->error;
  }

  $stmt->close();
  $conn->close();
}
?>
