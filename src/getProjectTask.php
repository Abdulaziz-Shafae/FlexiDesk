<?php
session_start();
header('Content-Type: application/json');
include 'db.php';

if (!isset($_SESSION['userID'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userID = $_SESSION['userID'];
$data = json_decode(file_get_contents('php://input'), true);

$projectID = $data['projectID'] ?? null;
$priority = $data['priority'] ?? '';
$status = $data['status'] ?? '';
$onlyMine = $data['onlyMine'] ?? false;

if (!$projectID) {
    echo json_encode(['error' => 'Missing project ID']);
    exit;
}

// Base query
$query = "
    SELECT t.taskID, t.taskName, t.description, t.deadline, t.priority, t.status, t.startDate,
           p.title AS projectTitle, u.name AS assignedToName
    FROM Tasks t
    JOIN Projects p ON t.projectID = p.projectID
    LEFT JOIN Users u ON t.assignedTo = u.userID
    WHERE t.projectID = ?
";

$params = [$projectID];
$types = "i";

// Apply filters
if ($priority !== '') {
    $query .= " AND t.priority = ?";
    $types .= "s";
    $params[] = $priority;
}

if ($status !== '') {
    $query .= " AND t.status = ?";
    $types .= "s";
    $params[] = $status;
}

if ($onlyMine) {
    $query .= " AND t.assignedTo = ?";
    $types .= "i";
    $params[] = $userID;
}

$query .= " ORDER BY t.deadline ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

echo json_encode($tasks);
