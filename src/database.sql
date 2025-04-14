-- Users Table CREATE TABLE Users (
    userID INT(10) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profileImage VARCHAR(255) DEFAULT NULL,
    jobTitle VARCHAR(100) DEFAULT NULL,
    department VARCHAR(100) DEFAULT NULL,
    bio TEXT DEFAULT NULL
);

-- Projects Table
CREATE TABLE Projects (
    projectID INT(10) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description VARCHAR(4000) NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL
);

-- Tasks Table
CREATE TABLE Tasks (
    taskID INT(10) AUTO_INCREMENT PRIMARY KEY,
    projectID INT(10),
    assignedTo INT(10),
    taskName VARCHAR(255) NOT NULL,
    deadline DATETIME NOT NULL,
    status ENUM('Pending', 'Completed', 'In Progress') NOT NULL,
    FOREIGN KEY (projectID) REFERENCES Projects(projectID),
    FOREIGN KEY (assignedTo) REFERENCES Users(userID)
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



-- Sample Data for Users Table
INSERT INTO Users (name, email, password, profileImage, jobTitle, department, bio)
VALUES 
('Alice Manager', 'alice@flexidesk.com', '$2b$12$pg.gexDNvnB/xfYL4/5yTueUFDjt78LsyZkbDRrjy1BGxP6Tuva5y', NULL, 'Project Manager', 'Marketing', 'Experienced project manager with 10+ years leading cross-functional teams.'),
('Bob Member', 'bob@flexidesk.com', '$2b$12$y9IpzwIXibwmSMpmNj/hM.KWf0DS0ldaRFBn.rdHmbpfW4Wc5Lpsy', NULL, 'Frontend Developer', 'Development', 'JavaScript expert who focuses on accessibility and performance.'),
('Charlie Member', 'charlie@flexidesk.com', '$2b$12$OkKtRdidemN.FbLEwYA0XeDV58g511gJ6tsYWcMiUln3EMm/VwlHO', NULL, 'Data Analyst', 'Finance', 'Passionate about numbers and transforming data into actionable insights.');

-- Sample Data for Projects Table
INSERT INTO Projects (title, description, startDate, endDate) 
VALUES 
('Cybersecurity Project', 'A project to enhance security.', '2024-01-01', '2024-05-23'),
('AI Research Project', 'Develop AI models for data analysis.', '2024-03-15', '2024-07-01');

-- Sample Data for user_projects Table
INSERT INTO user_projects (userID, projectID, role) 
VALUES
(1, 1, 'Manager'),
(2, 1, 'Member'),
(3, 1, 'Member'),
(1, 2, 'Manager'),
(2, 2, 'Member'),
(3, 2, 'Member');

-- Sample Data for Tasks Table
INSERT INTO Tasks (projectID, assignedTo, taskName, deadline, status) 
VALUES
(1, 2, 'Cybersecurity Risk Assessment', '2024-02-01 12:00:00', 'In Progress'),
(1, 3, 'Penetration Testing', '2024-03-01 12:00:00', 'Pending'),
(2, 2, 'AI Model Training', '2024-04-01 12:00:00', 'In Progress');

-- Sample Data for Notifications Table
INSERT INTO Notifications (recipientID, type, message) 
VALUES
(1, 'Reminder', 'Review cybersecurity risk assessments.'),
(2, 'Update', 'AI Research project tasks have been updated.');

-- Sample Data for Alerts Table
INSERT INTO Alerts (recipientID, priority, details) 
VALUES
(1, 'High', 'Security alert: Update required for the Cybersecurity Project.'),
(2, 'Medium', 'Reminder: Review AI model progress.');

-- Sample Data for Reports Table
INSERT INTO Reports (projectID, generatedBy, content) 
VALUES
(1, 1, 'Cybersecurity Project: 50% completed, focused on risk assessment and testing.'),
(2, 1, 'AI Research Project: 40% completed, initial model training finished.');

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