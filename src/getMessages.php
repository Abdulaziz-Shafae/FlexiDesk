<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_GET['project_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing project_id"]);
    exit();
}

if (!isset($_SESSION['userID'])) {
    http_response_code(401);
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$project_id = intval($_GET['project_id']);
$current_user_id = intval($_SESSION['userID']);

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
        "timestamp" => date("Y-m-d H:i", strtotime($row['timestamp'])),
        "username" => $row['username'],
        "is_self" => $row['user_id'] == $current_user_id
    ];
}

// clear output buffer just in case
if (ob_get_length()) ob_end_clean();

echo json_encode($messages);
$stmt->close();
$conn->close();
