<?php
session_start();
require 'vendor/autoload.php';

use Resend\Resend;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// STEP 1: Send Code
if ($data['step'] === 'send_code') {
    $user = $data['user'];
    $_SESSION['pending_user'] = $user;
    
    $code = rand(100000, 999999);
    $_SESSION['verification_code'] = $code;

    $resend = Resend::client('YOUR_RESEND_API_KEY_HERE');
    $resend->emails->send([
        'from' => 'FlexiDesk@hotmil.com',
        'to' => $user['email'],
        'subject' => 'Verify your email',
        'html' => "<h1>Your code: $code</h1>"
    ]);

    echo json_encode(["status" => "ok"]);
    exit;
}

// STEP 2: Verify Code
if ($data['step'] === 'verify_code') {
    $entered = $data['code'];
    if ($entered == $_SESSION['verification_code']) {
        $user = $_SESSION['pending_user'];
        include 'db.php';

        $hashedPassword = password_hash($user['password'], PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, jobTitle, department, bio) VALUES (?, ?, ?, 'Member', ?, ?, ?)");
        $stmt->bind_param("ssssss", $user['name'], $user['email'], $hashedPassword, $user['jobTitle'], $user['department'], $user['bio']);

        if ($stmt->execute()) {
            unset($_SESSION['verification_code']);
            unset($_SESSION['pending_user']);
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database insert failed"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Incorrect code"]);
    }
}
?>
