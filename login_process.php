<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize input
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Prepare query
    $sql = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email']; // Added for account linking

            // Redirect to product page
            header("Location: product.html");
            exit();
        } else {
            // Incorrect password
            header("Location: invalid.html");
            exit();
        }
    } else {
        // Email not found
        header("Location: invalid.html");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
