<?php
session_start();  // Start the session

// Include the database connection
include('db.php');

// Fetch projects from the database
$sql = "SELECT * FROM Projects";
$result = $conn->query($sql);

$projects = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
} else {
    $projects = [];  // No projects found
}

$conn->close();
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
        /* General Page Styling */
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        /* Logo Styling */
        .logo-container {
            margin-top: 20px;
        }

        .logo-container img {
            width: 200px; /* Adjust size as needed */
            height: auto;
        }

        /* Container for Projects */
        .projects-container {
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1);
        }

        /* Grid Layout for Projects */
        .projects-grid {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        /* Enlarged Project Card */
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

        /* Progress Bar */
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
        }

        /* Buttons Styling */
        .btn {
            display: block;
            width: 90%;
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

        /* Project Deadline */
        .deadline {
            font-size: 16px;
            color: #555;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <div id="navbar-placeholder"></div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="projects-container">
            <h1>Number of projects: <?php echo count($projects); ?></h1>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-card">
                        <img src="https://www.itarian.com/assets-new/images/project-management.png" alt="Project Image" />
                        <p class="project-title"><?php echo $project['title']; ?></p>
                        <p><strong>Progress</strong></p>
                        <div class="progress-bar-container">
                            <div class="progress-bar" style="width: <?php echo $project['progress']; ?>%"></div>
                        </div>
                        <a href="#" class="btn download-btn">Download Report</a>
                        <button onclick="enterProject(<?php echo $project['projectID']; ?>)" class="btn enter-btn">
                            Enter Project
                        </button>
                        <p class="deadline">Deadline: <?php echo $project['endDate']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function enterProject(projectID) {
            // Redirect or do something for entering the project
            console.log('Entering project with ID: ' + projectID);
            // window.location.href = 'projectDetails.php?id=' + projectID;
        }

        // Fetch and insert navbar
        fetch('navbar.php')  // Updated to navbar.php
            .then((response) => response.text())
            .then((data) => {
                document.getElementById('navbar-placeholder').innerHTML = data;
            })
            .catch((error) => console.error('Error loading navbar:', error));
    </script>
</body>
</html>
