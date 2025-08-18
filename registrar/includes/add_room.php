<?php
require_once __DIR__ . '/../../auth/middleware.php';

// Initialize messages
$error_message = '';
$success_message = '';


try {
    $conn = db();

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_room'])) {
        // Sanitize and validate inputs
        $room_name = trim(filter_input(INPUT_POST, 'room_name', FILTER_SANITIZE_STRING));
        $room_type = trim(filter_input(INPUT_POST, 'room_type', FILTER_SANITIZE_STRING));
        $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
        $building_id = filter_input(INPUT_POST, 'building_id', FILTER_VALIDATE_INT);

        if ($room_name && $room_type && $capacity !== false && $capacity > 0 && $building_id !== false) {
            $stmt = $conn->prepare("INSERT INTO rooms (room_name, room_type, capacity, building_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssii", $room_name, $room_type, $capacity, $building_id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Room added successfully!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $_SESSION['error_message'] = "Error adding room: " . $stmt->error;
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

    // Fetch rooms with building names
    $stmt = $conn->prepare("SELECT rooms.*, buildings.building_name 
                           FROM rooms 
                           JOIN buildings ON rooms.building_id = buildings.id 
                           ORDER BY rooms.created_at ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $error_message = $e->getMessage();
}

