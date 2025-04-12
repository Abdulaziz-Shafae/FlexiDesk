<?php
include('db.php');  // Assuming db.php contains the connection setup

// Get the email from the POST data
$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'];

// Query the database to check if the email exists
$sql = "SELECT * FROM Users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);  // Bind the email parameter
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Email already exists
    echo json_encode(['exists' => true]);
} else {
    // Email is available
    echo json_encode(['exists' => false]);
}
?>
