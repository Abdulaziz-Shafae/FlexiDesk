<?php
// Connect to database
include 'db_connect.php';

// Get POST data
$message = $_POST['message'];
$project = $_POST['project'];
$sender = 'You'; // You can modify later for real users
$timestamp = date('Y-m-d H:i:s');

// Insert into database
$stmt = $conn->prepare("INSERT INTO messages (project_name, sender, message, timestamp) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $project, $sender, $message, $timestamp);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>
