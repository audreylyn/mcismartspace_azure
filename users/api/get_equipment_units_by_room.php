<?php
header('Content-Type: application/json');

// Add authentication middleware
require_once '../../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

require_once '../../auth/dbh.inc.php';

if (!isset($_GET['room_id']) || !filter_var($_GET['room_id'], FILTER_VALIDATE_INT)) {
    echo json_encode(['success' => false, 'error' => 'A valid room ID is required.']);
    exit();
}

$room_id = $_GET['room_id'];

$sql = "SELECT eu.unit_id, eu.serial_number, e.name AS equipment_name FROM equipment_units eu JOIN equipment e ON eu.equipment_id = e.id WHERE eu.room_id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
    exit();
}

$stmt->bind_param('i', $room_id);
$stmt->execute();
$result = $stmt->get_result();
$equipment_units = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();

// Return the proper JSON format that the frontend expects
echo json_encode(['success' => true, 'units' => $equipment_units]);
