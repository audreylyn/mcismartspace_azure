<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

require_once '../auth/room_status_handler.php';

// Get user role and ID from session
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];
?>

<?php include "../partials/header_reservation-history.php"; ?>
<?php include "layout/sidebar.php"; ?>
<?php include "layout/topnav.php"; ?>

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

        // Get user info from session
        $userId = $_SESSION['user_id']; // User ID (either StudentID or TeacherID)
        $userRole = $_SESSION['role']; // User role (Student or Teacher)
        
        // Determine which ID field to use in the query based on role
        $idField = ($userRole === 'Student') ? 'StudentID' : 'TeacherID';

        // Get counts for each type
        $countSql = "SELECT 
                        COUNT(*) as TotalCount,
                        SUM(CASE WHEN EndTime > NOW() AND Status = 'approved' THEN 1 ELSE 0 END) as UpcomingCount,
                        SUM(CASE WHEN EndTime < NOW() AND Status = 'approved' THEN 1 ELSE 0 END) as CompletedCount,
                        SUM(CASE WHEN Status = 'rejected' THEN 1 ELSE 0 END) as CancelledCount
                     FROM room_requests 
                     WHERE $idField = ?";
        $countStmt = $conn->prepare($countSql);
        $countStmt->bind_param("i", $userId);
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
            // Query to get user's room requests
            $sql = "SELECT rr.*, r.room_name, r.room_type, r.capacity, b.building_name,
                    (SELECT GROUP_CONCAT(e.name SEPARATOR ', ') 
                     FROM room_equipment re 
                     JOIN equipment e ON re.equipment_id = e.id 
                     WHERE re.room_id = r.id) AS equipment_list
                    FROM room_requests rr 
                    JOIN rooms r ON rr.RoomID = r.id 
                    JOIN buildings b ON r.building_id = b.id 
                    WHERE rr.$idField = ? 
                    ORDER BY rr.StartTime DESC";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $userId);
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
                    $rejectionReason = $row['RejectionReason'] ?? ''; // <-- Fetch rejection reason
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
                        <button type="button" class="view-details-btn" onclick="showReservationDetails(
                                <?php echo $requestId; ?>,
                                '<?php echo htmlspecialchars($activityName, ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($buildingName, ENT_QUOTES); ?>',
                                '<?php echo htmlspecialchars($roomName, ENT_QUOTES); ?>',
                                '<?php echo $reservationDate; ?>',
                                '<?php echo $startTime; ?>',
                                '<?php echo $endTime; ?>',
                                '<?php echo $participants; ?>',
                                '<?php echo htmlspecialchars($purpose, ENT_QUOTES); ?>',
                                '<?php echo $statusLabel; ?>',
                                '<?php echo $type; ?>',
                                '<?php echo htmlspecialchars($equipment, ENT_QUOTES); ?>',
                                '<?php echo $capacity; ?>',
                                '<?php echo $roomType; ?>',
                                '<?php echo htmlspecialchars($rejectionReason, ENT_QUOTES); ?>' 
                            )">
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
                    <a href="users_browse_room.php" class="btn-action btn-new-request">Make a Reservation</a>
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
            <div class="modal-footer">
                <button type="button" class="btn-action btn-print" id="printButton" style="display:none;">
                    <i class="fa fa-file-pdf-o"></i> Export PDF
                </button>
                <div id="actionButtons">
                    <!-- Action buttons will be added dynamically based on status -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Cancel Modal -->
<div class="modal" id="confirmCancelModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="alert-style-modal">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5>Cancel Request</h5>
                <p>Are you sure you want to cancel this room request? This action cannot be undone.</p>
                <div class="alert-actions">
                    <button type="button" class="btn-action btn-secondary" data-dismiss="modal">No, Keep Request</button>
                    <button type="button" class="btn-action btn-danger" id="confirmCancelButton">
                        Yes, Cancel Request
                    </button>
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
<script src="../public/js/user_scripts/reservation_history_updated.js"></script>
<?php include "../partials/footer.php"; ?>