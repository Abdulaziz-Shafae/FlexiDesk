<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_POST['project_id']) || !isset($_POST['message'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing parameters"]);
    exit();
}

if (!isset($_SESSION['userID'])) {
    http_response_code(401);
    echo json_encode(["error" => "User not logged in"]);
    exit();
}

$user_id = intval($_SESSION['userID']);
$project_id = intval($_POST['project_id']);
$message = trim($_POST['message']);

if (empty($message)) {
    http_response_code(400);
    echo json_encode(["error" => "Message cannot be empty"]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO Messages (project_id, user_id, message) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $project_id, $user_id, $message);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => [
            "project_id" => $project_id,
            "user_id" => $user_id,
            "message" => $message,
            "timestamp" => date("Y-m-d H:i")
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to send message"]);
}
?>