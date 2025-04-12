<?php
session_start();
include('db.php');

if (!isset($_SESSION['userID'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$userID = $_SESSION['userID'];

$name = $data['name'] ?? '';
$email = $data['email'] ?? '';
$jobTitle = $data['jobTitle'] ?? '';
$department = $data['department'] ?? '';
$bio = $data['bio'] ?? '';

$sql = "UPDATE users SET name = ?, email = ?, jobTitle = ?, department = ?, bio = ? WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $name, $email, $jobTitle, $department, $bio, $userID);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
?>
