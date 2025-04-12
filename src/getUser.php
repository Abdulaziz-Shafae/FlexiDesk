<?php
session_start();
include('db.php');

// Must be logged in
if (!isset($_SESSION['userID'])) {
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

$userID = $_SESSION['userID'];

$sql = "SELECT name, email, jobTitle, department, bio FROM users WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(['error' => 'User not found']);
}
?>
