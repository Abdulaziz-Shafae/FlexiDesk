<?php
session_start();  // Start the session to manage user login

// Include database connection file
include('db.php');  // Assuming db.php contains the connection setup

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstName = $_POST['first-name'];
    $lastName = $_POST['last-name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

    // Validate password confirmation
    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit;
    }

    // Check if the email is already in use
    $sql = "SELECT * FROM Users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);  // Bind the email parameter to prevent SQL injection
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "This email is already registered!";
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the Users table
    $sql = "INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, 'Member')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $firstName, $email, $hashedPassword); // Bind parameters
    if ($stmt->execute()) {
        // Redirect to login page after successful account creation
        $_SESSION['loggedin'] = true;  // Log the user in immediately
        $_SESSION['username'] = $firstName;
        header('Location: homePage.php');  // Redirect to the home page after successful registration
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<?php
session_start();  // Start the session to manage user login

// Include database connection file
include('db.php');  // Assuming db.php contains the connection setup

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstName = $_POST['first-name'];
    $lastName = $_POST['last-name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

    // Validate password confirmation
    if ($password !== $confirmPassword) {
        echo "Passwords do not match!";
        exit;
    }

    // Check if the email is already in use
    $sql = "SELECT * FROM Users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);  // Bind the email parameter to prevent SQL injection
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "This email is already registered!";
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the Users table

    $sql = "INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, 'Member')";
    $stmt = $conn->prepare($sql);
    $fullName = $firstName . ' ' . $lastName;
    // Then bind $fullName in the insert query
    $stmt->bind_param("sss", $fullName, $email, $hashedPassword); // Bind parameters
    if ($stmt->execute()) {
        // Redirect to login page after successful account creation
        $_SESSION['loggedin'] = true;  // Log the user in immediately
        $_SESSION['username'] = $firstName;
        header('Location: homePage.php');  // Redirect to the home page after successful registration
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
