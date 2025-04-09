
-- Create Users Table
CREATE TABLE Users (
    userID INT(10) AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Manager', 'Member') NOT NULL,
    profileImage BLOB
);

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

-- Create Notifications Table
CREATE TABLE Notifications (
    notificationID INT(10) AUTO_INCREMENT PRIMARY KEY,
    recipientID INT(10),
    type ENUM('Reminder', 'Update', 'Alert') NOT NULL,
    message VARCHAR(4000) NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipientID) REFERENCES Users(userID)
);

-- Create Alerts Table
CREATE TABLE Alerts (
    alertID INT(10) AUTO_INCREMENT PRIMARY KEY,
    recipientID INT(10),
    priority ENUM('High', 'Medium', 'Low') NOT NULL,
    details VARCHAR(4000) NOT NULL,
    generatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipientID) REFERENCES Users(userID)
);

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
