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
        $purchased_at = !empty($_POST['purchased_at']) ? $_POST['purchased_at'] : null;
        $notes = !empty($_POST['notes']) ? trim($_POST['notes']) : null;

        if ($room_id && $equipment_id && $quantity > 0) {
            for ($i = 1; $i <= $quantity; $i++) {
                // Generate serial number if not provided
                $serial = strtoupper("EQ-" . $equipment_id . "-" . $room_id . "-" . uniqid());

                $stmt = $conn->prepare("INSERT INTO equipment_units (equipment_id, room_id, serial_number, purchased_at, notes) 
                                        VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $equipment_id, $room_id, $serial, $purchased_at, $notes);
                $stmt->execute();
                $stmt->close();
            }

            // Add audit record
            $audit_stmt = $conn->prepare("INSERT INTO equipment_audit (equipment_id, action, notes) VALUES (?, 'Assigned', ?)");
            $audit_notes = "Assigned {$quantity} unit(s) to room ID: " . $room_id;
            $audit_stmt->bind_param("is", $equipment_id, $audit_notes);
            $audit_stmt->execute();
            $audit_stmt->close();

            $_SESSION['success_message'] = "Equipment assigned successfully!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
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

