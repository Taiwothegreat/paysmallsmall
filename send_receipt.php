<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'];
  $product = $_POST['product'];
  $amount = $_POST['amount'];
  $balance = $_POST['balance'];
  $plan = $_POST['plan'];
  $duration = $_POST['duration'];

  $to_admin = "info@paysmallsmall.org";
  $subject_user = "Payment Confirmation for $product";
  $subject_admin = "New Payment Received - $product";

  $message_user = "
  Hello,

  Thank you for your payment via $plan plan for the product: $product.

  Amount Paid: ₦" . number_format($amount) . "
  Balance Remaining: ₦" . number_format($balance) . "
  Duration: $duration " . strtolower($plan) . "

  Your payment receipt has been recorded successfully.
  Regards,
  Paysmallsmall Team";

  $message_admin = "
  New payment received from: $email

  Product: $product
  Plan: $plan
  Amount Paid: ₦" . number_format($amount) . "
  Balance Remaining: ₦" . number_format($balance) . "
  Duration: $duration " . strtolower($plan) . "
  ";

  $headers = "From: info@paysmallsmall.org\r\nReply-To: info@paysmallsmall.org";

  // Send to both user and admin
  mail($email, $subject_user, $message_user, $headers);
  mail($to_admin, $subject_admin, $message_admin, $headers);

  echo "<h3 style='text-align:center; margin-top:100px;'>Thank you! Your payment details have been sent to your email.</h3>";
  echo "<p style='text-align:center;'><a href='product.html'>Return to Products</a></p>";
}
?>
