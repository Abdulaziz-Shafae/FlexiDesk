<?php
header('Content-Type: application/json');

session_start();
require __DIR__ . '/vendor/autoload.php';
include('db.php');

use GuzzleHttp\Client;


$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$purpose = $data['purpose'] ?? 'signup'; // default = signup
$code = rand(100000, 999999);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
  exit;
}


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


$_SESSION['verification'] = [
  'email' => $email,
  'code' => $code,
  'timestamp' => time()
];

$client = new Client();

try {
  $client->post('https://api.resend.com/emails', [
    'headers' => [
      'Authorization' => 'Bearer re_YfWDt5qe_PC7L5WLKgBn9TPKgupH1HzU9',
      'Content-Type' => 'application/json'
    ],
    'json' => [
      'from' => 'FlexiDesk <noreply@flexidesk.site>', // Updated from your verified domain
      'to' => $email, // Send to the user's email address
      'subject' => 'Email Verification Code',
      'html' => "<p>Your verification code is <strong>$code</strong></p>"
    ]
  ]);

  echo json_encode(['status' => 'success', 'message' => 'Verification code sent.']);
} catch (Exception $e) {
  // Add detailed error logging
  file_put_contents('email_error.log', date('Y-m-d H:i:s') . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n", FILE_APPEND);
  
  echo json_encode([
    'status' => 'error',
    'message' => 'Failed to send email.',
    'debug' => $e->getMessage()
  ]);
}