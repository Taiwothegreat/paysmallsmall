<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (!isset($_POST['token']) || !isset($_POST['password'])) {
        die("Invalid request.");
    }

    $token = $_POST['token'];
    $password = $_POST['password'];
    $now = date("Y-m-d H:i:s");

    // ✅ Validate token
    $sql = "SELECT id FROM accounts WHERE token = ? AND token_expiry > ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $token, $now);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("<h3 style='text-align:center;margin-top:80px;color:red;'>Invalid or expired token.</h3>");
    }

    $row = $result->fetch_assoc();
    $account_id = $row['id'];

    // ✅ Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // ✅ Update password + clear token
    $sql2 = "UPDATE accounts SET password = ?, token = NULL, token_expiry = NULL WHERE id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("si", $hashedPassword, $account_id);

    if ($stmt2->execute()) {
        echo "
        <div style='text-align:center; margin-top:80px;'>
            <h3 style='color:green;'>Password successfully updated!</h3>
            <a href='login.php' style='text-decoration:none;'>
                <button style='padding:10px 20px; background:#007bff; color:#fff; border:none; border-radius:4px; cursor:pointer;'>
                    Go to Login
                </button>
            </a>
        </div>";
    } else {
        echo "<h3 style='text-align:center;margin-top:80px;color:red;'>Error updating password. Please try again.</h3>";
    }

} else {
    header("Location: login.php");
    exit();
}
?>
