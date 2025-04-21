<?php
session_start();
include('db.php');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userID = $_SESSION['userID'];

$sql = "SELECT name, email, jobTitle, department, bio, profileImage FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    $user['profileImage'] = !empty($user['profileImage']) && file_exists("uploads/" . $user['profileImage'])
        ? "uploads/" . $user['profileImage']
        : "photos/profile-default-photo.jpeg";


    echo json_encode($user);
} else {
    echo json_encode(['error' => 'User not found']);
}
?>
