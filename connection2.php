<?php
// Database configuration
$servername = "localhost"; // Change this if your database is hosted on a different server
$username = "root"; // Your database username
$password = ""; // Your database password
$database = "leads"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
