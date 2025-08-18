<?php
require '../auth/middleware.php';
checkAccess(['Teacher']);

// Initialize response array
$response = [
    'valid' => false,
    'equipment' => null,
    'locations' => [],
    'message' => ''
];

// Check for required parameter
if (!isset($_GET['name']) || empty($_GET['name'])) {
    $response['message'] = 'Equipment name is required';
    echo json_encode($response);
    exit;
}

// Get equipment name from request
$equipmentName = $_GET['name'];

// Use centralized database connection
$conn = db();

// Prepare SQL to find equipment by name and get all its locations
$sql = "SELECT e.id, e.name, e.description, e.category, r.room_name, b.building_name 
        FROM equipment e
        LEFT JOIN room_equipment re ON e.id = re.equipment_id
        LEFT JOIN rooms r ON re.room_id = r.id
        LEFT JOIN buildings b ON r.building_id = b.id
        WHERE e.name LIKE ?";

// Prepare statement
$stmt = $conn->prepare($sql);
$searchName = "%$equipmentName%";
$stmt->bind_param("s", $searchName);
$stmt->execute();
$result = $stmt->get_result();

// Check if equipment exists with this name
if ($result->num_rows > 0) {
    // Equipment with this name exists
    $response['valid'] = true;
    $response['message'] = 'Equipment found';

    // Get all locations for this equipment
    $locations = [];
    $equipmentDetails = null;

    while ($row = $result->fetch_assoc()) {
        // Store basic equipment info if not already set
        if ($equipmentDetails === null) {
            $equipmentDetails = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'category' => $row['category']
            ];
        }

        // Add location if room and building are set
        if (!empty($row['room_name']) && !empty($row['building_name'])) {
            $locations[] = [
                'room' => $row['room_name'],
                'building' => $row['building_name']
            ];
        }
    }

    $response['equipment'] = $equipmentDetails;
    $response['locations'] = $locations;
} else {
    // Equipment not found with this name
    $response['message'] = 'Equipment name not found';
}

// Close database connection
$stmt->close();
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
