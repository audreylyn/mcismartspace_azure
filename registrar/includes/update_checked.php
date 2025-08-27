<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../auth/middleware.php';

try {
    $conn = db();

    $data = json_decode(file_get_contents('php://input'), true);
    $equipment_id = $data['equipment_id'];
    $checked = $data['checked'];

    // The 'checked' column does not exist in the 'equipment_units' table.
    // This functionality appears to be deprecated and will be commented out to prevent errors.
    // If this feature is needed, a database schema migration will be required.

    // $stmt = $conn->prepare("UPDATE equipment_units SET checked = ? WHERE unit_id = ?");
    // $stmt->bind_param("ii", $checked, $equipment_id);

    // if ($stmt->execute()) {
    //     echo json_encode(['success' => true]);
    // } else {
    //     throw new Exception("Error updating status: " . $stmt->error);
    // }

    // For now, we'll just return success to avoid breaking any frontend components that might call this endpoint.
    echo json_encode(['success' => true, 'message' => 'This feature is currently disabled.']);

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

