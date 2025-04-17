<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "flexidesk_db";
$port = 3308;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed internally.']);
    exit;}
?>
