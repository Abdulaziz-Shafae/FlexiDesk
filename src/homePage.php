<?php
session_start();
include('db.php');

$isLoggedIn = isset($_SESSION['userID']);
$userID = $isLoggedIn ? $_SESSION['userID'] : null;

// Handle project deletion if requested
if (isset($_POST['delete_project']) && isset($_POST['project_id']) && $isLoggedIn) {
  $projectID = $_POST['project_id'];
  
  // First check if user has permission to delete (only project owners or admins)
  $checkPermission = "SELECT role FROM user_projects WHERE userID = ? AND projectID = ?";
  $permStmt = $conn->prepare($checkPermission);
  $permStmt->bind_param("ii", $userID, $projectID);
  $permStmt->execute();
  $permResult = $permStmt->get_result();
  
  if ($permResult->num_rows > 0) {
    $permRow = $permResult->fetch_assoc();
    
    // Only allow Managers to delete projects
    if ($permRow['role'] == 'Manager') {
      // Begin transaction for safe deletion
      $conn->begin_transaction();
      
      try {
        // Delete tasks related to project
        $deleteTasksSQL = "DELETE FROM Tasks WHERE projectID = ?";
        $taskStmt = $conn->prepare($deleteTasksSQL);
        $taskStmt->bind_param("i", $projectID);
        $taskStmt->execute();
        
        // Delete user_projects relationships
        $deleteUserProjectsSQL = "DELETE FROM user_projects WHERE projectID = ?";
        $upStmt = $conn->prepare($deleteUserProjectsSQL);
        $upStmt->bind_param("i", $projectID);
        $upStmt->execute();
        
        // Finally delete the project itself
        $deleteProjectSQL = "DELETE FROM Projects WHERE projectID = ?";
        $projStmt = $conn->prepare($deleteProjectSQL);
        $projStmt->bind_param("i", $projectID);
        $projStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Set success message
        $_SESSION['message'] = "Project successfully deleted.";
        $_SESSION['message_type'] = "success";
      } catch (Exception $e) {
        // Rollback in case of error
        $conn->rollback();
        $_SESSION['message'] = "Error deleting project: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
      }
    } else {
      $_SESSION['message'] = "You don't have permission to delete this project.";
      $_SESSION['message_type'] = "error";
    }
  } else {
    $_SESSION['message'] = "Project not found or you don't have access.";
    $_SESSION['message_type'] = "error";
  }
  
  // Redirect to prevent form resubmission
  header("Location: homePage.php");
  exit();
}

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
      --danger-color: #dc3545;
      --danger-hover: #bd2130;
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
      --danger-color: #e05d65;
      --danger-hover: #f27680;
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

    /* ===== UPDATED NAVBAR STYLES ===== */
    /* Logo styling and hover effect */
    .logo {
      display: flex;
      align-items: center;
      text-decoration: none;
      transition: all 0.3s ease;
      position: relative;
      padding: 8px 0;
      margin-top: 12px;
    }

    .logo .logo-text {
      font-size: 2.5rem;
      font-weight: 700;
      color: #4169E1 !important; /* Royal blue color - FIXED to match other pages */
      letter-spacing: -0.03em;
      transition: color 0.3s ease, transform 0.3s ease;
    }

    .logo:hover .logo-text {
      color: #3d5af1 !important;
      transform: translateY(-2px);
    }

    /* Add underline effect to logo */
    .logo::after {
      content: '';
      position: absolute;
      bottom: 20px;
      left: 0;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, #3d5af1, #22d1ee);
      transition: width 0.3s ease;
      border-radius: 2px;
    }

    .logo:hover::after {
      width: 100%;
    }

    .dark-mode .logo .logo-text {
      color: #3d5af1 !important;
    }

    .dark-mode .logo:hover .logo-text {
      color: #22d1ee !important;
    }

    /* Hide the logo icon */
    .logo-icon {
      display: none;
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
      overflow: hidden;
    }

    .nav-links a::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 0;
      height: 2px;
      background: linear-gradient(90deg, #3d5af1, #22d1ee);
      transition: width 0.3s ease;
      border-radius: 2px;
    }

    .nav-links a:hover {
      color: var(--navbar-hover);
      background-color: transparent;
    }

    .nav-links a:hover::after {
      width: 100%;
    }

    .nav-links a.active {
      color: var(--navbar-active);
      font-weight: 600;
    }

    .nav-links a.active::after {
      width: 100%;
    }

    /* Auth Buttons enhanced hover effects */
    .auth-button {
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .login-btn:hover {
      background-color: rgba(61, 90, 241, 0.1);
      border-color: var(--navbar-hover);
      color: var(--navbar-hover);
      transform: translateY(-3px);
    }

    .signup-btn:hover {
      background-color: var(--button-hover);
      transform: translateY(-3px) scale(1.05);
      box-shadow: 0 8px 25px rgba(61, 90, 241, 0.3);
    }

    /* Dark mode toggle enhanced animation */
    .dark-mode-toggle {
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .dark-mode-toggle:hover {
      transform: scale(1.1);
    }

    .toggle-thumb {
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
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
      position: relative;
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

    .delete-btn {
      background: var(--danger-color);
    }

    .delete-btn:hover {
      background: var(--danger-hover);
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(220, 53, 69, 0.2);
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

    /* Alert messages */
    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 10px;
      color: white;
      font-weight: 500;
      text-align: center;
      opacity: 0;
      animation: fadeIn 0.5s forwards;
    }

    .alert-success {
      background-color: #28a745;
    }

    .alert-error {
      background-color: #dc3545;
    }

    @keyframes fadeIn {
      from {opacity: 0;}
      to {opacity: 1;}
    }

    /* Modal styling for delete confirmation */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      align-items: center;
      justify-content: center;
    }

    .modal-content {
      background-color: var(--card-bg-light);
      padding: 30px;
      border-radius: 15px;
      width: 400px;
      max-width: 90%;
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
      text-align: center;
      animation: modalAppear 0.3s;
    }

    @keyframes modalAppear {
      from {opacity: 0; transform: scale(0.8);}
      to {opacity: 1; transform: scale(1);}
    }

    .modal h2 {
      color: var(--primary-color);
      margin-bottom: 20px;
    }

    .modal p {
      margin-bottom: 25px;
      color: var(--text-color);
    }

    .modal-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
    }

    .modal-btn {
      padding: 12px 25px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      border: none;
    }

    .confirm-delete {
      background-color: var(--danger-color);
      color: white;
    }

    .confirm-delete:hover {
      background-color: var(--danger-hover);
      transform: translateY(-2px);
    }

    .cancel-delete {
      background-color: var(--subtle-bg);
      color: var(--text-color);
    }

    .cancel-delete:hover {
      background-color: var(--border-color);
      transform: translateY(-2px);
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
  <!-- DIRECTLY include navbar -->
  <?php include 'navbar.php'; ?>

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
      <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
          <?php echo $_SESSION['message']; ?>
        </div>
        <?php
        // Clear the message after displaying
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
      <?php endif; ?>
      
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
          <?php if ($project['role'] == 'Manager'): ?>
          <button onclick="confirmDelete(<?= $project['projectID'] ?>, '<?= addslashes($project['title']) ?>')" class="btn delete-btn">
            <i class="fas fa-trash-alt"></i> Delete Project
          </button>
          <?php endif; ?>
          <p class="deadline">Deadline: <?php echo $project['endDate']; ?> | <?php echo $project['role']; ?></p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <h2>Confirm Deletion</h2>
      <p>Are you sure you want to delete the project "<span id="projectTitle"></span>"?</p>
      <p>This action cannot be undone and will remove all tasks and team members associated with this project.</p>
      <div class="modal-buttons">
        <form id="deleteForm" method="POST" action="">
          <input type="hidden" name="project_id" id="projectId">
          <input type="hidden" name="delete_project" value="1">
          <button type="submit" class="modal-btn confirm-delete">Delete</button>
        </form>
        <button class="modal-btn cancel-delete" onclick="closeModal()">Cancel</button>
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
    
    // Delete project modal functions
    function confirmDelete(projectID, projectName) {
      document.getElementById('projectTitle').textContent = projectName;
      document.getElementById('projectId').value = projectID;
      document.getElementById('deleteModal').style.display = 'flex';
    }
    
    function closeModal() {
      document.getElementById('deleteModal').style.display = 'none';
    }
    
    // Close modal when clicking outside of it
    window.onclick = function(event) {
      const modal = document.getElementById('deleteModal');
      if (event.target == modal) {
        closeModal();
      }
    }
    
    // Make alerts fade out after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
      const alerts = document.querySelectorAll('.alert');
      alerts.forEach(alert => {
        setTimeout(() => {
          alert.style.opacity = '0';
          setTimeout(() => {
            alert.style.display = 'none';
          }, 500);
        }, 5000);
      });
      
      // Apply dark mode if set in localStorage
      if (localStorage.getItem('darkMode') === 'enabled') {
        document.body.classList.add('dark-mode');
      }
    });
  </script>
</body>
</html>