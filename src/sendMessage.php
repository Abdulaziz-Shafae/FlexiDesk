<?php
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);

// Validate inputs
if (!isset($data['projectId'], $data['userName'], $data['message'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
    exit;
}

$projectId = intval($data['projectId']);
$userName = trim($data['userName']);
$message = trim($data['message']);

$stmt = $conn->prepare("INSERT INTO messages (project_id, user_name, message) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $projectId, $userName, $message);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
