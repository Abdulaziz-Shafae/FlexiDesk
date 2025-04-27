<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    echo json_encode(["success" => false, "message" => "User not logged in."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $taskID = intval($_GET['id']);
    $userID = $_SESSION['userID'];

    // Confirm user is a manager in the task's project
    $check = $conn->prepare("
        SELECT t.projectID FROM Tasks t
        JOIN user_projects up ON t.projectID = up.projectID
        WHERE t.taskID = ? AND up.userID = ? AND up.role = 'Manager'
    ");
    $check->bind_param("ii", $taskID, $userID);
    $check->execute();
    $result = $check->get_result();

    // Perform the deletion
    $delete = $conn->prepare("DELETE FROM Tasks WHERE taskID = ?");
    $delete->bind_param("i", $taskID);
    $delete->execute();

    echo json_encode(["status" => "success", "message" => "Task deleted successfully."]);
} else {
    echo json_encode(["status" => "failed", "message" => "Invalid request."]);
}
?>
