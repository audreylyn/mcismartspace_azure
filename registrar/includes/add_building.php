<?php
require_once __DIR__ . '/../../auth/middleware.php';

// Initialize messages
$error_message = '';
$success_message = '';

try {
    $conn = db();

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_building'])) {
        // Sanitize and validate inputs
        $building_name = trim(filter_input(INPUT_POST, 'building_name', FILTER_SANITIZE_STRING));
        $department = trim(filter_input(INPUT_POST, 'department', FILTER_SANITIZE_STRING));
        $number_of_floors = filter_input(INPUT_POST, 'number_of_floors', FILTER_VALIDATE_INT);

        // Validate input values
        if (empty($building_name) || empty($department) || $number_of_floors === false) {
            $_SESSION['error_message'] = "Please fill all fields with valid values.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        // Validate number of floors (maximum 7)
        if ($number_of_floors <= 0 || $number_of_floors > 7) {
            $_SESSION['error_message'] = "Number of floors must be between 1 and 7.";
            header("Location: " . $_SERVER['PHP_SELF']);
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
            $_SESSION['error_message'] = "A building with the name '{$building_name}' already exists in the {$department} department.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        // If all validations pass, insert the new building
        $stmt = $conn->prepare("INSERT INTO buildings (building_name, department, number_of_floors) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $building_name, $department, $number_of_floors);

        if ($stmt->execute()) {
            $stmt->close();
            $_SESSION['success_message'] = "Building added successfully!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $stmt->close();
            $_SESSION['error_message'] = "Error adding building: " . $stmt->error;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Fetch buildings
    $stmt = $conn->prepare("SELECT * FROM buildings ORDER BY created_at ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $error_message = $e->getMessage();
}

