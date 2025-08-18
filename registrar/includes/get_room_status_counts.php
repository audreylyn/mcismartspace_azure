<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../auth/middleware.php';
checkAccess(['Registrar']);

try {
    if (!isset($_GET['room_id'])) {
        throw new Exception('Room ID is required');
    }

    $room_id = filter_var($_GET['room_id'], FILTER_VALIDATE_INT);
    if (!$room_id) {
        throw new Exception('Invalid room ID');
    }

    $conn = db();

    $sql = "SELECT 
        SUM(CASE WHEN status = 'working' THEN 1 ELSE 0 END) as working,
        SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance,
        SUM(CASE WHEN status = 'needs_repair' THEN 1 ELSE 0 END) as needs_repair,
        SUM(CASE WHEN status = 'missing' THEN 1 ELSE 0 END) as missing
        FROM room_equipment 
        WHERE room_id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        throw new Exception("Error executing query: " . $stmt->error);
    }

    $counts = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'working' => (int)$counts['working'],
        'maintenance' => (int)$counts['maintenance'],
        'needs_repair' => (int)$counts['needs_repair'],
        'missing' => (int)$counts['missing']
    ]);

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

