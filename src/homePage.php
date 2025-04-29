<?php
session_start();
include('db.php');

if (!isset($_SESSION['userID'])) {
  echo "<p style='text-align:center;'>Please <a href='Login.html'>login</a> to view your projects.</p>";
  exit;
}

$userID = $_SESSION['userID'];

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

$projects = [];
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
    $progressColor = '#dc3545'; // red: way behind
  } elseif ($progress < $expectedProgress - 5) {
    $progressColor = '#ffc107'; // yellow: slightly behind
  }

  $row['progressColor'] = $progressColor;
  $row['progress'] = $progress;
  $row['expected'] = $expectedProgress;
  $projects[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>FlexiDesk - Projects</title>
  <link rel="stylesheet" href="navbarStyle.css" />
  <script src="script.js" defer></script>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 0;
      text-align: center;
    }

    .logo-container {
      margin-top: 20px;
    }

    .projects-container {
      max-width: 1200px;
      margin: 20px auto;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1);
    }

    .projects-grid {
      display: flex;
      justify-content: center;
      gap: 30px;
      flex-wrap: wrap;
    }

    .project-card {
      width: 350px;
      height: 400px;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
      text-align: center;
    }

    .project-card img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 5px;
    }

    .project-title {
      font-size: 22px;
      font-weight: bold;
      margin: 15px 0;
    }

    .progress-bar-container {
      width: 90%;
      background: #ddd;
      border-radius: 5px;
      overflow: hidden;
      height: 20px;
      margin: 15px auto;
    }

    .progress-bar {
      height: 100%;
      background: #28a745;
      color: white;
      text-align: center;
      font-weight: bold;
      line-height: 20px;
    }

    .btn {
      display: block;
      width: 100%;
      padding: 12px;
      margin-top: 10px;
      color: white;
      text-decoration: none;
      border-radius: 5px;
      font-size: 16px;
      font-weight: bold;
      text-align: center;
      border: none;
      cursor: pointer;
    }

    .download-btn {
      background: #3498db;
    }

    .download-btn:hover {
      background: #217dbb;
    }

    .enter-btn {
      background: #2c3e50;
    }

    .enter-btn:hover {
      background: #1a252f;
    }

    .deadline {
      font-size: 16px;
      color: #555;
      margin-top: 10px;
      font-weight: bold;
    }

    .top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 10px 20px 10px;
    }

    .create-btn {
      background: #3498db;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      border: none;
      cursor: pointer;
      font-weight: bold;
      text-decoration: none;
    }

    .create-btn:hover {
      background: #2980b9;
    }
  </style>
</head>
<body>

  <div id="navbar-placeholder"></div>

    <div class="main-content">
        <div class="projects-container">
            <div class="top-bar">
              <h1>Number of projects: <?php echo count($projects); ?></h1>
              <div>
                <a href="createTask.html" class="create-btn">+ Create Task</a>
                <a href="createProject.html" class="create-btn">+ Create Project</a>
              </div>
            </div>

            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                <div class="project-card">
                    <img src="https://www.itarian.com/assets-new/images/project-management.png" alt="Project Image" />
                    <p class="project-title"><?php echo htmlspecialchars($project['title']); ?></p>
                    <div class="progress-bar-container">
                    <div class="progress-bar" style="width: <?= (int)$project['progress']; ?>%; background: <?= $project['progressColor']; ?>">
                      <?= (int)$project['progress']; ?>%
                    </div>
                    </div>
                    <button onclick="generateReport(<?= $project['projectID'] ?>)" class="btn download-btn">Download Report</button>

                    <button onclick="enterProject(<?= $project['projectID'] ?>, '<?= addslashes($project['title']) ?>', '<?= $project['role'] ?>')" class="btn enter-btn">
                      Enter Project
                    </button>
                    <p class="deadline">Deadline: <?php echo $project['endDate']; ?> | <?php echo $project['role']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

  <script>
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
    button.innerHTML = "Generating...";
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

    fetch('navbar.php')
      .then((response) => response.text())
      .then((data) => {
        document.getElementById('navbar-placeholder').innerHTML = data;
      });
  </script>
</body>
</html>
