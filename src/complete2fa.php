<?php
session_start();
include('db.php');

$data = json_decode(file_get_contents("php://input"), true);
$inputCode = $data['code'] ?? '';

if (!isset($_SESSION['verification'])) {
  echo json_encode(['status' => 'error', 'message' => 'No verification session found.']);
  exit;
}

$stored = $_SESSION['verification'];
$storedCode = $stored['code'];
$email = $stored['email'];
$timestamp = $stored['timestamp'];

// Check for expiry (5 minutes)
if (time() - $timestamp > 300) {
  unset($_SESSION['verification']);
  echo json_encode(['status' => 'error', 'message' => 'Verification code expired.']);
  exit;
}

// Check if code matches
if ($inputCode !== strval($storedCode)) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid code.']);
  exit;
}

// Lookup user
$stmt = $conn->prepare("SELECT userID, name FROM Users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo json_encode(['status' => 'error', 'message' => 'User not found.']);
  exit;
}

$user = $result->fetch_assoc();
$_SESSION['loggedin'] = true;
$_SESSION['userID'] = $user['userID'];
$_SESSION['username'] = $user['name'];

unset($_SESSION['verification']);
echo json_encode(['status' => 'success']);
?>
