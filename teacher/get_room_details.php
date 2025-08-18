<?php
require '../auth/middleware.php';
checkAccess(['Teacher']);

db();

// Validate input
if (!isset($_GET['room_id']) || !is_numeric($_GET['room_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
    exit;
}

$roomId = (int)$_GET['room_id'];

try {
    // Get room data with building information
    $stmt = $conn->prepare("
        SELECT r.*, b.building_name 
        FROM rooms r
        JOIN buildings b ON r.building_id = b.id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $roomResult = $stmt->get_result();

    if ($roomResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit;
    }

    $room = $roomResult->fetch_assoc();

    // Get equipment for this room
    $equipment = [];
    $equipmentStmt = $conn->prepare("
        SELECT e.name, re.quantity, re.status, e.description
        FROM room_equipment re
        JOIN equipment e ON re.equipment_id = e.id
        WHERE re.room_id = ?
    ");
    $equipmentStmt->bind_param("i", $roomId);
    $equipmentStmt->execute();
    $equipmentResult = $equipmentStmt->get_result();

    while ($equipmentRow = $equipmentResult->fetch_assoc()) {
        $equipment[] = $equipmentRow;
    }

    // Check if room is currently occupied but will be available later
    $availableTime = null;
    if ($room['RoomStatus'] === 'occupied') {
        $availabilityStmt = $conn->prepare("
            SELECT MIN(EndTime) as next_available 
            FROM room_requests 
            WHERE RoomID = ? AND Status = 'approved' AND EndTime > NOW()
        ");
        $availabilityStmt->bind_param("i", $roomId);
        $availabilityStmt->execute();
        $availabilityResult = $availabilityStmt->get_result();

        if ($availabilityRow = $availabilityResult->fetch_assoc()) {
            if ($availabilityRow['next_available']) {
                $availableTime = "Available after " . date('M d, Y h:i A', strtotime($availabilityRow['next_available']));
            }
        }
    }

    // Return JSON response
    echo json_encode([
        'success' => true,
        'room' => $room,
        'equipment' => $equipment,
        'availableTime' => $availableTime
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
