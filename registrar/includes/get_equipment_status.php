<?php
// Prevent any HTML output or warnings from appearing in the response
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header before any output
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../auth/middleware.php';
    checkAccess(['Registrar']);

    if (!isset($_GET['id'])) {
        throw new Exception('Equipment ID is required');
    }

    $equipment_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if (!$equipment_id) {
        throw new Exception('Invalid equipment ID');
    }

    $last_updated = isset($_GET['last_updated']) ? $_GET['last_updated'] : '';

    // Database connection
    $conn = db();

    // Prepare and execute query
    $stmt = $conn->prepare("
        SELECT 
            eu.status, 
            eu.last_updated, 
            (SELECT notes FROM equipment_audit ea WHERE ea.equipment_id = eu.equipment_id ORDER BY ea.audit_timestamp DESC LIMIT 1) as notes
        FROM equipment_units eu
        WHERE eu.unit_id = ?
    ");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $equipment_id);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Result failed: " . $stmt->error);
    }

    if ($row = $result->fetch_assoc()) {
        // Check if data has changed since last update
        $hasChanged = true;
        if ($last_updated !== '') {
            $db_last_updated = strtotime($row['last_updated']);
            $client_last_updated = strtotime($last_updated);
            $hasChanged = $db_last_updated > $client_last_updated;
        }

        echo json_encode([
            'success' => true,
            'hasChanged' => $hasChanged,
            'status' => $row['status'],
            'notes' => $row['notes'],
            'last_updated' => $row['last_updated']
        ]);
    } else {
        throw new Exception('Equipment not found');
    }

    $stmt->close();
} catch (Exception $e) {
    // Log error for debugging
    error_log("Error in get_equipment_status.php: " . $e->getMessage());

    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

