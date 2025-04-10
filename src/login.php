<?php
session_start();  // Start the session

// Example login check (replace with actual login logic)
$username = $_POST['username'];
$password = $_POST['password'];

// Query the database to validate the user
// Assuming you have a users table with columns 'username' and 'password'
$sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // User is found, set session variable
    $_SESSION['loggedin'] = true;
    $_SESSION['username'] = $username;  // Store username in session
    header('Location: homePage.html');  // Redirect to home page or dashboard
} else {
    echo "Invalid username or password.";
}
?>
