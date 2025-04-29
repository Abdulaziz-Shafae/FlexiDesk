<?php
include 'db.php';

if (!isset($_GET['projectId']) || !is_numeric($_GET['projectId'])) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid project ID"]);
    exit;
}

$projectId = intval($_GET['projectId']);

$query = "SELECT * FROM messages WHERE project_id = $projectId ORDER BY created_at ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Database query failed"]);
    exit;
}

$messages = [];

while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

echo json_encode($messages);
?>
