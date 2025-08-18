SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

DROP DATABASE IF EXISTS my_db;
CREATE DATABASE IF NOT EXISTS my_db;

-- Use the created database
USE my_db;

-- Create the roles table
CREATE TABLE `roles` (
  `RoleID` INT NOT NULL AUTO_INCREMENT,
  `RoleName` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`RoleID`)
);

-- Insert roles
INSERT INTO `roles` (`RoleID`, `RoleName`) VALUES
(1, 'Registrar'),
(2, 'Department Admin'),
(3, 'Teacher'),
(4, 'Student');

-- Create the registrar table
CREATE TABLE `registrar` (
  `regid` INT NOT NULL AUTO_INCREMENT,
  `Reg_Email` VARCHAR(50) NOT NULL,
  `Reg_Password` VARCHAR(255) NOT NULL, 
  PRIMARY KEY (`regid`)
);

-- Initial admin data for registrar
INSERT INTO `registrar` (`regid`, `Reg_Email`, `Reg_Password`) VALUES
(1, 'registrar@gmail.com', '1234'); 

CREATE TABLE `dept_admin` (
  `AdminID` INT NOT NULL AUTO_INCREMENT,
  `FirstName` VARCHAR(50) NOT NULL,
  `LastName` VARCHAR(50) NOT NULL,
  `Department` VARCHAR(50) NOT NULL,
  `Email` VARCHAR(50) NOT NULL,
  `Password` VARCHAR(255) NOT NULL, 
  PRIMARY KEY (`AdminID`)
);

-- Create the student table
CREATE TABLE `student` (
  `StudentID` INT NOT NULL AUTO_INCREMENT,
  `FirstName` VARCHAR(50) NOT NULL,
  `LastName` VARCHAR(50) NOT NULL,
  `Department` VARCHAR(50) NOT NULL,
  `Program` VARCHAR(50) NOT NULL,
  `YearSection` VARCHAR(50) NOT NULL,
  `Email` VARCHAR(50) NOT NULL,
  `Password` VARCHAR(255) NOT NULL, 
  PRIMARY KEY (`StudentID`)
);

ALTER TABLE student ADD COLUMN AdminID INT NOT NULL;

-- Create the teacher table
CREATE TABLE `teacher` (
  `TeacherID` INT NOT NULL AUTO_INCREMENT,
  `FirstName` VARCHAR(50) NOT NULL,
  `LastName` VARCHAR(50) NOT NULL,
  `Department` VARCHAR(50) NOT NULL,
  `Email` VARCHAR(50) NOT NULL,
  `Password` VARCHAR(255) NOT NULL, 
  PRIMARY KEY (`TeacherID`)
);

ALTER TABLE teacher ADD COLUMN AdminID INT NOT NULL;

-- Create buildings table
CREATE TABLE buildings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    building_name VARCHAR(255) NOT NULL,
    department VARCHAR(255) NOT NULL,
    number_of_floors INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create rooms table
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
    PRIMARY KEY (RequestID),
    FOREIGN KEY (StudentID) REFERENCES student(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (TeacherID) REFERENCES teacher(TeacherID) ON DELETE CASCADE,
    FOREIGN KEY (RoomID) REFERENCES rooms(id)
);


INSERT INTO buildings (building_name, department, number_of_floors) VALUES 
('Accountancy Building', 'Accountancy', 4),
('Business Administration Complex', 'Business Administration', 5),
('Hospitality Management Building', 'Hospitality Management', 3),
('Education and Arts Center', 'Education and Arts', 4),
('Criminal Justice Building', 'Criminal Justice', 3),
('Sports Complex', 'Athletics', 1);

INSERT INTO rooms (room_name, room_type, capacity, building_id) VALUES 
('ACC-101', 'Classroom', 40, 1),
('ACC-102', 'Classroom', 35, 1),
('ACC-103', 'Computer Lab', 30, 1),
('ACC-201', 'Classroom', 45, 1),
('ACC-202', 'Conference Room', 20, 1),
('ACC-301', 'Faculty Office', 5, 1),
('ACC-302', 'Faculty Office', 5, 1),
('BA-101', 'Classroom', 50, 2),
('BA-102', 'Computer Lab', 40, 2),
('BA-103', 'Classroom', 45, 2),
('BA-201', 'Lecture Hall', 100, 2),
('BA-202', 'Conference Room', 25, 2),
('BA-301', 'Faculty Office', 5, 2),
('BA-302', 'Faculty Office', 5, 2),
('BA-401', 'Research Lab', 15, 2),
('HM-101', 'Classroom', 35, 3),
('HM-102', 'Kitchen Lab', 25, 3),
('HM-103', 'Dining Practice Room', 30, 3),
('HM-201', 'Classroom', 40, 3),
('HM-202', 'Conference Room', 20, 3),
('HM-301', 'Faculty Office', 5, 3),
('EA-101', 'Classroom', 45, 4),
('EA-102', 'Art Studio', 30, 4),
('EA-103', 'Music Room', 25, 4),
('EA-201', 'Classroom', 40, 4),
('EA-202', 'Theater Room', 60, 4),
('EA-301', 'Faculty Office', 5, 4),
('EA-302', 'Faculty Office', 5, 4),
('CJ-101', 'Classroom', 40, 5),
('CJ-102', 'Simulation Lab', 30, 5),
('CJ-103', 'Computer Lab', 35, 5),
('CJ-201', 'Classroom', 45, 5),
('CJ-202', 'Conference Room', 20, 5),
('CJ-301', 'Faculty Office', 5, 5),
('GYM-MAIN', 'Gymnasium', 200, 6);

-- Create equipment table
CREATE TABLE equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(100), -- Optional: to categorize equipment
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create room_equipment table
CREATE TABLE room_equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    equipment_id INT NOT NULL,
    quantity INT DEFAULT 1,
    notes TEXT,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('working', 'needs_repair', 'maintenance', 'missing') DEFAULT 'working',
    statusCondition ENUM('working', 'needs_repair', 'maintenance', 'missing') DEFAULT 'working',
    checked BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (room_id) REFERENCES rooms(id),
    FOREIGN KEY (equipment_id) REFERENCES equipment(id)
);

CREATE TABLE IF NOT EXISTS equipment_audit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    action_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) 
);

-- Insert sample equipment data
INSERT INTO equipment (name, description, category) VALUES 
('Desktop Monitor', 'Dell 24-inch LCD Monitor', 'Computer Hardware'),
('Desktop Computer', 'Dell OptiPlex 7090', 'Computer Hardware'),
('Wireless Router', 'Cisco Business Wireless Router', 'Networking Equipment'),
('Projector', 'Epson PowerLite Projector', 'Classroom Technology'),
('Smart Board', '75" Interactive Display', 'Classroom Technology'),
('Laptop Computer', 'Lenovo ThinkPad X1 Carbon', 'Computer Hardware'),
('Network Switch', 'Cisco Catalyst 2960-X Series', 'Networking Equipment'),
('Access Point', 'Ubiquiti UniFi AP AC Pro', 'Networking Equipment'),
('Document Camera', 'IPEVO V4K Ultra High Definition', 'Classroom Technology'),
('Conference Speakerphone', 'Jabra Speak 710', 'Classroom Technology'),
('Wireless Microphone', 'Shure BLX24/SM58 Wireless System', 'Audio Visual Equipment'),
('Digital Mixer', 'Yamaha TF1 Digital Mixing Console', 'Audio Visual Equipment'),
('Video Camera', 'Sony HXR-NX80 4K HD', 'Audio Visual Equipment'),
('Laser Printer', 'HP LaserJet Pro M404dn', 'Office Equipment'),
('Scanner', 'Epson WorkForce ES-400', 'Office Equipment'),
('Microscope', 'OMAX 40X-2000X Digital Microscope', 'Lab Equipment'),
('Chemistry Glassware Set', 'Comprehensive Lab Glassware Kit', 'Lab Equipment'),
('Commercial Stove', 'Vulcan 60" Gas Range', 'Kitchen Equipment'),
('Food Processor', 'Robot Coupe R2N', 'Kitchen Equipment'),
('Basketball Hoop', 'Spalding Arena View', 'Sports Equipment'),
('Volleyball Net', 'Tandem Sports Collegiate Net System', 'Sports Equipment'),
('Gymnastic Mats', 'Resilite 2" Vinyl Mats', 'Sports Equipment'),
('Interactive Whiteboard', 'SMART Board 6000 Pro Series', 'Classroom Technology'),
('Tablet', 'Apple iPad Pro 12.9"', 'Computer Hardware'),
('Virtual Reality Headset', 'Oculus Quest 2', 'Educational Technology');

CREATE TABLE `equipment_issues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `equipment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `issue_type` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','in_progress','resolved','rejected') DEFAULT 'pending',
  `statusCondition` enum('working','needs_repair','maintenance','missing') DEFAULT 'working',
  `admin_response` text DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (student_id) REFERENCES student(StudentID) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES teacher(TeacherID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Insert sample room equipment assignments
INSERT INTO room_equipment (room_id, equipment_id, quantity) VALUES 
(3, 1, 20), 
(3, 2, 20),
(3, 3, 1),
(2, 4, 1),
(2, 5, 1);

-- Update existing room_equipment entries
UPDATE room_equipment SET status = 'working' WHERE status IS NULL;


--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('room_status_last_check', '2025-03-31 15:47:03', '2025-03-31 07:47:03');

COMMIT;
