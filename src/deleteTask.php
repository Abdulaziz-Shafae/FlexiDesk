<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID'])) {
    die("User not logged in.");
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $taskID = $_GET['id'];

    // Confirm task ownership or manager role
    $check = $conn->prepare("
        SELECT t.projectID FROM Tasks t
        JOIN user_projects up ON t.projectID = up.projectID
        WHERE t.taskID = ? AND up.userID = ? AND up.role = 'Manager'
    ");
    $check->bind_param("ii", $taskID, $_SESSION['userID']);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        die("You are not authorized to delete this task.");
    }

    // Proceed to delete
    $delete = $conn->prepare("DELETE FROM Tasks WHERE taskID = ?");
    $delete->bind_param("i", $taskID);
    $delete->execute();

    echo "Task deleted successfully.";
} else {
    echo "Invalid request.";
}
?>
