<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

//automatically update room statuses
require_once '../auth/room_status_handler.php';

// Get user role and ID from session
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>MCiSmartSpace</title>

<link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">

<!-- Bootstrap -->
<link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Font Awesome -->
<link href="../vendors/fontawesome/css/font-awesome.min.css" rel="stylesheet">
<!-- jQuery custom content scroller -->
<link href="../vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css" rel="stylesheet" />

<!-- Custom Theme Style -->
<link href="../public/css/user_styles/custom.css" rel="stylesheet">
<link href="../public/css/user_styles/custom2.css" rel="stylesheet">

<!-- Include our custom CSS -->
<link href="../public/css/user_styles/room-browser.css" rel="stylesheet">
<link href="../public/css/user_styles/room-browser-styles.css" rel="stylesheet">
<link href="../public/css/user_styles/room-reservation.css" rel="stylesheet">

<!-- Custom responsive styles -->
<style>
@media (max-width: 768px) {
.view-toggle.btn-group {
    display: none !important;
}
}

/* Add style for department buildings */
.filter-checkbox-item.user-department .checkbox-label {
font-weight: bold;
color: #1e7e34;
}
.filter-checkbox-item.user-department .fa-home {
color: #1e7e34;
margin-left: 5px;
}
</style>

</head>

<body class="nav-md">
<div class="container body">
<div class="main_container">
<div class="col-md-3 left_col menu_fixed">
    <div class="left_col scroll-view">
        <div class="navbar nav_title" style="border: 0;">
            <div class="logo-container">
                <a href="#" class="site-branding">
                    <img class="meyclogo" src="../public/assets/logo.webp" alt="meyclogo">
                    <span class="title-text">MCiSmartSpace</span>
                </a>
            </div>
        </div>

        <div class="clearfix"></div>

        <br />

        <!-- sidebar menu -->
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
                <ul class="nav side-menu" class="navbar nav_title" style="border: 0;">
                    <li class="active">
                        <a href="users_browse_room.php">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2 flex-shrink-0" data-lov-id="src/components/layout/Sidebar.tsx:89:20" data-lov-name="Icon" data-component-path="src/components/layout/Sidebar.tsx" data-component-line="89" data-component-file="Sidebar.tsx" data-component-name="Icon" data-component-content="%7B%22className%22%3A%22flex-shrink-0%22%7D">
                                    <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                                    <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                                    <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                                    <path d="M10 6h4"></path>
                                    <path d="M10 10h4"></path>
                                    <path d="M10 14h4"></path>
                                    <path d="M10 18h4"></path>
                                </svg>
                            </div>
                            <div class="menu-text">
                                <span>Browse Room</span>
                                <span class="fa fa-chevron-down" style="opacity: 0;"></span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="users_reservation_history.php">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 8v4l3 3"></path>
                                    <circle cx="12" cy="12" r="10"></circle>
                                </svg>
                            </div>
                            <div class="menu-text">
                                <span>Reservation History</span>
                                <span class="fa fa-chevron-down" style="opacity: 0;"></span>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a href="equipment_report_status.php">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                                </svg>
                            </div>
                            <div class="menu-text">
                                <span>Equipment Reports</span>
                                <span class="fa fa-chevron-down" style="opacity: 0;"></span>
                            </div>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <!-- /sidebar menu -->
    </div>
</div>

<?php include "layout/topnav.php"; ?>
<!-- Page content -->
<div class="right_col" role="main">
    <div>
        <div class="row">
            <div class="col-md-12">
                <!-- Updated Search and Filter Section based on the provided image -->
                <div class="search-filter-wrapper">
                    <div class="search-bar-container">
                        <div class="search-input-wrapper">
                            <i class="fa fa-search search-icon"></i>
                            <input type="text" id="searchRooms" class="form-control" placeholder="Search rooms...">
                            <i class="fa fa-times search-clear-icon" id="clearSearch" style="display: none;"></i>
                        </div>

                        <div class="view-toggle-container">
                            <button class="filter-button" id="filterToggleBtn" type="button" onclick="toggleFilterDropdown(event)">
                                <i class="fa fa-filter"></i> Filters <span class="filter-count-bubble" id="filterCountBubble">0</span> <i class="fa fa-chevron-down filter-chevron"></i>
                            </button>

                            <div class="view-toggle btn-group" role="group">
                                <button type="button" class="btn active" id="gridView">
                                    <i class="fa fa-th-large"></i>
                                </button>
                                <button type="button" class="btn" id="listView">
                                    <i class="fa fa-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Container for active filter tags -->
                    <div class="applied-filters" id="appliedFilters">
                        <!-- Filter tags will be added here dynamically -->
                    </div>

                    <div class="filter-dropdown" id="filterDropdown" style="display:none;">
                        <div class="filter-section">
                            <h3 class="filter-heading">Buildings</h3>
                            <div class="filter-count"><span id="buildingCount">0 selected</span></div>
                            <div class="filter-options">
                                <?php
                                // Connect to database
                                $conn = db();

                                // Get the department of the logged-in student
                                $user_id = $_SESSION['user_id'];
                                $user_role = $_SESSION['role'];
                                $user_department = '';
                                
                                // Get the department based on user role
                                if ($user_role == 'Student') {
                                    $user_query = "SELECT Department FROM student WHERE StudentID = ?";
                                } elseif ($user_role == 'Teacher') {
                                    $user_query = "SELECT Department FROM teacher WHERE TeacherID = ?";
                                }
                                
                                $stmt = $conn->prepare($user_query);
                                $stmt->bind_param("i", $user_id);
                                $stmt->execute();
                                $user_result = $stmt->get_result();
                                
                                if ($user_result->num_rows > 0) {
                                    $user_row = $user_result->fetch_assoc();
                                    $user_department = $user_row['Department'];
                                }

                                // Get all buildings, but mark the ones for the user's department
                                $sql = "SELECT id, building_name, department FROM buildings ORDER BY 
                                        CASE WHEN department = ? THEN 0 ELSE 1 END, 
                                        building_name ASC";
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("s", $user_department);
                                $stmt->execute();
                                $result = $stmt->get_result();

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $isUserDepartmentBuilding = ($row['department'] == $user_department);
                                        echo '<div class="filter-checkbox-item' . ($isUserDepartmentBuilding ? ' user-department' : '') . '">';
                                        echo '<label>';
                                        echo '<input type="checkbox" name="building" value="' . $row['id'] . '" class="building-checkbox"' . 
                                                ($isUserDepartmentBuilding ? ' checked' : '') . '>';
                                        echo '<span class="checkbox-label">' . $row['building_name'] . 
                                                ($isUserDepartmentBuilding ? ' <i class="fa fa-home" title="Your department building"></i>' : '') . '</span>';
                                        echo '</label>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>

                        <div class="filter-section">
                            <h3 class="filter-heading">Room Types</h3>
                            <div class="filter-count"><span id="roomTypeCount">0 selected</span></div>
                            <div class="filter-options">
                                <?php
                                // Query to get distinct room types
                                $sql = "SELECT DISTINCT room_type FROM rooms";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<div class="filter-checkbox-item">';
                                        echo '<label>';
                                        echo '<input type="checkbox" name="roomType" value="' . $row['room_type'] . '" class="roomtype-checkbox">';
                                        echo '<span class="checkbox-label">' . $row['room_type'] . '</span>';
                                        echo '</label>';
                                        echo '</div>';
                                    }
                                }
                                ?>
                            </div>
                        </div>

                        <div class="filter-section">
                            <h3 class="filter-heading">Minimum Capacity</h3>
                            <div class="filter-count"><span id="capacityValue">Any</span></div>
                            <div class="filter-options capacity-filter">
                                <input type="range" id="capacitySlider" min="0" max="100" value="0" class="capacity-slider">
                            </div>
                        </div>

                        <div class="filter-section">
                            <div class="filter-toggle-item">
                                <div class="toggle-icon"><i class="fa fa-desktop"></i></div>
                                <span class="toggle-label">Has Equipment</span>
                                <label class="switch">
                                    <input type="checkbox" id="hasEquipment">
                                    <span class="slider round"></span>
                                </label>
                            </div>

                            <div class="filter-toggle-item">
                                <div class="toggle-icon"><i class="fa fa-check-circle"></i></div>
                                <span class="toggle-label">Only Available</span>
                                <label class="switch">
                                    <input type="checkbox" id="onlyAvailable" checked>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        </div>

                        <div class="filter-actions">
                            <button id="applyFilters" class="apply-button">
                                <i class="fa fa-check"></i> Apply Filters
                            </button>
                            <button id="resetFilters" class="reset-button">
                                <i class="fa fa-refresh"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>

                <div id="roomCount" class="room-count-display">6 rooms found</div>

                <!-- No results message container -->
                <div id="noResultsMessage" class="no-results-container" style="display: none;">
                    <div class="no-results-content">
                        <div class="no-results-icon">
                            <i class="fa fa-book"></i>
                        </div>
                        <h3>No rooms found</h3>
                        <p>We couldn't find any rooms matching your filter criteria. Try adjusting your filters or search terms.</p>
                    </div>
                </div>

                <!-- Room Display Section Stays the Same -->
                <div class="row" id="roomsGrid">
                    <?php
                    // Start building the query with basic structure
                    $base_sql = "SELECT r.id, r.room_name, r.room_type, r.capacity, r.RoomStatus, b.id as building_id, b.building_name, 
                (SELECT COUNT(*) FROM room_equipment re WHERE re.room_id = r.id) as equipment_count
                FROM rooms r 
                JOIN buildings b ON r.building_id = b.id";

                    // Initialize where clauses array and parameter array
                    $where_clauses = [];
                    $params = [];
                    $param_types = "";

                    // Get user department buildings
                    $user_department_buildings = [];
                    if (!isset($_GET['building_ids'])) {
                        // First visit or no filter applied yet
                        $user_id = $_SESSION['user_id'];
                        $user_role = $_SESSION['role'];
                        
                        // Get user department
                        if ($user_role == 'Student') {
                            $user_query = "SELECT Department FROM student WHERE StudentID = ?";
                        } elseif ($user_role == 'Teacher') {
                            $user_query = "SELECT Department FROM teacher WHERE TeacherID = ?";
                        }
                        
                        $stmt = $conn->prepare($user_query);
                        $stmt->bind_param("i", $user_id);
                        $stmt->execute();
                        $user_result = $stmt->get_result();
                        
                        if ($user_result->num_rows > 0) {
                            $user_row = $user_result->fetch_assoc();
                            $user_department = $user_row['Department'];
                            
                            // Get building IDs for user's department
                            $building_query = "SELECT id FROM buildings WHERE department = ?";
                            $stmt = $conn->prepare($building_query);
                            $stmt->bind_param("s", $user_department);
                            $stmt->execute();
                            $building_result = $stmt->get_result();
                            
                            while ($brow = $building_result->fetch_assoc()) {
                                $user_department_buildings[] = $brow['id'];
                            }
                        }
                    }

                    // Get filter parameters
                    $building_ids = isset($_GET['building_ids']) ? $_GET['building_ids'] : $user_department_buildings;
                    $room_types = isset($_GET['room_types']) ? $_GET['room_types'] : [];
                    $min_capacity = isset($_GET['min_capacity']) ? intval($_GET['min_capacity']) : 0;
                    $has_equipment = isset($_GET['has_equipment']) && $_GET['has_equipment'] === 'true';
                    $only_available = isset($_GET['only_available']) && $_GET['only_available'] === 'true';
                    $search_term = isset($_GET['search']) ? $_GET['search'] : '';

                    // Add where clauses based on filters
                    if (!empty($building_ids)) {
                        $placeholders = str_repeat('?,', count($building_ids) - 1) . '?';
                        $where_clauses[] = "b.id IN ($placeholders)";
                        foreach ($building_ids as $id) {
                            $params[] = $id;
                            $param_types .= "i";
                        }
                    }

                    if (!empty($room_types)) {
                        $placeholders = str_repeat('?,', count($room_types) - 1) . '?';
                        $where_clauses[] = "r.room_type IN ($placeholders)";
                        foreach ($room_types as $type) {
                            $params[] = $type;
                            $param_types .= "s";
                        }
                    }

                    if ($min_capacity > 0) {
                        $where_clauses[] = "r.capacity >= ?";
                        $params[] = $min_capacity;
                        $param_types .= "i";
                    }

                    if ($has_equipment) {
                        $where_clauses[] = "(SELECT COUNT(*) FROM room_equipment re WHERE re.room_id = r.id) > 0";
                    }

                    if ($only_available) {
                        $where_clauses[] = "r.RoomStatus = 'available'";
                    }

                    if (!empty($search_term)) {
                        $where_clauses[] = "(r.room_name LIKE ? OR b.building_name LIKE ? OR r.room_type LIKE ?)";
                        $search_param = "%$search_term%";
                        $params[] = $search_param;
                        $params[] = $search_param;
                        $params[] = $search_param;
                        $param_types .= "sss";
                    }

                    // Combine all where clauses
                    if (!empty($where_clauses)) {
                        $base_sql .= " WHERE " . implode(" AND ", $where_clauses);
                    }

                    // Add ordering
                    $base_sql .= " ORDER BY r.room_name";

                    // Prepare and execute the query
                    $stmt = $conn->prepare($base_sql);

                    // Bind parameters if we have any
                    if (!empty($params)) {
                        $stmt->bind_param($param_types, ...$params);
                    }

                    $stmt->execute();
                    $result = $stmt->get_result();

                    // Display room count
                    $room_count = $result->num_rows;
                    echo "<script>document.getElementById('roomCount').innerText = '$room_count " . ($room_count === 1 ? "room" : "rooms") . " found';</script>";

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $roomId = $row['id'];
                            $roomName = $row['room_name'];
                            $roomType = $row['room_type'];
                            $capacity = $row['capacity'];
                            $buildingId = $row['building_id'];
                            $buildingName = $row['building_name'];
                            $status = strtolower($row['RoomStatus']);
                            $hasEquipment = $row['equipment_count'] > 0;

                            // Set status class and text
                            $statusClass = "";
                            $statusText = "";

                            switch ($status) {
                                case 'available':
                                    $statusClass = "label-success";
                                    $statusText = "Available";
                                    break;
                                case 'occupied':
                                    $statusClass = "label-warning";
                                    $statusText = "Occupied";
                                    break;
                                case 'maintenance':
                                    $statusClass = "label-danger";
                                    $statusText = "Maintenance";
                                    break;
                                default:
                                    $statusClass = "label-default";
                                    $statusText = "Unknown";
                            }
                    ?>
                            <div class="col-md-4 room-card"
                                data-room-id="<?php echo $roomId; ?>"
                                data-building-id="<?php echo $buildingId; ?>"
                                data-room-type="<?php echo $roomType; ?>"
                                data-capacity="<?php echo $capacity; ?>"
                                data-status="<?php echo $status; ?>"
                                data-has-equipment="<?php echo $hasEquipment ? 'true' : 'false'; ?>">
                                <div class="x_panel">
                                    <div class="x_title bg-header">
                                        <div class="room-header">
                                            <div class="room-chip">
                                                <h2 class="room-name"><?php echo $roomName; ?></h2>
                                                <span class="label <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                            </div>
                                            <p class="building-name"><?php echo $buildingName; ?></p>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="x_content">
                                        <div class="room-info">
                                            <i class="fa fa-users"></i> Capacity: <?php echo $capacity; ?>
                                        </div>
                                        <div class="room-info">
                                            <i class="fa fa-th-large"></i> Type: <?php echo $roomType; ?>
                                        </div>
                                        <?php if ($hasEquipment) { ?>
                                            <div class="room-info">
                                                <i class="fa fa-desktop"></i> Has Equipment
                                            </div>
                                        <?php } ?>
                                        <div class="action-buttons">
                                            <button type="button" class="btn-view" onclick="showRoomDetails(<?php echo $roomId; ?>)">
                                                <i class="fa fa-info-circle"></i> View Details
                                            </button>
                                            <?php if ($status == 'available') { ?>
                                                <button type="button" class="btn-reserve" onclick="showReservationModal(<?php echo $roomId; ?>)">
                                                    <i class="fa fa-calendar-plus-o"></i> Reserve
                                                </button>
                                            <?php } else { ?>
                                                <button type="button" class="btn-unavailable" disabled>
                                                    <i class="fa fa-calendar-times-o"></i> Unavailable
                                                </button>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    } else {
                        // Show the styled no results message
                        echo "<script>document.getElementById('noResultsMessage').style.display = 'flex';</script>";
                    }

                    // Close the database connection
                    $conn->close();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Room Details Modal -->
<div class="modal fade" id="roomDetailsModal" tabindex="-1" role="dialog" aria-labelledby="roomDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-body" id="roomDetailsContent">
                <div class="text-center">
                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                    <p>Loading room details...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Reservation Modal -->
<div class="modal fade" id="reservationModal" tabindex="-1" role="dialog" aria-labelledby="reservationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-header-content">
                    <h4 class="modal-title" id="reservationModalLabel">Room Request</h4>
                    <p class="modal-subtitle">Fill out the form to request a room for your activity</p>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="reservationModalContent">
                <div class="reservation-card">
                    <!-- Reservation Form -->
                    <form id="reservationForm" method="POST" action="process_reservation.php">
                        <!-- Step Progress -->
                        <div class="step-progress">
                            <div class="step-item active" id="step1Item">
                                <div class="step-circle">1</div>
                                <div class="step-title">Reservation Details</div>
                                <div class="step-subtitle">Provide information about your activity</div>
                            </div>
                            <div class="step-item" id="step2Item">
                                <div class="step-circle">2</div>
                                <div class="step-title">Date & Time</div>
                                <div class="step-subtitle">Select when you need the room</div>
                            </div>
                            <div class="step-item" id="step3Item">
                                <div class="step-circle">3</div>
                                <div class="step-title">Confirm Room</div>
                                <div class="step-subtitle">Review your room selection</div>
                            </div>
                        </div>

                        <!-- Step 1: Reservation Details -->
                        <div class="step-content active" id="step1">
                            <div class="form-group">
                                <label for="activityName" class="form-label">Activity Name</label>
                                <input type="text" id="activityName" name="activityName" class="form-control" placeholder="e.g., Group Study Session" required>
                                <div class="form-hint">Activity name must be at least 2 words</div>
                            </div>

                            <div class="form-group">
                                <label for="purpose" class="form-label">Purpose</label>
                                <textarea id="purpose" name="purpose" class="form-control" placeholder="Describe the purpose of your reservation..." required></textarea>
                                <div class="form-hint">Purpose must be at least 1 sentence</div>
                            </div>

                            <div class="form-group">
                                <label for="participants" class="form-label">Number of Participants</label>
                                <input type="number" id="participants" name="participants" class="form-control" placeholder="e.g., 10" min="1" required>
                                <div class="form-hint">Please enter a valid number of participants</div>
                            </div>

                            <div class="modal-btns">
                                <div></div> <!-- Empty div for alignment -->
                                <button type="button" class="btn-next" id="toStep2">Next</button>
                            </div>
                        </div>

                        <!-- Step 2: Date & Time -->
                        <div class="step-content" id="step2">
                            <div class="form-group">
                                <label for="reservationDate" class="form-label">Reservation Date</label>
                                <div class="date-input-container">
                                    <i class="fa fa-calendar date-input-icon"></i>
                                    <?php
                                    // Generate today's date in YYYY-MM-DD format
                                    $today = date('Y-m-d');
                                    ?>
                                    <input type="date" id="reservationDate" name="reservationDate" class="form-control date-input" min="<?php echo $today; ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="startTime" class="form-label">Start Time</label>
                                <div class="time-input-container">
                                    <i class="fa fa-clock-o time-input-icon"></i>
                                    <select id="startTime" name="reservationTime" class="form-control time-input" required>
                                        <option value="">Select a start time</option>
                                        <option value="7:00">7:00 AM</option>
                                        <option value="7:30">7:30 AM</option>
                                        <option value="8:00">8:00 AM</option>
                                        <option value="8:30">8:30 AM</option>
                                        <option value="9:00">9:00 AM</option>
                                        <option value="9:30">9:30 AM</option>
                                        <option value="10:00">10:00 AM</option>
                                        <option value="10:30">10:30 AM</option>
                                        <option value="11:00">11:00 AM</option>
                                        <option value="11:30">11:30 AM</option>
                                        <option value="12:00">12:00 PM</option>
                                        <option value="12:30">12:30 PM</option>
                                        <option value="13:00">1:00 PM</option>
                                        <option value="13:30">1:30 PM</option>
                                        <option value="14:00">2:00 PM</option>
                                        <option value="14:30">2:30 PM</option>
                                        <option value="15:00">3:00 PM</option>
                                        <option value="15:30">3:30 PM</option>
                                        <option value="16:00">4:00 PM</option>
                                        <option value="16:30">4:30 PM</option>
                                        <option value="17:00">5:00 PM</option>
                                        <option value="17:30">5:30 PM</option>
                                        <option value="18:00">6:00 PM</option>
                                        <option value="18:30">6:30 PM</option>
                                        <option value="19:00">7:00 PM</option>
                                        <option value="19:30">7:30 PM</option>
                                        <option value="20:00">8:00 PM</option>
                                        <option value="20:30">8:30 PM</option>
                                        <option value="21:00">9:00 PM</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Duration</label>
                                <div class="duration-container">
                                    <div class="duration-input-group">
                                        <input type="number" id="durationHours" name="durationHours" class="form-control" min="0" max="8" value="1" required>
                                        <span class="duration-label">hours</span>
                                        <input type="number" id="durationMinutes" name="durationMinutes" class="form-control" min="0" max="59" value="30" required>
                                        <span class="duration-label">minutes</span>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="endTime" class="form-label">End Time</label>
                                <div class="time-input-container">
                                    <i class="fa fa-clock-o time-input-icon"></i>
                                    <input type="text" id="endTime" name="endTime" class="form-control time-input" readonly>
                                </div>
                            </div>

                            <div class="modal-btns">
                                <button type="button" class="btn-back" id="backToStep1">Back</button>
                                <button type="button" class="btn-next" id="toStep3">Next</button>
                            </div>
                        </div>

                        <!-- Step 3: Select Room -->
                        <div class="step-content" id="step3">
                            <div class="form-group">
                                <label class="form-label">Selected Room</label>
                                <div class="selected-room-info" id="selectedRoomInfo">
                                    <!-- This will be populated with room info via JavaScript -->
                                </div>
                            </div>

                            <input type="hidden" id="selectedRoom" name="roomId" required>

                            <div class="modal-btns">
                                <button type="button" class="btn-back" id="backToStep2">Back</button>
                                <button type="submit" class="btn-submit" id="submitReservation">Submit Request</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create a hidden template for room details -->
<script id="roomDetailsTemplate" type="text/template">
    <div class="room-details-container">
<div class="room-header-info">
<div class="row">
    <div class="col-md-8">
        <h3 class="room-name">{roomName}</h3>
        <p class="building-name">{buildingName}</p>
    </div>
    <div class="col-md-4 text-right">
        <span class="label label-{statusClass}"{statusTooltip}>
            <i class="fa fa-{statusIcon}"></i> {statusText}
        </span>
    </div>
</div>
</div>

<div class="room-info-section">
<h4>Room Information</h4>
<div class="row">
    <div class="col-md-6">
        <div class="info-item">
            <i class="fa fa-building"></i>
            <div class="info-content">
                <label>Building</label>
                <p>{buildingName}</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-item">
            <i class="fa fa-th-large"></i>
            <div class="info-content">
                <label>Room Type</label>
                <p>{roomType}</p>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="info-item">
            <i class="fa fa-users"></i>
            <div class="info-content">
                <label>Capacity</label>
                <p>{capacity} persons</p>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-item">
            <i class="fa fa-clock-o"></i>
            <div class="info-content">
                <label>Status</label>
                <p{statusTooltip}>{statusText}</p>
            </div>
        </div>
    </div>
</div>
</div>

<div class="equipment-section">
<h4>Room Equipment</h4>
<div class="equipment-list">
    {equipmentList}
</div>
</div>

<div class="action-section text-center">
{reserveButton}
</div>
</div>
</script>



<!-- Add a hidden form for traditional filtering method -->
<form id="filterForm" method="GET" action="users_browse_room.php" style="display: none;">
    <!-- Hidden inputs will be populated by JavaScript -->
    <div id="hiddenInputsContainer"></div>
    <input type="submit" id="submitFilters">
</form>

<!-- footer content -->
<footer>
    <div class="pull-right">
        Meycauayan College Incorporated - <a href="#">Mission || Vision || Values</a>
    </div>
    <div class="clearfix"></div>
</footer>
<!-- /footer content -->

<!-- Simple direct script to handle filter toggling -->
<script>
    // This function uses direct DOM manipulation without relying on jQuery
    function toggleFilterDropdown(event) {
        console.log("Direct toggle function called");
        const dropdown = document.getElementById("filterDropdown");
        const btn = document.getElementById("filterToggleBtn");

        if (dropdown.style.display === "none" || dropdown.style.display === "") {
            dropdown.style.display = "block";
            btn.classList.add("active");
            console.log("Dropdown shown");
        } else {
            dropdown.style.display = "none";
            btn.classList.remove("active");
            console.log("Dropdown hidden");
        }

        // Prevent event propagation
        event.preventDefault();
        event.stopPropagation();
        return false;
    }

    // Add click outside handler when DOM is loaded
    document.addEventListener("DOMContentLoaded", function() {
        document.addEventListener("click", function(event) {
            const dropdown = document.getElementById("filterDropdown");
            const btn = document.getElementById("filterToggleBtn");

            // Only act if dropdown is visible
            if (dropdown.style.display === "block") {
                // If click is outside button and dropdown
                if (!event.target.closest("#filterToggleBtn") && !event.target.closest("#filterDropdown")) {
                    dropdown.style.display = "none";
                    btn.classList.remove("active");
                    console.log("Outside click - dropdown closed");
                }
            }
        });
    });
</script>

<!-- Include external JavaScript file -->
<script src="../public/js/user_scripts/room-browser-scripts.js"></script>
<!-- Include jQuery first if not already included earlier -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Include external JavaScript file -->
<script src="../public/js/user_scripts/room-browser-scripts.js"></script>

<?php include "../partials/footer.php"; ?>