<?php
session_start();

if (!isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
  echo "You must verify your email first.";
  exit;
}
unset($_SESSION['verified']);

include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['first-name']);
    $lastName = trim($_POST['last-name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];
    $jobTitle = trim($_POST['job-title']);
    $department = trim($_POST['department']);
    $bio = trim($_POST['bio']);

    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit;
    }

    $checkQuery = "SELECT userID FROM Users WHERE email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        echo "This email is already registered!";
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $fullName = $firstName . ' ' . $lastName;

    $insertQuery = "INSERT INTO Users (name, email, password, jobTitle, department, bio, two_factor_enabled)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $twoFactorEnabled = false; // Default value is false
    $stmt->bind_param("ssssssi", $fullName, $email, $hashedPassword, $jobTitle, $department, $bio, $twoFactorEnabled);

    if ($stmt->execute()) {
        $_SESSION['loggedin'] = true;
        $_SESSION['userID'] = $stmt->insert_id;
        $_SESSION['username'] = $fullName;

        header('Location: homePage.php');
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
