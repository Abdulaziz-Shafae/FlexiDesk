<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require __DIR__ . '/vendor/autoload.php';
include('db.php');
if (!isset($conn) || !$conn) {
  echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
  exit;
}

use GuzzleHttp\Client;

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$purpose = $data['purpose'] ?? 'signup'; // default = signup
$code = rand(100000, 999999);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
  exit;
}


if ($purpose === 'reset') {
  $stmt = $conn->prepare("SELECT id FROM Users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email not found']);
    exit;
  }
} elseif ($purpose === 'signup') {
  $stmt = $conn->prepare("SELECT id FROM Users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($result->num_rows > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Email already registered']);
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
      'from' => 'FlexiDesk <onboarding@resend.dev>',
      'to' => [$email],
      'subject' => 'Email Verification Code',
      'html' => "<p>Your verification code is <strong>$code</strong></p>"
    ]
  ]);

  echo json_encode(['status' => 'success', 'message' => 'Verification code sent.']);
} catch (Exception $e) {
  echo json_encode([
    'status' => 'error',
    'message' => 'Failed to send email.',
    'debug' => $e->getMessage()
  ]);
}
