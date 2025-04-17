<?php
session_start();
require __DIR__ . '/vendor/autoload.php';
use GuzzleHttp\Client;

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
  exit;
}

$code = rand(100000, 999999);

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
