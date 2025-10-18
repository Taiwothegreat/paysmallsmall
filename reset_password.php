<?php
include 'db_connect.php';

if (isset($_GET['token'])) {
  $token = $_GET['token'];
  $now = date("Y-m-d H:i:s");

  $sql = "SELECT * FROM users WHERE token = ? AND token_expiry > ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ss", $token, $now);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - Paysmallsmall</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body style="background:#f7f7f7; font-family:Arial,sans-serif;">
  <div class="container" style="max-width:400px;margin-top:80px;background:#fff;padding:30px;border-radius:6px;box-shadow:0 0 10px rgba(0,0,0,0.1);">
    <h3 class="text-center" style="margin-bottom:25px;">Create a New Password</h3>
    <form action="update_password.php" method="post">
      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
      <div class="form-group">
        <label>New Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block" style="background:#007bff; border:none;">Reset Password</button>
    </form>
  </div>
</body>
</html>
<?php
  } else {
    echo "<h3 style='text-align:center;margin-top:80px;color:red;'>Invalid or expired token.</h3>";
  }
}
?>
