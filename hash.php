<?php
// Original password entered by the user
$password = "MySecurePassword123";

// Hash the password before saving to the database
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Display the hashed password
echo "Hashed Password: " . $hashed_password . "<br>";

// Later, when verifying during login
$user_input = "MySecurePassword123"; // password entered at login

if (password_verify($user_input, $hashed_password)) {
    echo "✅ Password is correct!";
} else {
    echo "❌ Invalid password.";
}
?> 
