<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Set current page for navigation highlighting
$current_page = 'dashboard';

$conn = db();

// Get admin ID and department
$admin_id = $_SESSION['user_id'];
$department = $_SESSION['department'] ?? '';

// If department is not set in session, try to get it from database for backward compatibility
if (empty($department)) {
    $stmt = $conn->prepare("SELECT Department FROM dept_admin WHERE AdminID = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $department = $row['Department'];
        // Store in session for future use
        $_SESSION['department'] = $department;
    }
}

// For testing/debugging: Use a default department if none is found
if (empty($department)) {
    $department = 'Business Administration'; // Replace with one from your database
    // Uncomment this line to see the user_id issue
    // echo "Warning: No department found for admin ID: " . $admin_id;
}

// Get teacher count - all teachers in the current department
$teacher_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM teacher WHERE Department = ?");
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $teacher_count = $row['count'];
}

// Get student count - all students in the current department
$student_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM student WHERE Department = ?");
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $student_count = $row['count'];
}

// Get room count - direct count from buildings and rooms
$room_count = 0;
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM rooms r
    JOIN buildings b ON r.building_id = b.id
    WHERE b.department = ?
");
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $room_count = $row['count'];
}

// Get equipment count - direct count from equipments in rooms of the department
$equipment_count = 0;
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM room_equipment re
    JOIN rooms r ON re.room_id = r.id
    JOIN buildings b ON r.building_id = b.id
    WHERE b.department = ?
");
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $equipment_count = $row['count'];
}

// Calculate pending room requests count for the current department
$pending_requests = 0;
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM room_requests rr
    LEFT JOIN student s ON rr.StudentID = s.StudentID
    LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
    WHERE rr.Status = 'pending' AND (s.Department = ? OR t.Department = ?)
");
$stmt->bind_param("ss", $department, $department);
$stmt->execute();
$result = $stmt->get_result();
$pending_requests = 0;
while ($row = $result->fetch_assoc()) {
    $pending_requests += $row['count'];
}

// Calculate unresolved equipment issues for the current department
$unresolved_issues = 0;
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM equipment_issues ei
    JOIN student s ON ei.student_id = s.StudentID AND s.Department = ?
    WHERE (ei.status = 'pending' OR ei.status = 'in_progress')
    UNION
    SELECT COUNT(*) as count 
    FROM equipment_issues ei
    JOIN teacher t ON ei.teacher_id = t.TeacherID AND t.Department = ?
    WHERE (ei.status = 'pending' OR ei.status = 'in_progress')
");
$stmt->bind_param("ss", $department, $department);
$stmt->execute();
$result = $stmt->get_result();
$unresolved_issues = 0;
while ($row = $result->fetch_assoc()) {
    $unresolved_issues += $row['count'];
}

// Get equipment status statistics
$equipment_stats = [];
$stmt = $conn->prepare("
    SELECT re.status, COUNT(*) as count 
    FROM room_equipment re
    JOIN rooms r ON re.room_id = r.id
    JOIN buildings b ON r.building_id = b.id
    WHERE b.department = ?
    GROUP BY re.status
");
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $equipment_stats[$row['status']] = $row['count'];
}

// Get equipment issues statistics
$issue_stats = [];
$stmt = $conn->prepare("
    SELECT ei.status, COUNT(*) as count 
    FROM equipment_issues ei
    JOIN room_equipment re ON ei.equipment_id = re.equipment_id
    JOIN rooms r ON re.room_id = r.id
    JOIN buildings b ON r.building_id = b.id
    WHERE b.department = ?
    GROUP BY ei.status
");
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $issue_stats[$row['status']] = $row['count'];
}

// Get monthly room request trends (last 6 months)
$monthly_stats = [];
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(RequestDate, '%Y-%m') as month,
        COUNT(*) as count 
    FROM room_requests r
    JOIN rooms rm ON r.RoomID = rm.id
    JOIN buildings b ON rm.building_id = b.id
    WHERE b.department = ?
    AND RequestDate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(RequestDate, '%Y-%m')
    ORDER BY month ASC
");
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $monthly_stats[$row['month']] = $row['count'];
}

// Get recent equipment issues
$recent_issues = [];
$stmt = $conn->prepare("
    SELECT ei.id, e.name as equipment_name, ei.issue_type, ei.status, ei.reported_at
    FROM equipment_issues ei
    JOIN equipment e ON ei.equipment_id = e.id
    JOIN room_equipment re ON ei.equipment_id = re.equipment_id
    JOIN rooms r ON re.room_id = r.id
    JOIN buildings b ON r.building_id = b.id
    WHERE b.department = ?
    ORDER BY ei.reported_at DESC
    LIMIT 5
");
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_issues[] = $row;
}

// Get room request statistics for current department
$room_stats = [];
$stmt = $conn->prepare("
    SELECT rr.Status, COUNT(*) as count 
    FROM room_requests rr
    LEFT JOIN student s ON rr.StudentID = s.StudentID
    LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
    WHERE (s.Department = ? OR t.Department = ?)
    GROUP BY rr.Status
");
$stmt->bind_param("ss", $department, $department);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    if (isset($room_stats[$row['Status']])) {
        $room_stats[$row['Status']] += $row['count'];
    } else {
        $room_stats[$row['Status']] = $row['count'];
    }
}

// Get equipment issues statistics for current department
$issue_stats = [];
$stmt = $conn->prepare("
    SELECT ei.status, COUNT(*) as count 
    FROM equipment_issues ei
    JOIN student s ON ei.student_id = s.StudentID
    WHERE s.Department = ?
    GROUP BY ei.status
    UNION
    SELECT ei.status, COUNT(*) as count 
    FROM equipment_issues ei
    JOIN teacher t ON ei.teacher_id = t.TeacherID
    WHERE t.Department = ?
    GROUP BY ei.status
");
$stmt->bind_param("ss", $department, $department);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    if (isset($issue_stats[$row['status']])) {
        $issue_stats[$row['status']] += $row['count'];
    } else {
        $issue_stats[$row['status']] = $row['count'];
    }
}

// Get monthly room request trends (last 6 months) for current department
$monthly_stats = [];
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(rr.RequestDate, '%Y-%m') as month,
        COUNT(*) as count 
    FROM room_requests rr
    JOIN student s ON rr.StudentID = s.StudentID
    WHERE s.Department = ?
    AND rr.RequestDate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(rr.RequestDate, '%Y-%m')
    UNION
    SELECT 
        DATE_FORMAT(rr.RequestDate, '%Y-%m') as month,
        COUNT(*) as count 
    FROM room_requests rr
    JOIN teacher t ON rr.TeacherID = t.TeacherID
    WHERE t.Department = ?
    AND rr.RequestDate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(rr.RequestDate, '%Y-%m')
    ORDER BY month ASC
");
$stmt->bind_param("ss", $department, $department);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    if (isset($monthly_stats[$row['month']])) {
        $monthly_stats[$row['month']] += $row['count'];
    } else {
        $monthly_stats[$row['month']] = $row['count'];
    }
}

// Get recent equipment issues for current department
$recent_issues = [];
$stmt = $conn->prepare("
    SELECT 
        ei.id, 
        e.name as equipment_name, 
        ei.issue_type, 
        ei.status, 
        ei.reported_at,
        COALESCE(s.FirstName, t.FirstName) as first_name,
        COALESCE(s.LastName, t.LastName) as last_name,
        CASE 
            WHEN s.StudentID IS NOT NULL THEN 'Student' 
            WHEN t.TeacherID IS NOT NULL THEN 'Teacher' 
            ELSE 'Unknown' 
        END as user_type
    FROM equipment_issues ei
    JOIN equipment e ON ei.equipment_id = e.id
    LEFT JOIN student s ON ei.student_id = s.StudentID AND s.Department = ?
    LEFT JOIN teacher t ON ei.teacher_id = t.TeacherID AND t.Department = ?
    WHERE (s.StudentID IS NOT NULL OR t.TeacherID IS NOT NULL)
    ORDER BY ei.reported_at DESC
    LIMIT 5
");
$stmt->bind_param("ss", $department, $department);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_issues[] = $row;
}

// Get recent room usage data - approved requests only
$recent_room_usage = [];
$stmt = $conn->prepare("
    SELECT rr.RequestID, rr.StartTime, rr.EndTime, rr.ActivityName, r.room_name, b.building_name, 
        CASE 
            WHEN rr.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
            WHEN rr.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
        END as user_name,
        CASE 
            WHEN rr.StudentID IS NOT NULL THEN 'Student'
            WHEN rr.TeacherID IS NOT NULL THEN 'Teacher'
        END as user_role,
        CASE 
            WHEN NOW() BETWEEN rr.StartTime AND rr.EndTime THEN 'Active Now'
            WHEN NOW() > rr.EndTime THEN 'Completed'
            ELSE 'Upcoming'
        END as usage_status
    FROM room_requests rr
    JOIN rooms r ON rr.RoomID = r.id
    JOIN buildings b ON r.building_id = b.id
    LEFT JOIN student s ON rr.StudentID = s.StudentID
    LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
    WHERE rr.Status = 'approved'
    AND (s.Department = ? OR t.Department = ?)
    ORDER BY 
        CASE 
            WHEN NOW() BETWEEN rr.StartTime AND rr.EndTime THEN 0
            WHEN NOW() < rr.StartTime THEN 1
            ELSE 2
        END, 
        rr.StartTime DESC
    LIMIT 5
");
$stmt->bind_param("ss", $department, $department);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recent_room_usage[] = $row;
}

// Get rooms with most equipment issues
$rooms_with_most_issues = [];
$stmt = $conn->prepare("
    SELECT 
        r.id as room_id,
        r.room_name,
        b.building_name,
        COUNT(ei.id) as issue_count
    FROM equipment_issues ei
    JOIN room_equipment re ON ei.equipment_id = re.equipment_id
    JOIN rooms r ON re.room_id = r.id
    JOIN buildings b ON r.building_id = b.id
    WHERE b.department = ?
    GROUP BY r.id, r.room_name, b.building_name
    ORDER BY issue_count DESC
    LIMIT 5
");
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $rooms_with_most_issues[] = $row;
}

// Get most requested rooms
$most_requested_rooms = [];
$stmt = $conn->prepare("
    SELECT 
        r.id as room_id,
        r.room_name,
        b.building_name,
        COUNT(rr.RequestID) as request_count,
        ROUND((COUNT(CASE WHEN rr.Status = 'approved' THEN 1 ELSE NULL END) / COUNT(rr.RequestID)) * 100, 1) as approval_rate
    FROM room_requests rr
    JOIN rooms r ON rr.RoomID = r.id
    JOIN buildings b ON r.building_id = b.id
    WHERE b.department = ?
    GROUP BY r.id, r.room_name, b.building_name
    ORDER BY request_count DESC
    LIMIT 5
");
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $most_requested_rooms[] = $row;
}

// No need to close the connection, it's managed by the db() function.
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #1e5631;
            /* Dark forest green */
            --secondary-color: #d4af37;
            /* Timeless gold */
            --accent-color: #0f2d1a;
            /* Darker green */
            --success-color: #2e7d32;
            /* Forest green */
            --danger-color: #c62828;
            /* Deep red */
            --warning-color: #f9a825;
            /* Gold-yellow */
            --info-color: #0277bd;
            /* Deep blue */
            --text-color: #333;
            --text-muted: #6c757d;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
        }

        .dashboard-container {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 20px;
            padding: 20px;
        }

        .stat-card {
            grid-column: span 3;
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 24px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            border-left: 4px solid var(--primary-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .chart-card {
            grid-column: span 6;
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            transition: var(--transition);
        }

        .chart-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .chart-card-full {
            grid-column: span 12;
        }

        .issues-card {
            grid-column: span 12;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 1rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-bottom: 15px;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .card-title .icon {
            margin-right: 8px;
            color: var(--primary-color);
        }

        .card-content {
            position: relative;
        }

        .issue-item {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 12px;
            background-color: rgba(0, 0, 0, 0.02);
            transition: var(--transition);
        }

        .issue-item:hover {
            background-color: rgba(0, 0, 0, 0.04);
        }

        .issue-item:last-child {
            margin-bottom: 0;
        }

        .issue-title {
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 1.05rem;
        }

        .issue-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .badge {
            display: inline-block;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-pending {
            background-color: var(--warning-color);
            color: #000;
        }

        .badge-in-progress {
            background-color: var(--info-color);
            color: #fff;
        }

        .badge-resolved {
            background-color: var(--success-color);
            color: #fff;
        }

        .badge-rejected {
            background-color: var(--danger-color);
            color: #fff;
        }

        .action-link {
            display: inline-block;
            padding: 8px 16px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            text-align: center;
            margin-top: 16px;
        }

        .action-link:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .stat-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2.5rem;
            color: var(--secondary-color);
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .stat-card {
                grid-column: span 6;
            }

            .chart-card {
                grid-column: span 12;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                grid-template-columns: repeat(6, 1fr);
                gap: 15px;
                padding: 15px;
            }

            .stat-card {
                grid-column: span 3;
            }

            .chart-card,
            .issues-card {
                grid-column: span 6;
            }
        }

        @media (max-width: 576px) {
            .stat-card {
                grid-column: span 6;
            }
        }

        .quick-links {
            grid-column: span 12;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .quick-link-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            display: flex;
            flex-direction: column;
            transition: var(--transition);
            height: 100%;
        }

        .quick-link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .quick-link-header {
            margin-bottom: 16px;
        }

        .quick-link-title {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 4px;
        }

        .quick-link-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .quick-link-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 16px;
            opacity: 0.8;
        }

        .quick-link-footer {
            margin-top: auto;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .quick-link-btn {
            flex: 1;
            padding: 8px 12px;
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            border-radius: 6px;
            text-align: center;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .quick-link-btn:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* Main section adjustments */
        .section.main-section {
            padding: 0;
            margin: 0;
            max-width: 100%;
        }

        /* Style updates to match site theme */
        .stat-card .stat-icon {
            color: var(--primary-color);
        }

        .stat-value {
            color: var(--primary-color);
        }

        .chart-header h3 {
            color: var(--primary-color);
        }

        .status-badge.pending {
            background-color: var(--warning-color);
        }

        .status-badge.in-progress {
            background-color: var(--info-color);
        }

        .status-badge.resolved {
            background-color: var(--success-color);
        }

        .status-badge.rejected {
            background-color: var(--danger-color);
        }

        .issues-header h3 {
            color: var(--primary-color);
        }
        
        /* Dropdown fix */
        .navbar-item.dropdown.is-active .navbar-dropdown {
            display: block;
        }
        
        .navbar-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            z-index: 20;
            background-color: white;
            border-radius: 4px;
            box-shadow: 0 0.5em 1em -0.125em rgba(10, 10, 10, 0.1), 0 0 0 1px rgba(10, 10, 10, 0.02);
        }
        
        /* Table styles */
        .dashboard-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        
        .dashboard-table th,
        .dashboard-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .dashboard-table th {
            font-weight: 600;
            color: var(--text-color);
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .dashboard-table tr:last-child td {
            border-bottom: none;
        }
        
        .dashboard-table tr:hover {
            background-color: rgba(0, 0, 0, 0.01);
        }
        
        .has-text-right {
            text-align: right !important;
        }
        
        .has-text-centered {
            text-align: center !important;
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        .badge-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .badge-warning {
            background-color: var(--warning-color);
            color: black;
        }
        
        .badge-danger {
            background-color: var(--danger-color);
            color: white;
        }
    </style>
</head>

<body>
    <div id="app">
        <?php include 'layout/topnav.php'; ?>
        <?php include 'layout/sidebar.php'; ?>

        <section class="section main-section">
            <div class="dashboard-container">

                <!-- Statistics Cards -->
                <div class="stat-card">
                    <i class="mdi mdi-account-tie stat-icon"></i>
                    <div class="stat-value"><?php echo $teacher_count; ?></div>
                    <div class="stat-label">Teachers</div>
                </div>

                <div class="stat-card">
                    <i class="mdi mdi-account-group stat-icon"></i>
                    <div class="stat-value"><?php echo $student_count; ?></div>
                    <div class="stat-label">Students</div>
                </div>

                <div class="stat-card">
                    <i class="mdi mdi-clock-alert stat-icon"></i>
                    <div class="stat-value"><?php echo $pending_requests; ?></div>
                    <div class="stat-label">Pending Requests</div>
                </div>

                <div class="stat-card">
                    <i class="mdi mdi-alert-circle stat-icon"></i>
                    <div class="stat-value"><?php echo $unresolved_issues; ?></div>
                    <div class="stat-label">Unresolved Issues</div>
                </div>

                <!-- First Row Charts -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-chart-pie"></i></span>
                            Room Request Status
                        </h3>
                    </div>
                    <div class="card-content">
                        <canvas id="roomStatusChart" height="220"></canvas>
                    </div>
                </div>

                <!-- Second Row Charts -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-chart-bar"></i></span>
                            Equipment Issues by Status
                        </h3>
                    </div>
                    <div class="card-content">
                        <canvas id="issuesStatusChart" height="220"></canvas>
                    </div>
                </div>

                <!-- Full Width Chart -->
                <div class="chart-card chart-card-full">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-chart-line"></i></span>
                            Monthly Room Request Trends
                        </h3>
                    </div>
                    <div class="card-content">
                        <canvas id="monthlyTrendsChart" height="100"></canvas>
                    </div>
                </div>

                <!-- Recent Issues Section -->
                <div class="chart-card issues-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-clipboard-alert"></i></span>
                            Recent Equipment Issues
                        </h3>
                    </div>
                    <div class="card-content">
                        <?php if (count($recent_issues) > 0): ?>
                            <?php foreach ($recent_issues as $issue): ?>
                                <div class="issue-item">
                                    <div class="issue-title"><?php echo htmlspecialchars($issue['equipment_name']); ?> - <?php echo htmlspecialchars($issue['issue_type']); ?></div>
                                    <div class="issue-meta">
                                        <span>Reported: <?php echo date('M j, Y g:i A', strtotime($issue['reported_at'])); ?></span>
                                        <span class="badge badge-<?php echo strtolower($issue['status']); ?>"><?php echo $issue['status']; ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <a href="dept_equipment_report.php" class="action-link">View All Issues</a>
                        <?php else: ?>
                            <p class="text-center py-4">No recent equipment issues reported.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Recent Room Usage -->
            <div class="chart-card issues-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <span class="icon"><i class="mdi mdi-door"></i></span>
                        Recent Room Usage
                    </h3>
                </div>
                <div class="card-content">
                    <?php if (count($recent_room_usage) > 0): ?>
                        <?php foreach ($recent_room_usage as $usage): ?>
                            <div class="issue-item">
                                <div class="issue-title">
                                    <?php echo htmlspecialchars($usage['room_name']); ?>, 
                                    <?php echo htmlspecialchars($usage['building_name']); ?> - 
                                    <?php echo htmlspecialchars($usage['ActivityName']); ?>
                                </div>
                                <div class="issue-meta">
                                    <span>User: <?php echo htmlspecialchars($usage['user_name']); ?> (<?php echo $usage['user_role']; ?>)</span>
                                    <span>Time: <?php echo date('M j, Y g:i A', strtotime($usage['StartTime'])); ?></span>
                                    <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $usage['usage_status'])); ?>"><?php echo $usage['usage_status']; ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <a href="dept_room_usage_logs.php" class="action-link">View All Usage</a>
                    <?php else: ?>
                        <p class="text-center py-4">No recent room usage data found.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Two column layout for Rooms with Most Issues and Most Requested Rooms -->
            <div class="dashboard-container">
                <!-- Rooms with Most Issues -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-alert-circle"></i></span>
                            Rooms with Most Issues
                        </h3>
                    </div>
                    <div class="card-content table-responsive">
                        <table class="dashboard-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Room</th>
                                    <th>Building</th>
                                    <th class="has-text-right">Issue Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($rooms_with_most_issues) > 0): ?>
                                    <?php foreach ($rooms_with_most_issues as $room): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($room['room_name']); ?></td>
                                            <td><?php echo htmlspecialchars($room['building_name']); ?></td>
                                            <td class="has-text-right">
                                                <span class="badge badge-danger"><?php echo $room['issue_count']; ?></span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3" class="has-text-centered">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Most Requested Rooms -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-bookmark-check"></i></span>
                            Most Requested Rooms
                        </h3>
                    </div>
                    <div class="card-content table-responsive">
                        <table class="dashboard-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Room</th>
                                    <th>Building</th>
                                    <th class="has-text-right">Requests</th>
                                    <th class="has-text-right">Approval Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($most_requested_rooms) > 0): ?>
                                    <?php foreach ($most_requested_rooms as $room): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($room['room_name']); ?></td>
                                            <td><?php echo htmlspecialchars($room['building_name']); ?></td>
                                            <td class="has-text-right"><?php echo $room['request_count']; ?></td>
                                            <td class="has-text-right">
                                                <span class="badge badge-<?php echo ($room['approval_rate'] >= 70) ? 'success' : (($room['approval_rate'] >= 40) ? 'warning' : 'danger'); ?>">
                                                    <?php echo $room['approval_rate']; ?>%
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="has-text-centered">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script type="text/javascript" src="../public/js/main.min.js"></script>
    <script>
        // Toggle dropdown menu for sidebar
        function toggleIcon(el) {
            el.classList.toggle("active");
            var icon = el.querySelector(".toggle-icon i");
            if (icon.classList.contains("mdi-plus")) {
                icon.classList.remove("mdi-plus");
                icon.classList.add("mdi-minus");
            } else {
                icon.classList.remove("mdi-minus");
                icon.classList.add("mdi-plus");
            }
            var submenu = el.nextElementSibling;
            if (submenu) {
                if (submenu.style.display === "block") {
                    submenu.style.display = "none";
                } else {
                    submenu.style.display = "block";
                }
            }
        }
        
        // Toggle dropdown menu for navbar
        function toggleDropdown(el) {
            const dropdown = el.closest('.dropdown');
            dropdown.classList.toggle('is-active');
        }
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('.dropdown.is-active');
            dropdowns.forEach(function(dropdown) {
                // If the click is outside the dropdown
                if (!dropdown.contains(event.target)) {
                    dropdown.classList.remove('is-active');
                }
            });
        });

        // Initialize charts once the DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize sidebar dropdowns
            const dropdowns = document.querySelectorAll('a.dropdown');
            dropdowns.forEach(function(dropdown) {
                const submenu = dropdown.nextElementSibling;
                if (submenu) {
                    submenu.style.display = "none";
                }
            });
            
            // Room Status Chart - Updated colors to match site theme
            const roomStatusData = {
                pending: <?php echo isset($room_stats['pending']) ? $room_stats['pending'] : 0; ?>,
                approved: <?php echo isset($room_stats['approved']) ? $room_stats['approved'] : 0; ?>,
                rejected: <?php echo isset($room_stats['rejected']) ? $room_stats['rejected'] : 0; ?>
            };

            const roomStatusChart = new Chart(
                document.getElementById('roomStatusChart'), {
                    type: 'pie',
                    data: {
                        labels: ['Pending', 'Approved', 'Rejected'],
                        datasets: [{
                            data: [roomStatusData.pending, roomStatusData.approved, roomStatusData.rejected],
                            backgroundColor: ['#d4af37', '#1e5631', '#c62828'], // Gold, Dark Green, Deep Red
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                }
            );

            // Equipment Issues Chart - Updated colors to match site theme
            const issueStatusData = {
                pending: <?php echo isset($issue_stats['pending']) ? $issue_stats['pending'] : 0; ?>,
                in_progress: <?php echo isset($issue_stats['in_progress']) ? $issue_stats['in_progress'] : 0; ?>,
                resolved: <?php echo isset($issue_stats['resolved']) ? $issue_stats['resolved'] : 0; ?>,
                rejected: <?php echo isset($issue_stats['rejected']) ? $issue_stats['rejected'] : 0; ?>
            };

            const issuesStatusChart = new Chart(
                document.getElementById('issuesStatusChart'), {
                    type: 'pie',
                    data: {
                        labels: ['Pending', 'In Progress', 'Resolved', 'Rejected'],
                        datasets: [{
                            data: [
                                issueStatusData.pending,
                                issueStatusData.in_progress,
                                issueStatusData.resolved,
                                issueStatusData.rejected
                            ],
                            backgroundColor: [
                                '#d4af37', // Gold for pending
                                '#3e7650', // Medium green for in progress
                                '#1e5631', // Dark green for resolved
                                '#c62828' // Deep red for rejected
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    font: {
                                        size: 12
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                }
            );

            // Monthly Trends Chart - Updated colors to match site theme
            const labels = <?php echo json_encode(array_keys($monthly_stats)); ?>;
            const data = <?php echo json_encode(array_values($monthly_stats)); ?>;

            const monthlyTrendsChart = new Chart(
                document.getElementById('monthlyTrendsChart'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Room Requests',
                            data: data,
                            borderColor: '#1e5631', // Dark green for line
                            backgroundColor: 'rgba(30, 86, 49, 0.15)', // Transparent green for area
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: '#d4af37', // Gold for data points
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    display: true,
                                    color: 'rgba(0,0,0,0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 12
                                    }
                                }
                            }
                        }
                    }
                }
            );
        });
    </script>
</body>

</html>