<?php
session_start();
include 'db.php';

if (!isset($_SESSION['userID'])) {
    die("User not logged in.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['taskID'], $_POST['projectID'], $_POST['status'], $_POST['taskName'], $_POST['taskType'],$_POST['taskDescription'], $_POST['priority'], $_POST['assignedTo'], $_POST['startDate'], $_POST['endDate'])) {
        die("Missing required fields.");
    }

    $taskID = $_POST['taskID'];
    $projectID = $_POST['projectID'];
    $taskName = $_POST['taskName'];
    $taskType = $_POST['taskType'];
    $taskDescription = trim($_POST['taskDescription']) ?: null;
    $startDate = $_POST['startDate'];
    $endDate = $_POST['endDate'];
    $priority = $_POST['priority'];
    $assignedTo = $_POST['assignedTo'];
    $status = $_POST['status'];
    $filePath = NULL;

    // Optional file upload
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
    // Prepare the SQL query for updating the task
    if ($filePath !== NULL) {
        $sql = "UPDATE Tasks SET taskName=?, taskType=?, description=?, priority=?, assignedTo=?, startDate=?, endDate=?, status=?, filePath=? WHERE taskID=?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("SQL prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sssssssssi", $taskName, $taskType, $taskDescription, $priority, $assignedTo, $startDate, $endDate, $status, $filePath, $taskID);
    } else {
        $sql = "UPDATE Tasks SET taskName=?, taskType=?, description=?, priority=?, assignedTo=?, startDate=?, endDate=?, status=? WHERE taskID=?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("SQL prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssssssssi", $taskName, $taskType, $taskDescription, $priority, $assignedTo, $startDate, $endDate, $status, $taskID);
    }

    // Execute the query and check for success
    if ($stmt->execute()) {
        header('Content-Type: application/json');
        echo json_encode(["status" => "success", "message" => "Task updated successfully!"]);
    } else {
        die("Error executing query: " . $stmt->error);
    }
} else {
    echo "Invalid request.";
}
?>
