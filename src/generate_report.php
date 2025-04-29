<?php
// generate_report.php - Simple HTML report generator (no mPDF required)
session_start();
include('db.php');

// Check if project ID is provided
if (!isset($_GET['project_id']) || empty($_GET['project_id'])) {
    die('Error: Project ID is required');
}

// Get the project ID and validate it
$project_id = intval($_GET['project_id']);

try {
    // Fetch project details
    $stmt = $conn->prepare("
        SELECT * FROM Projects 
        WHERE projectID = ?
    ");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $project = $result->fetch_assoc();
    
    if (!$project) {
        die('Error: Project not found');
    }
    
    // Fetch project manager
    $stmt = $conn->prepare("
        SELECT u.* 
        FROM Users u
        JOIN user_projects up ON u.userID = up.userID
        WHERE up.projectID = ? 
        AND up.role = 'Manager'
        LIMIT 1
    ");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $manager = $result->fetch_assoc();
    
    // Fetch assigned members
    $stmt = $conn->prepare("
        SELECT u.userID, u.name, u.email, u.jobTitle, u.department, up.role
        FROM Users u
        JOIN user_projects up ON u.userID = up.userID
        WHERE up.projectID = ?
        ORDER BY up.role DESC, u.name ASC
    ");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $members = [];
    while ($row = $result->fetch_assoc()) {
        $members[] = $row;
    }
    
    // Fetch tasks
    $stmt = $conn->prepare("
        SELECT t.*, u.name as assignedToName
        FROM Tasks t
        LEFT JOIN Users u ON t.assignedTo = u.userID
        WHERE t.projectID = ?
        ORDER BY t.endDate ASC, t.priority DESC
    ");
    $stmt->bind_param("i", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }
    
    // Count tasks by status
    $taskCounts = [
        'Pending' => 0,
        'In Progress' => 0,
        'Completed' => 0,
        'Total' => 0
    ];
    
    foreach ($tasks as $task) {
        $taskCounts[$task['status']]++;
        $taskCounts['Total']++;
    }
    
    // Calculate project progress using the same formula from homePage.php
    $progress = 0;
    if ($taskCounts['Total'] > 0) {
        $progressRaw = ($taskCounts['Completed'] + (0.5 * $taskCounts['In Progress'])) / $taskCounts['Total'] * 100;
        $progress = round($progressRaw);
    }
    
    // Calculate project duration in days
    $startDate = new DateTime($project['startDate']);
    $endDate = new DateTime($project['endDate']);
    $duration = $startDate->diff($endDate)->days;
    
    // Calculate days left
    $today = new DateTime();
    $daysLeft = $today->diff($endDate)->days;
    if ($today > $endDate) {
        $daysLeft = 0;
    }
    
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}

// Get current time for report generation timestamp
$generatedTime = date('F j, Y, g:i a');
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Project Report: <?php echo htmlspecialchars($project['title']); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        h2 {
            color: #3498db;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-top: 25px;
        }
        
        h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        
        .project-meta {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        
        .meta-row {
            margin-bottom: 8px;
        }
        
        .meta-label {
            font-weight: bold;
            color: #555;
        }
        
        .summary-box {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            background-color: #f5f9ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .summary-item {
            text-align: center;
            padding: 10px;
            width: 20%;
        }
        
        .summary-number {
            font-size: 24px;
            font-weight: bold;
            color: #3498db;
        }
        
        .summary-label {
            font-size: 12px;
            color: #666;
        }
        
        .progress-container {
            width: 100%;
            background-color: #f5f5f5;
            border-radius: 10px;
            margin: 15px 0;
            height: 25px;
            overflow: hidden;
        }
        
        .progress-bar {
            height: 100%;
            background-color: #4CAF50;
            text-align: center;
            line-height: 25px;
            color: white;
            font-weight: bold;
        }
        
        .task-stats {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
        }
        
        .task-stat-box {
            flex: 1;
            text-align: center;
            padding: 15px;
            margin: 0 5px;
            border-radius: 5px;
        }
        
        .task-pending {
            background-color: #fff8e1;
        }
        
        .task-in-progress {
            background-color: #e3f2fd;
        }
        
        .task-completed {
            background-color: #e8f5e9;
        }
        
        .task-stat-number {
            font-size: 20px;
            font-weight: bold;
        }
        
        .task-stat-label {
            font-size: 12px;
            color: #555;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .milestone {
            background-color: #fffcf5;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-pending {
            background-color: #ffeeba;
            color: #856404;
        }
        
        .badge-in-progress {
            background-color: #b8daff;
            color: #004085;
        }
        
        .badge-completed {
            background-color: #c3e6cb;
            color: #155724;
        }
        
        .badge-low {
            background-color: #e2e3e5;
            color: #383d41;
        }
        
        .badge-medium-low {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        .badge-medium {
            background-color: #ede8ff;
            color: #6c5ce7;
        }
        
        .badge-high {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-critical {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            color: #777;
            font-size: 12px;
        }
        
        .print-controls {
            text-align: center;
            margin: 20px 0;
            padding: 10px;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 0 5px;
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: #2980b9;
        }
        
        #pdf-instructions {
            max-width: 400px;
            margin: 15px auto;
            background-color: #f5f5f5;
            border-radius: 6px;
            padding: 15px;
            text-align: left;
            border-left: 4px solid #3498db;
        }
        
        #pdf-instructions ol {
            padding-left: 20px;
            margin-bottom: 0;
        }
        
        .green-btn {
            background-color: #27ae60;
        }
        
        .green-btn:hover {
            background-color: #219653;
        }
        
        .gray-btn {
            background-color: #95a5a6;
        }
        
        .gray-btn:hover {
            background-color: #7f8c8d;
        }
        
        @media print {
            .print-controls, #pdf-instructions {
                display: none !important;
            }
            
            body {
                padding: 0;
                margin: 0;
            }
            
            @page {
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="print-controls">
            <button class="btn" onclick="window.print();">Print Report</button>
            <button class="btn" onclick="showPdfInstructions();">Save as PDF</button>
            
            <!-- PDF Instructions Panel - Hidden by default -->
            <div id="pdf-instructions" style="display: none;">
                <h3>To save as PDF:</h3>
                <ol>
                    <li>Click "Print" in the dialog that opens</li>
                    <li>Choose "Save as PDF" or "Microsoft Print to PDF" as the printer</li>
                    <li>Click Save</li>
                </ol>
                <button class="btn green-btn" onclick="window.print();">Continue to Print</button>
                <button class="btn gray-btn" onclick="hidePdfInstructions();">Cancel</button>
            </div>
        </div>
        
        <div class="header">
            <h1>Project Report</h1>
            <p>Generated on <?php echo $generatedTime; ?></p>
        </div>
        
        <div class="project-meta">
            <h2><?php echo htmlspecialchars($project['title']); ?></h2>
            <div class="meta-row">
                <span class="meta-label">Description:</span>
                <span><?php echo htmlspecialchars($project['description']); ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Project Manager:</span>
                <span><?php echo (isset($manager['name']) ? htmlspecialchars($manager['name']) : 'Not assigned'); ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Start Date:</span>
                <span><?php echo date('F j, Y', strtotime($project['startDate'])); ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">End Date:</span>
                <span><?php echo date('F j, Y', strtotime($project['endDate'])); ?></span>
            </div>
            <?php if(isset($project['boardCode'])): ?>
            <div class="meta-row">
                <span class="meta-label">Board Code:</span>
                <span><?php echo htmlspecialchars($project['boardCode']); ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="summary-box">
            <div class="summary-item">
                <div class="summary-number"><?php echo $duration; ?></div>
                <div class="summary-label">TOTAL DAYS</div>
            </div>
            <div class="summary-item">
                <div class="summary-number"><?php echo $daysLeft; ?></div>
                <div class="summary-label">DAYS LEFT</div>
            </div>
            <div class="summary-item">
                <div class="summary-number"><?php echo $taskCounts['Total']; ?></div>
                <div class="summary-label">TASKS</div>
            </div>
            <div class="summary-item">
                <div class="summary-number"><?php echo count($members); ?></div>
                <div class="summary-label">TEAM MEMBERS</div>
            </div>
        </div>
        
        <h2>Project Progress</h2>
        <div class="progress-container">
            <div class="progress-bar" style="width: <?php echo $progress; ?>%">
                <?php echo $progress; ?>%
            </div>
        </div>
        
        <div class="task-stats">
            <div class="task-stat-box task-pending">
                <div class="task-stat-number"><?php echo $taskCounts['Pending']; ?></div>
                <div class="task-stat-label">PENDING</div>
            </div>
            <div class="task-stat-box task-in-progress">
                <div class="task-stat-number"><?php echo $taskCounts['In Progress']; ?></div>
                <div class="task-stat-label">IN PROGRESS</div>
            </div>
            <div class="task-stat-box task-completed">
                <div class="task-stat-number"><?php echo $taskCounts['Completed']; ?></div>
                <div class="task-stat-label">COMPLETED</div>
            </div>
        </div>
        
        <h2>Team Members</h2>
        <?php if (count($members) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Job Title</th>
                        <th>Department</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['name']); ?></td>
                            <td><?php echo htmlspecialchars($member['email']); ?></td>
                            <td><?php echo htmlspecialchars($member['jobTitle']); ?></td>
                            <td><?php echo htmlspecialchars($member['department']); ?></td>
                            <td><?php echo htmlspecialchars($member['role']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No team members assigned to this project.</p>
        <?php endif; ?>
        
        <h2>Tasks</h2>
        <?php if (count($tasks) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Task Name</th>
                        <th>Description</th>
                        <th>Assigned To</th>
                        <th>End Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): 
                        $priorityClass = '';
                        $statusClass = '';
                        
                        // Set priority badge class
                        switch ($task['priority']) {
                            case 'low':
                                $priorityClass = 'badge-low';
                                break;
                            case 'medium-low':
                                $priorityClass = 'badge-medium-low';
                                break;
                            case 'medium':
                                $priorityClass = 'badge-medium';
                                break;
                            case 'high':
                                $priorityClass = 'badge-high';
                                break;
                            case 'critical':
                                $priorityClass = 'badge-critical';
                                break;
                        }
                        
                        // Set status badge class
                        switch ($task['status']) {
                            case 'Pending':
                                $statusClass = 'badge-pending';
                                break;
                            case 'In Progress':
                                $statusClass = 'badge-in-progress';
                                break;
                            case 'Completed':
                                $statusClass = 'badge-completed';
                                break;
                        }
                        
                        // Add milestone class if it's a milestone task
                        $rowClass = (isset($task['taskType']) && $task['taskType'] === 'Milestone') ? 'milestone' : '';
                    ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td><?php echo htmlspecialchars($task['taskName']); ?> 
                                <?php if(isset($task['taskType']) && $task['taskType'] === 'Milestone'): ?>
                                    <i>(Milestone)</i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($task['description']); ?></td>
                            <td><?php echo htmlspecialchars($task['assignedToName'] ?? 'Unassigned'); ?></td>
                            <td><?php echo date('M j, Y', strtotime($task['endDate'])); ?></td>
                            <td><span class="badge <?php echo $priorityClass; ?>"><?php echo htmlspecialchars(ucfirst($task['priority'])); ?></span></td>
                            <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($task['status']); ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tasks found for this project.</p>
        <?php endif; ?>
        
        <div class="footer">
            <p>Report generated by FlexiDesk Project Management System</p>
            <p>© <?php echo date('Y'); ?> FlexiDesk - All rights reserved</p>
        </div>
    </div>
    
    <script>
        function showPdfInstructions() {
            document.getElementById('pdf-instructions').style.display = 'block';
        }
        
        function hidePdfInstructions() {
            document.getElementById('pdf-instructions').style.display = 'none';
        }
    </script>
</body>
</html>