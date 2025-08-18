<?php
require_once __DIR__ . '/../../auth/middleware.php';

// Initialize messages
$error_message = '';
$success_message = '';

try {
    $conn = db();

    // Handle form submission for adding equipment
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_equipment'])) {
        $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
        $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
        $category = trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING));

        if ($name && $description && $category) {
            $stmt = $conn->prepare("INSERT INTO equipment (name, description, category) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $description, $category);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Equipment added successfully!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $_SESSION['error_message'] = "Error adding equipment: " . $stmt->error;
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

    // Handle form submission for assigning equipment
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_equipment'])) {
        $room_id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
        $equipment_id = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

        if ($room_id && $equipment_id && $quantity > 0) {
            $stmt = $conn->prepare("INSERT INTO room_equipment (room_id, equipment_id, quantity) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $room_id, $equipment_id, $quantity);

            if ($stmt->execute()) {
                // Add audit record
                $audit_stmt = $conn->prepare("INSERT INTO equipment_audit (equipment_id, action, notes) VALUES (?, 'Assigned', ?)");
                $notes = "Assigned to room ID: " . $room_id;
                $audit_stmt->bind_param("is", $equipment_id, $notes);
                $audit_stmt->execute();
                $audit_stmt->close();

                $_SESSION['success_message'] = "Equipment assigned successfully!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $_SESSION['error_message'] = "Error assigning equipment: " . $stmt->error;
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = "Invalid assignment data provided.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Fetch all equipment
    $stmt = $conn->prepare("SELECT id, name, description, category FROM equipment ORDER BY id ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    $equipment_list = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    $error_message = $e->getMessage();
}

