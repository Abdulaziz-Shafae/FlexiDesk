<?php
header('Content-Type: application/json');

session_start();
require __DIR__ . '/vendor/autoload.php';
include('db.php');

use Resend\Resend;

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$purpose = $data['purpose'] ?? 'signup'; // default = signup
$code = rand(100000, 999999);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
  exit;
}

// Check email based on purpose
if ($purpose === 'reset') {
  $stmt = $conn->prepare("SELECT userID FROM Users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email not found']);
    exit;
  }
} elseif ($purpose === 'signup') {
  $stmt = $conn->prepare("SELECT userID FROM Users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
    exit;
  }
} elseif ($purpose === '2fa') {
  // Only check if email exists (same as reset)
  $stmt = $conn->prepare("SELECT userID FROM Users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email not found']);
    exit;
  }
}

// Store verification data in session
$_SESSION['verification'] = [
  'email' => $email,
  'code' => $code,
  'timestamp' => time()
];

// Initialize Resend client with your API key
$resend = Resend::client('re_CLMUqmKb_KLFyyPNgGpxhXs62nuJiaWSQ');

try {
  // Send email using the new Resend API structure
  $response = $resend->emails->send([
    'from' => 'FlexiDesk <onboarding@resend.dev>',
    'to' => $email, // Use the actual user email in production
    // 'to' => 'amm1r.abdu@gmail.com', // For testing
    'subject' => 'FlexiDesk Verification Code',
    'html' => "<p>Your verification code is <strong>$code</strong></p>"
  ]);

  echo json_encode(['status' => 'success', 'message' => 'Verification code sent.']);
} catch (Exception $e) {
  echo json_encode([
    'status' => 'error',
    'message' => 'Failed to send email.',
    'debug' => $e->getMessage()
  ]);
}