<?php
// Prevent any HTML output or warnings from appearing in the response
error_reporting(0);
ini_set('display_errors', 0);

// Set JSON header before any output
header('Content-Type: application/json');

try {
    require_once __DIR__ . '/../../auth/middleware.php';
    checkAccess(['Registrar']);

    // Get JSON data
    $jsonData = file_get_contents('php://input');
    if (!$jsonData) {
        throw new Exception('No data received');
    }

    $data = json_decode($jsonData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    if (!isset($data['equipment_id']) || !isset($data['status'])) {
        throw new Exception('Missing required fields: equipment_id and status are required');
    }

    // Validate and sanitize input
    $equipment_id = filter_var($data['equipment_id'], FILTER_VALIDATE_INT);
    if (!$equipment_id) {
        throw new Exception('Invalid equipment ID');
    }

    $notes = isset($data['notes']) ? filter_var($data['notes'], FILTER_SANITIZE_STRING) : '';
    $status = filter_var($data['status'], FILTER_SANITIZE_STRING);

    // Validate status
    $valid_statuses = ['working', 'needs_repair', 'maintenance', 'missing'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception('Invalid status value. Must be one of: ' . implode(', ', $valid_statuses));
    }

    // Database connection
    $conn = db();

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Get the equipment_id from equipment_units table first
        $get_equipment_id = $conn->prepare("SELECT equipment_id FROM equipment_units WHERE unit_id = ?");
        if (!$get_equipment_id) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $get_equipment_id->bind_param("i", $equipment_id);
        if (!$get_equipment_id->execute()) {
            throw new Exception("Error getting equipment ID: " . $get_equipment_id->error);
        }

        $result = $get_equipment_id->get_result();
        if (!$result || $result->num_rows === 0) {
            throw new Exception("No equipment found with ID: $equipment_id");
        }

        $actual_equipment_id = $result->fetch_assoc()['equipment_id'];
        $get_equipment_id->close();

        // Update equipment audit information
        $stmt = $conn->prepare("UPDATE equipment_units SET status = ?, last_updated = NOW() WHERE unit_id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("si", $status, $equipment_id);

        if (!$stmt->execute()) {
            throw new Exception("Error updating equipment: " . $stmt->error);
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception("No equipment found with ID: $equipment_id");
        }

        // Add audit log entry with the correct equipment_id
        $audit_stmt = $conn->prepare("INSERT INTO equipment_audit (equipment_id, action, notes) VALUES (?, 'Updated', ?)");
        if (!$audit_stmt) {
            throw new Exception("Prepare audit failed: " . $conn->error);
        }

        $audit_stmt->bind_param("is", $actual_equipment_id, $notes);

        if (!$audit_stmt->execute()) {
            throw new Exception("Error adding audit log: " . $audit_stmt->error);
        }

        // Get the updated timestamp
        $time_stmt = $conn->prepare("SELECT last_updated FROM equipment_units WHERE unit_id = ?");
        if (!$time_stmt) {
            throw new Exception("Prepare time query failed: " . $conn->error);
        }

        $time_stmt->bind_param("i", $equipment_id);

        if (!$time_stmt->execute()) {
            throw new Exception("Error getting timestamp: " . $time_stmt->error);
        }

        $time_result = $time_stmt->get_result();
        $last_updated = $time_result->fetch_assoc()['last_updated'];

        // Commit transaction
        $conn->commit();

        // Close statements
        $stmt->close();
        $audit_stmt->close();
        $time_stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Equipment audit updated successfully',
            'last_updated' => $last_updated
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
} catch (Exception $e) {
    // Log error for debugging
    error_log("Error in update_equipment_audit.php: " . $e->getMessage());

    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
