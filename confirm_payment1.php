<?php
include 'db_connect.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Find payment by token
    $stmt = $conn->prepare("SELECT * FROM payments WHERE admin_token=? AND status='pending'");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($payment = $result->fetch_assoc()) {
        $new_balance = $payment['total_amount'] - $payment['amount_paid'];

        // Update payment record
        $update = $conn->prepare("UPDATE payments SET status='confirmed', balance=? WHERE admin_token=?");
        $update->bind_param("ds", $new_balance, $token);
        $update->execute();

        // Send confirmation email to customer
        $to = $payment['customer_email'];
        $subject = "Payment Confirmed - " . $payment['product_name'];
        $message = "
          <html><body>
          <h2>Payment Received Successfully!</h2>
          <p>Your payment of ₦" . number_format($payment['amount_paid'], 2) . " has been confirmed.</p>
          <p>New Balance: ₦" . number_format($new_balance, 2) . "</p>
          <p>Thank you for using Paysmallsmall.</p>
          </body></html>
        ";

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: Paysmallsmall <no-reply@paysmallsmall.org>\r\n";

        mail($to, $subject, $message, $headers);

        echo "<h2 style='color:green;text-align:center;'>✅ Payment confirmed successfully!</h2>";
    } else {
        echo "<h3 style='color:red;text-align:center;'>❌ Invalid or already confirmed token.</h3>";
    }
}
?>
