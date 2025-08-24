<?php
// Process approve/reject actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve_request'])) {
        $requestId = intval($_POST['request_id']);
        $adminId = $_SESSION['user_id'] ?? null;
        $adminFirstName = $_SESSION['firstname'] ?? null;
        $adminLastName = $_SESSION['lastname'] ?? null;

        // Fetch the request details to check for conflicts
        $sql = "SELECT RoomID, StartTime, EndTime FROM room_requests WHERE RequestID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $requestId);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        $stmt->close();

        if ($request) {
            $roomId = $request['RoomID'];
            $startTime = $request['StartTime'];
            $endTime = $request['EndTime'];

            // Check for overlapping approved requests
            $sql = "SELECT rr.*, CASE 
                        WHEN rr.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
                        WHEN rr.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
                    END as RequesterName,
                    CASE 
                        WHEN rr.StudentID IS NOT NULL THEN 'Student'
                        WHEN rr.TeacherID IS NOT NULL THEN 'Teacher'
                    END as RequesterType
                    FROM room_requests rr
                    LEFT JOIN student s ON rr.StudentID = s.StudentID
                    LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
                    WHERE RoomID = ?
                    AND Status = 'approved'
                    AND ((StartTime < ? AND EndTime > ?) OR (StartTime < ? AND EndTime > ?))
                    AND RequestID != ?"; // Exclude the current request
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssi", $roomId, $endTime, $startTime, $startTime, $endTime, $requestId);
            $stmt->execute();
            $conflictResult = $stmt->get_result();

            if ($conflictResult->num_rows > 0) {
                $conflict = $conflictResult->fetch_assoc();
                $conflictMessage = "Cannot approve: Room is already occupied by " . htmlspecialchars($conflict['RequesterName']) . " (" . $conflict['RequesterType'] . ").";
                $_SESSION['error_message'] = $conflictMessage;
                $stmt->close();
                header("Location: dept_room_approval.php");
                exit();
            }
            $stmt->close();
        }

        // If no conflict, proceed with approval
        $sql = "UPDATE room_requests SET Status = 'approved', ApprovedBy = ?, ApproverFirstName = ?, ApproverLastName = ? WHERE RequestID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issi", $adminId, $adminFirstName, $adminLastName, $requestId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Request approved successfully";
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['reject_request'])) {
        $requestId = intval($_POST['request_id']);
        $rejectionReason = trim($_POST['rejection_reason']);
        $adminId = $_SESSION['user_id'] ?? null;
        $adminFirstName = $_SESSION['firstname'] ?? null;
        $adminLastName = $_SESSION['lastname'] ?? null;

        $sql = "UPDATE room_requests SET Status = 'rejected', RejectionReason = ?, RejectedBy = ?, RejecterFirstName = ?, RejecterLastName = ? WHERE RequestID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissi", $rejectionReason, $adminId, $adminFirstName, $adminLastName, $requestId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Request rejected successfully";
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    // Redirect to prevent form resubmission
    header("Location: dept_room_approval.php");
    exit();
}