<?php
session_start();  // Start the session to manage login state

// Include the database connection
include('db.php'); 

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare the query to find the user by email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);  // Bind the email parameter to prevent SQL injection
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // User found, fetch the user data
        $user = $result->fetch_assoc();

        // Verify the password using password_verify()
        if (password_verify($password, $user['password'])) {
            // Password is correct, start a session and redirect to the home page
            $_SESSION['loggedin'] = true;
            $_SESSION['userID'] = $user['userID'];  // Store user ID in session
            $_SESSION['username'] = $user['name']; // Store username in session
            header('Location: homePage.php');  // Redirect to home page after successful login
            exit();
        }
    }

    // On fail
    header('Location: login.html?error=1');
    exit();

}
?>
