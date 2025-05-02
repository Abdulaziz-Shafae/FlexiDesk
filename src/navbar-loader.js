// Function to load the navbar with consistent styling and functionality
function loadNavbar() {
  fetch('navbar.php')
    .then(response => response.text())
    .then(data => {
      document.getElementById('navbar-container').innerHTML = data;
      
      // Set active page
      setActivePage();
      
      // Initialize dark mode
      initDarkMode();
      
      // Fix logo styling to match homepage
      fixLogoStyling();
      
      // Add event listeners to navbar elements
      addEventListeners();
    })
    .catch(error => {
      console.error('Error loading navbar:', error);
      // Fallback to static navbar in case of error
      loadStaticNavbar();
    });
}

// Fallback function to load static navbar if PHP fails
function loadStaticNavbar() {
  const navbarHTML = `
  <nav class="navbar">
    <a href="homePage.php" class="logo">
      <h2 class="logo-text">FlexiDesk</h2>
    </a>
    
    <button class="menu-toggle" onclick="toggleMenu()">
      <span></span>
      <span></span>
      <span></span>
    </button>
    
    <div class="nav-links">
      <a href="homePage.php" id="home-link">Home</a>
      <a href="about.html" id="about-link">About</a>
      <a href="contact.html" id="contact-link">Contact</a>
      
      <div class="auth-buttons">
        <button class="auth-button login-btn" onclick="window.location.href='Login.html'">Log in</button>
        <button class="auth-button signup-btn" onclick="window.location.href='createAccount.html'">Sign up</button>
      </div>
    </div>
    
    <div class="dark-mode-container">
      <div class="dark-mode-toggle" id="dark-mode-switch" onclick="toggleDarkMode()">
        <div class="toggle-thumb">
          <i class="toggle-icon" id="theme-icon">☀️</i>
        </div>
      </div>
    </div>
  </nav>
  `;
  
  document.getElementById('navbar-container').innerHTML = navbarHTML;
  setActivePage();
  initDarkMode();
  fixLogoStyling();
  addEventListeners();
}

// Function to set the active page in the navbar
function setActivePage() {
  // Get current page filename and path
  const currentPage = window.location.pathname.split('/').pop().toLowerCase();
  
  // Clear any existing active classes
  const navLinks = document.querySelectorAll('.nav-links a');
  navLinks.forEach(link => link.classList.remove('active'));
  
  // Set active class based on the current page
  if (currentPage === 'homepage.php' || currentPage === '' || currentPage === 'index.html' || currentPage === 'index.php') {
    const homeLink = document.querySelector('.nav-links a[href="homePage.php"]');
    if (homeLink) homeLink.classList.add('active');
  } 
  else if (currentPage === 'about.html' || currentPage.includes('about')) {
    const aboutLink = document.querySelector('.nav-links a[href="about.html"]');
    if (aboutLink) aboutLink.classList.add('active');
  } 
  else if (currentPage === 'contact.html' || currentPage.includes('contact')) {
    const contactLink = document.querySelector('.nav-links a[href="contact.html"]');
    if (contactLink) contactLink.classList.add('active');
  }
}

// Function to initialize dark mode based on localStorage
function initDarkMode() {
  if (localStorage.getItem('darkMode') === 'enabled') {
    document.body.classList.add('dark-mode');
    const toggleIcon = document.querySelector('.toggle-icon');
    if (toggleIcon) toggleIcon.textContent = '🌙';
    
    const toggleThumb = document.querySelector('.toggle-thumb');
    if (toggleThumb) toggleThumb.style.left = '26px';
  }
}

// Function to fix the FlexiDesk logo styling
function fixLogoStyling() {
  const logoText = document.querySelector('.logo-text');
  if (logoText) {
    logoText.style.fontSize = '2rem';
    logoText.style.fontWeight = '700';
    logoText.style.color = '#4169E1';
    logoText.style.fontFamily = "'Inter', 'Arial', sans-serif";
    logoText.style.letterSpacing = '-0.02em';
  }
}

// Function to add event listeners to navbar elements
function addEventListeners() {
  // Dark mode toggle
  const darkModeToggle = document.getElementById('dark-mode-switch');
  if (darkModeToggle) {
    darkModeToggle.addEventListener('click', toggleDarkMode);
  }
  
  // Mobile menu toggle
  const menuToggle = document.querySelector('.menu-toggle');
  if (menuToggle) {
    menuToggle.addEventListener('click', toggleMenu);
  }
}

// Dark Mode Toggle Function (globally accessible)
function toggleDarkMode() {
  document.body.classList.toggle('dark-mode');
  
  // Save preference to localStorage
  localStorage.setItem('darkMode', document.body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
  
  // Update icon based on dark mode state
  const toggleIcon = document.querySelector('.toggle-icon');
  if (toggleIcon) {
    toggleIcon.textContent = document.body.classList.contains('dark-mode') ? '🌙' : '☀️';
  }
  
  // Update toggle thumb position
  const toggleThumb = document.querySelector('.toggle-thumb');
  if (toggleThumb) {
    toggleThumb.style.left = document.body.classList.contains('dark-mode') ? '26px' : '2px';
  }
}

// Toggle mobile menu function
function toggleMenu() {
  document.querySelector('.navbar').classList.toggle('menu-open');
}

// Profile dropdown function
function profiledropdown() {
  const profileList = document.getElementById('profileList');
  if (profileList) {
    profileList.style.display = profileList.style.display === 'flex' ? 'none' : 'flex';
  }
}

// Make sure all key functions are globally accessible
window.toggleDarkMode = toggleDarkMode;
window.toggleMenu = toggleMenu;
window.profiledropdown = profiledropdown;

// Load navbar when the document is ready
document.addEventListener('DOMContentLoaded', loadNavbar);