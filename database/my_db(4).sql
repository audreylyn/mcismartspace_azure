use mcismartdb;

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

CREATE TABLE equipment_units (
    unit_id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,        -- FK to equipment type
    room_id INT NOT NULL,             -- FK to where it's located
    serial_number VARCHAR(100) UNIQUE, -- optional manufacturer tag
    status ENUM('working', 'needs_repair', 'maintenance', 'missing') DEFAULT 'working',
    purchased_at DATE DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id),
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Equipment Issues
CREATE TABLE equipment_issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,             -- points to specific unit
    student_id INT DEFAULT NULL,
    teacher_id INT DEFAULT NULL,
    issue_type VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('pending','in_progress','resolved','rejected') DEFAULT 'pending',
    statusCondition ENUM('working', 'needs_repair', 'maintenance', 'missing') DEFAULT 'working',
    admin_response TEXT DEFAULT NULL,
    reported_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    resolved_at TIMESTAMP NULL DEFAULT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    reference_number VARCHAR(15) DEFAULT NULL,
    rejection_reason TEXT DEFAULT NULL,
    FOREIGN KEY (unit_id) REFERENCES equipment_units(unit_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES student(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teacher(TeacherID) ON DELETE CASCADE
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

COMMIT;

DROP TABLE IF EXISTS `teacher`;
DROP TABLE IF EXISTS `student`;
DROP TABLE IF EXISTS `room_requests`;
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

INSERT INTO `registrar` (`regid`, `Reg_Email`, `Reg_Password`, `RoleID`) VALUES
(1, 'registrar@gmail.com', '1234', 1);

INSERT INTO `buildings` (`id`, `building_name`, `department`, `number_of_floors`, `created_at`) VALUES
(1, 'Accountancy Building', 'Accountancy', 4, '2025-05-22 12:05:20'),
(2, 'Business Administration Complex', 'Business Administration', 5, '2025-05-22 12:05:20'),
(3, 'Hospitality Management Building', 'Hospitality Management', 3, '2025-05-22 12:05:20'),
(4, 'Education and Arts Center', 'Education, Arts, and Sciences', 4, '2025-05-22 12:05:20'),
(5, 'Criminal Justice Building', 'Criminal Justice Education', 3, '2025-05-22 12:05:20'),
(6, 'Sports Complex', 'Athletics', 1, '2025-08-18 21:17:52');

INSERT INTO `equipment` (`id`, `name`, `description`, `category`, `created_at`) VALUES
(1, 'Smart TV', 'A smart television with internet capabilities', 'Electronics', '2025-05-22 12:05:20'),
(2, 'TV Remote', 'Remote control compatible with smart TVs', 'Accessories', '2025-05-22 12:05:20'),
(3, 'Projector', 'Digital projector for presentations', 'Electronics', '2025-05-22 12:05:20'),
(4, 'Electric Fan', 'Oscillating electric fan for ventilation', 'Appliances', '2025-05-22 12:05:20'),
(5, 'Aircon', 'Air conditioning unit for room cooling', 'Appliances', '2025-05-22 12:05:20'),
(6, 'Speaker', 'Audio speaker system for sound output', 'Audio Equipment', '2025-05-22 12:05:20'),
(7, 'Microphone', 'Handheld microphone for voice amplification', 'Audio Equipment', '2025-05-22 12:05:20'),
(8, 'Lapel', 'Clip-on lapel microphone for presentations', 'Audio Equipment', '2025-05-22 12:05:20'),
(9, 'HDMI Cable', 'High-Definition Multimedia Interface cable for audio/video connection', 'Accessories', '2025-05-22 12:05:20'),
(10, 'Lapel', 'lapel lapel', 'Teaching Materials', '2025-08-25 03:07:09');

INSERT INTO system_settings (setting_key, setting_value, updated_at) VALUES
('room_status_last_check', '2025-03-31 15:47:03', '2025-03-31 07:47:03');

SET SQL_SAFE_UPDATES = 0;
CALL reset_all_requests();

SET SQL_SAFE_UPDATES = 0;
CALL reset_all_working();


