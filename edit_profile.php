<?php
session_start();
include 'db_connect.php';

// Initialize message
$message = "";

// Check session
$id = $_SESSION['account_id'] ?? null;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);

    if ($id) {
        // Update with password hashing
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE accounts SET name=?, email=?, address=?, password=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $email, $address, $hashed_password, $id);
        } else {
            // Update without changing password if left blank
            $stmt = $conn->prepare("UPDATE accounts SET name=?, email=?, address=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $email, $address, $id);
        }

        if ($stmt->execute()) {
            $message = "Profile updated successfully!";
        } else {
            $message = "Error updating profile: " . $conn->error;
        }
        $stmt->close();
    } else {
        $message = "Note: You are not logged in. Changes won't be saved.";
    }
}

// Fetch user info if logged in
if ($id) {
    $stmt = $conn->prepare("SELECT name, email, address FROM accounts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($name, $email, $address);
    $stmt->fetch();
    $stmt->close();
} else {
    $name = $email = $address = "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background-color: #f8f8f8;
        }
        .container {
            background: white;
            padding: 20px 30px;
            max-width: 500px;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        form input {
            width: 100%;
            padding: 10px;
            margin: 6px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            color: green;
            font-weight: bold;
        }
        .note {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Profile</h2>

    <?php if ($message): ?>
        <p class="<?php echo $id ? 'message' : 'note'; ?>"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST" action="edit_profile.php">
        <label>Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <label>Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <label>Address</label>
        <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>">

        <label>New Password (leave blank to keep current password)</label>
        <input type="password" name="password" placeholder="Enter new password">

        <button type="submit">Save Changes</button>
    </form>
</div>

</body>
</html>
