<?php
session_start();  // Start the session
?>

<!-- Navbar HTML -->
<div class="navbar">
  <div>
    <h2 class="logo">FlexiDesk</h2>
  </div>

  <div>
    <a href="homePage.php">Home</a>
    <a href="about.html">About</a>
    <a href="test.html">Charts</a>
    <a href="contact.html">Contact</a>
    <a href="groupChat.html">Chat</a>
  </div>

  <div>
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true): ?>
      <!-- If the user is logged in -->
      <button class="profile-btn" onclick="profiledropdown()">
        <img src="photos/profile-default-photo.jpeg" alt="Profile">
      </button>
      <div class="profile-list" id="profileList">
        <div class="profile-item" onclick="window.location.href='userProfile.html'">Profile</div>
        <div class="profile-item" onclick="window.location.href='settings.html'">Settings</div>
        <div class="profile-item" onclick="window.location.href='switchLanguage.html'">Switch Language</div>
        <div class="dark-mode">
          <label for="dark-mode-switch">Dark Mode</label>
          <div class="switch">
            <input type="checkbox" id="dark-mode-switch" />
            <span class="slider"></span>
          </div>
        </div>
        <div class="profile-item" onclick="window.location.href='logout.php'">Logout</div>
      </div>
    <?php else: ?>
      <!-- If the user is NOT logged in -->
      <button class="login-signin" onclick="window.location.href='Login.html'">Log in</button>
      <button class="login-signin" onclick="window.location.href='createAccount.html'">Sign up</button>
    <?php endif; ?>
  </div>
</div>
