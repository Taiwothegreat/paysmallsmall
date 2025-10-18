<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, password FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // ✅ Set the session
            $_SESSION['account_id'] = $id;

            // ✅ Redirect to edit profile
            header("Location: edit_profile.php");
            exit;
        } else {
            echo "Incorrect password.";
        }
    } else {
        echo "No account found with that email.";
    }
    $stmt->close();
}
?>
