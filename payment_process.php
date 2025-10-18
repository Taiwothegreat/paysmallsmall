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

    // Email confirmation
    $subject = "Payment Confirmation - PaySmallSmall";
    $message = "
    <html>
    <head><title>Payment Confirmation</title></head>
    <body style='font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px;'>
      <div style='max-width:600px; margin:auto; background:#ffffff; border-radius:10px; padding:30px; box-shadow:0 0 10px rgba(0,0,0,0.1);'>
        <h2 style='color:#2c3e50;'>Thank You for Your Payment!</h2>
        <p>Your payment for <strong>$product_name</strong> has been successfully recorded.</p>
        <p><strong>Amount Paid:</strong> ₦".number_format($price)."<br>
        <strong>Plan Type:</strong> $plan_type<br>
        <strong>Duration:</strong> $duration<br>
        <strong>Payment Option:</strong> $payment_option</p>
        <hr>
        <h4>Bank Details</h4>
        <p><strong>Bank Name:</strong> Access Bank<br>
        <strong>Account Name:</strong> Paysmallsmall Limited<br>
        <strong>Account Number:</strong> 0123456789</p>
        <p style='margin-top:20px;'>We appreciate your trust in <strong>PaySmallSmall</strong>.</p>
      </div>
    </body>
    </html>";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: PaySmallSmall <info@paysmallsmall.org>\r\n";

    mail($email, $subject, $message, $headers);

    header("Location: payment_success.html");
    exit();
}
?>
