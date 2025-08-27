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

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_equipment'])) {
        $room_id = filter_input(INPUT_POST, 'room_id', FILTER_VALIDATE_INT);
        $equipment_id = filter_input(INPUT_POST, 'equipment_id', FILTER_VALIDATE_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
        $serial_number = trim($_POST['serial_number'] ?? ''); // optional input field
    
        if ($room_id && $equipment_id && $quantity > 0) {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("INSERT INTO equipment_units (equipment_id, room_id, serial_number) VALUES (?, ?, ?)");

                for ($i = 0; $i < $quantity; $i++) {
                    $current_serial_number = '';
                    if (!empty($serial_number)) {
                        // Use provided serial as a base and append a number
                        $current_serial_number = $serial_number . '-' . ($i + 1);
                    } else {
                        // Auto-generate a unique serial number if none is provided
                        $current_serial_number = 'EQ-' . strtoupper(uniqid());
                    }
                    
                    $stmt->bind_param("iis", $equipment_id, $room_id, $current_serial_number);
                    $stmt->execute();
                }
                $stmt->close();

                // Commit the transaction if all units were inserted successfully
                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['error_message'] = "An error occurred during assignment: " . $e->getMessage();
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
    
            // Audit trail
            $audit_stmt = $conn->prepare("INSERT INTO equipment_audit (equipment_id, action, notes) VALUES (?, 'Assigned', ?)");
            $notes = "Assigned $quantity unit(s) to room ID: " . $room_id;
            $audit_stmt->bind_param("is", $equipment_id, $notes);
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
    

    // Fetch all equipment and their room assignments
    $sql = "SELECT eu.unit_id, e.name, e.description, e.category, eu.serial_number, eu.status, eu.created_at,
       r.room_name, b.building_name
FROM equipment_units eu
JOIN equipment e ON eu.equipment_id = e.id
JOIN rooms r ON eu.room_id = r.id
JOIN buildings b ON r.building_id = b.id
ORDER BY eu.unit_id DESC;
";

    $result = $conn->query($sql);
    $equipment_list = $result->fetch_all(MYSQLI_ASSOC);

    // Fetch rooms for dropdown
    $rooms_sql = "SELECT r.id, r.room_name, b.building_name 
                  FROM rooms r
                  JOIN buildings b ON r.building_id = b.id";
    $rooms_result = $conn->query($rooms_sql);
    $rooms_list = $rooms_result->fetch_all(MYSQLI_ASSOC);

    // Fetch all equipment for dropdown (no restrictions)
    $equipment_sql = "SELECT id, name FROM equipment";
    $equipment_result = $conn->query($equipment_sql);
    $equipment_dropdown = $equipment_result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $error_message = $e->getMessage();
}

