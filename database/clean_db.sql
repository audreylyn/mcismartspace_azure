SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- =========================
-- Roles
-- =========================
CREATE TABLE `roles` (
  `RoleID` INT NOT NULL AUTO_INCREMENT,
  `RoleName` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`RoleID`)
);

INSERT INTO `roles` (`RoleID`, `RoleName`) VALUES
(1, 'Registrar'),
(2, 'Department Admin'),
(3, 'Teacher'),
(4, 'Student');

-- =========================
-- Registrar
-- =========================
CREATE TABLE `registrar` (
  `regid` INT NOT NULL AUTO_INCREMENT,
  `Reg_Email` VARCHAR(50) NOT NULL,
  `Reg_Password` VARCHAR(255) NOT NULL,
  `RoleID` INT NOT NULL DEFAULT 1,
  PRIMARY KEY (`regid`),
  FOREIGN KEY (`RoleID`) REFERENCES roles(`RoleID`)
);

-- =========================
-- Department Admin
-- =========================
CREATE TABLE `dept_admin` (
  `AdminID` INT NOT NULL AUTO_INCREMENT,
  `FirstName` VARCHAR(50) NOT NULL,
  `LastName` VARCHAR(50) NOT NULL,
  `Department` VARCHAR(50) NOT NULL,
  `Email` VARCHAR(50) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `RoleID` INT NOT NULL DEFAULT 2,
  PRIMARY KEY (`AdminID`),
  FOREIGN KEY (`RoleID`) REFERENCES roles(`RoleID`)
);

-- =========================
-- Student
-- =========================
CREATE TABLE `student` (
  `StudentID` INT NOT NULL AUTO_INCREMENT,
  `FirstName` VARCHAR(50) NOT NULL,
  `LastName` VARCHAR(50) NOT NULL,
  `Department` VARCHAR(50) NOT NULL,
  `Program` VARCHAR(50) NOT NULL,
  `YearSection` VARCHAR(50) NOT NULL,
  `Email` VARCHAR(50) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `AdminID` INT NOT NULL,
  `RoleID` INT NOT NULL DEFAULT 4,
  `PenaltyStatus` ENUM('none', 'warning', 'banned') DEFAULT 'none',
  `PenaltyExpiresAt` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`StudentID`),
  FOREIGN KEY (`AdminID`) REFERENCES dept_admin(`AdminID`) ON DELETE CASCADE,
  FOREIGN KEY (`RoleID`) REFERENCES roles(`RoleID`)
);

-- =========================
-- Teachers
-- =========================
CREATE TABLE `teacher` (
  `TeacherID` INT NOT NULL AUTO_INCREMENT,
  `FirstName` VARCHAR(50) NOT NULL,
  `LastName` VARCHAR(50) NOT NULL,
  `Department` VARCHAR(50) NOT NULL,
  `Email` VARCHAR(50) NOT NULL,
  `Password` VARCHAR(255) NOT NULL,
  `AdminID` INT NOT NULL,
  `RoleID` INT NOT NULL DEFAULT 3,
  PRIMARY KEY (`TeacherID`),
  FOREIGN KEY (`AdminID`) REFERENCES dept_admin(`AdminID`) ON DELETE CASCADE,
  FOREIGN KEY (`RoleID`) REFERENCES roles(`RoleID`)
);

-- =========================
-- Buildings
-- =========================
CREATE TABLE buildings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    building_name VARCHAR(255) NOT NULL,
    department VARCHAR(255) NOT NULL,
    number_of_floors INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- Rooms
-- =========================
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_name VARCHAR(255) NOT NULL,
    room_type VARCHAR(255) NOT NULL,
    capacity INT NOT NULL,
    RoomStatus ENUM('available', 'occupied', 'maintenance') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    building_id INT,
    FOREIGN KEY (building_id) REFERENCES buildings(id)
);

-- =========================
-- Room Requests
-- =========================

CREATE TABLE room_requests (
    RequestID INT NOT NULL AUTO_INCREMENT,
    StudentID INT,
    TeacherID INT,
    RoomID INT NOT NULL,
    ActivityName VARCHAR(255) NOT NULL,
    Purpose TEXT NOT NULL,
    RequestDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ReservationDate DATETIME NOT NULL,
    StartTime DATETIME NOT NULL,
    EndTime DATETIME NOT NULL,
    NumberOfParticipants INT NOT NULL,
    Status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    RejectionReason TEXT,
    ApprovedBy INT DEFAULT NULL,
    ApproverFirstName VARCHAR(128) DEFAULT NULL,
    ApproverLastName VARCHAR(128) DEFAULT NULL,
    RejectedBy INT DEFAULT NULL,
    RejecterFirstName VARCHAR(128) DEFAULT NULL,
    RejecterLastName VARCHAR(128) DEFAULT NULL,
    reference_number VARCHAR(15) DEFAULT NULL,
    PRIMARY KEY (RequestID),
    FOREIGN KEY (StudentID) REFERENCES student(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (TeacherID) REFERENCES teacher(TeacherID) ON DELETE CASCADE,
    FOREIGN KEY (RoomID) REFERENCES rooms(id)
);


-- =========================
-- Equipment
-- =========================
CREATE TABLE equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Room Equipment (cleaned)
CREATE TABLE room_equipment (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT NOT NULL,
  equipment_id INT NOT NULL,
  quantity INT DEFAULT 1,
  notes TEXT,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status ENUM('working', 'needs_repair', 'maintenance', 'missing') DEFAULT 'working',
  FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
  FOREIGN KEY (equipment_id) REFERENCES equipment(id)
);

-- Equipment Audit
CREATE TABLE equipment_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) 
);

-- Equipment Issues (cleaned)
CREATE TABLE equipment_issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    room_id INT NOT NULL, 
    student_id INT,
    teacher_id INT,
    issue_type VARCHAR(128) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'resolved', 'rejected') DEFAULT 'pending',
    reported_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME,
    reference_number VARCHAR(20),
    rejection_reason TEXT,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (student_id) REFERENCES student(StudentID),
    FOREIGN KEY (teacher_id) REFERENCES teacher(TeacherID)
);

-- =========================
CREATE TABLE system_settings (
  setting_key VARCHAR(50) NOT NULL,
  setting_value TEXT DEFAULT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (setting_key)
);

-- =========================
-- Penalty System
-- =========================
CREATE TABLE penalty (
  id INT NOT NULL AUTO_INCREMENT,
  student_id INT NOT NULL,
  type ENUM('warning', 'ban') NOT NULL,
  reason TEXT NOT NULL,
  descriptions TEXT NOT NULL,
  issued_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at TIMESTAMP NULL DEFAULT NULL,
  issued_by INT DEFAULT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (student_id) REFERENCES student(StudentID) ON DELETE CASCADE,
  FOREIGN KEY (issued_by) REFERENCES dept_admin(AdminID) ON DELETE SET NULL
);


CREATE TABLE login_attempts (
    id INT NOT NULL AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL,
    email VARCHAR(100) NOT NULL,
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success TINYINT(1) DEFAULT 0,
    PRIMARY KEY (id),
    INDEX idx_ip_time (ip_address, attempt_time),
    INDEX idx_cleanup (attempt_time)
);

-- =========================
-- Triggers for Reference Numbers
-- =========================

-- Trigger for equipment issues reference numbers
DELIMITER //
CREATE TRIGGER before_equipment_issue_insert
BEFORE INSERT ON equipment_issues
FOR EACH ROW
BEGIN
    IF NEW.reference_number IS NULL THEN
        SET NEW.reference_number = CONCAT('EQ', LPAD(FLOOR(RAND() * 1000000), 6, '0'));
    END IF;
END//

-- Trigger for room requests reference numbers
CREATE TRIGGER before_room_request_insert
BEFORE INSERT ON room_requests
FOR EACH ROW
BEGIN
    IF NEW.reference_number IS NULL THEN
        SET NEW.reference_number = CONCAT('RM', LPAD(FLOOR(RAND() * 1000000), 6, '0'));
    END IF;
END//
DELIMITER ;

COMMIT;


DROP TABLE IF EXISTS `teacher`;
DROP TABLE IF EXISTS `student`;
DROP TABLE IF EXISTS `room_requests`;
DROP TABLE IF EXISTS `room_equipment`;
DROP TABLE IF EXISTS `equipment_issues`;
DROP TABLE IF EXISTS `equipment_audit`;
DROP TABLE IF EXISTS `equipment`;
DROP TABLE IF EXISTS `penalty`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `rooms`;
DROP TABLE IF EXISTS `buildings`;
DROP TABLE IF EXISTS `dept_admin`;
DROP TABLE IF EXISTS `registrar`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `system_settings`;