<?php
require_once __DIR__ . '/../../auth/middleware.php';
checkAccess(['Registrar']);

header('Content-Type: application/json');

try {
    $conn = db();
    
    // Query the buildings table
    $query = "SELECT building_name, department, number_of_floors, created_at FROM buildings ORDER BY department, building_name";
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Error fetching buildings: " . $conn->error);
    }
    
    // Create a temporary file
    $tempFile = tempnam(sys_get_temp_dir(), 'buildings_');
    $csvFile = fopen($tempFile, 'w');
    
    // Add UTF-8 BOM for Excel compatibility
    fprintf($csvFile, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add header row
    fputcsv($csvFile, ['Building Name', 'Department', 'Number of Floors', 'Created At']);
    
    // Add data rows
    while ($row = $result->fetch_assoc()) {
        fputcsv($csvFile, [
            $row['building_name'],
            $row['department'],
            $row['number_of_floors'],
            $row['created_at']
        ]);
    }
    
    fclose($csvFile);
    
    // Create a random file name
    $fileName = 'buildings_export_' . date('Ymd_His') . '.csv';
    
    // Move the temp file to the uploads directory
    $uploadDir = __DIR__ . '/../../uploads/exports/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $destFile = $uploadDir . $fileName;
    rename($tempFile, $destFile);
    
    // Return the file URL
    $fileUrl = '../uploads/exports/' . $fileName;
    
    echo json_encode([
        'status' => 'success',
        'fileName' => $fileName,
        'fileUrl' => $fileUrl
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
