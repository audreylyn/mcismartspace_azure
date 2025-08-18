<?php
require '../auth/middleware.php';
checkAccess(['Teacher']);

// Include room status handler to automatically update room statuses
require_once '../auth/room_status_handler.php';

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    db();

    // Validate required fields
    $requiredFields = ['activityName', 'purpose', 'participants', 'reservationDate', 'reservationTime', 'endTime', 'roomId'];
    $missing = [];

    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        $_SESSION['error_message'] = "The following fields are required: " . implode(', ', $missing);
        header("Location: tc_browse_room.php");
        exit();
    }

    // Get form data
    $activityName = trim($_POST['activityName']);
    $purpose = trim($_POST['purpose']);
    $participants = intval($_POST['participants']);
    $reservationDate = $_POST['reservationDate'];
    $startTime = $_POST['reservationTime'];
    $endTime = $_POST['endTime'];
    $roomId = intval($_POST['roomId']);
    $teacherId = $_SESSION['user_id']; // Changed from studentId to teacherId

    // Validate data
    if (strlen($activityName) < 3) {
        $_SESSION['error_message'] = "Activity name must be at least 3 characters";
        header("Location: tc_browse_room.php");
        exit();
    }

    if (strlen($purpose) < 10) {
        $_SESSION['error_message'] = "Purpose must be at least 10 characters";
        header("Location: tc_browse_room.php");
        exit();
    }

    if ($participants < 1) {
        $_SESSION['error_message'] = "Number of participants must be at least 1";
        header("Location: tc_browse_room.php");
        exit();
    }

    // Format date and times for database
    $startTimeFormatted = $reservationDate . ' ' . $startTime . ':00';
    $endTimeFormatted = $reservationDate . ' ' . $endTime . ':00';

    // Check if start time is before end time
    $startTimestamp = strtotime($startTimeFormatted);
    $endTimestamp = strtotime($endTimeFormatted);

    if ($startTimestamp >= $endTimestamp) {
        $_SESSION['error_message'] = "End time must be after start time";
        header("Location: tc_browse_room.php");
        exit();
    }

    // Check if the room is available for the selected time
    $checkSql = "SELECT COUNT(*) as count 
                FROM room_requests 
                WHERE RoomID = ? 
                AND Status = 'approved' 
                AND (
                    (StartTime <= ? AND EndTime >= ?) OR 
                    (StartTime <= ? AND EndTime >= ?) OR 
                    (StartTime >= ? AND EndTime <= ?)
                )";

    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param(
        "issssss",
        $roomId,
        $endTimeFormatted,
        $startTimeFormatted,  // Case 1: New booking starts during existing booking
        $startTimeFormatted,
        $startTimeFormatted, // Case 2: New booking ends during existing booking
        $startTimeFormatted,
        $endTimeFormatted    // Case 3: New booking contains existing booking
    );
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $conflictCount = $checkResult->fetch_assoc()['count'];

    if ($conflictCount > 0) {
        $_SESSION['error_message'] = "This room is already booked for the selected time. Please choose another time or room.";
        header("Location: tc_browse_room.php");
        exit();
    }

    // Check room capacity
    $capacitySql = "SELECT capacity FROM rooms WHERE id = ?";
    $capacityStmt = $conn->prepare($capacitySql);
    $capacityStmt->bind_param("i", $roomId);
    $capacityStmt->execute();
    $capacityResult = $capacityStmt->get_result();

    if ($capacityResult->num_rows > 0) {
        $roomCapacity = $capacityResult->fetch_assoc()['capacity'];
        if ($participants > $roomCapacity) {
            $_SESSION['error_message'] = "The number of participants exceeds the room capacity of $roomCapacity";
            header("Location: tc_browse_room.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Selected room not found";
        header("Location: tc_browse_room.php");
        exit();
    }

    // Insert reservation request into database
    $insertSql = "INSERT INTO room_requests (TeacherID, RoomID, ActivityName, Purpose, StartTime, EndTime, NumberOfParticipants, Status, RequestDate) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param(
        "iissssi",
        $teacherId,
        $roomId,
        $activityName,
        $purpose,
        $startTimeFormatted,
        $endTimeFormatted,
        $participants
    );

    if ($insertStmt->execute()) {
        $_SESSION['success_message'] = "Your room reservation request has been submitted successfully. Please check the request status page for updates.";
        header("Location: tc_room_status.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error submitting request: " . $insertStmt->error;
        header("Location: tc_browse_room.php");
        exit();
    }

    // Close statements and connection
    $insertStmt->close();
    $checkStmt->close();
    $capacityStmt->close();
    $conn->close();
} else {
    // If not a POST request, redirect to the reservation form
    $_SESSION['error_message'] = "Invalid request method";
    header("Location: tc_browse_room.php");
    exit();
}
