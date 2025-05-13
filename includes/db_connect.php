<?php
// Database connection parameters
$db_host = 'localhost';
$db_user = 'root'; // Default XAMPP username
$db_password = ''; // Default XAMPP password (empty)
$db_name = 'kaambuddy';

// Create connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");
?> 