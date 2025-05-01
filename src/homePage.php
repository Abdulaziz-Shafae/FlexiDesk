<?php
session_start();
include('db.php');

$isLoggedIn = isset($_SESSION['userID']);
$userID = $isLoggedIn ? $_SESSION['userID'] : null;

// Only run the SQL query if user is logged in
$projects = [];
if ($isLoggedIn) {
  $sql = "SELECT 
            p.projectID, 
            p.title, 
            p.description,
            p.startDate, 
            p.endDate, 
            up.role, 
            COALESCE(ROUND((
              SUM(
                CASE 
                  WHEN t.status = 'Completed' THEN 1 
                  WHEN t.status = 'In Progress' THEN 0.5 
                  ELSE 0 
                END
              ) / NULLIF(COUNT(t.taskID), 0)
            ) * 100, 0), 0) AS progress
          FROM Projects p
          JOIN user_projects up ON p.projectID = up.projectID
          LEFT JOIN Tasks t ON t.projectID = p.projectID
          WHERE up.userID = ?
          GROUP BY p.projectID";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $userID);
  $stmt->execute();
  $result = $stmt->get_result();

  while ($row = $result->fetch_assoc()) {
    $startDate = new DateTime($row['startDate']);
    $endDate = new DateTime($row['endDate']);
    $today = new DateTime();

    $totalDays = max($startDate->diff($endDate)->days, 1);  // Avoid division by zero
    $elapsedDays = max($startDate->diff($today)->days, 0);
    $progress = (int)$row['progress'];

    $expectedProgress = round(($elapsedDays / $totalDays) * 100);

    // Default color
    $progressColor = '#28a745'; // green

    if ($progress < $expectedProgress - 20) {
      $progressColor = '#dc3545'; // red: Way Behind
    } elseif ($progress < $expectedProgress - 5) {
      $progressColor = '#ffc107'; // yellow: slightly behind
    }

    $row['progressColor'] = $progressColor;
    $row['progress'] = $progress;
    $row['expected'] = $expectedProgress;
    $projects[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FlexiDesk - Project Management</title>
  
  <!-- Include CSS files -->
  <link rel="stylesheet" href="navbarStyle.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  
  <style>
    /* ===== VARIABLES ===== */
    :root {
      /* Light mode colors */
      --primary-color: #0d2240;
      --secondary-color: #3d5af1;
      --accent-color: #22d1ee;
      --text-color: #1a1a1a;
      --background-color: #f4f4f4;
      --card-bg-light: #ffffff;
      --card-shadow: rgba(0, 0, 0, 0.08);
      --gradient-start: #3d5af1;
      --gradient-end: #22d1ee;
      --border-color: rgba(0, 0, 0, 0.08);
      --subtle-bg: #f8f9fa;
      --navbar-bg: rgba(255, 255, 255, 0.9);
      --navbar-text: #0d2240;
      --navbar-shadow: rgba(0, 0, 0, 0.05);
      --navbar-hover: #3d5af1;
      --navbar-active: #22d1ee;
      --button-bg: #3d5af1;
      --button-text: #ffffff;
      --button-hover: #0d2240;
    }

    /* Dark mode variables */
    .dark-mode {
      --primary-color: #22d1ee;
      --secondary-color: #3d5af1;
      --accent-color: #f5f5f5;
      --text-color: #f1f1f1;
      --background-color: #111111;
      --card-bg-light: #1a1a1a;
      --card-shadow: rgba(0, 0, 0, 0.25);
      --gradient-start: #3d5af1;
      --gradient-end: #22d1ee;
      --border-color: rgba(255, 255, 255, 0.1);
      --subtle-bg: #1e1e1e;
      --navbar-bg: rgba(17, 17, 17, 0.95);
      --navbar-text: #f1f1f1;
      --navbar-shadow: rgba(0, 0, 0, 0.3);
      --navbar-hover: #22d1ee;
      --navbar-active: #3d5af1;
      --button-bg: #22d1ee;
      --button-text: #111111;
      --button-hover: #3d5af1;
    }

    /* ===== RESET & BASE ===== */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    body {
      font-family: 'Inter', 'Arial', sans-serif;
      background-color: var(--background-color);
      color: var(--text-color);
      margin: 0;
      padding: 0;
      min-height: 100vh;
      transition: all 0.3s ease;
      line-height: 1.6;
    }

    /* ===== NAVBAR STYLES ===== */
    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      height: 80px;
      padding: 0 40px;
      background-color: var(--navbar-bg);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      box-shadow: 0 4px 30px var(--navbar-shadow);
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
    }

    .navbar.scrolled {
      height: 70px;
      padding: 0 30px;
    }

    /* Logo Styles */
    .logo {
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
    }

    .logo-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
      border-radius: 10px;
      color: white;
      font-size: 18px;
    }

    .logo-text {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--navbar-text);
      letter-spacing: -0.03em;
      transition: all 0.3s ease;
    }

    .logo:hover .logo-text {
      color: var(--navbar-hover);
    }

    .logo:hover .logo-icon {
      transform: rotate(-5deg) scale(1.1);
    }

    /* Navigation Links */
    .nav-links {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .nav-links a {
      position: relative;
      padding: 8px 16px;
      color: var(--navbar-text);
      text-decoration: none;
      font-weight: 500;
      font-size: 1rem;
      transition: all 0.3s ease;
      border-radius: 6px;
    }

    .nav-links a:hover {
      color: var(--navbar-hover);
      background-color: rgba(61, 90, 241, 0.05);
    }

    .nav-links a.active {
      color: var(--navbar-active);
      font-weight: 600;
    }

    .nav-links a.active::after {
      content: '';
      position: absolute;
      bottom: 2px;
      left: 50%;
      transform: translateX(-50%);
      width: 20px;
      height: 3px;
      background: linear-gradient(90deg, var(--secondary-color), var(--accent-color));
      border-radius: 3px;
    }

    /* Auth Buttons */
    .auth-buttons {
      display: flex;
      gap: 12px;
    }

    .auth-button {
      padding: 10px 20px;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.95rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .login-btn {
      background-color: transparent;
      color: var(--navbar-text);
      border: 1px solid var(--navbar-text);
    }

    .login-btn:hover {
      background-color: rgba(61, 90, 241, 0.05);
      border-color: var(--navbar-hover);
      color: var(--navbar-hover);
    }

    .signup-btn {
      background-color: var(--button-bg);
      color: var(--button-text);
      box-shadow: 0 4px 15px rgba(61, 90, 241, 0.25);
    }

    .signup-btn:hover {
      background-color: var(--button-hover);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(61, 90, 241, 0.3);
    }

    /* Dark Mode Toggle */
    .dark-mode-container {
      display: flex;
      align-items: center;
      margin-right: 15px;
    }

    .dark-mode .login-container {
    align-items: center;
    justify-content: center;
    }

    .dark-mode .login-card {
    margin: 0 auto;
    position: relative;
    }

    .dark-mode-toggle {
      position: relative;
      width: 48px;
      height: 24px;
      border-radius: 12px;
      background-color: rgba(0, 0, 0, 0.1);
      display: flex;
      align-items: center;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .dark-mode .dark-mode-toggle {
      background-color: rgba(255, 255, 255, 0.2);
    }

    .toggle-thumb {
      position: absolute;
      left: 2px;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background-color: white;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .dark-mode .toggle-thumb {
      left: 26px;
      background-color: var(--accent-color);
    }

    .toggle-icon {
      font-size: 12px;
      color: #ffc107;
    }

    .dark-mode .toggle-icon {
      color: #2c3e50;
    }

    /* Mobile Menu Button (visible on small screens) */
    .menu-toggle {
      display: none;
      flex-direction: column;
      justify-content: space-between;
      width: 30px;
      height: 21px;
      background: transparent;
      border: none;
      cursor: pointer;
      padding: 0;
      z-index: 1001;
    }

    .menu-toggle span {
      width: 100%;
      height: 3px;
      background-color: var(--navbar-text);
      border-radius: 3px;
      transition: all 0.3s ease;
    }

    /* Mobile Menu styles */
    @media (max-width: 768px) {
      .menu-toggle {
        display: flex;
      }
      
      .menu-open .menu-toggle span:nth-child(1) {
        transform: translateY(9px) rotate(45deg);
      }
      
      .menu-open .menu-toggle span:nth-child(2) {
        opacity: 0;
      }
      
      .menu-open .menu-toggle span:nth-child(3) {
        transform: translateY(-9px) rotate(-45deg);
      }
      
      .nav-links {
        position: fixed;
        top: 80px;
        left: 0;
        right: 0;
        background-color: var(--navbar-bg);
        flex-direction: column;
        padding: 20px;
        box-shadow: 0 10px 30px var(--navbar-shadow);
        clip-path: polygon(0 0, 100% 0, 100% 0, 0 0);
        transition: all 0.4s cubic-bezier(0.215, 0.61, 0.355, 1);
        pointer-events: none;
      }
      
      .menu-open .nav-links {
        clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
        pointer-events: all;
      }
      
      .nav-links a {
        width: 100%;
        text-align: center;
        padding: 15px;
      }
      
      .auth-buttons {
        margin-top: 15px;
        width: 100%;
      }
      
      .auth-button {
        flex: 1;
      }
    }

    /* ===== Login Message Styling ===== */
    .login-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
    text-align: center;
    padding: 0 20px;
    width: 100%; /* Ensure full width */
    }

    .login-card {
    background: var(--card-bg-light);
    border-radius: 15px;
    box-shadow: 0 10px 30px var(--card-shadow);
    padding: 50px;
    max-width: 500px;
    width: 100%;
    margin: 0 auto; /* Explicit centering */
    border: 1px solid var(--border-color);
    position: relative; /* Ensure proper stacking context */
    }

    .login-icon {
      font-size: 4rem;
      color: var(--secondary-color);
      margin-bottom: 20px;
    }

    .login-title {
      font-size: 2rem;
      color: var(--primary-color);
      margin-bottom: 15px;
    }

    .login-message {
      font-size: 1.1rem;
      color: var(--text-color);
      margin-bottom: 30px;
      line-height: 1.6;
    }

    .login-button {
      display: inline-block;
      padding: 14px 30px;
      background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
      color: white;
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
      transition: all 0.3s ease;
      font-size: 1.1rem;
      border: none;
      cursor: pointer;
    }

    .login-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(61, 90, 241, 0.3);
    }

    .signup-link {
      margin-top: 20px;
      font-size: 1rem;
    }

    .signup-link a {
      color: var(--secondary-color);
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    .signup-link a:hover {
      color: var(--accent-color);
      text-decoration: underline;
    }

    /* ===== Projects Styling ===== */
    .main-content {
      padding-top: 120px;
      padding-bottom: 80px;
    }

    .projects-container {
      max-width: 1200px;
      margin: 0 auto;
      background: var(--card-bg-light);
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 10px 30px var(--card-shadow);
      border: 1px solid var(--border-color);
    }

    .projects-grid {
      display: flex;
      justify-content: center;
      gap: 30px;
      flex-wrap: wrap;
      margin-top: 40px;
    }

    .project-card {
      width: 350px;
      min-height: 400px;
      background: var(--card-bg-light);
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 5px 20px var(--card-shadow);
      text-align: center;
      transition: all 0.3s ease;
      border: 1px solid var(--border-color);
    }

    .project-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px var(--card-shadow);
    }

    .project-card img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 10px;
      margin-bottom: 15px;
    }

    .project-title {
      font-size: 1.5rem;
      font-weight: 700;
      margin: 15px 0;
      color: var(--primary-color);
    }

    .progress-bar-container {
      width: 100%;
      background: var(--subtle-bg);
      border-radius: 10px;
      overflow: hidden;
      height: 20px;
      margin: 20px 0;
    }

    .progress-bar {
      height: 100%;
      color: white;
      text-align: center;
      font-weight: 600;
      line-height: 20px;
      transition: width 1s ease;
      border-radius: 10px;
      font-size: 0.9rem;
    }

    .btn {
      display: block;
      width: 100%;
      padding: 14px;
      margin-top: 15px;
      color: white;
      text-decoration: none;
      border-radius: 10px;
      font-size: 1rem;
      font-weight: 600;
      text-align: center;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .download-btn {
      background: var(--secondary-color);
    }

    .download-btn:hover {
      background: var(--accent-color);
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(61, 90, 241, 0.2);
    }

    .enter-btn {
      background: var(--primary-color);
    }

    .enter-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(13, 34, 64, 0.2);
    }

    .deadline {
      font-size: 0.95rem;
      color: var(--text-color);
      margin-top: 15px;
      opacity: 0.8;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 30px;
      border-bottom: 1px solid var(--border-color);
      margin-bottom: 30px;
    }

    .top-bar h1 {
      font-size: 1.8rem;
      color: var(--primary-color);
    }

    .create-btn {
      background: linear-gradient(135deg, var(--gradient-start), var(--gradient-end));
      color: white;
      padding: 12px 20px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      font-weight: 600;
      text-decoration: none;
      margin-left: 10px;
      transition: all 0.3s ease;
      font-size: 0.95rem;
    }

    .create-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 15px rgba(61, 90, 241, 0.3);
    }

    /* ===== REVEAL ANIMATIONS ===== */
    .reveal {
      position: relative;
      opacity: 0;
      transform: translateY(50px);
      transition: all 1s cubic-bezier(0.5, 0, 0, 1);
    }

    .reveal.active {
      opacity: 1;
      transform: translateY(0);
    }

    .delay-1 { transition-delay: 0.1s; }
    .delay-2 { transition-delay: 0.2s; }
    .delay-3 { transition-delay: 0.3s; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
      .top-bar {
        flex-direction: column;
        gap: 20px;
        text-align: center;
      }
      
      .login-card {
        padding: 30px;
      }
      
      .projects-container {
        padding: 30px;
      }
      
      .project-card {
        width: 100%;
      }
    }
  </style>
</head>
<body>
  <!-- Navbar Container -->
  <div id="navbar-container"></div>

  <?php if (!$isLoggedIn): ?>
  <!-- Login Message -->
  <div class="login-container">
    <div class="login-card reveal">
      <div class="login-icon">
        <i class="fas fa-lock"></i>
      </div>
      <h1 class="login-title">Welcome to FlexiDesk</h1>
      <p class="login-message">Please log in to view your projects and access all features. Our platform helps you manage tasks, collaborate with your team, and boost productivity.</p>
      <a href="Login.html" class="login-button">Log in</a>
      <p class="signup-link">Don't have an account? <a href="createAccount.html">Sign up</a></p>
    </div>
  </div>
  <?php else: ?>
  <!-- Projects Content -->
  <div class="main-content">
    <div class="projects-container reveal">
      <div class="top-bar">
        <h1>Your Projects (<?php echo count($projects); ?>)</h1>
        <div>
          <a href="createTask.html" class="create-btn"><i class="fas fa-tasks"></i> Create Task</a>
          <a href="createProject.html" class="create-btn"><i class="fas fa-project-diagram"></i> Create Project</a>
        </div>
      </div>

      <div class="projects-grid">
        <?php foreach ($projects as $project): ?>
        <div class="project-card reveal delay-1">
          <img src="https://www.itarian.com/assets-new/images/project-management.png" alt="Project Image" />
          <p class="project-title"><?php echo htmlspecialchars($project['title']); ?></p>
          <div class="progress-bar-container">
            <div class="progress-bar" style="width: <?= (int)$project['progress']; ?>%; background: <?= $project['progressColor']; ?>">
              <?= (int)$project['progress']; ?>%
            </div>
          </div>
          <button onclick="generateReport(<?= $project['projectID'] ?>)" class="btn download-btn">
            <i class="fas fa-download"></i> Download Report
          </button>
          <button onclick="enterProject(<?= $project['projectID'] ?>, '<?= addslashes($project['title']) ?>', '<?= $project['role'] ?>')" class="btn enter-btn">
            <i class="fas fa-arrow-right"></i> Enter Project
          </button>
          <p class="deadline">Deadline: <?php echo $project['endDate']; ?> | <?php echo $project['role']; ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Animation Script -->
  <script>
    // Reveal animations on scroll
    function reveal() {
      const reveals = document.querySelectorAll(".reveal");
      for (let i = 0; i < reveals.length; i++) {
        const windowHeight = window.innerHeight;
        const elementTop = reveals[i].getBoundingClientRect().top;
        const elementVisible = 150;
        
        if (elementTop < windowHeight - elementVisible) {
          reveals[i].classList.add("active");
        }
      }
    }
    
    window.addEventListener("scroll", reveal);
    window.addEventListener("load", reveal);

    // Project Functions
    function generateReport(projectID) {
      // Show loading indicator 
      const buttons = document.querySelectorAll(`.project-card button.download-btn`);
      let button = null;
      
      // Find the specific button that was clicked
      for (let i = 0; i < buttons.length; i++) {
        if (buttons[i].onclick.toString().includes(projectID)) {
          button = buttons[i];
          break;
        }
      }
      
      if (button) {
        const originalText = button.innerHTML;
        button.innerHTML = "<i class='fas fa-spinner fa-spin'></i> Generating...";
        button.disabled = true;
        
        // Open the report generator in a new tab
        window.open(`generate_report.php?project_id=${projectID}`, '_blank');
        
        // Reset the button after a short delay
        setTimeout(() => {
          button.innerHTML = originalText;
          button.disabled = false;
        }, 1500);
      }
    }

    function enterProject(projectID, projectName, role) {
      sessionStorage.setItem('selectedProjectID', projectID);
      sessionStorage.setItem('selectedProjectName', projectName);
      sessionStorage.setItem('selectedProjectRole', role);
      window.location.href = 'tableView.html';
    }
  </script>

  <!-- Load Navbar -->
  <script src="navbar-loader.js"></script>
</body>
</html>