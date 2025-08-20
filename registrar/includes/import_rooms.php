<?php
session_start();
require_once __DIR__ . '/../../auth/middleware.php';

// Create a new database connection
$db = db();

// Set up response structure
$response = [
    'status' => 'error',
    'message' => 'An unknown error occurred.',
    'new_rooms' => [],
    'errors' => []
];

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = "Invalid request method. Only POST is allowed.";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Check if the form was submitted with the right parameters
if (!isset($_POST['importSubmit'])) {
    $response['message'] = "Missing required form parameter.";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Validate file upload
if (!isset($_FILES['file'])) {
    $response['message'] = "No file was uploaded.";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Check for upload errors
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
        UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
        UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
        UPLOAD_ERR_NO_FILE => "No file was uploaded.",
        UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
        UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
        UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload."
    ];
    
    $errorMessage = "File upload error: ";
    $errorMessage .= isset($uploadErrors[$_FILES['file']['error']]) 
        ? $uploadErrors[$_FILES['file']['error']] 
        : "Unknown error code: " . $_FILES['file']['error'];
    
    $response['message'] = $errorMessage;
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Validate file type
$fileType = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
if (strtolower($fileType) !== 'csv') {
    $response['message'] = "Only CSV files are allowed. Received file type: " . $fileType;
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Check if the uploaded file is not empty
if ($_FILES['file']['size'] <= 0) {
    $response['message'] = "Uploaded file is empty.";
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Process the CSV file
try {
    $fileHandle = @fopen($_FILES['file']['tmp_name'], 'r');
    if (!$fileHandle) {
        throw new Exception("Failed to open the uploaded file.");
    }
    
    // Try to read the header row
    $headerRow = fgetcsv($fileHandle);
    if ($headerRow === false) {
        throw new Exception("Failed to read the CSV header row.");
    }
    
    // Get valid buildings from the database
    $buildings_result = $db->query("SELECT id, building_name FROM buildings");
    if (!$buildings_result) {
        throw new Exception("Database error: " . $db->error);
    }
    
    $validBuildings = [];
    while ($building = $buildings_result->fetch_assoc()) {
        $validBuildings[strtolower(trim($building['building_name']))] = $building['id'];
    }
    
    // Process the data rows
    $importedIds = [];
    $lineNumber = 1; // Start at 2 (header is line 1)
    
    while (($row = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {
        $lineNumber++;
        
        // Check if row has enough columns
        if (count($row) < 4) {
            $response['errors'][] = "Insufficient columns in CSV. Expected at least 4 columns.";
            continue;
        }
        
        // Trim and validate each field
        $room_name = trim($row[0]);
        $room_type = trim($row[1]);
        $capacity = trim($row[2]);
        $building_name = trim($row[3]);
        
        // Validate all required fields are present
        $validationErrors = [];
        if (empty($room_name)) $validationErrors[] = "Room name is required";
        if (empty($room_type)) $validationErrors[] = "Room type is required";
        if (empty($capacity)) $validationErrors[] = "Capacity is required";
        if (!is_numeric($capacity) || $capacity <= 0) $validationErrors[] = "Capacity must be a positive number";
        // Add maximum capacity validation based on room type
        if ($room_type == 'Classroom' && is_numeric($capacity) && $capacity > 50) {
            $validationErrors[] = "Classroom capacity cannot exceed 50 people";
        } elseif (is_numeric($capacity) && $capacity > 500) {
            $validationErrors[] = "Room capacity cannot exceed 500 people";
        }
        if (empty($building_name)) $validationErrors[] = "Building name is required";
        
        if (!empty($validationErrors)) {
            $response['errors'][] = "Room data invalid: " . implode(", ", $validationErrors) . " (CSV row $lineNumber)";
            continue;
        }
        
        // Check if building exists
        $building_key = strtolower($building_name);
        if (!isset($validBuildings[$building_key])) {
            $response['errors'][] = "Building '$building_name' not found in database.";
            continue;
        }
        $building_id = $validBuildings[$building_key];
        
        // Check for duplicate room - now checking room_name, room_type and building_id
        $stmt = $db->prepare("SELECT COUNT(*) FROM rooms WHERE room_name = ? AND room_type = ? AND building_id = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $db->error);
        }
        $stmt->bind_param("ssi", $room_name, $room_type, $building_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            $response['errors'][] = "Room '$room_name' of type '$room_type' in '$building_name' already exists.";
        } else {
            // Insert the new room
            $stmt = $db->prepare("INSERT INTO rooms (room_name, room_type, capacity, building_id) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Database error: " . $db->error);
            }
            $stmt->bind_param("ssii", $room_name, $room_type, $capacity, $building_id);
            if ($stmt->execute()) {
                $importedIds[] = $stmt->insert_id;
            } else {
                $response['errors'][] = "Failed to add room '$room_name'. Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    fclose($fileHandle);
    
    // If any records were successfully imported, fetch their details
    if (!empty($importedIds)) {
        $response['status'] = 'success';
        $ids_placeholder = implode(',', array_fill(0, count($importedIds), '?'));
        $types = str_repeat('i', count($importedIds));
        
        $sql = "SELECT b.building_name, b.department, b.number_of_floors, r.id as room_id, r.room_name, r.room_type, r.capacity, r.building_id 
                FROM rooms r 
                JOIN buildings b ON r.building_id = b.id 
                WHERE r.id IN ($ids_placeholder)";
        
        $stmt = $db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $db->error);
        }
        
        $stmt->bind_param($types, ...$importedIds);
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()) {
            $response['new_rooms'][] = $row;
        }
        $stmt->close();
    }
    
    // Build response message
    $message = '';
    $successCount = count($response['new_rooms']);
    $errorCount = count($response['errors']);
    
    if ($successCount > 0) {
        $message .= "<strong>" . $successCount . " room" . ($successCount > 1 ? "s" : "") . " imported successfully.</strong>";
        $response['status'] = 'success';
    } else {
        $message .= "<strong>No rooms were imported.</strong>";
    }
    
    if ($errorCount > 0) {
        $message .= "<br><br>" . $errorCount . " record" . ($errorCount > 1 ? "s" : "") . " failed:";
        
        // Create a formatted list of errors
        $message .= "<ul style='margin-top: 8px; padding-left: 20px;'>";
        foreach ($response['errors'] as $error) {
            $message .= "<li>" . $error . "</li>";
        }
        $message .= "</ul>";
    }
    
    $response['message'] = $message;
    
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = "Error processing import: " . $e->getMessage();
    if (isset($fileHandle) && $fileHandle) {
        fclose($fileHandle);
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
