<?php
include 'db.php';

$projectId = $_GET['projectId'];

$stmt = $conn->prepare("SELECT * FROM project_messages WHERE project_id = ? ORDER BY timestamp ASC");
$stmt->bind_param("i", $projectId);
$stmt->execute();

$result = $stmt->get_result();
$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode($messages);

$stmt->close();
$conn->close();
?>