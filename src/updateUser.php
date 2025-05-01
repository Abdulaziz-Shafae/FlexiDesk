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
$jobTitle = $data['jobTitle'] ?? '';
$department = trim($data['department'] ?? '');
$bio = $data['bio'] ?? '';

// Check if department exists in MetaValues
if (!empty($department)) {
    $checkDep = "SELECT id FROM MetaValues WHERE type = 'Department' AND value = ?";
    $stmt = $conn->prepare($checkDep);
    $stmt->bind_param("s", $department);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Insert department if not found
        $insertDep = "INSERT INTO MetaValues (type, value) VALUES ('Department', ?)";
        $stmt = $conn->prepare($insertDep);
        $stmt->bind_param("s", $department);
        $stmt->execute();
    }
}

// Check if job title exists in MetaValues
if (!empty($jobTitle)) {
    $checkJobTitle = "SELECT id FROM MetaValues WHERE type = 'JobTitle' AND value = ?";
    $stmt = $conn->prepare($checkJobTitle);
    $stmt->bind_param("s", $jobTitle);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        // Insert job title if not found
        $insertJobTitle = "INSERT INTO MetaValues (type, value) VALUES ('JobTitle', ?)";
        $stmt = $conn->prepare($insertJobTitle);
        $stmt->bind_param("s", $jobTitle);
        $stmt->execute();
    }
}

// Now update the user's information
$sql = "UPDATE users SET name = ?, jobTitle = ?, department = ?, bio = ? WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssi", $name, $jobTitle, $department, $bio, $userID);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
?>
