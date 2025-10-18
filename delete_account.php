<?php
session_start();
include 'db_connect.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        session_destroy();
        header("Location: index.html?message=Account+deleted+successfully");
        exit();
    } else {
        echo "Error deleting account: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: login.html");
    exit();
}
?>
