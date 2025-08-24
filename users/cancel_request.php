<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

// Check if the request is a POST request and contains the request ID
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request_id']) && isset($_POST['cancel_request'])) {
    $requestId = intval($_POST['request_id']);
    $userId = $_SESSION['user_id']; // Get the current user's ID
    $userRole = $_SESSION['role']; // Get the user's role

    // Field name depends on user role
    $idField = $userRole === 'Student' ? 'StudentID' : 'TeacherID';
    
    // Check if the request belongs to the current user
    $checkSql = "SELECT RequestID FROM room_requests WHERE RequestID = ? AND $idField = ? AND Status = 'pending'";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ii", $requestId, $userId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // The request exists and belongs to the current student, proceed with deletion
        $deleteSql = "DELETE FROM room_requests WHERE RequestID = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $requestId);

        if ($deleteStmt->execute()) {
            $_SESSION['success_message'] = "Your room reservation request has been successfully cancelled.";
        } else {
            $_SESSION['error_message'] = "Error cancelling request: " . $deleteStmt->error;
        }

        $deleteStmt->close();
    } else {
        $_SESSION['error_message'] = "Invalid request or you don't have permission to cancel this request.";
    }

    $checkStmt->close();
} else {
    $_SESSION['error_message'] = "Invalid request.";
}

// Close database connection
$conn->close();

// Redirect back to the reservation history page
header("Location: users_reservation_history.php");
exit();
