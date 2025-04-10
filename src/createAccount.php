<?php
session_start(); // Start the session to manage the user session

// Include database connection file
include('db.php');  // Assuming you have db.php with the connection setup

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $firstName = $_POST['first-name'];
    $lastName = $_POST['last-name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password before storing it in the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the Users table
    $sql = "INSERT INTO Users (name, email, password, role) VALUES (?, ?, ?, 'Member')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $firstName, $email, $hashedPassword); // Bind the parameters to prevent SQL injection

    // Execute the query
    if ($stmt->execute()) {
        $_SESSION['loggedin'] = true;  // Start the session for the new user
        $_SESSION['username'] = $firstName;  // Set the session username (you can set full name if needed)
        header('Location: homePage.php');  // Redirect to the home page after successful account creation
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!-- HTML for Create Account Form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FlexiDesk - Create Account</title>
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

      body {
        background-color: #f5f5f5;
        transition:
          background-color 0.3s ease,
          color 0.3s ease;
      }

      body.dark-mode {
        background-color: #121212;
        color: #ffffff;
      }

      .container {
        margin-top: 150px;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-height: 100vh;
      }

      .form-container {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 400px;
        transition:
          background-color 0.3s ease,
          color 0.3s ease;
      }

      body.dark-mode .form-container {
        background-color: #1e1e1e;
        color: #ffffff;
      }

      .form-title {
        text-align: center;
        color: #2c3e50;
        margin-bottom: 2rem;
        font-size: 1.8rem;
        transition: color 0.3s ease;
      }

      body.dark-mode .form-title {
        color: #ffffff;
      }

      /* Floating Label Input Styles */
      .wave-group {
        position: relative;
        margin-bottom: 1.5rem;
        width: 100%;
      }

      .wave-group .input {
        font-size: 16px;
        padding: 10px 10px 10px 5px;
        display: block;
        width: 100%;
        border: none;
        border-bottom: 2px solid #515151;
        background: transparent;
        color: inherit;
      }

      .wave-group .input:focus {
        outline: none;
      }

      .wave-group .label {
        color: #999;
        font-size: 18px;
        position: absolute;
        pointer-events: none;
        left: 5px;
        top: 10px;
        transition: 0.2s ease all;
      }

      .wave-group .input:focus ~ .label,
      .wave-group .input:valid ~ .label {
        transform: translateY(-20px);
        font-size: 14px;
        color: #5264ae;
      }

      body.dark-mode .wave-group .label {
        color: #666;
      }

      /* Password Toggle Eye Icon */
      .password-container {
        position: relative;
      }

      .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 1.2rem;
        color: #3498db;
      }

      .password-toggle:hover {
        opacity: 0.8;
      }

      /* Password Requirements */
      .password-requirements {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 1rem;
      }

      .password-requirements li {
        margin-bottom: 0.5rem;
      }

      .password-requirements li.valid {
        color: #27ae60;
      }

      .password-requirements li.invalid {
        color: #e74c3c;
      }

      /* Button */
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

      .create-account-btn {
        background-color: #3498db;
        color: white;
      }

      .btn:hover {
        opacity: 0.9;
        transform: translateY(-2px);
      }    </style>
</head>
<body>
    <!-- Navbar -->
    <div id="navbar-placeholder"></div>

    <div class="container">
      <div class="form-container">
        <h2 class="form-title">Create Account</h2>
        <form id="signup-form" method="POST" action="createAccount.php">
          <div class="wave-group">
            <input type="text" name="first-name" class="input" required />
            <label class="label">First Name</label>
          </div>

          <div class="wave-group">
            <input type="text" name="last-name" class="input" required />
            <label class="label">Last Name</label>
          </div>

          <div class="wave-group">
            <input type="email" name="email" class="input" required />
            <label class="label">Organization Email</label>
          </div>

          <div class="password-container wave-group">
            <input type="password" name="password" class="input" required />
            <label class="label">Password</label>
            <span class="password-toggle" onclick="togglePasswordVisibility('password', this)">👁️</span>
          </div>

          <div class="password-container wave-group">
            <input type="password" name="confirm-password" class="input" required />
            <label class="label">Re-enter Password</label>
            <span class="password-toggle" onclick="togglePasswordVisibility('confirm-password', this)">👁️</span>
          </div>

          <button type="submit" class="btn create-account-btn">Create Account</button>
        </form>
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

      // Password Toggle
      function togglePasswordVisibility(inputId, icon) {
        const input = document.getElementById(inputId);
        input.type = input.type === 'password' ? 'text' : 'password';
        icon.textContent = input.type === 'password' ? '👁️' : '🙈';
      }

      // Password Validation (as in your existing form)
      const passwordInput = document.getElementById('password');
      const requirements = {
        length: document.getElementById('length'),
        lowercase: document.getElementById('lowercase'),
        uppercase: document.getElementById('uppercase'),
        number: document.getElementById('number'),
        special: document.getElementById('special'),
      };

      passwordInput.addEventListener('input', () => {
        const password = passwordInput.value;
        requirements.length.classList.toggle('valid', password.length >= 8);
        requirements.lowercase.classList.toggle('valid', /[a-z]/.test(password));
        requirements.uppercase.classList.toggle('valid', /[A-Z]/.test(password));
        requirements.number.classList.toggle('valid', /\d/.test(password));
        requirements.special.classList.toggle('valid', /[!@#$%^&*(),.?":{}|<>]/.test(password));
      });
    </script>
</body>
</html>
