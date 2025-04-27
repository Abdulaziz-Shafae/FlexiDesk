<?php
session_start();
include('db.php');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$startDate = $data['startDate'] ?? '';
$endDate = $data['endDate'] ?? '';
$teamMembers = $data['teamMembers'] ?? []; // New line
$userID = $_SESSION['userID'];

if (!$title || !$description || !$startDate || !$endDate) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$sql = "INSERT INTO Projects (title, description, startDate, endDate) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $title, $description, $startDate, $endDate);

if ($stmt->execute()) {
    $projectID = $stmt->insert_id;

    // Add manager (current user)
    $role = 'Manager';
    $link = $conn->prepare("INSERT INTO user_projects (userID, projectID, role) VALUES (?, ?, ?)");
    $link->bind_param("iis", $userID, $projectID, $role);
    $link->execute();

    // Add team members (excluding manager)
    $role = 'Member';
    foreach ($teamMembers as $memberID) {
        if (is_numeric($memberID) && $memberID != $userID) {
            $link = $conn->prepare("INSERT INTO user_projects (userID, projectID, role) VALUES (?, ?, ?)");
            $link->bind_param("iis", $memberID, $projectID, $role);
            $link->execute();
        }
    }

    echo json_encode(['status' => 'success', 'projectID' => $projectID]);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
?>
