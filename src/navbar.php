<?php
session_start();
include 'db.php';

$profileImage = 'photos/profile-default-photo.jpeg'; // Default image

if (isset($_SESSION['userID'])) {
    $userID = $_SESSION['userID'];

    $stmt = $conn->prepare("SELECT profileImage FROM Users WHERE userID = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $stmt->bind_result($imgPath);
    
    if ($stmt->fetch()) {
        if (!empty($imgPath) && file_exists($imgPath)) {
            $profileImage = $imgPath;
        }
    }
    
    $stmt->close();
}
?>


<!-- Navbar HTML -->
<div class="navbar">
  <div>
    <h2 class="logo">FlexiDesk</h2>
  </div>

  <div>
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true): ?>
      <!-- If the user is logged in -->
      <a href="homePage.php">Home</a>
      <a href="groupChat.php">Chat</a>
    <?php endif; ?>
    
    <!-- Links accessible for all users -->
    <a href="about.html">About</a>
    <a href="contact.html">Contact</a>
  </div>

  <div>
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true): ?>
      <!-- If the user is logged in -->
      <button class="profile-btn" onclick="profiledropdown()">
        <img src="<?= htmlspecialchars($profileImage) ?>" alt="Profile">
      </button>
      <div class="profile-list" id="profileList">
      <div class="profile-item" onclick="window.location.href='userProfile.html?tab=settings'">Settings</div>
      <div class="profile-item" onclick="window.location.href='userProfile.html?tab=my-tasks'">My Tasks</div>
      <div class="profile-item" onclick="window.location.href='userProfile.html?tab=notifications'">Notifications</div>
      
      <!--
        add on later 

        <div class="profile-item" onclick="window.location.href='switchLanguage.php'">Switch Language</div>
        <div class="dark-mode">
          <label for="dark-mode-switch">Dark Mode</label>
          <div class="switch">
            <input type="checkbox" id="dark-mode-switch" />
            <span class="slider"></span>
          </div>
        </div>
    -->
        <div class="profile-item" onclick="window.location.href='logout.php'">Logout</div>
      </div>
    <?php else: ?>
      <!-- If the user is NOT logged in -->
      <button class="login-signin" onclick="window.location.href='Login.html'">Log in</button>
      <button class="login-signin" onclick="window.location.href='createAccount.html'">Sign up</button>
    <?php endif; ?>
  </div>
</div>
