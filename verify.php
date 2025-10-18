<?php
session_start();
include 'db_connect.php';

if (isset($_GET['token'])) {
  $token = $_GET['token'];

  // Check if the token exists and user not yet verified
  $sql = "SELECT * FROM users WHERE token = ? AND verified = 0";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("s", $token);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Update verification status
    $update = "UPDATE users SET verified = 1, token = NULL WHERE id = ?";
    $stmt2 = $conn->prepare($update);
    $stmt2->bind_param("i", $user['id']);
    $stmt2->execute();

    // Start session and log the user in automatically
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['fullname'] = $user['fullname'];
    $_SESSION['email'] = $user['email'];

    // Redirect to product page
    header("Location: product.html");
    exit();
  } else {
    echo "<h2 style='font-family:Arial;text-align:center;margin-top:50px;color:red;'>Invalid or expired verification link.</h2>";
  }

  $stmt->close();
  $conn->close();
} else {
  echo "<h2 style='font-family:Arial;text-align:center;margin-top:50px;color:red;'>No token provided.</h2>";
}
?>
