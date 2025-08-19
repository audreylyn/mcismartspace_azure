-- Add columns for tracking who approved or rejected requests
ALTER TABLE room_requests 
ADD COLUMN ApprovedBy INT NULL,
ADD COLUMN ApproverName VARCHAR(255) NULL,
ADD COLUMN RejectedBy INT NULL,
ADD COLUMN RejecterName VARCHAR(255) NULL;


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
    ApprovedBy INT NOT NULL,
    ApproverFirstName VARCHAR(128) NOT NULL,
    ApproverLastName VARCHAR(128) NOT NULL,
    RejectedBy INT NOT NULL,
    RejecterFirstName VARCHAR(128) NOT NULL,
    RejecterLastName VARCHAR(128) NOT NULL,
    PRIMARY KEY (RequestID),
    FOREIGN KEY (StudentID) REFERENCES student(StudentID) ON DELETE CASCADE,
    FOREIGN KEY (TeacherID) REFERENCES teacher(TeacherID) ON DELETE CASCADE,
    FOREIGN KEY (RoomID) REFERENCES rooms(id)
);

-- Add foreign key constraints (optional, remove if not needed)
-- ALTER TABLE room_requests
-- ADD CONSTRAINT fk_approved_by FOREIGN KEY (ApprovedBy) REFERENCES dept_admin(id) ON DELETE SET NULL,
-- ADD CONSTRAINT fk_rejected_by FOREIGN KEY (RejectedBy) REFERENCES dept_admin(id) ON DELETE SET NULL;
