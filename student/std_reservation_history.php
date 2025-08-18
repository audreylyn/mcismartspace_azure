<?php
require '../auth/middleware.php';
checkAccess(['Student']);

require_once '../auth/room_status_handler.php';
?>

<?php include "../partials/header_reservation-history.php"; ?>

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

                    <li>
                        <a href="std_browse_room.php">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2 flex-shrink-0">
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
                        <a href="std_room_status.php">
                            <div class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                    <path d="M8 14h3"></path>
                                    <path d="M14 14h3"></path>
                                    <path d="M8 18h3"></path>
                                    <path d="M14 18h3"></path>
                                </svg>
                            </div>
                            <div class="menu-text">
                                <span>Reservation Status</span>
                                <span class="fa fa-chevron-down" style="opacity: 0;"></span>
                            </div>
                        </a>
                    </li>
                    <li class="active">
                        <a href="std_reservation_history.php">
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
<?php include "../partials/topnav.php"; ?>

<!-- Page content -->
<div class="right_col" role="main">
    <div class="history-container">
        <!-- Display success/error messages -->
        <?php
        // Display success message if any
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success fade-alert" role="alert">';
            echo $_SESSION['success_message'];
            echo '</div>';
            unset($_SESSION['success_message']); // Clear the message
        }

        // Display error message if any
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger fade-alert" role="alert">';
            echo $_SESSION['error_message'];
            echo '</div>';
            unset($_SESSION['error_message']); // Clear the message
        }
        ?>

        <h3 class="title">Reservation History</h3>
        <p class="subtitle">View your complete history of room reservations</p>

        <!-- Search and Filter Row -->
        <div class="search-filter-row">
            <div class="search-container">
                <i class="fa fa-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Search by room or building...">
            </div>
            <div class="status-filter">
                <span class="status-filter-label">Status:</span>
                <select class="status-filter-select" id="statusFilter">
                    <option value="all">All Reservations</option>
                    <option value="approved">Approved</option>
                    <option value="pending">Pending</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        <?php
        // Initialize session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        db();

        // Get student ID from session
        $studentId = $_SESSION['user_id']; // Adjust based on your session structure

        // Get counts for each type
        $countSql = "SELECT 
                        COUNT(*) as TotalCount,
                        SUM(CASE WHEN EndTime > NOW() AND Status = 'approved' THEN 1 ELSE 0 END) as UpcomingCount,
                        SUM(CASE WHEN EndTime < NOW() AND Status = 'approved' THEN 1 ELSE 0 END) as CompletedCount,
                        SUM(CASE WHEN Status = 'rejected' THEN 1 ELSE 0 END) as CancelledCount
                     FROM room_requests 
                     WHERE StudentID = ?";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $studentId);
        $countStmt->execute();
        $countResult = $countStmt->get_result();

        // Initialize counts
        $totalCount = 0;
        $upcomingCount = 0;
        $completedCount = 0;
        $cancelledCount = 0;

        // Set counts from result
        if ($row = $countResult->fetch_assoc()) {
            $totalCount = $row['TotalCount'];
            $upcomingCount = $row['UpcomingCount'];
            $completedCount = $row['CompletedCount'];
            $cancelledCount = $row['CancelledCount'];
        }
        $countStmt->close();
        ?>

        <!-- Tab Container -->
        <div class="tab-container">
            <div class="history-tabs">
                <div class="history-tab active" data-filter="all">All Reservations <span class="history-count"><?php echo $totalCount; ?></span></div>
                <div class="history-tab" data-filter="upcoming">Ongoing <span class="history-count"><?php echo $upcomingCount; ?></span></div>
                <div class="history-tab" data-filter="completed">Completed <span class="history-count"><?php echo $completedCount; ?></span></div>
                <div class="history-tab" data-filter="cancelled">Cancelled <span class="history-count"><?php echo $cancelledCount; ?></span></div>
            </div>
        </div>

        <!-- Reservation List -->
        <div class="reservation-list">
            <?php
            // Query to get student's room requests
            $sql = "SELECT rr.*, r.room_name, r.room_type, r.capacity, b.building_name,
                    (SELECT GROUP_CONCAT(e.name SEPARATOR ', ') 
                     FROM room_equipment re 
                     JOIN equipment e ON re.equipment_id = e.id 
                     WHERE re.room_id = r.id) AS equipment_list
                    FROM room_requests rr 
                    JOIN rooms r ON rr.RoomID = r.id 
                    JOIN buildings b ON r.building_id = b.id 
                    WHERE rr.StudentID = ? 
                    ORDER BY rr.StartTime DESC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $requestId = $row['RequestID'];
                    $activityName = htmlspecialchars($row['ActivityName']);
                    $purpose = htmlspecialchars($row['Purpose']);
                    $roomName = htmlspecialchars($row['room_name']);
                    $buildingName = htmlspecialchars($row['building_name']);
                    $roomType = htmlspecialchars($row['room_type']);
                    $capacity = $row['capacity'];
                    $participants = $row['NumberOfParticipants'];
                    $requestDate = date('M j, Y', strtotime($row['RequestDate']));
                    $reservationDate = date('M j, Y', strtotime($row['StartTime']));
                    $startTime = date('g:i A', strtotime($row['StartTime']));
                    $endTime = date('g:i A', strtotime($row['EndTime']));
                    $status = $row['Status'];
                    $equipment = $row['equipment_list'] ?: 'None';
                    $endTimeObj = new DateTime($row['EndTime']);
                    $now = new DateTime();

                    // Determine type for filtering
                    $type = $status;
                    if ($status == 'approved') {
                        if ($endTimeObj < $now) {
                            $type = 'completed';
                        } else {
                            $type = 'upcoming';
                        }
                    } else if ($status == 'rejected') {
                        $type = 'cancelled';
                    }

                    // Set badge class and label based on status
                    $badgeClass = 'badge-' . $status;
                    $statusLabel = ucfirst($status);

                    if ($status == 'approved') {
                        if ($endTimeObj < $now) {
                            $badgeClass = 'badge-completed';
                            $statusLabel = 'Completed';
                        }
                    } else if ($status == 'rejected') {
                        $badgeClass = 'badge-cancelled';
                        $statusLabel = 'Cancelled';
                    }
            ?>
                    <div class="reservation-card" data-type="<?php echo $type; ?>" data-room="<?php echo strtolower($roomName); ?>" data-building="<?php echo strtolower($buildingName); ?>" data-status="<?php echo $status; ?>">
                        <div class="reservation-header">
                            <div class="room-info">
                                <div class="room-name">
                                    <?php echo $roomName; ?>
                                    <span class="status-badge <?php echo $badgeClass; ?>"><?php echo $statusLabel; ?></span>
                                </div>
                                <div class="building-name"><?php echo $buildingName; ?></div>
                            </div>
                            <div class="reservation-time">
                                <div class="reservation-date">
                                    <i class="fa fa-calendar"></i>
                                    <?php echo $reservationDate; ?>
                                </div>
                                <div class="reservation-hours"><?php echo $startTime; ?> - <?php echo $endTime; ?></div>
                            </div>
                        </div>
                        <div class="reservation-details">
                            <div class="detail-group">
                                <div class="detail-label">
                                    <i class="fa fa-map-marker"></i> Location
                                </div>
                                <div class="detail-value"><?php echo $buildingName; ?></div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">
                                    <i class="fa fa-th-large"></i> Room Type & Capacity
                                </div>
                                <div class="detail-value"><?php echo $roomType; ?>, <?php echo $capacity; ?> people</div>
                            </div>
                            <div class="detail-group">
                                <div class="detail-label">
                                    <i class="fa fa-desktop"></i> Equipment
                                </div>
                                <div class="detail-value">Available</div>
                            </div>
                        </div>
                        <div class="reservation-footer">
                            <div class="reserved-date">Reserved on <?php echo $requestDate; ?></div>
                            <button type="button" class="view-details-btn" onclick="showReservationDetails(<?php echo $requestId; ?>, '<?php echo htmlspecialchars($activityName, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($buildingName, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($roomName, ENT_QUOTES); ?>', '<?php echo $reservationDate; ?>', '<?php echo $startTime; ?>', '<?php echo $endTime; ?>', '<?php echo $participants; ?>', '<?php echo htmlspecialchars($purpose, ENT_QUOTES); ?>', '<?php echo $statusLabel; ?>', '<?php echo $type; ?>', '<?php echo htmlspecialchars($equipment, ENT_QUOTES); ?>', '<?php echo $capacity; ?>', '<?php echo $roomType; ?>')">
                                Details <i class="fa fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                <?php
                }
            } else {
                ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fa fa-history"></i>
                    </div>
                    <div class="empty-state-text">No reservation history yet</div>
                    <div class="empty-state-subtext">Your reservations will appear here</div>
                    <a href="std_browse_room.php" class="btn-action btn-new-request">Make a Reservation</a>
                </div>
            <?php
            }
            $stmt->close();
            $conn->close();
            ?>
        </div>
    </div>
</div>

<!-- Reservation Details Modal -->
<div class="modal" id="reservationDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reservation Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="reservationDetailsContent">
                    <!-- Content will be filled by JavaScript -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- footer content -->
<footer>
    <div class="pull-right">
        Meycauayan College Incorporated - <a href="#">Mission || Vision || Values</a>
    </div>
    <div class="clearfix"></div>
</footer>
<!-- /footer content -->

<!-- Include external JavaScript file -->
<script src="../public/js/user_scripts/reservation_history.js"></script>
<?php include "../partials/footer.php"; ?>