<?php
include('db.php');

$data = json_decode(file_get_contents('php://input'), true);
$department = trim($data['department']);

if ($department) {
    $sql = "INSERT IGNORE INTO Departments (name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $department);
    $stmt->execute();
}
echo json_encode(['status' => 'ok']);
?>
