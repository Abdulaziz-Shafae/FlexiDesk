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
  <div class="logo">
    <div class="logo-icon">
      <i class="fas fa-tasks"></i>
    </div>
    <h2 class="logo-text">FlexiDesk</h2>
  </div>
  
  <!-- Mobile Menu Toggle Button -->
  <button class="menu-toggle">
    <span></span>
    <span></span>
    <span></span>
  </button>
  
  <div class="nav-links">
    <!-- Navigation Links -->
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true): ?>
      <!-- If the user is logged in -->
      <a href="homePage.php" <?php echo ($currentPage == 'homePage.php') ? 'class="active"' : ''; ?>>Home</a>
    <?php endif; ?>
    
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
    <div class="dark-mode-toggle" id="dark-mode-switch">
      <div class="toggle-thumb">
        <i class="fas fa-sun toggle-icon"></i>
      </div>
    </div>
  </div>
</nav>

<script>
  // Mobile Menu Toggle
  const menuToggle = document.querySelector('.menu-toggle');
  const navbar = document.querySelector('.navbar');
  
  menuToggle.addEventListener('click', () => {
    navbar.classList.toggle('menu-open');
  });
  
  // Navbar Scroll Effect
  window.addEventListener('scroll', () => {
    if (window.scrollY > 50) {
      navbar.classList.add('scrolled');
    } else {
      navbar.classList.remove('scrolled');
    }
  });
  
  // Dark Mode Toggle
  const darkModeToggle = document.getElementById('dark-mode-switch');
  const body = document.body;
  
  darkModeToggle.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
    
    // Update icon based on dark mode state
    const toggleIcon = document.querySelector('.toggle-icon');
    if (body.classList.contains('dark-mode')) {
      toggleIcon.classList.remove('fa-sun');
      toggleIcon.classList.add('fa-moon');
    } else {
      toggleIcon.classList.remove('fa-moon');
      toggleIcon.classList.add('fa-sun');
    }
  });
  
  // Check for dark mode preference in localStorage
  if (localStorage.getItem('darkMode') === 'enabled') {
    body.classList.add('dark-mode');
    const toggleIcon = document.querySelector('.toggle-icon');
    toggleIcon.classList.remove('fa-sun');
    toggleIcon.classList.add('fa-moon');
  }
  
  // Profile dropdown function
  function profiledropdown() {
    const profileList = document.getElementById('profileList');
    profileList.style.display = profileList.style.display === 'flex' ? 'none' : 'flex';
  }
  
  // Close dropdown when clicking outside
  window.addEventListener('click', function(event) {
    const profileList = document.getElementById('profileList');
    const profileBtn = document.querySelector('.profile-btn');
    
    if (profileList && !profileBtn.contains(event.target) && !profileList.contains(event.target)) {
      profileList.style.display = 'none';
    }
  });
</script>