<?php
session_start();
header('Content-Type: application/json');
include('db.php');

$data = json_decode(file_get_contents("php://input"), true);
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['status' => 'error', 'message' => 'Missing credentials.']);
    exit;
}

$sql = "SELECT userID, name, password, two_factor_enabled FROM Users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        if (!empty($user['two_factor_enabled'])) {
            $_SESSION['pending_email'] = $email;
            echo json_encode(['status' => '2fa_required']);
            exit;
        }

        // No 2FA required
        $_SESSION['loggedin'] = true;
        $_SESSION['userID'] = $user['userID'];
        $_SESSION['username'] = $user['name'];

        echo json_encode(['status' => 'success']);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
?>
