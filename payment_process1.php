<?php
include 'db_connect.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $plan_type = $_POST['plan_type'];
    $duration = $_POST['duration'];
    $payment_option = $_POST['payment_option'];
    $email = $_POST['email'];
    $user_id = $_SESSION['user_id'] ?? null;

    if (!$user_id) {
        die("User not logged in. Please sign in first.");
    }

    // Insert payment record
    $sql = "INSERT INTO order_history (product_name, price, plan_type, duration, date, user_id)
            VALUES (?, ?, ?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdsii", $product_name, $price, $plan_type, $duration, $user_id);
    $stmt->execute();

    // Get the last inserted order ID
    $order_id = $conn->insert_id;

    // Send confirmation email to admin
    $subject_admin = "Confirm New Payment - PaySmallSmall";
    $message_admin = "
    <html>
    <head><title>Payment Confirmation Request</title></head>
    <body style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;'>
      <div style='max-width:600px; margin:auto; background:#ffffff; border-radius:10px; padding:30px; box-shadow:0 0 10px rgba(0,0,0,0.1);'>
        <h2 style='color:#2c3e50;'>Payment Confirmation Request</h2>
        <p>A new payment has been made and needs your confirmation.</p>
        <p><strong>Product:</strong> $product_name<br>
        <strong>Amount:</strong> ₦".number_format($price)."<br>
        <strong>Plan Type:</strong> $plan_type<br>
        <strong>Duration:</strong> $duration<br>
        <strong>User Email:</strong> $email</p>
        <hr>
        <p style='text-align:center;'>
          <a href='https://www.paysmallsmall.org/confirm_payment.php?order_id=$order_id&email=".urlencode($email)."'
             style='background:#27ae60; color:white; padding:10px 20px; border-radius:5px; text-decoration:none;'>
             Confirm Payment
          </a>
        </p>
      </div>
    </body>
    </html>";

    $headers_admin = "MIME-Version: 1.0\r\n";
    $headers_admin .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers_admin .= "From: PaySmallSmall <info@paysmallsmall.org>\r\n";

    // Temporary admin address for testing
    #mail("taiwoomosehin6@gmail.com", $subject_admin, $message_admin, $headers_admin);
    mail("info@paysmallsmall.org", $subject_admin, $message_admin, $headers_admin);

    // Redirect to success page
    header("Location: payment_success.html");
    exit();
}
?>
