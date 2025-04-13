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
$department = trim($data['department'] ?? '');
$bio = $data['bio'] ?? '';

if (!empty($department)) {
    $checkDep = "SELECT departmentID FROM Departments WHERE name = ?";
    $stmt = $conn->prepare($checkDep);
    $stmt->bind_param("s", $department);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $insertDep = "INSERT INTO Departments (name) VALUES (?)";
        $stmt = $conn->prepare($insertDep);
        $stmt->bind_param("s", $department);
        $stmt->execute();
    }
}

$sql = "UPDATE users SET name = ?, email = ?, jobTitle = ?, department = ?, bio = ? WHERE userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $name, $email, $jobTitle, $department, $bio, $userID);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => $stmt->error]);
}
?>
