<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $token = $_POST['token'];
  $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

  $sql = "UPDATE users SET password = ?, token = NULL, token_expiry = NULL WHERE token = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $password, $token);
  if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo "<h3 style='text-align:center;margin-top:80px;color:green;'>Password reset successfully! <a href='login.html'>Login Now</a></h3>";
  } else {
    echo "<h3 style='text-align:center;margin-top:80px;color:red;'>Invalid or expired token.</h3>";
  }
}
?>
