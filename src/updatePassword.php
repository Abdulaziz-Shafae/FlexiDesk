<?php
header("Content-Type: application/json");
include('db.php');

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$currentPassword = $data['currentPassword'] ?? '';
$newPassword = $data['newPassword'] ?? '';

if (!$email || !$currentPassword || !$newPassword) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
}

// ✅ 1. Get current password hash
$stmt = $conn->prepare("SELECT password FROM Users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email not found.']);
    exit;
}

$row = $result->fetch_assoc();
$storedHash = $row['password'];

// ✅ 2. Check if current password is correct
if (!password_verify($currentPassword, $storedHash)) {
    echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
    exit;
}

// ✅ 3. Hash and update new password
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
$update = $conn->prepare("UPDATE Users SET password = ? WHERE email = ?");
$update->bind_param("ss", $newHash, $email);

if ($update->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed.']);
}

$conn->close();
?>
