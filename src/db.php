<?php
session_start();  // Start the session

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "flexidesk_db";
$port = 3308;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
