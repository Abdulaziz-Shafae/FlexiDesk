<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_GET['projectId']) || !is_numeric($_GET['projectId'])) {
    echo json_encode([]); // return empty array instead of nothing
    exit;
}

$projectId = intval($_GET['projectId']);

$query = "SELECT * FROM messages WHERE project_id = $projectId ORDER BY timestamp ASC";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode([]); // return empty array on query failure
    exit;
}

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = $row;
}

echo json_encode($messages);

//getMessages.php
?>
