<?php
include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $fullname = $_POST['fullname'];
  $email = $_POST['email'];
  $phone = $_POST['phone'];
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
  $sql = "INSERT INTO users (fullname, email, phone, state, password) VALUES (?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssss", $fullname, $email, $phone, $state, $password);

  if ($stmt->execute()) {
    $user_id = $stmt->insert_id; // new user’s ID

    // ✅ Create matching account record
    $acc_sql = "INSERT INTO accounts (user_id, name, email, phone, state, verification_status) VALUES (?, ?, ?, ?, ?, ?)";
    $acc_stmt = $conn->prepare($acc_sql);
    $verification_status = 'Pending';
    $acc_stmt->bind_param("isssss", $user_id, $fullname, $email, $phone, $state, $verification_status);
    $acc_stmt->execute();
    $acc_stmt->close();

    // ✅ Send welcome email
    $to = $email;
    $subject = "Welcome to PaySmallSmall!";
    $message = "
    <html>
    <head><title>Welcome to PaySmallSmall!</title></head>
    <body style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;'>
      <div style='max-width: 600px; margin: auto; background: #ffffff; border-radius: 10px; padding: 30px; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
        <h2 style='color: #2c3e50;'>Hello $fullname,</h2>
        <p>Thank you for signing up with <strong>PaySmallSmall</strong>!</p>
        <p>Your registration was successful. You can now start exploring our flexible product payment plans.</p>
        <p><a href='https://www.paysmallsmall.org/products.php' style='display:inline-block; padding:10px 20px; background-color:#2c3e50; color:#ffffff; text-decoration:none; border-radius:5px;'>Explore Products</a></p>
        <p style='margin-top:20px;'>Best regards,<br>The PaySmallSmall Team</p>
      </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: PaySmallSmall <info@paysmallsmall.org>\r\n";

    mail($to, $subject, $message, $headers);

    // ✅ Store session info
    $_SESSION['user_id'] = $user_id;
    $_SESSION['fullname'] = $fullname;
    $_SESSION['email'] = $email;

    // ✅ Redirect to product page
    header("Location: products.php");
    exit();
  } else {
    echo "Error: " . $stmt->error;
  }

  $stmt->close();
  $conn->close();
}
?>
