<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../auth/middleware.php';
checkAccess(['Registrar']);

try {
    $conn = db();

    $sql = "SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'working' THEN 1 ELSE 0 END) as working,
        SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance,
        SUM(CASE WHEN status = 'needs_repair' THEN 1 ELSE 0 END) as needs_repair,
        SUM(CASE WHEN status = 'missing' THEN 1 ELSE 0 END) as missing
        FROM room_equipment";

    $result = $conn->query($sql);
    if (!$result) {
        throw new Exception("Error executing query: " . $conn->error);
    }

    $counts = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'total' => (int)$counts['total'],
        'working' => (int)$counts['working'],
        'maintenance' => (int)$counts['maintenance'],
        'needs_repair' => (int)$counts['needs_repair'],
        'missing' => (int)$counts['missing']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
}
