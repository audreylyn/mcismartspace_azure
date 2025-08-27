<?php
// This file handles data loading for the registrar dashboard

// Initialize database connection
function loadDashboardData() {
    $conn = db();
    $data = [];
    
    // Get total buildings count
    $data['buildings_count'] = 0;
    $sql = "SELECT COUNT(*) as count FROM buildings";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $data['buildings_count'] = $row['count'];
    }

    // Get total rooms count
    $data['rooms_count'] = 0;
    $sql = "SELECT COUNT(*) as count FROM rooms";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $data['rooms_count'] = $row['count'];
    }

    // Get total equipment count
    $data['equipment_count'] = 0;
    $sql = "SELECT COUNT(*) as count FROM equipment";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $data['equipment_count'] = $row['count'];
    }

    // Get department count
    $data['department_count'] = 0;
    $sql = "SELECT COUNT(DISTINCT department) as count FROM buildings";
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $data['department_count'] = $row['count'];
    }

    // Get room status counts
    $data['room_status_counts'] = [
        'available' => 0,
        'occupied' => 0,
        'maintenance' => 0
    ];
    $sql = "SELECT RoomStatus, COUNT(*) as count FROM rooms GROUP BY RoomStatus";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['RoomStatus']);
        if (isset($data['room_status_counts'][$status])) {
            $data['room_status_counts'][$status] = $row['count'];
        }
    }

    // Get equipment status counts
    $data['equipment_status'] = [
        'working' => 0,
        'needs_repair' => 0,
        'maintenance' => 0,
        'missing' => 0
    ];
    $sql = "SELECT status, COUNT(*) as count FROM equipment_units GROUP BY status";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $status = strtolower($row['status']);
        if (isset($data['equipment_status'][$status])) {
            $data['equipment_status'][$status] = $row['count'];
        }
    }

    // Get department statistics
    $data['department_stats'] = [];
    $sql = "SELECT 
                b.department, 
                COUNT(DISTINCT b.id) as building_count,
                COUNT(DISTINCT r.id) as room_count,
                SUM(r.capacity) as total_capacity
            FROM 
                buildings b
            LEFT JOIN 
                rooms r ON b.id = r.building_id
            GROUP BY 
                b.department
            ORDER BY 
                room_count DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $data['department_stats'][] = $row;
    }

    // Get room type distribution
    $data['room_types'] = [];
    $sql = "SELECT room_type, COUNT(*) as count FROM rooms GROUP BY room_type ORDER BY count DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $data['room_types'][$row['room_type']] = $row['count'];
    }

    // Get equipment category distribution
    $data['equipment_categories'] = [];
    $sql = "SELECT category, COUNT(*) as count FROM equipment GROUP BY category ORDER BY count DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $data['equipment_categories'][$row['category']] = $row['count'];
    }

    // Get the 5 most recent equipment issues
    $data['recent_issues'] = [];
    $sql = "SELECT 
                ei.id, 
                e.name as equipment_name, 
                ei.issue_type, 
                ei.status, 
                ei.reported_at,
                rm.room_name, 
                b.building_name, 
                b.department
            FROM 
                equipment_issues ei
            LEFT JOIN 
                equipment_units re ON ei.unit_id = re.unit_id
            JOIN 
                equipment e ON re.equipment_id = e.id
            LEFT JOIN 
                rooms rm ON re.room_id = rm.id
            LEFT JOIN 
                buildings b ON rm.building_id = b.id
            ORDER BY 
                ei.reported_at DESC
            LIMIT 5";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $data['recent_issues'][] = $row;
    }

    // Get buildings with the most rooms
    $data['largest_buildings'] = [];
    $sql = "SELECT 
                b.id, 
                b.building_name, 
                b.department, 
                COUNT(r.id) as room_count,
                SUM(r.capacity) as total_capacity
            FROM 
                buildings b
            LEFT JOIN 
                rooms r ON b.id = r.building_id
            GROUP BY 
                b.id, b.building_name, b.department
            ORDER BY 
                room_count DESC
            LIMIT 5";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $data['largest_buildings'][] = $row;
    }

    // Get rooms with the highest capacity
    $data['high_capacity_rooms'] = [];
    $sql = "SELECT 
                r.id, 
                r.room_name, 
                r.room_type, 
                r.capacity, 
                b.building_name, 
                b.department
            FROM 
                rooms r
            JOIN 
                buildings b ON r.building_id = b.id
            ORDER BY 
                r.capacity DESC
            LIMIT 5";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $data['high_capacity_rooms'][] = $row;
    }

    // Count rooms by department
    $data['rooms_by_department'] = [];
    $sql = "SELECT 
                b.department, 
                COUNT(r.id) as room_count 
            FROM 
                buildings b
            LEFT JOIN 
                rooms r ON b.id = r.building_id
            GROUP BY 
                b.department
            ORDER BY 
                room_count DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $data['rooms_by_department'][$row['department']] = $row['room_count'];
    }
    
    return $data;
}
