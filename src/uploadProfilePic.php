<?php
session_start();
include('db.php');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$userID = $_SESSION['userID'];

if (!isset($_FILES['profilePic'])) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['profilePic'];
$targetDir = "uploads/";
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = "profile_" . $userID . "." . strtolower($extension);
$targetPath = $targetDir . $newFileName;

// Create uploads folder if not exists
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Move file
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Save filename in DB
    $sql = "UPDATE users SET profilePicture = ? WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newFileName, $userID);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'path' => $targetPath]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
}
?>
