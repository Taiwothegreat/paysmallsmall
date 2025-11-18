<?php
include 'db_connect.php';

if (isset($_GET['token'])) {

    $token = $_GET['token'];
    $now = date("Y-m-d H:i:s");

    $sql = "SELECT * FROM accounts WHERE token = ? AND token_expiry > ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $token, $now);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
?>
<!DOCTYPE html>
<html>
<head>
<title>Create New Password</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body>
<div class="container" style="max-width:400px;margin-top:80px;background:#fff;padding:30px;border-radius:6px;">
  <h3 class="text-center">Create a New Password</h3>
  <form action="update_password.php" method="post">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <div class="form-group">
      <label>New Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
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
