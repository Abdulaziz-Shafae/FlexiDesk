<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$query = $data['query'] ?? '';

$stmt = $conn->prepare("SELECT userID, name FROM Users WHERE name LIKE CONCAT('%', ?, '%') LIMIT 10");
$stmt->bind_param("s", $query);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = ['id' => $row['userID'], 'name' => $row['name']];
}

echo json_encode($users);
?>
