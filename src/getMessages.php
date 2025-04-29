<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_GET['project_id']) || !isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing parameters"]);
    exit();
}

$project_id = intval($_GET['project_id']);
$current_user_id = intval($_GET['user_id']);

$stmt = $conn->prepare("
    SELECT m.message, m.user_id, m.timestamp, u.name AS username
    FROM Messages m
    JOIN Users u ON m.user_id = u.userID
    WHERE m.project_id = ?
    ORDER BY m.timestamp ASC
");

if (!$stmt) {
    echo json_encode(["error" => "Database error: " . $conn->error]);
    exit();
}

$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];

while ($row = $result->fetch_assoc()) {
    $messages[] = [
        "message" => $row['message'],
        "user_id" => $row['user_id'],
        "timestamp" => $row['timestamp'],
        "username" => $row['username'],
        "is_self" => $row['user_id'] == $current_user_id
    ];
}

echo json_encode($messages);
$stmt->close();
$conn->close();
?>
