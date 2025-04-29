<?php
// Connect to database
include 'db_connect.php';

// Get project name from GET request
$project = $_GET['project'];

// Fetch messages
$stmt = $conn->prepare("SELECT sender, message, timestamp FROM messages WHERE project_name = ? ORDER BY timestamp ASC");
$stmt->bind_param("s", $project);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

// Output JSON
header('Content-Type: application/json');
echo json_encode($messages);

$stmt->close();
$conn->close();
?>
