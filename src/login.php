<?php
session_start();  // Start the session to manage login state

// Include the database connection file
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
        } else {
            // Invalid password
            echo "Invalid username or password.";
        }
    } else {
        // No user found with the given email
        echo "Invalid username or password.";
    }
}
?>

<!-- HTML for Login Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <link rel="stylesheet" href="navbarStyle.css" />
    <script src="script.js" defer></script>

    <style>
      /* General Styles */
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Arial', sans-serif;
      }

      /* Light Theme Variables */
      :root {
        --bg-color: #f5f5f5;
        --text-color: #2c3e50;
        --header-bg: #2c3e50;
        --card-bg: white;
        --input-border: #515151;
        --input-bg: transparent;
      }

      /* Dark Theme Variables */
      .dark-mode {
        --bg-color: #181818;
        --text-color: #f1f1f1;
        --header-bg: #1a1a1a;
        --card-bg: #282828;
        --input-border: #f1f1f1;
        --input-bg: #333;
      }

      body {
        background-color: var(--bg-color);
        color: var(--text-color);
        transition:
          background-color 0.3s ease,
          color 0.3s ease;
      }

      .container {
        margin-top: 150px;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 100vh;
      }

      .form-container {
        background: var(--card-bg);
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
        transition:
          background-color 0.3s ease,
          color 0.3s ease;
      }

      .form-title {
        text-align: center;
        color: var(--text-color);
        margin-bottom: 2rem;
        font-size: 1.8rem;
      }

      .input-group {
        margin-bottom: 1.5rem;
      }

      input {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--input-border);
        border-radius: 5px;
        font-size: 1rem;
        margin-bottom: 1rem;
        background-color: var(--input-bg);
        color: var(--text-color);
      }

      .btn {
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 5px;
        font-size: 1rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
      }

      .login-btn {
        background-color: #3498db;
        color: white;
      }

      .signup-section {
        text-align: center;
        margin-top: 2rem;
      }

      .signup-text {
        color: var(--text-color);
        margin-bottom: 1rem;
      }

      .signup-btn {
        background-color: #2c3e50;
        color: white;
        text-decoration: none;
        padding: 10px 30px;
        border-radius: 5px;
        transition: all 0.3s ease;
      }

      .btn:hover {
        opacity: 0.9;
        transform: translateY(-2px);
      }

      .signup-btn:hover {
        background-color: #34495e;
      }

      @media (max-width: 480px) {
        .form-container {
          margin: 1rem;
          padding: 1.5rem;
        }

        .logo {
          font-size: 2rem;
        }
      }    </style>
</head>
<body>
    <!-- Navbar -->
    <div id="navbar-placeholder"></div>

    <div class="container">
      <div class="form-container">
        <h2 class="form-title">Login to Your Account</h2>
        <!-- The form now sends data to login.php -->
        <form method="POST" action="login.php">
          <div class="input-group">
            <input type="email" name="email" placeholder="Email Address" required />
          </div>
          <div class="input-group">
            <input type="password" name="password" placeholder="Password" required />
          </div>
          <button type="submit" class="btn login-btn">Login</button>
        </form>

        <div class="signup-section">
          <p class="signup-text">Don't have an account?</p>
          <a href="createAccount.php" class="btn signup-btn">Create Account</a>
        </div>
      </div>
    </div>

    <script>
      // Fetch and insert navbar
      fetch('navbar.php')
        .then((response) => response.text())
        .then((data) => {
          document.getElementById('navbar-placeholder').innerHTML = data;
        })
        .catch((error) => console.error('Error loading navbar:', error));
    </script>
</body>
</html>
