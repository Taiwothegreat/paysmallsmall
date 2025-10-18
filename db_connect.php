<?php
// db_connect.php
$servername = "localhost";
$username = "root"; // Change this if using a live server
$password = "";     // Add your real password if any
$dbname = "paysmallsmall_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
