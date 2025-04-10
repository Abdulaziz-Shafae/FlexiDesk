-- Create Users Table
CREATE TABLE Users (
    userID INT(10) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Manager', 'Member') NOT NULL,
    profileImage BLOB
);

-- Insert sample data into Users Table
INSERT INTO Users (name, email, password, role) 
VALUES 
('Alice Manager', 'alice@flexidesk.com', 'password123', 'Manager'),
('Bob Member', 'bob@flexidesk.com', 'password123', 'Member'),
('Charlie Member', 'charlie@flexidesk.com', 'password123', 'Member');

-- Create Projects Table
CREATE TABLE Projects (
    projectID INT(10) AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description VARCHAR(4000) NOT NULL,
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    managerID INT(10),
    FOREIGN KEY (managerID) REFERENCES Users(userID)
);

-- Insert sample data into Projects Table
INSERT INTO Projects (title, description, startDate, endDate, managerID) 
VALUES 
('Cybersecurity Project', 'A project focused on improving cybersecurity measures.', '2024-01-01', '2024-05-23', 1),
('AI Research Project', 'Research and development of AI technologies for better decision making.', '2024-03-15', '2024-07-01', 1),
('Data Protection Project', 'A project dedicated to enhancing data privacy and protection.', '2024-02-01', '2024-06-15', 1);

-- Create Tasks Table
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

-- Insert sample data into Tasks Table
INSERT INTO Tasks (projectID, assignedTo, taskName, deadline, status) 
VALUES
(1, 2, 'Task 1: Cybersecurity Risk Assessment', '2024-02-15 12:00:00', 'In Progress'),
(1, 3, 'Task 2: Penetration Testing', '2024-03-01 12:00:00', 'Pending'),
(2, 2, 'Task 1: AI Model Training', '2024-04-01 12:00:00', 'In Progress'),
(3, 2, 'Task 1: Data Encryption Implementation', '2024-04-15 12:00:00', 'Pending');

-- Create Notifications Table
CREATE TABLE Notifications (
    notificationID INT(10) AUTO_INCREMENT PRIMARY KEY,
    recipientID INT(10),
    type ENUM('Reminder', 'Update', 'Alert') NOT NULL,
    message VARCHAR(4000) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipientID) REFERENCES Users(userID)
);

-- Insert sample data into Notifications Table
INSERT INTO Notifications (recipientID, type, message) 
VALUES
(1, 'Reminder', 'Don\'t forget to review the cybersecurity project.'),
(2, 'Update', 'The AI research project has been updated with new tasks.'),
(3, 'Alert', 'New data protection task has been assigned to you.');

-- Create Alerts Table
CREATE TABLE Alerts (
    alertID INT(10) AUTO_INCREMENT PRIMARY KEY,
    recipientID INT(10),
    priority ENUM('High', 'Medium', 'Low') NOT NULL,
    details VARCHAR(4000) NOT NULL,
    generatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipientID) REFERENCES Users(userID)
);

-- Insert sample data into Alerts Table
INSERT INTO Alerts (recipientID, priority, details) 
VALUES
(1, 'High', 'Security alert: Critical vulnerability discovered in system.'),
(2, 'Medium', 'Reminder: AI project deadline is approaching.'),
(3, 'Low', 'Data protection task update: New encryption method available.');

-- Create Reports Table
CREATE TABLE Reports (
    reportID INT(10) AUTO_INCREMENT PRIMARY KEY,
    projectID INT(10),
    generatedBy INT(10),
    content VARCHAR(4000) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (projectID) REFERENCES Projects(projectID),
    FOREIGN KEY (generatedBy) REFERENCES Users(userID)
);

-- Insert sample data into Reports Table
INSERT INTO Reports (projectID, generatedBy, content) 
VALUES
(1, 1, 'Project Progress: 30% completed with all cybersecurity measures in progress.'),
(2, 1, 'AI Research Progress: Initial model training completed, working on data collection.'),
(3, 1, 'Data Protection: Encryption method research completed, implementation ongoing.');
