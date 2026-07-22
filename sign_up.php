<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_connect.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Load PHPMailer (make sure PHPMailer folder exists in same directory)
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $fullname = $_POST['fullname'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
  $address = $_POST['address'];
  $state = $_POST['state'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  // ✅ Check if email already exists
  $check_sql = "SELECT id FROM users WHERE email = ?";
  $check_stmt = $conn->prepare($check_sql);
  $check_stmt->bind_param("s", $email);
  $check_stmt->execute();
  $check_result = $check_stmt->get_result();

  if ($check_result->num_rows > 0) {
    header("Location: account_exists.html");
    exit();
  }

  // ✅ Insert into `users` table
  $sql = "INSERT INTO users (fullname, email, phone, address, state, password) VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssss", $fullname, $email, $phone, $address, $state, $password);

  if ($stmt->execute()) {

    $user_id = $stmt->insert_id;

    // ✅ Create matching account record
    $acc_sql = "INSERT INTO accounts (user_id, name, email, phone, address, state, verification_status) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $acc_stmt = $conn->prepare($acc_sql);

    $verification_status = 'Pending';

    $acc_stmt->bind_param(
      "issssss",
      $user_id,
      $fullname,
      $email,
      $phone,
      $address,
      $state,
      $verification_status
    );

    $acc_stmt->execute();
    $acc_stmt->close();

    // ✅ Send welcome email via PHPMailer
    $mail = new PHPMailer(true);

    try {

        $mail->isSMTP();
        $mail->Host       = 'mail.paysmallsmall.org';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@paysmallsmall.org';
        $mail->Password   = 'czX^%9XZFtJR#@';
        $mail->Port       = 465;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

        $mail->setFrom('info@paysmallsmall.org', 'PaySmallSmall');
        $mail->addAddress($email, $fullname);

        $mail->isHTML(true);
        $mail->Subject = "Welcome to PaySmallSmall!";

        $mail->Body = "
        <html>
        <head><title>Welcome to PaySmallSmall!</title></head>

        <body style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;'>

          <div style='max-width: 600px; margin: auto; background: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>

            <h2 style='color: #2c3e50;'>Hello $fullname,</h2>

            <p>
              Thank you for signing up with
              <strong>PaySmallSmall</strong>!
            </p>

            <p>
              Your registration was successful.
              You can now start exploring our flexible
              product payment plans.
            </p>

            <p>
              <a href='https://www.paysmallsmall.org/product.html'
                 style='display:inline-block;
                 padding:10px 20px;
                 background-color:#2c3e50;
                 color:#ffffff;
                 text-decoration:none;
                 border-radius:5px;'>

                 Explore Products
              </a>
            </p>

            <p style='margin-top:20px;'>
              Best regards,<br>
              The PaySmallSmall Team
            </p>

          </div>

        </body>
        </html>
        ";

        if ($mail->send()) {

            error_log("✅ Email sent successfully to $email");

        } else {

            error_log("❌ Failed to send email: " . $mail->ErrorInfo);
        }

    } catch (Exception $e) {

        error_log("❌ Exception: {$mail->ErrorInfo}");
    }

    // ✅ Store session info
    $_SESSION['user_id'] = $user_id;
    $_SESSION['fullname'] = $fullname;
    $_SESSION['email'] = $email;
    $_SESSION['state'] = $state;

echo "
<!DOCTYPE html>
<html>
<body>
<script>
localStorage.setItem('customerLGA', " . json_encode($state) . ");
localStorage.setItem('customerName', " . json_encode($fullname) . ");
localStorage.setItem('customerEmail', " . json_encode($email) . ");
window.location.href = 'product.html';
</script>
</body>
</html>";

    // ✅ Redirect to product page
    header("Location: product.html");
    exit();

  } else {

    echo "Error: " . $stmt->error;
  }

  $stmt->close();
  $conn->close();
}
?>