<?php
// This script is included in reg_summary.php and handles adding/updating rooms.
// It should not be accessed directly.
if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
    exit("This script cannot be accessed directly.");
}

// Handle Add Room
if (isset($_POST['add_room'])) {
    $room_name = trim($_POST['room_name']);
    $room_type = trim($_POST['room_type']);
    $capacity = trim($_POST['capacity']);
    $building_id = trim($_POST['building_id']);

    if (empty($room_name) || empty($room_type) || empty($capacity) || empty($building_id)) {
        $_SESSION['error_message'] = "All fields are required.";
    } else if ($room_type == 'Classroom' && $capacity > 50) {
        $_SESSION['error_message'] = "Classroom capacity cannot exceed 50 people.";
    } else if ($capacity > 500) {
        $_SESSION['error_message'] = "Room capacity cannot exceed 500 people.";
    } else {
        // Check for duplicate room name, type within the same building
        $check_sql = "SELECT id FROM rooms WHERE room_name = ? AND room_type = ? AND building_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ssi", $room_name, $room_type, $building_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $_SESSION['error_message'] = "A room with this name and type already exists in the selected building.";
        } else {
            $sql = "INSERT INTO rooms (room_name, room_type, capacity, building_id) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssii", $room_name, $room_type, $capacity, $building_id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "New room added successfully.";
            } else {
                $_SESSION['error_message'] = "Error adding room: " . $conn->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
    header("Location: reg_summary.php");
    exit();
}

// Handle Update Room
if (isset($_POST['update_room'])) {
    $room_id = trim($_POST['room_id']);
    $room_name = trim($_POST['room_name']);
    $room_type = trim($_POST['room_type']);
    $capacity = trim($_POST['capacity']);
    $building_id = trim($_POST['building_id']);

    if (empty($room_id) || empty($room_name) || empty($room_type) || empty($capacity) || empty($building_id)) {
        $_SESSION['error_message'] = "All fields are required for updating.";
    } else if ($room_type == 'Classroom' && $capacity > 50) {
        $_SESSION['error_message'] = "Classroom capacity cannot exceed 50 people.";
    } else if ($capacity > 500) {
        $_SESSION['error_message'] = "Room capacity cannot exceed 500 people.";
    } else {
        // Check for duplicate room name, type within the same building, excluding the current room
        $check_sql = "SELECT id FROM rooms WHERE room_name = ? AND room_type = ? AND building_id = ? AND id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ssii", $room_name, $room_type, $building_id, $room_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $_SESSION['error_message'] = "Another room with this name and type already exists in the selected building.";
        } else {
            $sql = "UPDATE rooms SET room_name = ?, room_type = ?, capacity = ?, building_id = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssiii", $room_name, $room_type, $capacity, $building_id, $room_id);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Room updated successfully.";
            } else {
                $_SESSION['error_message'] = "Error updating room: " . $conn->error;
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
    header("Location: reg_summary.php");
    exit();
}

// Handle Delete Room
if (isset($_GET['delete_room'])) {
    $room_id = $_GET['delete_room'];
    if (empty($room_id)) {
        $_SESSION['error_message'] = "Invalid room ID.";
    } else {
        $sql = "DELETE FROM rooms WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $room_id);
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Room deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Error deleting room. It might be in use.";
        }
        $stmt->close();
    }
    header("Location: reg_summary.php");
    exit();
}
?>

