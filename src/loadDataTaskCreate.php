<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID'])) {
    http_response_code(403);
    echo json_encode(["error" => "User not logged in."]);
    exit;
}

$userID = $_SESSION['userID'];

if (isset($_GET['projects'])) {
    // Fetch only projects where user is a manager
    $stmt = $conn->prepare("
        SELECT p.projectID, p.title, p.startDate, p.endDate 
        FROM Projects p 
        JOIN user_projects up ON p.projectID = up.projectID 
        WHERE up.userID = ? AND up.role = 'Manager'
    ");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    $projects = [];
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
    echo json_encode($projects);
    exit;
}

if (isset($_GET['members']) && isset($_GET['projectID'])) {
    $projectID = intval($_GET['projectID']);

    $stmt = $conn->prepare("
        SELECT u.userID, u.name 
        FROM Users u 
        JOIN user_projects up ON u.userID = up.userID 
        WHERE up.projectID = ? AND up.role = 'Member'
    ");
    $stmt->bind_param("i", $projectID);
    $stmt->execute();
    $result = $stmt->get_result();

    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
    echo json_encode($members);
    exit;
}

http_response_code(400);
echo json_encode(["error" => "Invalid request"]);
exit;
?>
