-- Indexes for registrar
CREATE INDEX idx_registrar_email ON registrar(Reg_Email);
CREATE INDEX idx_registrar_role ON registrar(RoleID);

-- Indexes for department admin
CREATE INDEX idx_deptadmin_email ON dept_admin(Email);
CREATE INDEX idx_deptadmin_department ON dept_admin(Department);
CREATE INDEX idx_deptadmin_role ON dept_admin(RoleID);

-- Indexes for students
CREATE INDEX idx_student_email ON student(Email);
CREATE INDEX idx_student_department ON student(Department);
CREATE INDEX idx_student_admin ON student(AdminID);
CREATE INDEX idx_student_role ON student(RoleID);

-- Indexes for teachers
CREATE INDEX idx_teacher_email ON teacher(Email);
CREATE INDEX idx_teacher_department ON teacher(Department);
CREATE INDEX idx_teacher_admin ON teacher(AdminID);
CREATE INDEX idx_teacher_role ON teacher(RoleID);

-- Indexes for buildings
CREATE INDEX idx_building_name ON buildings(building_name);
CREATE INDEX idx_building_department ON buildings(department);

-- Indexes for rooms
CREATE INDEX idx_room_name ON rooms(room_name);
CREATE INDEX idx_room_status ON rooms(RoomStatus);
CREATE INDEX idx_room_building ON rooms(building_id);

-- Indexes for room requests
CREATE INDEX idx_roomreq_student ON room_requests(StudentID);
CREATE INDEX idx_roomreq_teacher ON room_requests(TeacherID);
CREATE INDEX idx_roomreq_room ON room_requests(RoomID);
CREATE INDEX idx_roomreq_status ON room_requests(Status);
CREATE INDEX idx_roomreq_reservation ON room_requests(ReservationDate);

-- Indexes for equipment
CREATE INDEX idx_equipment_name ON equipment(name);
CREATE INDEX idx_equipment_category ON equipment(category);

-- Indexes for room_equipment
CREATE INDEX idx_roomeq_room ON room_equipment(room_id);
CREATE INDEX idx_roomeq_equipment ON room_equipment(equipment_id);
CREATE INDEX idx_roomeq_status ON room_equipment(status);

-- Indexes for equipment_audit
CREATE INDEX idx_eq_audit_equipment ON equipment_audit(equipment_id);

-- Indexes for equipment_issues
CREATE INDEX idx_eq_issue_equipment ON equipment_issues(equipment_id);
CREATE INDEX idx_eq_issue_student ON equipment_issues(student_id);
CREATE INDEX idx_eq_issue_teacher ON equipment_issues(teacher_id);
CREATE INDEX idx_eq_issue_status ON equipment_issues(status);

-- Indexes for system settings (search by key)
CREATE INDEX idx_sys_settings_key ON system_settings(setting_key);

CREATE INDEX idx_student_dept_program ON student(Department, Program);
CREATE INDEX idx_student_dept_year ON student(Department, YearSection);
CREATE INDEX idx_student_dept_role ON student(Department, RoleID);

CREATE INDEX idx_teacher_dept_role ON teacher(Department, RoleID);

CREATE INDEX idx_roomreq_room_date ON room_requests(RoomID, ReservationDate);
CREATE INDEX idx_roomreq_status_date ON room_requests(Status, ReservationDate);


CREATE INDEX idx_roomeq_room_status ON room_equipment(room_id, status);


