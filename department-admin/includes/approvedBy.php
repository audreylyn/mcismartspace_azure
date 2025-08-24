<?php
// Process approve/reject actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve_request'])) {
        $requestId = intval($_POST['request_id']);
        $adminId = $_SESSION['user_id'];
        $adminFirstName = $_SESSION['firstname'];
        $adminLastName = $_SESSION['lastname'];
        
        // Update request with approved status and admin info
        $sql = "UPDATE room_requests SET Status = 'approved', ApprovedBy = ?, ApproverFirstName = ?, ApproverLastName = ? WHERE RequestID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $adminId, $adminFirstName, $adminLastName, $requestId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Request approved successfully";
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['reject_request'])) {
        $requestId = intval($_POST['request_id']);
        $rejectionReason = trim($_POST['rejection_reason']);
        $adminId = $_SESSION['user_id'];
        $adminFirstName = $_SESSION['firstname'];
        $adminLastName = $_SESSION['lastname'];

        $sql = "UPDATE room_requests SET Status = 'rejected', RejectionReason = ?, RejectedBy = ?, RejecterFirstName = ?, RejecterLastName = ? WHERE RequestID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sisss", $rejectionReason, $adminId, $adminFirstName, $adminLastName, $requestId);

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