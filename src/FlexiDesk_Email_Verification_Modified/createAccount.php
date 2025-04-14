<?php
session_start();
require 'vendor/autoload.php';
use Resend\Resend;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verification_code'])) {
        if ($_POST['verification_code'] == $_SESSION['verification_code']) {
            $user = $_SESSION['user_data'];
            $conn = new mysqli("localhost", "root", "", "flexidesk");
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            $stmt = $conn->prepare("INSERT INTO Users (name, email, password, role, jobTitle, department, bio) VALUES (?, ?, ?, 'Member', ?, ?, ?)");
            $fullName = $user['first-name'] . ' ' . $user['last-name'];
            $stmt->bind_param("ssssss", $fullName, $user['email'], $user['password'], $user['job-title'], $user['department'], $user['bio']);
            $stmt->execute();
            $stmt->close();
            $conn->close();
            echo json_encode(["success" => true]);
            exit;
        } else {
            echo json_encode(["error" => "Invalid code"]);
            exit;
        }
    }

    $code = random_int(100000, 999999);
    $_SESSION['verification_code'] = $code;
    $_SESSION['user_data'] = $_POST;

    $resend = Resend::client('your_resend_api_key_here');
    $resend->emails->send([
        'from' => 'FlexiDesk <noreply@yourdomain.com>',
        'to' => [$_POST['email']],
        'subject' => 'FlexiDesk Email Verification',
        'html' => "<p>Your verification code is <strong>{$code}</strong></p>"
    ]);

    echo json_encode(["code_sent" => true]);
}
?>