<?php
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

$projectId = $data['projectId'];
$userName = $data['userName'];
$message = $data['message'];

$stmt = $conn->prepare("INSERT INTO project_messages (project_id, user_name, message) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $projectId, $userName, $message);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>