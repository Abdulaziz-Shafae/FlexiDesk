<?php
session_start();
header('Content-Type: application/json');

include 'db.php';

if (!isset($_SESSION['userID'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

$userID = $_SESSION['userID'];
$priority = $_GET['priority'] ?? '';
$status = $_GET['status'] ?? '';

// Base query: fetch tasks assigned to the logged-in user
$query = "
    SELECT t.taskName, t.description, t.deadline, t.priority, t.status, p.title AS projectTitle
    FROM Tasks t
    JOIN Projects p ON t.projectID = p.projectID
    WHERE t.assignedTo = ?
";

// Dynamically add filtering conditions if set
$params = [$userID];
$types = "i";

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

$query .= " ORDER BY t.deadline ASC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$tasks = [];
while ($row = $result->fetch_assoc()) {
    $tasks[] = $row;
}

echo json_encode($tasks);
