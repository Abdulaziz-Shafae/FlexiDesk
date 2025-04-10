<?php
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "flexidesk_db";           

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);  // Display error if connection fails
}

// If the connection is successful, the $conn variable will be used to interact with the database
?>
