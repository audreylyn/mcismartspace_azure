<?php
require_once __DIR__ . '/../../auth/middleware.php';
checkAccess(['Registrar']);

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'An unknown error occurred.'
];

try {
    $conn = db();

    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Sanitize and validate inputs
        $building_name = trim(filter_input(INPUT_POST, 'building_name', FILTER_SANITIZE_STRING));
        $department = trim(filter_input(INPUT_POST, 'department', FILTER_SANITIZE_STRING));
        $number_of_floors = filter_input(INPUT_POST, 'number_of_floors', FILTER_VALIDATE_INT);

        // Validate input values
        if (empty($building_name) || empty($department) || $number_of_floors === false) {
            $response['message'] = "Please fill all fields with valid values.";
            echo json_encode($response);
            exit();
        }

        // Validate number of floors (maximum 7)
        if ($number_of_floors <= 0 || $number_of_floors > 7) {
            $response['message'] = "Number of floors must be between 1 and 7.";
            echo json_encode($response);
            exit();
        }
        
        // Check for duplicate building name in the same department
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM buildings WHERE building_name = ? AND department = ?");
        $check_stmt->bind_param("ss", $building_name, $department);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $row = $check_result->fetch_assoc();
        $check_stmt->close();
        
        if ($row['count'] > 0) {
            $response['message'] = "A building with the name '{$building_name}' already exists in the {$department} department.";
            echo json_encode($response);
            exit();
        }

        // If all validations pass, insert the new building
        $stmt = $conn->prepare("INSERT INTO buildings (building_name, department, number_of_floors) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $building_name, $department, $number_of_floors);

        if ($stmt->execute()) {
            // Get the ID of the new building
            $new_building_id = $stmt->insert_id;
            
            // Fetch the created building with timestamp
            $get_stmt = $conn->prepare("SELECT building_name, department, number_of_floors, created_at FROM buildings WHERE id = ?");
            $get_stmt->bind_param("i", $new_building_id);
            $get_stmt->execute();
            $result = $get_stmt->get_result();
            $new_building = $result->fetch_assoc();
            $get_stmt->close();
            
            // Format the response
            $response = [
                'status' => 'success',
                'message' => "Building added successfully!",
                'building' => [
                    'name' => $new_building['building_name'],
                    'department' => $new_building['department'],
                    'floors' => $new_building['number_of_floors'],
                    'created_at' => $new_building['created_at']
                ]
            ];
            
            $stmt->close();
        } else {
            $response['message'] = "Error adding building: " . $stmt->error;
            $stmt->close();
        }
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
