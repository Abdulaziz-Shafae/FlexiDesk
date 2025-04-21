<?php
session_start();
header('Content-Type: application/json');
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

if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    $sql = "UPDATE users SET profileImage = ? WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $newFileName, $userID);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'path' => $targetPath]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
}
?>
