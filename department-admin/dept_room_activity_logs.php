<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

// Include room status handler to automatically update room statuses
require_once '../auth/room_status_handler.php';

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to get time ago format
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Create a weeks property
    $weeks = floor($diff->d / 7);
    $days = $diff->d - ($weeks * 7);

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    
    $values = array(
        'y' => $diff->y,
        'm' => $diff->m,
        'w' => $weeks,
        'd' => $days,
        'h' => $diff->h,
        'i' => $diff->i,
        's' => $diff->s
    );
    
    $parts = array();
    foreach ($string as $k => $v) {
        if ($values[$k]) {
            $parts[$k] = $values[$k] . ' ' . $v . ($values[$k] > 1 ? 's' : '');
        }
    }

    if (!$full) $parts = array_slice($parts, 0, 1);
    return $parts ? implode(', ', $parts) . ' ago' : 'just now';
}

// Connect to database
db();

// Get department for filtering
$adminDepartment = $_SESSION['department'] ?? '';

// Handle filters
$roomFilter = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
$buildingFilter = isset($_GET['building_id']) ? intval($_GET['building_id']) : 0;
$usageFilter = isset($_GET['usage']) ? $_GET['usage'] : '';
$dateFilter = isset($_GET['date_range']) ? intval($_GET['date_range']) : 30;

// Base query - only show approved status rows
$sql = "SELECT rr.*, r.room_name, r.room_type, b.building_name, 
        CASE 
            WHEN rr.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
            WHEN rr.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
        END as user_name,
        CASE 
            WHEN rr.StudentID IS NOT NULL THEN s.Department
            WHEN rr.TeacherID IS NOT NULL THEN t.Department
        END as user_department,
        CASE 
            WHEN rr.StudentID IS NOT NULL THEN 'Student'
            WHEN rr.TeacherID IS NOT NULL THEN 'Teacher'
        END as user_role,
        CASE 
            WHEN rr.StudentID IS NOT NULL THEN s.StudentID
            WHEN rr.TeacherID IS NOT NULL THEN t.TeacherID
        END as user_id,
        CASE 
            WHEN rr.StudentID IS NOT NULL THEN s.Email
            WHEN rr.TeacherID IS NOT NULL THEN t.Email
        END as user_email,
        da.FirstName as admin_first_name, 
        da.LastName as admin_last_name
        FROM room_requests rr
        JOIN rooms r ON rr.RoomID = r.id
        JOIN buildings b ON r.building_id = b.id
        LEFT JOIN student s ON rr.StudentID = s.StudentID
        LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
        LEFT JOIN dept_admin da ON rr.approvedBy = da.AdminID
        WHERE rr.Status = 'approved'";

// Add filters
$params = [];
$types = "";

// Department filter
if (!empty($adminDepartment)) {
    $sql .= " AND (s.Department = ? OR t.Department = ?)";
    $params[] = $adminDepartment;
    $params[] = $adminDepartment;
    $types .= "ss";
}

// Room filter
if ($roomFilter > 0) {
    $sql .= " AND rr.RoomID = ?";
    $params[] = $roomFilter;
    $types .= "i";
}

// Building filter
if ($buildingFilter > 0) {
    $sql .= " AND b.id = ?";
    $params[] = $buildingFilter;
    $types .= "i";
}

// Usage filter (active/completed)
if (!empty($usageFilter)) {
    if ($usageFilter == 'active') {
        $sql .= " AND rr.EndTime > NOW() AND rr.StartTime <= NOW()";
    } else if ($usageFilter == 'completed') {
        $sql .= " AND rr.EndTime < NOW()";
    }
}

// Date range filter
if ($dateFilter > 0) {
    $sql .= " AND rr.RequestDate >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $params[] = $dateFilter;
    $types .= "i";
}

// Order by most recent first
$sql .= " ORDER BY rr.RequestDate DESC";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get rooms for filter dropdown
$roomsSql = "SELECT r.id, r.room_name, b.building_name 
             FROM rooms r 
             JOIN buildings b ON r.building_id = b.id 
             ORDER BY b.building_name, r.room_name";
$roomsResult = $conn->query($roomsSql);

// Get buildings for filter dropdown
$buildingsSql = "SELECT id, building_name FROM buildings ORDER BY building_name";
$buildingsResult = $conn->query($buildingsSql);

// Get activity counts - only for approved status
$countSql = "SELECT 
    COUNT(*) as total_count,
    SUM(CASE WHEN EndTime > NOW() THEN 1 ELSE 0 END) as active_count,
    SUM(CASE WHEN EndTime < NOW() THEN 1 ELSE 0 END) as completed_count
    FROM room_requests 
    WHERE Status = 'approved'";

// Add department filter if applicable
if (!empty($adminDepartment)) {
    $countSql .= " AND (EXISTS (SELECT 1 FROM student s WHERE room_requests.StudentID = s.StudentID AND s.Department = ?) 
                   OR EXISTS (SELECT 1 FROM teacher t WHERE room_requests.TeacherID = t.TeacherID AND t.Department = ?))";
    $countStmt = $conn->prepare($countSql);
    $countStmt->bind_param("ss", $adminDepartment, $adminDepartment);
} else {
    $countStmt = $conn->prepare($countSql);
}

$countStmt->execute();
$countResult = $countStmt->get_result();
$countData = $countResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Usage Logs</title>
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_2.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">
    
    <style>
        .dashboard-tiles {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .tile {
            background-color: white;
            border-radius: 8px;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .tile-pending { border-top: 4px solid #f97316; }
        .tile-approved { border-top: 4px solid #10b981; }
        .tile-rejected { border-top: 4px solid #ef4444; }
        .tile-active { border-top: 4px solid #3b82f6; }
        .tile-completed { border-top: 4px solid #8b5cf6; }
        .tile-total { border-top: 4px solid #64748b; }

        .tile-count {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }

        .tile-label {
            color: #64748b;
            font-size: 0.875rem;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            font-weight: 600;
            margin-right: 0.75rem;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 500;
            color: #1e293b;
        }

        .user-role {
            font-size: 0.75rem;
            color: #64748b;
        }

        .activity-status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff7ed;
            color: #c2410c;
        }

        .status-approved {
            background-color: #ecfdf5;
            color: #047857;
        }

        .status-rejected {
            background-color: #fef2f2;
            color: #b91c1c;
        }

        .status-active {
            background-color: #eff6ff;
            color: #1d4ed8;
        }

        .status-completed {
            background-color: #f8fafc;
            color: #475569;
        }

        .form-select {
            width: 100%;
            padding: 0.625rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .table-container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            margin-top: 1.5rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .view-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
            border-radius: 0.375rem;
            background-color: #f1f5f9;
            color: #334155;
            transition: all 0.2s;
        }

        .view-btn:hover {
            background-color: #e2e8f0;
        }
    </style>
</head>

<body>
    <div id="app">
        <?php include 'layout/topnav.php'; ?>
        <?php include 'layout/sidebar.php'; ?>

        <section class="section main-section">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-clipboard-text-clock"></i></span>
                        Room Usage Logs
                    </p>
                </header>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
                <?php endif; ?>

                <div class="card-content">
                    <!-- Activity Logs Table -->
                    <div class="table-container">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <div style="display: flex; align-items: center;">
                                <label style="margin-right: 10px;">Show</label>
                                <select id="entriesSelect" class="form-select" style="width: 80px; margin-right: 10px;">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                                <label>entries</label>
                            </div>
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div>
                                    <label style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.375rem; display: block; font-weight: 500;">Usage Status</label>
                                    <select id="usage-filter" class="form-select" style="width: 150px;">
                                        <option value="">All Usage</option>
                                        <option value="active" <?php echo isset($_GET['usage']) && $_GET['usage'] === 'active' ? 'selected' : ''; ?>>Currently Active</option>
                                        <option value="completed" <?php echo isset($_GET['usage']) && $_GET['usage'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </div>                  
                                <!-- Search bar -->
                                <div>
                                    <label style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.375rem; display: block; font-weight: 500;">Search</label>
                                    <input type="text" id="customSearch" class="form-select" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem;" placeholder="Search by user, room, or activity...">
                                </div>
                            </div>
                        </div>
                        <table id="activityTable" class="table is-fullwidth is-striped">
                            <thead>
                                <tr class="titles">
                                    <th>User</th>
                                    <th>Room</th>
                                    <th>Activity</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): 
                                        // Determine activity status
                                        $now = new DateTime();
                                        $startTime = new DateTime($row['StartTime']);
                                        $endTime = new DateTime($row['EndTime']);
                                        
                                        $status = $row['Status'];
                                        // Since we're only showing approved status, just determine if active or completed
                                        if ($now > $endTime) {
                                            $statusClass = "status-completed";
                                            $statusLabel = "Completed";
                                        } else {
                                            $statusClass = "status-active";
                                            $statusLabel = "Active Now";
                                        }
                                        
                                        // Get user initials for avatar
                                        $nameParts = explode(' ', $row['user_name']);
                                        $initials = '';
                                        if (count($nameParts) >= 2) {
                                            $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1));
                                        } else {
                                            $initials = strtoupper(substr($row['user_name'], 0, 2));
                                        }
                                    ?>
                                        <tr>
                                            <td data-label="User">
                                                <div class="user-info">
                                                    <div class="user-avatar"><?php echo $initials; ?></div>
                                                    <div class="user-details">
                                                        <span class="user-name"><?php echo htmlspecialchars($row['user_name']); ?></span>
                                                        <span class="user-role"><?php echo $row['user_role']; ?> (<?php echo htmlspecialchars($row['user_department']); ?>)</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td data-label="Room"><?php echo htmlspecialchars($row['room_name'] . ' (' . $row['building_name'] . ')'); ?></td>
                                            <td data-label="Activity"><?php echo htmlspecialchars($row['ActivityName']); ?></td>
                                            <td data-label="Date & Time">
                                                <?php 
                                                    echo date('M j, Y', strtotime($row['StartTime'])); 
                                                    echo '<br><span style="font-size: 0.8rem; color: #64748b;">';
                                                    echo date('g:i A', strtotime($row['StartTime'])) . ' - ' . date('g:i A', strtotime($row['EndTime']));
                                                    echo '</span>';
                                                ?>
                                            </td>
                                            <td data-label="Status">
                                                <span class="activity-status <?php echo $statusClass; ?>">
                                                    <?php echo $statusLabel; ?>
                                                </span>
                                            </td>
                                            <td data-label="Last Updated">
                                                <?php 
                                                    echo time_elapsed_string($row['RequestDate']);
                                                    if ($status == 'approved' && !empty($row['admin_first_name'])) {
                                                        echo '<br><span style="font-size: 0.8rem; color: #64748b;">by ' . 
                                                            htmlspecialchars($row['admin_first_name'] . ' ' . $row['admin_last_name']) . '</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td class="action-buttons">
                                                <a href="dept_room_approval.php?view=<?php echo $row['RequestID']; ?>" class="view-btn" title="View Details">
                                                    <i class="mdi mdi-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="has-text-centered">No activity logs found matching your criteria.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="../public/js/admin_scripts/main.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#activityTable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search by user, room, activity...",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries"
                },
                dom: 'rt<"bottom"p><"clear">',  // Only show pagination at bottom
                pageLength: 10,
                ordering: true,
                paging: true,
                lengthChange: false, // Disable built-in length changing
                columnDefs: [{
                    targets: -1,
                    orderable: false
                }]
            });

            // Custom search handling
            $('#customSearch').on('keyup', function() {
                table.search(this.value).draw();
            });
            
            // Handle custom entries select
            $('#entriesSelect').on('change', function() {
                table.page.len(parseInt($(this).val())).draw();
            });

            // Apply filters button
            $('#apply-filters').on('click', function() {
                var usageFilter = $('#usage-filter').val();
                var buildingFilter = $('#building-filter').val();
                var roomFilter = $('#room-filter').val();
                var dateFilter = $('#date-filter').val();
                
                var url = 'dept_room_activity_logs.php?';
                
                if (usageFilter) url += 'usage=' + usageFilter + '&';
                if (buildingFilter) url += 'building_id=' + buildingFilter + '&';
                if (roomFilter) url += 'room_id=' + roomFilter + '&';
                if (dateFilter) url += 'date_range=' + dateFilter + '&';
                
                // Remove trailing &
                url = url.replace(/&$/, '');
                
                window.location.href = url;
            });

            // Reset filters button
            $('#reset-filters').on('click', function() {
                window.location.href = 'dept_room_activity_logs.php';
            });

            // Building filter change event
            $('#building-filter').on('change', function() {
                var buildingId = $(this).val();
                
                // If no building is selected, show all rooms
                if (!buildingId) {
                    $('#room-filter option').show();
                    return;
                }
                
                // Hide rooms that don't belong to the selected building
                $('#room-filter option').each(function() {
                    var optionText = $(this).text();
                    var selectedBuilding = $('#building-filter option:selected').text();
                    
                    if ($(this).val() === '') {
                        // Always show "All Rooms" option
                        $(this).show();
                    } else if (optionText.indexOf(selectedBuilding) >= 0) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
                
                // Reset room selection if the current selection is now hidden
                if ($('#room-filter option:selected').is(':hidden')) {
                    $('#room-filter').val('');
                }
            });

            // Auto-fade success messages after 3 seconds
            if ($('.alert-success').length > 0) {
                setTimeout(function() {
                    $('.alert-success').fadeOut(1000, function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        });

        function toggleIcon(element) {
            const icon = element.querySelector('.toggle-icon i');
            if (icon.classList.contains('mdi-plus')) {
                icon.classList.remove('mdi-plus');
                icon.classList.add('mdi-minus');
            } else {
                icon.classList.remove('mdi-minus');
                icon.classList.add('mdi-plus');
            }
        }
    </script>
</body>
</html>
