<?php
require_once __DIR__ . '/../../auth/middleware.php';

try {
    $conn = db();

    // Set proper charset
    $conn->set_charset("utf8mb4");

    // Handle delete request
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete_room'])) {
        $room_id = filter_input(INPUT_GET, 'delete_room', FILTER_VALIDATE_INT);

        if ($room_id) {
            $delete_sql = "DELETE FROM rooms WHERE id = ?";
            $delete_stmt = $conn->prepare($delete_sql);
            $delete_stmt->bind_param("i", $room_id);

            if ($delete_stmt->execute()) {
                $_SESSION['success_message'] = "Room deleted successfully";
            } else {
                $_SESSION['error_message'] = "Error deleting room";
            }
            $delete_stmt->close();

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Fetch buildings and rooms using prepared statement with ORDER BY
    $sql = "SELECT buildings.id AS building_id, buildings.building_name, buildings.department, buildings.number_of_floors, 
                   rooms.id AS room_id, rooms.room_name, rooms.room_type, rooms.capacity 
            FROM buildings 
            INNER JOIN rooms ON buildings.id = rooms.building_id
            ORDER BY buildings.building_name ASC, rooms.room_name ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Handle form submission for editing
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update') {
        $room_id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
        $room_name = trim(filter_input(INPUT_POST, 'room_name', FILTER_SANITIZE_STRING));
        $room_type = trim(filter_input(INPUT_POST, 'room_type', FILTER_SANITIZE_STRING));
        $capacity = filter_input(INPUT_POST, 'capacity', FILTER_VALIDATE_INT);
        $building_id = filter_input(INPUT_POST, 'building_id', FILTER_VALIDATE_INT);

        if ($room_id && $room_name && $room_type && $capacity !== false && $building_id !== false) {
            // Check if room exists before updating
            $check_sql = "SELECT id FROM rooms WHERE id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $room_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $update_sql = "UPDATE rooms SET room_name = ?, room_type = ?, capacity = ?, building_id = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ssiii", $room_name, $room_type, $capacity, $building_id, $room_id);

                if ($update_stmt->execute()) {
                    $_SESSION['success_message'] = "Room updated successfully";
                } else {
                    $_SESSION['error_message'] = "Error updating room";
                }
                $update_stmt->close();
            }
            $check_stmt->close();

            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
} catch (Exception $e) {
    // Log error and display user-friendly message
    error_log($e->getMessage());
    echo "<div class='notification is-danger'>An error occurred. Please try again later.</div>";
}

