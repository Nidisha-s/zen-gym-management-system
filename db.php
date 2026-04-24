<?php
// Disable error display (important for security)
ini_set('display_errors', 0);
error_reporting(0);

// Database credentials
$servername = "localhost";
$username = "YOUR_DB_USER";
$password = "YOUR_DB_PASSWORD";

// Admin database connection
$conn_admin = new mysqli($servername, $username, $password, "admin");

// User database connection
$conn_user = new mysqli($servername, $username, $password, "user");

// Check connections
if ($conn_admin->connect_error) {
    die("Admin DB connection failed.");
}

if ($conn_user->connect_error) {
    die("User DB connection failed.");
}
?>