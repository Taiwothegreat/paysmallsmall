<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "<p style='font-family:Arial;padding:20px;'>User not found. Please log in again.</p>";
    exit();
}

// Update logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    $alt_phone = trim($_POST['alt_phone']);

    $update = $conn->prepare("UPDATE users SET email = ?, first_name = ?, last_name = ?, phone = ?, alt_phone = ? WHERE id = ?");
    $update->bind_param("sssssi", $email, $first_name, $last_name, $phone, $alt_phone, $user_id);

    if ($update->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location='account.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error updating profile. Please try again.');</script>";
    }
}

function pick_field($arr, $candidates, $default = '') {
    foreach ($candidates as $c) {
        if (array_key_exists($c, $arr) && $arr[$c] !== null && $arr[$c] !== '') {
            return $arr[$c];
        }
    }
    return $default;
}

$email          = pick_field($user, ['email'], '');
$first_name     = pick_field($user, ['first_name', 'fname'], '');
$last_name      = pick_field($user, ['last_name', 'lname'], '');
$phone          = pick_field($user, ['phone', 'mobile'], '');
$alt_phone      = pick_field($user, ['alt_phone', 'alternate_phone'], '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Profile - Paysmallsmall</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <style>
    body { background:#f8f9fa; font-family:Arial, sans-serif; padding-top:20px; }
    .form-container { background:#fff; padding:25px; border-radius:6px; max-width:600px; margin:30px auto; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
    h3 { text-align:center; margin-bottom:20px; color:#333; }
    label { font-weight:600; margin-top:10px; }
    input[type="text"], input[type="email"] { width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; margin-bottom:15px; }
    button { background:#007bff; color:#fff; border:none; padding:10px 20px; border-radius:4px; width:100%; font-weight:bold; }
    button:hover { background:#0056b3; }
  </style>
</head>
<body>

<div class="form-container">
  <h3>Edit Profile</h3>
  <form method="POST" action="">
    <label>Email</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

    <label>First Name</label>
    <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>

    <label>Last Name</label>
    <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>

    <label>Phone Number</label>
    <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>">

    <label>Alternate Phone Number</label>
    <input type="text" name="alt_phone" value="<?php echo htmlspecialchars($alt_phone); ?>">

    <button type="submit">Submit Changes</button>
  </form>
</div>

</body>
</html>
