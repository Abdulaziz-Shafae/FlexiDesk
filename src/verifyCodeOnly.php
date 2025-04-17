<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$userCode = $data['code'] ?? '';

if (!isset($_SESSION['verification'])) {
    echo json_encode(['status' => 'error', 'message' => 'Verification session not found.']);
    exit;
}

$storedCode = $_SESSION['verification']['code'];
$sentTime = $_SESSION['verification']['timestamp'];

if ((string)$storedCode === (string)$userCode) {
    $_SESSION['verified'] = true;
    unset($_SESSION['verification']);
    echo json_encode(['status' => 'success', 'message' => 'Code verified successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Incorrect code.']);
}
