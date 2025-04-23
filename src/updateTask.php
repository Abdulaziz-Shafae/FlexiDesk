<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID'])) {
    die("User not logged in.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskID = $_POST['taskID'];
    $projectID = $_POST['projectID'];
    $taskName = $_POST['taskName'];
    $taskType = $_POST['taskType'];
    $taskDescription = $_POST['taskDescription'];
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $priority = $_POST['priority'];
    $assignedTo = $_POST['assignedTo'];
    $filePath = NULL;

    // Check if the user is a manager in this project
    $checkManager = $conn->prepare("SELECT * FROM user_projects WHERE userID = ? AND projectID = ? AND role = 'Manager'");
    $checkManager->bind_param("ii", $_SESSION['userID'], $projectID);
    $checkManager->execute();
    if ($checkManager->get_result()->num_rows === 0) {
        die("You are not authorized to update this task.");
    }

    // Handle optional file upload
    if (isset($_FILES['taskFile']) && $_FILES['taskFile']['error'] == 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = basename($_FILES['taskFile']['name']);
        $targetPath = $uploadDir . time() . '_' . $fileName;

        if (move_uploaded_file($_FILES['taskFile']['tmp_name'], $targetPath)) {
            $filePath = $targetPath;
        } else {
            die("File upload failed.");
        }
    }

    // Prepare update query
    if ($filePath !== NULL) {
        $sql = "UPDATE Tasks SET taskName=?, taskType=?, description=?, priority=?, assignedTo=?, startDate=?, endDate=?, filePath=? WHERE taskID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $taskName, $taskType, $taskDescription, $priority, $assignedTo, $startDate, $endDate, $filePath, $taskID);
    } else {
        $sql = "UPDATE Tasks SET taskName=?, taskType=?, description=?, priority=?, assignedTo=?, startDate=?, endDate=? WHERE taskID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", $taskName, $taskType, $taskDescription, $priority, $assignedTo, $startDate, $endDate, $taskID);
    }

    $stmt->execute();

    echo "Task updated successfully!";
} else {
    echo "Invalid request.";
}
?>
