-- =========================
-- Sample Data
-- =========================

INSERT INTO `registrar` (`regid`, `Reg_Email`, `Reg_Password`, `RoleID`) VALUES
(1, 'registrar@gmail.com', '1234', 1);

INSERT INTO `buildings` (`id`, `building_name`, `department`, `number_of_floors`, `created_at`) VALUES
(1, 'Accountancy Building', 'Accountancy', 4, '2025-05-22 12:05:20'),
(2, 'Business Administration Complex', 'Business Administration', 5, '2025-05-22 12:05:20'),
(3, 'Hospitality Management Building', 'Hospitality Management', 3, '2025-05-22 12:05:20'),
(4, 'Education and Arts Center', 'Education, Arts, and Sciences', 4, '2025-05-22 12:05:20'),
(5, 'Criminal Justice Building', 'Criminal Justice Education', 3, '2025-05-22 12:05:20'),
(6, 'Sports Complex', 'Athletics', 1, '2025-08-18 21:17:52');

INSERT INTO system_settings (setting_key, setting_value, updated_at) VALUES
('room_status_last_check', '2025-03-31 15:47:03', '2025-03-31 07:47:03');

INSERT INTO rooms (room_name, room_type, capacity, building_id) VALUES 
('ACC-101', 'Classroom', 40, 1),
('ACC-102', 'Classroom', 40, 1),
('ACC-103', 'Classroom', 40, 1),
('ACC-201', 'Classroom', 45, 1),
('ACC-202', 'Classroom', 40, 1),
('ACC-301', 'Classroom', 40, 1),
('ACC-302', 'Classroom', 40, 1),
('BA-101', 'Classroom', 50, 2),
('BA-102', 'Classroom', 40, 2),
('BA-103', 'Classroom', 45, 2),
('BA-201', 'Classroom', 50, 2),
('BA-202', 'Classroom', 40, 2),
('BA-301', 'Classroom', 40, 2),
('BA-302', 'Classroom', 40, 2),
('BA-401', 'Classroom', 40, 2),
('HM-101', 'Classroom', 40, 3),
('HM-102', 'Classroom', 40, 3),
('HM-103', 'Classroom', 40, 3),
('HM-201', 'Classroom', 40, 3),
('HM-202', 'Classroom', 40, 3),
('HM-301', 'Classroom', 40, 3),
('EA-101', 'Classroom', 45, 4),
('EA-102', 'Classroom', 40, 4),
('EA-103', 'Classroom', 40, 4),
('EA-201', 'Classroom', 40, 4),
('EA-202', 'Classroom', 50, 4),
('EA-301', 'Classroom', 40, 4),
('EA-302', 'Classroom', 40, 4),
('CJ-101', 'Classroom', 40, 5),
('CJ-102', 'Classroom', 40, 5),
('CJ-103', 'Classroom', 40, 5),
('CJ-201', 'Classroom', 45, 5),
('CJ-202', 'Classroom', 40, 5),
('CJ-301', 'Classroom', 40, 5),
('GYM-MAIN', 'Gymnasium', 200, 6);
