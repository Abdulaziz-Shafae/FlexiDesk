<?php
include 'db.php';
session_start();
header('Content-Type: application/json');

$userId = $_SESSION['userID']; 

$stmt = $conn->prepare("
  SELECT p.projectID AS id, p.title
  FROM Projects p
  JOIN user_projects up ON up.projectID = p.projectID
  WHERE up.userID = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();

$result = $stmt->get_result();
$projects = [];

while ($row = $result->fetch_assoc()) {
    $projects[] = $row;
}

echo json_encode($projects);
$stmt->close();
$conn->close();
?>