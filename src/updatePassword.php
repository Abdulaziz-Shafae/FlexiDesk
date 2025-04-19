<?php
header("Content-Type: application/json");
include('db.php');

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$newPassword = $data['newPassword'] ?? '';
$currentPassword = $data['currentPassword'] ?? null;
$mode = $data['mode'] ?? 'profile'; // default is profile

if (!$email || !$newPassword) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
}

if ($mode === 'profile') {
    if (!$currentPassword) {
        echo json_encode(['status' => 'error', 'message' => 'Current password is required.']);
        exit;
    }

    // Validate current password
    $stmt = $conn->prepare("SELECT password FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email not found.']);
        exit;
    }

    $row = $result->fetch_assoc();
    if (!password_verify($currentPassword, $row['password'])) {
        echo json_encode(['status' => 'error', 'message' => 'Incorrect current password.']);
        exit;
    }
}

// Now update password
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
$updateStmt = $conn->prepare("UPDATE Users SET password = ? WHERE email = ?");
$updateStmt->bind_param("ss", $hashedPassword, $email);

if ($updateStmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update password.']);
}
?>
