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
      if (!empty($imgPath) && file_exists("uploads/" . $imgPath)) {
          $profileImage = "uploads/" . $imgPath;
      }
    }
    
    $stmt->close();
}

// Set active class for current page
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!-- Universal Navbar -->
<nav class="navbar">
  <!-- Logo -->
  <a href="homePage.php" class="logo">
    <h2 class="logo-text">FlexiDesk</h2>
  </a>
  
  <!-- Mobile Menu Toggle Button -->
  <button class="menu-toggle" onclick="toggleMenu()">
    <span></span>
    <span></span>
    <span></span>
  </button>
  
  <div class="nav-links">
    <!-- Home link visible to all -->
    <a href="homePage.php" <?php echo ($currentPage == 'homePage.php') ? 'class="active"' : ''; ?>>Home</a>
    
    <!-- Links accessible for all users -->
    <a href="about.html" <?php echo ($currentPage == 'about.html') ? 'class="active"' : ''; ?>>About</a>
    <a href="contact.html" <?php echo ($currentPage == 'contact.html') ? 'class="active"' : ''; ?>>Contact</a>
    
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true): ?>
      <!-- If the user is logged in -->
      <div class="profile-container">
        <button class="profile-btn" onclick="profiledropdown()">
          <img src="<?= htmlspecialchars($profileImage) . '?t=' . time() ?>" alt="Profile">
        </button>
        
        <div class="profile-list" id="profileList">
          <div class="profile-item" onclick="window.location.href='userProfile.html?tab=settings'">Settings</div>
          <div class="profile-item" onclick="window.location.href='userProfile.html?tab=my-tasks'">My Tasks</div>
          <div class="profile-item" onclick="window.location.href='userProfile.html?tab=notifications'">Notifications</div>
          <div class="profile-item" onclick="window.location.href='logout.php'">Logout</div>
        </div>
      </div>
    <?php else: ?>
      <!-- If the user is NOT logged in -->
      <div class="auth-buttons">
        <button class="auth-button login-btn" onclick="window.location.href='Login.html'">Log in</button>
        <button class="auth-button signup-btn" onclick="window.location.href='createAccount.html'">Sign up</button>
      </div>
    <?php endif; ?>
  </div>
  
  <!-- Dark Mode Toggle -->
  <div class="dark-mode-container">
    <div class="dark-mode-toggle" id="dark-mode-switch" onclick="toggleDarkMode()">
      <div class="toggle-thumb">
        <i class="toggle-icon" id="theme-icon">☀️</i>
      </div>
    </div>
  </div>
</nav>

<script>
  // Mobile Menu Toggle
  function toggleMenu() {
    document.querySelector('.navbar').classList.toggle('menu-open');
  }
  
  // Navbar Scroll Effect
  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
      document.querySelector('.navbar').classList.add('scrolled');
    } else {
      document.querySelector('.navbar').classList.remove('scrolled');
    }
  });
  
  // Dark Mode Toggle Function
  function toggleDarkMode() {
    const body = document.body;
    body.classList.toggle('dark-mode');
    
    // Save preference to localStorage
    localStorage.setItem('darkMode', body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
    
    // Update icon based on dark mode state
    const toggleIcon = document.querySelector('.toggle-icon');
    if (toggleIcon) {
      toggleIcon.textContent = body.classList.contains('dark-mode') ? '🌙' : '☀️';
    }
    
    // Update toggle thumb position
    const toggleThumb = document.querySelector('.toggle-thumb');
    if (toggleThumb) {
      toggleThumb.style.left = body.classList.contains('dark-mode') ? '26px' : '2px';
    }
  }
  
  // Apply dark mode if enabled in localStorage
  document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('darkMode') === 'enabled') {
      document.body.classList.add('dark-mode');
      const toggleIcon = document.querySelector('.toggle-icon');
      if (toggleIcon) {
        toggleIcon.textContent = '🌙';
      }
      
      // Update toggle thumb position
      const toggleThumb = document.querySelector('.toggle-thumb');
      if (toggleThumb) {
        toggleThumb.style.left = '26px';
      }
    }
  });
  
  // Profile dropdown function
  function profiledropdown() {
    const profileList = document.getElementById('profileList');
    if (profileList) {
      profileList.style.display = profileList.style.display === 'flex' ? 'none' : 'flex';
    }
  }
  
  // Close dropdown when clicking outside
  window.addEventListener('click', function(event) {
    const profileList = document.getElementById('profileList');
    const profileBtn = document.querySelector('.profile-btn');
    
    if (profileList && profileBtn && !profileBtn.contains(event.target) && !profileList.contains(event.target)) {
      profileList.style.display = 'none';
    }
  });
  
  // Make toggleDarkMode accessible globally
  window.toggleDarkMode = toggleDarkMode;
  window.toggleMenu = toggleMenu;
  window.profiledropdown = profiledropdown;
</script>