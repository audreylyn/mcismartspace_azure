<?php
require '../../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

header('Content-Type: application/json');
$conn = db();

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'equipment':
            // Get all equipment with their categories
            $sql = "SELECT DISTINCT id, name, category FROM equipment ORDER BY category, name";
            $result = $conn->query($sql);
            
            $equipment = [];
            while ($row = $result->fetch_assoc()) {
                $equipment[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $equipment]);
            break;

        case 'buildings':
            // Get all buildings
            $sql = "SELECT id, building_name, department FROM buildings ORDER BY building_name";
            $result = $conn->query($sql);
            
            $buildings = [];
            while ($row = $result->fetch_assoc()) {
                $buildings[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $buildings]);
            break;

        case 'rooms':
            $building_id = $_GET['building_id'] ?? '';
            
            if (empty($building_id)) {
                echo json_encode(['success' => false, 'message' => 'Building ID required']);
                break;
            }
            
            // Get rooms for specific building
            $sql = "SELECT id, room_name, room_type, capacity FROM rooms WHERE building_id = ? ORDER BY room_name";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $building_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $rooms = [];
            while ($row = $result->fetch_assoc()) {
                $rooms[] = $row;
            }
            
            echo json_encode(['success' => true, 'data' => $rooms]);
            break;

        case 'equipment_in_room':
            $room_id = $_GET['room_id'] ?? '';
            $equipment_id = $_GET['equipment_id'] ?? '';
            
            if (empty($room_id) || empty($equipment_id)) {
                echo json_encode(['success' => false, 'message' => 'Room ID and Equipment ID required']);
                break;
            }
            
            // Check if equipment exists in the specified room
            $sql = "SELECT eu.*, e.name as equipment_name, r.room_name, b.building_name 
                    FROM equipment_units eu
                    JOIN equipment e ON eu.equipment_id = e.id
                    JOIN rooms r ON eu.room_id = r.id
                    JOIN buildings b ON r.building_id = b.id
                    WHERE eu.room_id = ? AND eu.equipment_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $room_id, $equipment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $equipment_data = $result->fetch_assoc();
                echo json_encode(['success' => true, 'exists' => true, 'data' => $equipment_data]);
            } else {
                // Get equipment name for better error message
                $equipment_name_sql = "SELECT name FROM equipment WHERE id = ?";
                $equipment_stmt = $conn->prepare($equipment_name_sql);
                $equipment_stmt->bind_param("i", $equipment_id);
                $equipment_stmt->execute();
                $equipment_result = $equipment_stmt->get_result();
                $equipment_row = $equipment_result->fetch_assoc();
                $equipment_name = $equipment_row ? $equipment_row['name'] : 'Unknown Equipment';
                
                // Get current room name
                $room_name_sql = "SELECT room_name, building_name FROM rooms r JOIN buildings b ON r.building_id = b.id WHERE r.id = ?";
                $room_stmt = $conn->prepare($room_name_sql);
                $room_stmt->bind_param("i", $room_id);
                $room_stmt->execute();
                $room_result = $room_stmt->get_result();
                $room_data = $room_result->fetch_assoc();
                $room_name = $room_data['room_name'] ?? 'Unknown Room';
                $building_name = $room_data['building_name'] ?? 'Unknown Building';
                
                // Find alternative rooms where this equipment is available
                $alternatives_sql = "SELECT r.room_name, b.building_name, COUNT(eu.unit_id) as quantity
                                   FROM equipment_units eu
                                   JOIN rooms r ON eu.room_id = r.id
                                   JOIN buildings b ON r.building_id = b.id
                                   WHERE eu.equipment_id = ?
                                   GROUP BY r.id, b.id
                                   ORDER BY b.building_name, r.room_name
                                   LIMIT 5";
                $alt_stmt = $conn->prepare($alternatives_sql);
                $alt_stmt->bind_param("i", $equipment_id);
                $alt_stmt->execute();
                $alt_result = $alt_stmt->get_result();
                
                $alternatives = [];
                while ($alt_row = $alt_result->fetch_assoc()) {
                    $alternatives[] = $alt_row;
                }
                
                echo json_encode([
                    'success' => true, 
                    'exists' => false, 
                    'message' => 'Equipment not found in this room',
                    'equipment_name' => $equipment_name,
                    'room_name' => $room_name,
                    'building_name' => $building_name,
                    'alternatives' => $alternatives
                ]);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
