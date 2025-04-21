<?php
session_start();
include('db.php');

$data = json_decode(file_get_contents("php://input"), true);
$projectID = $data['projectID'] ?? null;

if (!$projectID) {
  echo json_encode(['error' => 'No project ID provided']);
  exit;
}

$stmt = $conn->prepare("SELECT startDate, endDate FROM Projects WHERE projectID = ?");
$stmt->bind_param("i", $projectID);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  echo json_encode([
    'startDate' => $row['startDate'],
    'endDate' => $row['endDate']
  ]);
} else {
  echo json_encode(['error' => 'Project not found']);
}
?>
