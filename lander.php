<?php
include 'db_connect.php';
session_start();

// Enable error reporting during setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("<div class='alert alert-danger text-center'>Invalid or missing reset token.</div>");
}

$token = $_GET['token'];

// Step 1: Verify the token
$stmt = $conn->prepare("SELECT id, email, token_expiry FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<div class='alert alert-danger text-center'>Invalid or expired token.</div>");
}

$user = $result->fetch_assoc();

// Check if token expired
if (strtotime($user['token_expiry']) < time()) {
    die("<div class='alert alert-warning text-center'>This reset link has expired. Please request a new one.</div>");
}

// Step 2: If form submitted → update password
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $update = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE id = ?");
    $update->bind_param("si", $newPassword, $user['id']);
    $update->execute();

    echo "<div class='alert alert-success text-center'>✅ Password updated successfully! You can now <a href='login.php'>login</a>.</div>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Reset Password | PaySmallSmall</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .reset-box { max-width: 450px; margin: 100px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    .btn-primary { background-color: #2c3e50; border: none; }
  </style>
</head>
<body>
  <div class="reset-box">
    <h3 class="text-center" style="color:#2c3e50;">Reset Your Password</h3>
    <p class="text-center">Enter a new password for your PaySmallSmall account.</p>
    <form method="POST">
      <div class="form-group">
        <label>New Password</label>
        <input type="password" name="password" class="form-control" required minlength="6" placeholder="Enter new password">
      </div>
      <button type="submit" class="btn btn-primary btn-block">Update Password</button>
    </form>
  </div>
</body>
</html>
