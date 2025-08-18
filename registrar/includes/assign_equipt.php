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
            // Check if equipment is already assigned
            $check_stmt = $conn->prepare("SELECT id FROM room_equipment WHERE room_id = ? AND equipment_id = ?");
            $check_stmt->bind_param("ii", $room_id, $equipment_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $_SESSION['error_message'] = "This equipment is already assigned to this room.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }

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

    // Fetch all equipment and their room assignments
    $sql = "SELECT e.id, e.name, e.description, e.category, 
            COALESCE(r.room_name, 'Unassigned') as room_name,
            COALESCE(b.building_name, '') as building_name,
            COALESCE(re.quantity, 0) as quantity
            FROM equipment e
            INNER JOIN room_equipment re ON e.id = re.equipment_id
            INNER JOIN rooms r ON re.room_id = r.id
            INNER JOIN buildings b ON r.building_id = b.id
            ORDER BY e.created_at DESC";

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

