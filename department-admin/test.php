<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

// Initialize database connection
$conn = db();

// Get admin ID and department from session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$admin_id = $_SESSION['user_id'];
$department = $_SESSION['department'] ?? '';

if (empty($department)) {
    $stmt = $conn->prepare("SELECT Department FROM dept_admin WHERE AdminID = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $department = $row['Department'];
        $_SESSION['department'] = $department;
    } else {
        // For testing/debugging: Use a default department if none is found
        $department = 'Business Administration';
    }
}

// Get total buildings count for the department
$buildings_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM buildings WHERE department = ?");
$stmt->bind_param("s", $department);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $buildings_count = $row['count'];
}

// Get total rooms count for the department
$rooms_count = 0;
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
    $rooms_count = $row['count'];
}

// Get total equipment count for the department
$equipment_count = 0;
$stmt = $conn->prepare("
    SELECT COUNT(re.id) as count 
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

// Get total pending requests count for the department
$pending_requests_count = 0;
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
$row = $result->fetch_assoc();
$pending_requests_count = $row ? $row['count'] : 0;

// Get room requests by status
$room_status = [
    'approved' => 0,
    'pending' => 0,
    'rejected' => 0,
    'cancelled' => 0
];
$sql = "SELECT Status, COUNT(*) as count FROM room_requests GROUP BY Status";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $status = strtolower($row['Status']);
    if (isset($room_status[$status])) {
        $room_status[$status] = $row['count'];
    }
}

// Get equipment status counts
$equipment_status = [
    'operational' => 0,
    'maintenance' => 0,
    'broken' => 0,
    'replaced' => 0
];
$sql = "SELECT status, COUNT(*) as count FROM room_equipment GROUP BY status";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $status = strtolower($row['status']);
    if (isset($equipment_status[$status])) {
        $equipment_status[$status] = $row['count'];
    }
}

// Get recent room requests
$recent_requests = [];
$sql = "SELECT r.RequestID as id, r.RequestDate, r.Status, rm.room_name, b.building_name, 
               CASE 
                   WHEN r.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
                   WHEN r.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
                   ELSE 'Unknown'
               END as requester_name,
               CASE 
                   WHEN r.StudentID IS NOT NULL THEN 'Student' 
                   WHEN r.TeacherID IS NOT NULL THEN 'Teacher' 
                   ELSE 'Unknown' 
               END as requester_type
        FROM room_requests r
        LEFT JOIN rooms rm ON r.RoomID = rm.id
        LEFT JOIN buildings b ON rm.building_id = b.id
        LEFT JOIN student s ON r.StudentID = s.StudentID
        LEFT JOIN teacher t ON r.TeacherID = t.TeacherID
        ORDER BY r.RequestDate DESC
        LIMIT 5";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $recent_requests[] = $row;
}

// Get monthly room request statistics (last 6 months)
$monthly_requests = [];
$sql = "SELECT DATE_FORMAT(RequestDate, '%Y-%m') as month, COUNT(*) as count 
        FROM room_requests 
        WHERE RequestDate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(RequestDate, '%Y-%m')
        ORDER BY month ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $monthly_requests[$row['month']] = $row['count'];
}

// Get recent equipment issues
$recent_issues = [];
$sql = "SELECT ei.id, e.name as equipment_name, ei.issue_type, ei.status, ei.reported_at,
               CASE 
                   WHEN ei.student_id IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
                   WHEN ei.teacher_id IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
                   ELSE 'Unknown'
               END as reporter_name,
               CASE 
                   WHEN ei.student_id IS NOT NULL THEN 'Student' 
                   WHEN ei.teacher_id IS NOT NULL THEN 'Teacher' 
                   ELSE 'Unknown' 
               END as reporter_type,
               rm.room_name, b.building_name
        FROM equipment_issues ei
        JOIN equipment e ON ei.equipment_id = e.id
        LEFT JOIN room_equipment re ON ei.equipment_id = re.equipment_id
        LEFT JOIN rooms rm ON re.room_id = rm.id
        LEFT JOIN buildings b ON rm.building_id = b.id
        LEFT JOIN student s ON ei.student_id = s.StudentID
        LEFT JOIN teacher t ON ei.teacher_id = t.TeacherID
        ORDER BY ei.reported_at DESC
        LIMIT 5";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $recent_issues[] = $row;
}

// Get most frequently used rooms (top 5)
$popular_rooms = [];
$sql = "SELECT rm.id, rm.room_name, b.building_name, COUNT(r.RequestID) as request_count
        FROM rooms rm
        JOIN buildings b ON rm.building_id = b.id
        LEFT JOIN room_requests r ON rm.id = r.RoomID
        GROUP BY rm.id
        ORDER BY request_count DESC
        LIMIT 5";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $popular_rooms[] = $row;
}

// Get rooms with the most issues reported
$issue_prone_rooms = [];
$sql = "SELECT rm.id, rm.room_name, b.building_name, COUNT(ei.id) as issue_count
        FROM rooms rm
        JOIN buildings b ON rm.building_id = b.id
        JOIN room_equipment re ON rm.id = re.room_id
        JOIN equipment_issues ei ON re.equipment_id = ei.equipment_id
        GROUP BY rm.id
        ORDER BY issue_count DESC
        LIMIT 5";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $issue_prone_rooms[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/soft-ui-dashboard-tailwind.min.css">
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

        .table-card {
            grid-column: span 12;
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            transition: var(--transition);
            margin-bottom: 20px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .dashboard-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .dashboard-table th,
        .dashboard-table td {
            padding: 12px 15px;
            text-align: left;
        }
        
        .dashboard-table thead tr {
            background-color: rgba(0, 0, 0, 0.02);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .dashboard-table tbody tr {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .dashboard-table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
    </style>
</head>

<body>
    <div id="app">
        <nav id="navbar-main" class="navbar is-fixed-top">
            <div class="navbar-brand">
                <a class="navbar-item mobile-aside-button">
                    <span class="icon"><i class="mdi mdi-forwardburger mdi-24px"></i></span>
                </a>
                <div class="navbar-item"></div></div>
                    <section class="is-title-bar">
                        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
                            <ul>
                                <li>Department Admin</li>
                                <li>Dashboard</li>
                            </ul>
                        </div>
                    </section>
                </div>
            </div>
            <div class="navbar-brand is-right">
                <a class="navbar-item --jb-navbar-menu-toggle" data-target="navbar-menu">
                    <span class="icon"><i class="mdi mdi-dots-vertical mdi-24px"></i></span>
                </a>
            </div>
            <div class="navbar-menu" id="navbar-menu">
                <div class="navbar-end">
                    <div class="navbar-item dropdown has-divider">
                        <a class="navbar-link" onclick="toggleDropdown(this)">
                            <span>Hello, <?php echo $_SESSION['name']; ?></span>
                            <span class="icon">
                                <i class="mdi mdi-chevron-down"></i>
                            </span>
                        </a>
                        <div class="navbar-dropdown">
                            <a class="navbar-item" href="../auth/logout.php">
                                <span class="icon"><i class="mdi mdi-logout"></i></span>
                                <span>Log Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <aside class="aside is-placed-left is-expanded">
            <div class="aside-tools">
                <div class="logo">
                    <a href="#"><img class="meyclogo" src="../public/assets/logo.webp" alt="logo"></a>
                    <p>MC RMIS</p>
                </div>
            </div>
            <div class="menu is-menu-main">
                <ul class="menu-list">
                    <li class="active">
                        <a href="registrar.php">
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                    <path d="M5 4h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1"></path>
                                    <path d="M5 16h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1"></path>
                                    <path d="M15 12h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1"></path>
                                    <path d="M15 4h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1"></path>
                                </svg> </span>
                            <span class="#">Dashboard</span>
                        </a>
                    </li>
                </ul>
                <ul class="menu-list">
                    <li>
                        <a href="reg_add_admin.php">
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path>
                                    <path d="M16 19h6"></path>
                                    <path d="M19 16v6"></path>
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4"></path>
                                </svg></span>
                            <span class="#">Add Admin</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown" onclick="toggleIcon(this)">
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                    <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path>
                                    <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"></path>
                                    <path d="M16 5l3 3"></path>
                                </svg></span>
                            <span class="#">Manage Rooms</span>
                            <span class="icon toggle-icon"><i class="mdi mdi-plus"></i></span>
                        </a>
                        <ul>
                            <li>
                                <a href="./reg_add_blg.php">
                                    <span>Add Building</span>
                                </a>
                            </li>
                            <li>
                                <a href="./reg_summary.php">
                                    <span>Facility Management</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a class="dropdown" onclick="toggleIcon(this)">
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                    <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path>
                                    <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"></path>
                                    <path d="M16 5l3 3"></path>
                                </svg></span>
                            <span class="#">Manage Equipment</span>
                            <span class="icon toggle-icon"><i class="mdi mdi-plus"></i></span>
                        </a>
                        <ul>
                            <li>
                                <a href="./reg_add_equipt.php">
                                    <span>Add Equipment</span>
                                </a>
                            </li>
                            <li>
                                <a href="./reg_assign_equipt.php">
                                    <span>Assign Equipment</span>
                                </a>
                            </li>
                            <li>
                                <a href="./reg_audit_equipt.php">
                                    <span>Equipment Audit</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </aside>

        <section class="section main-section">
            <div class="dashboard-container">
                <!-- Statistics Cards -->
                <div class="stat-card">
                    <div class="stat-value"><?php echo $buildings_count; ?></div>
                    <div class="stat-label">Total Buildings</div>
                    <div class="stat-icon">
                        <i class="mdi mdi-office-building"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-value"><?php echo $rooms_count; ?></div>
                    <div class="stat-label">Total Rooms</div>
                    <div class="stat-icon">
                        <i class="mdi mdi-door"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-value"><?php echo $equipment_count; ?></div>
                    <div class="stat-label">Total Equipment</div>
                    <div class="stat-icon">
                        <i class="mdi mdi-desktop-mac"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-value"><?php echo $pending_requests_count; ?></div>
                    <div class="stat-label">Pending Requests</div>
                    <div class="stat-icon">
                        <i class="mdi mdi-clock-outline"></i>
                    </div>
                </div>

                <!-- Recent Room Requests -->
                <div class="chart-card chart-card-full">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-clipboard-list"></i></span>
                            Recent Room Requests
                        </h3>
                        <a href="#" class="action-link">View All</a>
                    </div>
                    <div class="card-content table-responsive">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Room</th>
                                    <th>Building</th>
                                    <th>Requester</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_requests as $request): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($request['RequestDate'])); ?></td>
                                    <td><?php echo htmlspecialchars($request['room_name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['building_name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['requester_name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['requester_type']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo strtolower($request['Status']); ?>">
                                            <?php echo ucfirst($request['Status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($recent_requests)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No recent requests found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Charts: Room Request Status and Equipment Status -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-chart-pie"></i></span>
                            Room Request Status
                        </h3>
                    </div>
                    <div class="card-content">
                        <canvas id="roomStatusChart" height="300"></canvas>
                    </div>
                </div>

                <div class="chart-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-chart-pie"></i></span>
                            Equipment Status
                        </h3>
                    </div>
                    <div class="card-content">
                        <canvas id="equipmentStatusChart" height="300"></canvas>
                    </div>
                </div>

                <!-- Monthly Trend Chart -->
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
                
                <!-- Most Popular Rooms -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-star"></i></span>
                            Most Requested Rooms
                        </h3>
                    </div>
                    <div class="card-content table-responsive">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Room</th>
                                    <th>Building</th>
                                    <th>Requests</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($popular_rooms as $room): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($room['room_name']); ?></td>
                                    <td><?php echo htmlspecialchars($room['building_name']); ?></td>
                                    <td><?php echo $room['request_count']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($popular_rooms)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center;">No data available</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Rooms with Most Issues -->
                <div class="chart-card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-alert-circle"></i></span>
                            Rooms with Most Issues
                        </h3>
                    </div>
                    <div class="card-content table-responsive">
                        <table class="dashboard-table">
                            <thead>
                                <tr>
                                    <th>Room</th>
                                    <th>Building</th>
                                    <th>Issues</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($issue_prone_rooms as $room): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($room['room_name']); ?></td>
                                    <td><?php echo htmlspecialchars($room['building_name']); ?></td>
                                    <td><?php echo $room['issue_count']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($issue_prone_rooms)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center;">No data available</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Equipment Issues -->
                <div class="chart-card chart-card-full">
                    <div class="card-header">
                        <h3 class="card-title">
                            <span class="icon"><i class="mdi mdi-tools"></i></span>
                            Recent Equipment Issues
                        </h3>
                        <a href="#" class="action-link">View All</a>
                    </div>
                    <div class="card-content">
                        <?php foreach ($recent_issues as $issue): ?>
                        <div class="issue-item">
                            <div class="issue-title">
                                <?php echo htmlspecialchars($issue['equipment_name']); ?> - 
                                <?php echo htmlspecialchars($issue['issue_type']); ?>
                            </div>
                            <div class="issue-meta">
                                <div>
                                    <strong>Reporter:</strong> <?php echo htmlspecialchars($issue['reporter_name']); ?> (<?php echo $issue['reporter_type']; ?>)
                                </div>
                                <div>
                                    <strong>Location:</strong> <?php echo htmlspecialchars($issue['room_name']); ?>, <?php echo htmlspecialchars($issue['building_name']); ?>
                                </div>
                                <div>
                                    <strong>Reported:</strong> <?php echo date('M d, Y', strtotime($issue['reported_at'])); ?>
                                </div>
                                <div>
                                    <span class="badge badge-<?php echo $issue['status']; ?>">
                                        <?php echo ucfirst($issue['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($recent_issues)): ?>
                        <div class="issue-item">
                            <div class="issue-title">No recent equipment issues found</div>
                        </div>
                        <?php endif; ?>
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
        
        // Initialize charts once the DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Room Status Chart
            const roomStatusLabels = ['Approved', 'Pending', 'Rejected', 'Cancelled'];
            const roomStatusData = [
                <?php echo $room_status['approved'] ?? 0; ?>,
                <?php echo $room_status['pending'] ?? 0; ?>,
                <?php echo $room_status['rejected'] ?? 0; ?>,
                <?php echo $room_status['cancelled'] ?? 0; ?>
            ];
            const roomStatusColors = [
                '#2e7d32', // success - green
                '#f9a825', // warning - gold
                '#c62828', // danger - red
                '#6c757d'  // muted - gray
            ];

            const roomStatusChart = new Chart(
                document.getElementById('roomStatusChart'),
                {
                    type: 'doughnut',
                    data: {
                        labels: roomStatusLabels,
                        datasets: [{
                            data: roomStatusData,
                            backgroundColor: roomStatusColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            title: {
                                display: false
                            }
                        }
                    }
                }
            );

            // Equipment Status Chart
            const equipmentStatusLabels = ['Operational', 'Maintenance', 'Broken', 'Replaced'];
            const equipmentStatusData = [
                <?php echo $equipment_status['operational'] ?? 0; ?>,
                <?php echo $equipment_status['maintenance'] ?? 0; ?>,
                <?php echo $equipment_status['broken'] ?? 0; ?>,
                <?php echo $equipment_status['replaced'] ?? 0; ?>
            ];
            const equipmentStatusColors = [
                '#2e7d32', // operational - green
                '#f9a825', // maintenance - gold
                '#c62828', // broken - red
                '#0277bd'  // replaced - blue
            ];

            const equipmentStatusChart = new Chart(
                document.getElementById('equipmentStatusChart'),
                {
                    type: 'doughnut',
                    data: {
                        labels: equipmentStatusLabels,
                        datasets: [{
                            data: equipmentStatusData,
                            backgroundColor: equipmentStatusColors,
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            title: {
                                display: false
                            }
                        }
                    }
                }
            );

            // Monthly Trends Chart
            <?php 
            $month_labels = [];
            $month_data = [];
            foreach ($monthly_requests as $month => $count) {
                $month_name = date('M Y', strtotime($month));
                $month_labels[] = '"' . $month_name . '"';
                $month_data[] = $count;
            }
            ?>
            
            const monthlyLabels = [<?php echo implode(',', $month_labels); ?>];
            const monthlyData = [<?php echo implode(',', $month_data); ?>];

            const monthlyTrendsChart = new Chart(
                document.getElementById('monthlyTrendsChart'),
                {
                    type: 'line',
                    data: {
                        labels: monthlyLabels,
                        datasets: [{
                            label: 'Room Requests',
                            data: monthlyData,
                            borderColor: '#1e5631',
                            backgroundColor: 'rgba(30, 86, 49, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
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