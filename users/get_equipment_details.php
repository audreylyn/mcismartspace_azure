<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

require_once '../auth/dbh.inc.php';
$conn = db();

// Validate input
if (!isset($_GET['room_id']) || !is_numeric($_GET['room_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
    exit;
}

$roomId = (int)$_GET['room_id'];

try {
    // Get equipment for this room
    $equipment = [];
    $equipmentStmt = $conn->prepare("
        SELECT e.name, re.quantity, re.status, e.description, e.category
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

    // Return JSON response
    echo json_encode([
        'success' => true,
        'equipment' => $equipment
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
