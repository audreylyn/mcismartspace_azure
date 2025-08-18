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

        if ($building_name && $department && $number_of_floors !== false && $number_of_floors > 0) {
            $stmt = $conn->prepare("INSERT INTO buildings (building_name, department, number_of_floors) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $building_name, $department, $number_of_floors);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Building added successfully!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $_SESSION['error_message'] = "Error adding building: " . $stmt->error;
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Please fill all fields with valid values.";
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

