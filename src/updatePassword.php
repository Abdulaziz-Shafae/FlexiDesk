<?php
header("Content-Type: application/json");
session_start();
include('db.php');

$data = json_decode(file_get_contents("php://input"), true);
$newPassword = trim($data['newPassword'] ?? '');
$currentPassword = $data['currentPassword'] ?? null;
$mode = $data['mode'] ?? 'profile';
$twoFactorEnabled = isset($data['twoFactorEnabled']) ? (bool)$data['twoFactorEnabled'] : null;

$userID = null;

if ($mode === 'profile') {
    if (!isset($_SESSION['userID'])) {
        echo json_encode(['status' => 'error', 'message' => 'Not logged in.']);
        exit;
    }

    $userID = $_SESSION['userID'];

    // If password update is requested, validate it
    if ($newPassword !== '') {
        if (!$currentPassword) {
            echo json_encode(['status' => 'error', 'message' => 'Current password is required.']);
            exit;
        }

        // Fetch current password
        $stmt = $conn->prepare("SELECT password FROM Users WHERE userID = ?");
        $stmt->bind_param("i", $userID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'User not found.']);
            exit;
        }

        $row = $result->fetch_assoc();
        if (!password_verify($currentPassword, $row['password'])) {
            echo json_encode(['status' => 'error', 'message' => 'Incorrect current password.']);
            exit;
        }
    }
} elseif ($mode === 'reset') {
    $email = $data['email'] ?? '';
    if (!$email) {
        echo json_encode(['status' => 'error', 'message' => 'Email is required.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT userID FROM Users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email not found.']);
        exit;
    }

    $userID = $result->fetch_assoc()['userID'];
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid mode.']);
    exit;
}

// Build update SQL
if ($newPassword !== '') {
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    if ($twoFactorEnabled !== null) {
        $stmt = $conn->prepare("UPDATE Users SET password = ?, two_factor_enabled = ? WHERE userID = ?");
        $stmt->bind_param("sii", $hashedPassword, $twoFactorEnabled, $userID);
    } else {
        $stmt = $conn->prepare("UPDATE Users SET password = ? WHERE userID = ?");
        $stmt->bind_param("si", $hashedPassword, $userID);
    }
} else {
    // Only update 2FA
    if ($twoFactorEnabled !== null) {
        $stmt = $conn->prepare("UPDATE Users SET two_factor_enabled = ? WHERE userID = ?");
        $stmt->bind_param("ii", $twoFactorEnabled, $userID);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nothing to update.']);
        exit;
    }
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update.']);
}
?>
