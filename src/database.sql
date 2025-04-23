-- Deletes all tables (order matters due to foreign keys)
DROP TABLE IF EXISTS Reports, Alerts, Notifications, user_projects, Tasks, Projects, Users, MetaValues;

-- Users Table 
CREATE TABLE Users (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profileImage VARCHAR(255),
    jobTitle VARCHAR(100),
    department VARCHAR(100),
    bio TEXT
);

-- Projects Table
CREATE TABLE Projects (
    projectID INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL
);

-- Tasks Table
CREATE TABLE Tasks (
    taskID INT AUTO_INCREMENT PRIMARY KEY,
    projectID INT NOT NULL,
    taskName VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    startDate DATE DEFAULT NULL,
    endDate DATE NOT NULL,
    priority ENUM('low', 'medium-low', 'medium', 'high', 'critical') DEFAULT 'medium',
    taskType ENUM('Task', 'Milestone') NOT NULL DEFAULT 'Task',
    assignedTo INT,
    filePath VARCHAR(255),
    status ENUM('Pending', 'In Progress', 'Completed') NOT NULL DEFAULT 'Pending',
    FOREIGN KEY (projectID) REFERENCES Projects(projectID) ON DELETE CASCADE,
    FOREIGN KEY (assignedTo) REFERENCES Users(userID) ON DELETE SET NULL
);

-- user_projects Table (Junction Table)
CREATE TABLE user_projects (
    userID INT(10),
    projectID INT(10),
    role ENUM('Manager', 'Member') NOT NULL,
    PRIMARY KEY (userID, projectID),
    FOREIGN KEY (userID) REFERENCES Users(userID),
    FOREIGN KEY (projectID) REFERENCES Projects(projectID)
);

-- Notifications Table
CREATE TABLE Notifications (
    notificationID INT(10) AUTO_INCREMENT PRIMARY KEY,
    recipientID INT(10),
    type ENUM('Reminder', 'Update', 'Alert') NOT NULL,
    message VARCHAR(4000) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipientID) REFERENCES Users(userID)
);

-- Alerts Table
CREATE TABLE Alerts (
    alertID INT(10) AUTO_INCREMENT PRIMARY KEY,
    recipientID INT(10),
    priority ENUM('High', 'Medium', 'Low') NOT NULL,
    details VARCHAR(4000) NOT NULL,
    generatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipientID) REFERENCES Users(userID)
);

-- Reports Table
CREATE TABLE Reports (
    reportID INT(10) AUTO_INCREMENT PRIMARY KEY,
    projectID INT(10),
    generatedBy INT(10),
    content VARCHAR(4000) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projectID) REFERENCES Projects(projectID),
    FOREIGN KEY (generatedBy) REFERENCES Users(userID)
);

-- Departments , JobTitles Table
CREATE TABLE MetaValues (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('Department', 'JobTitle') NOT NULL,
  value VARCHAR(100) NOT NULL UNIQUE
);


-- Sample Users
INSERT INTO Users (name, email, password, profileImage, jobTitle, department, bio) VALUES
('Alice Johnson', 'alice@example.com', 'hashed_pw1', NULL, 'Software Engineer', 'Development', 'Frontend React specialist'),
('Bob Smith', 'bob@example.com', 'hashed_pw2', NULL, 'Project Manager', 'Management', 'Scrum expert & project lead'),
('Charlie Lee', 'charlie@example.com', 'hashed_pw3', NULL, 'QA Tester', 'QA', 'Manual and automated testing'),
('Dina Yusuf', 'dina@example.com', 'hashed_pw4', NULL, 'HR Specialist', 'Human Resources', 'Recruitment & employee relations'),
('Elias Roman', 'elias@example.com', 'hashed_pw5', NULL, 'IT Support', 'Information Technology', 'Hardware & networking');

-- Sample Projects
INSERT INTO Projects (title, description, startDate, endDate) VALUES
('Website Redesign', 'Modern UI overhaul of company site', '2025-04-23', '2025-07-22'),
('Mobile App Launch', 'Cross-platform mobile launch', '2025-05-03', '2025-08-01'),
('Internal CRM System', 'Rebuild CRM system for sales team', '2025-05-13', '2025-08-21');

-- Sample user_projects
INSERT INTO user_projects (userID, projectID, role) VALUES
(1, 1, 'Member'),
(2, 1, 'Manager'),
(3, 1, 'Member'),
(2, 2, 'Manager'),
(1, 2, 'Member'),
(4, 3, 'Manager'),
(5, 3, 'Member');

-- Sample Tasks
INSERT INTO Tasks (projectID, taskName, description, startDate, endDate, priority, taskType, assignedTo, filePath, status) VALUES
(1, 'Design homepage mockup', 'Figma prototype', '2025-04-24', '2025-05-03', 'high', 'Milestone', 1, NULL, 'In Progress'),
(1, 'SEO audit', 'Analyze SEO', '2025-05-04', '2025-05-13', 'medium-low', 'Task', 2, NULL, 'Pending'),
(1, 'Bug fixes round 1', 'Fix styling bugs', '2025-05-14', '2025-05-23', 'medium', 'Task', 3, NULL, 'Completed'),
(2, 'Create splash screen', 'App launch screen', '2025-05-05', '2025-05-18', 'medium', 'Task', 1, NULL, 'Pending'),
(2, 'Set up backend auth', 'Firebase & Node setup', '2025-05-15', '2025-05-25', 'critical', 'Milestone', 2, NULL, 'In Progress'),
(2, 'Push notification test', 'FCM integration', '2025-05-28', '2025-06-02', 'medium-low', 'Task', 3, NULL, 'Pending'),
(3, 'Define user roles', 'Admin, Sales, Viewers', '2025-05-14', '2025-05-23', 'high', 'Task', 4, NULL, 'Completed'),
(3, 'Dashboard wireframe', 'Initial wireframe UX', '2025-05-24', '2025-06-02', 'medium', 'Task', 5, NULL, 'In Progress');

-- MetaValues - Departments
INSERT INTO MetaValues (type, value) VALUES
('Department', 'Development'),
('Department', 'Design'),
('Department', 'QA'),
('Department', 'Management'),
('Department', 'Finance'),
('Department', 'Information Technology');

-- MetaValues - Job Titles
INSERT INTO MetaValues (type, value) VALUES
('JobTitle', 'Software Engineer'),
('JobTitle', 'UI/UX Designer'),
('JobTitle', 'Project Manager'),
('JobTitle', 'HR Specialist'),
('JobTitle', 'Financial Analyst'),
('JobTitle', 'IT Support');

-- Notifications
INSERT INTO Notifications (recipientID, type, message) VALUES
(1, 'Reminder', 'Don\'t forget to submit homepage mockup by Friday.'),
(3, 'Update', 'Bug fix task marked as completed.');

-- Alerts
INSERT INTO Alerts (recipientID, priority, details) VALUES
(2, 'High', 'CRM Project has critical milestone in 5 days.');
