<?php
session_start();
include 'db_connect.php';

$user_id = $_SESSION['user_id'];

if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir);
    }

    $filename = time() . "_" . basename($_FILES["profile_pic"]["name"]);
    $target_file = $target_dir . $filename;

    if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
        $query = "UPDATE users SET profile_pic = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $target_file, $user_id);
        $stmt->execute();
    }
}

header("Location: account.php");
exit();
?>
