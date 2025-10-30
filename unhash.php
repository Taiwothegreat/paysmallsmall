<?php
$hash = '$2y$10$veJ9gs3Ephxj/J.hvCp/QOOfgRE9h30oV11uZwFDLKir88oAZD5VC';
$plain = '';

if (password_verify($plain, $hash)) {
    echo "Password verified!";
} else {
    echo "Invalid password.";
}
?>
