<?php
session_start();
include('db.php');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$userID = $_SESSION['userID'];

if (!isset($_FILES['profilePic']) || $_FILES['profilePic']['error'] !== 0) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['profilePic'];
$targetDir = "uploads/";
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$newFileName = "profile_" . $userID . "." . $extension;
$targetPath = $targetDir . $newFileName;

// Create uploads folder if it doesn't exist
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Delete any existing profile image with other extensions
$extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
foreach ($extensions as $ext) {
    $oldFile = $targetDir . "profile_" . $userID . "." . $ext;
    if (file_exists($oldFile)) {
        unlink($oldFile);
    }
}

// Move new uploaded file
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    $sql = "UPDATE users SET profileImage = ? WHERE userID = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("si", $newFileName, $userID);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'path' => $targetPath]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update DB']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
}
?>
