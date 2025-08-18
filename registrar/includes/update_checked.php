<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../auth/middleware.php';

try {
    $conn = db();

    $data = json_decode(file_get_contents('php://input'), true);
    $equipment_id = $data['equipment_id'];
    $checked = $data['checked'];

    $stmt = $conn->prepare("UPDATE room_equipment SET checked = ? WHERE id = ?");
    $stmt->bind_param("ii", $checked, $equipment_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Error updating status: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

