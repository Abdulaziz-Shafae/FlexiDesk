// Function to load the navbar
function loadNavbar() {
    fetch('navbar.html')
      .then(response => response.text())
      .then(data => {
        document.getElementById('navbar-container').innerHTML = data;
        
        // Set active page
        setActivePage();
        
        // Initialize navbar functionality
        initNavbar();
      })
      .catch(error => {
        console.error('Error loading navbar:', error);
        document.getElementById('navbar-container').innerHTML = 
          '<p>Error loading navigation. Please refresh the page.</p>';
      });
  }
  
  // Function to set the active page in the navbar
  function setActivePage() {
    // Get current page filename
    const currentPage = window.location.pathname.split('/').pop();
    
    // Set active class based on current page
    if (currentPage === '' || currentPage === 'index.html') {
      document.getElementById('home-link')?.classList.add('active');
    } else if (currentPage === 'about.html') {
      document.getElementById('about-link')?.classList.add('active');
    } else if (currentPage === 'contact.html') {
      document.getElementById('contact-link')?.classList.add('active');
    }
  }
  
  // Function to initialize navbar functionality
  function initNavbar() {
    // Check for dark mode preference in localStorage
    if (localStorage.getItem('darkMode') === 'enabled') {
      document.body.classList.add('dark-mode');
      const toggleIcon = document.getElementById('theme-icon');
      if (toggleIcon) {
        toggleIcon.classList.remove('fa-sun');
        toggleIcon.classList.add('fa-moon');
      }
    }
  }
  
  // Function to toggle menu on mobile
  function toggleMenu() {
    const navbar = document.querySelector('.navbar');
    navbar.classList.toggle('menu-open');
  }
  
  // Function to toggle dark mode
  function toggleDarkMode() {
    const body = document.body;
    body.classList.toggle('dark-mode');
    
    // Save preference to localStorage
    localStorage.setItem('darkMode', body.classList.contains('dark-mode') ? 'enabled' : 'disabled');
    
    // Update icon based on dark mode state
    const toggleIcon = document.getElementById('theme-icon');
    if (toggleIcon) {
      if (body.classList.contains('dark-mode')) {
        toggleIcon.classList.remove('fa-sun');
        toggleIcon.classList.add('fa-moon');
      } else {
        toggleIcon.classList.remove('fa-moon');
        toggleIcon.classList.add('fa-sun');
      }
    }
  }
  
  // Load navbar when the document is ready
  document.addEventListener('DOMContentLoaded', loadNavbar);