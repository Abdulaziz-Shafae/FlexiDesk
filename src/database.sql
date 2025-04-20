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


-- Sample Data user
INSERT INTO Users (name, email, password, profileImage, jobTitle, department, bio) VALUES
('Alice Johnson', 'alice@example.com', 'hashed_password_1', NULL, 'Developer', 'IT', 'Frontend specialist.'),
('Bob Smith', 'bob@example.com', 'hashed_password_2', NULL, 'Project Manager', 'Management', 'Team leader.'),
('Charlie Lee', 'charlie@example.com', 'hashed_password_3', NULL, 'QA Tester', 'QA', 'Bug hunter.');

-- Sample Data project
INSERT INTO Projects (title, description, startDate, endDate) VALUES
('Website Redesign', 'Update the corporate website for modern look.', '2024-04-01', '2024-06-01'),
('Mobile App Launch', 'Release Android/iOS app for customers.', '2024-05-01', '2024-07-30'),
('Internal Tool Upgrade', 'Refactor and enhance internal dashboards.', '2024-06-01', '2024-07-31'),
('Customer Survey Campaign', 'Collect feedback via online survey.', '2024-06-11', '2024-08-10'),
('AI Chatbot Integration', 'Integrate AI assistant into support.', '2024-06-21', '2024-08-20');

-- Sample Data user_projects
-- Bob is Manager for both projects
-- Alice and Charlie are Members
INSERT INTO user_projects (userID, projectID, role) VALUES
(2, 1, 'Manager'),
(2, 2, 'Manager'),
(1, 1, 'Member'),
(3, 2, 'Member'),
(2, 3, 'Manager'),
(3, 3, 'Member'),
(2, 4, 'Manager'),
(3, 4, 'Member'),
(2, 5, 'Manager'),
(1, 5, 'Member');

-- Sample Data tasks
INSERT INTO Tasks (projectID, taskName, description, startDate, deadline, priority, assignedTo, filePath, status) VALUES
-- Website Redesign (Project 1)
(1, 'Create wireframes', 'Design homepage wireframes.', '2024-04-03', '2024-04-10 17:00:00', 'medium', 1, NULL, 'Pending'),
(1, 'Design logo concepts', 'Draft logo ideas.', '2024-04-06', '2024-04-13 14:00:00', 'medium-low', 1, NULL, 'Pending'),
(1, 'Write homepage content', 'Draft homepage copy.', '2024-04-12', '2024-04-22 11:00:00', 'low', 1, NULL, 'In Progress'),
(1, 'Review SEO strategy', 'Improve site SEO.', '2024-04-14', '2024-04-25 10:00:00', 'high', 2, NULL, 'Pending'),

-- Mobile App Launch (Project 2)
(2, 'Create onboarding screens', 'Design welcome flow.', '2024-05-03', '2024-05-13 16:00:00', 'medium', 1, NULL, 'In Progress'),
(2, 'Set up Firebase analytics', 'Integrate analytics.', '2024-05-04', '2024-05-16 13:00:00', 'high', 2, NULL, 'Pending'),
(2, 'Bug regression testing', 'Retest bugs.', '2024-05-07', '2024-05-17 12:00:00', 'medium', 3, NULL, 'Pending'),
(2, 'Write test cases', 'Create unit tests.', '2024-05-02', '2024-05-12 12:00:00', 'medium-low', 3, NULL, 'Completed'),

-- Internal Tool Upgrade (Project 3)
(3, 'Audit current dashboard', 'List performance issues.', '2024-06-02', '2024-06-10 13:00:00', 'medium', 2, NULL, 'Pending'),
(3, 'Implement new filters', 'Add data filtering options.', '2024-06-05', '2024-06-15 15:00:00', 'medium-low', 1, NULL, 'In Progress'),
(3, 'Fix chart bugs', 'Resolve display bugs in charts.', '2024-06-07', '2024-06-18 14:00:00', 'high', 3, NULL, 'Pending'),

-- Customer Survey Campaign (Project 4)
(4, 'Design survey', 'Create survey questions.', '2024-06-12', '2024-06-20 17:00:00', 'medium', 1, NULL, 'Pending'),
(4, 'Launch email campaign', 'Send emails to target users.', '2024-06-15', '2024-06-25 11:00:00', 'critical', 2, NULL, 'Pending'),
(4, 'Analyze survey results', 'Summarize feedback.', '2024-07-01', '2024-07-10 10:00:00', 'medium', 3, NULL, 'Pending'),

-- AI Chatbot Integration (Project 5)
(5, 'Define chatbot scope', 'List features.', '2024-06-22', '2024-06-29 16:00:00', 'medium-low', 1, NULL, 'In Progress'),
(5, 'Integrate NLP model', 'Connect to backend AI.', '2024-06-25', '2024-07-05 17:00:00', 'high', 2, NULL, 'Pending'),
(5, 'Run chatbot testing', 'Simulate user input tests.', '2024-07-06', '2024-07-15 14:00:00', 'medium', 3, NULL, 'Pending');



-- Sample Data for MetaValues Table
    -- (Departments)
    INSERT INTO MetaValues (type, value) VALUES
    ('Department', 'Marketing'),
    ('Department', 'Development'),
    ('Department', 'Design'),
    ('Department', 'Human Resources'),
    ('Department', 'Finance'),
    ('Department', 'Information Technology');
    -- (Job Titles)
    INSERT INTO MetaValues (type, value) VALUES
    ('JobTitle', 'Software Engineer'),
    ('JobTitle', 'UI/UX Designer'),
    ('JobTitle', 'Project Manager'),
    ('JobTitle', 'HR Specialist'),
    ('JobTitle', 'Financial Analyst'),
    ('JobTitle', 'IT Support');

-- Sample Data Notifications
INSERT INTO Notifications (recipientID, type, message) VALUES
(1, 'Reminder', 'Your task "Create wireframes" is due in 2 days.'),
(3, 'Update', 'The task "Write test cases" was marked as completed.');

-- Sample Data Alerts
INSERT INTO Alerts (recipientID, priority, details) VALUES
(2, 'High', 'Project deadline approaching: Website Redesign ends in 7 days.');
