<?php
session_start();
include 'db.php'; // Ensure the DB connection file exists

if (!isset($_SESSION['userID'])) {
    die("User not logged in.");
}

$userID = $_SESSION['userID'];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $checkManager->bind_param("ii", $userID, $projectID);
    $checkManager->execute();
    $result = $checkManager->get_result();

    if ($result->num_rows === 0) {
        die("You are not a manager in this project.");
    }

    // Handle file upload if exists
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

    // Fetch project dates for validation
    $getDates = $conn->prepare("SELECT startDate, endDate FROM Projects WHERE projectID = ?");
    $getDates->bind_param("i", $projectID);
    $getDates->execute();
    $dateResult = $getDates->get_result();

    if ($dateResult->num_rows === 0) {
        die("Project not found.");
    }

    $projectDates = $dateResult->fetch_assoc();
    $projectStart = $projectDates['startDate'];
    $projectEnd = $projectDates['endDate'];

    if ($startDate < $projectStart || $endDate > $projectEnd || $startDate > $endDate) {
        die("Invalid task date: must be between project start and end dates.");
    }

    // Insert task
    $stmt = $conn->prepare("INSERT INTO Tasks (projectID, assignedTo, taskName, description, priority, startDate, endDate, filePath, taskType, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("iisssssss", $projectID, $assignedTo, $taskName, $taskDescription, $priority, $startDate, $endDate, $filePath, $taskType);    
    $stmt->execute();

    echo "Task created successfully!";
} else {
    echo "Invalid request.";
}
?>
