<?php
$password = "hNyhU#~NK(7l"; 

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo "Original: $password<br>";
echo "Hashed: $hashedPassword<br>";

// To verify later (like during login)
if (password_verify("hNyhU#~NK(7l", $hashedPassword)) {
    echo "Password verified!";
} else {
    echo "Invalid password!";
}

?>
