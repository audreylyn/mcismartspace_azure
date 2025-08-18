<?php
require '../auth/middleware.php';
checkAccess(['Student']);

require_once '../auth/room_status_handler.php';
?>

<?php include "../partials/header.php"; ?>
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
                    <li class="active">
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
                    <li>
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
    <div class="request-status-container">
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

        <div>
            <h3 class="title">Room Reservation Requests</h3>
            <p class="subtitle">View the status of all your room reservation requests</p>

            <?php
            // Initialize session if not already started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            db();

            // Get student ID from session
            $studentId = $_SESSION['user_id']; // Adjust based on your session structure

            // Get counts for each status
            $countSql = "SELECT Status, COUNT(*) as Count FROM room_requests WHERE StudentID = ? GROUP BY Status";
            $countStmt = $conn->prepare($countSql);
            $countStmt->bind_param("i", $studentId);
            $countStmt->execute();
            $countResult = $countStmt->get_result();

            // Initialize counts
            $totalCount = 0;
            $pendingCount = 0;
            $approvedCount = 0;
            $rejectedCount = 0;

            // Set counts from result
            while ($row = $countResult->fetch_assoc()) {
                if ($row['Status'] == 'pending') {
                    $pendingCount = $row['Count'];
                } elseif ($row['Status'] == 'approved') {
                    $approvedCount = $row['Count'];
                } elseif ($row['Status'] == 'rejected') {
                    $rejectedCount = $row['Count'];
                }
                $totalCount += $row['Count'];
            }
            $countStmt->close();
            ?>

            <!-- Status Tabs -->
            <div class="status-tabs">
                <div class="status-tab active" data-status="all">All <span class="status-count"><?php echo $totalCount; ?></span></div>
                <div class="status-tab" data-status="pending">Pending <span class="status-count"><?php echo $pendingCount; ?></span></div>
                <div class="status-tab" data-status="approved">Approved <span class="status-count"><?php echo $approvedCount; ?></span></div>
                <div class="status-tab" data-status="rejected">Rejected <span class="status-count"><?php echo $rejectedCount; ?></span></div>
            </div>

            <!-- Request List -->
            <div class="request-list">
                <?php
                // Query to get student's room requests
                $sql = "SELECT rr.*, r.room_name, r.room_type, r.capacity, b.building_name 
                        FROM room_requests rr 
                        JOIN rooms r ON rr.RoomID = r.id 
                        JOIN buildings b ON r.building_id = b.id 
                        WHERE rr.StudentID = ?
                        ORDER BY rr.RequestDate DESC";

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
                        $participants = $row['NumberOfParticipants'];
                        $requestDate = date('M j, Y', strtotime($row['RequestDate']));
                        $reservationDate = date('M j, Y', strtotime($row['StartTime']));
                        $status = $row['Status'];
                        $rejectionReason = htmlspecialchars($row['RejectionReason'] ?? '');

                        // Set status badge class and icon
                        $badgeClass = '';
                        $statusIcon = '';
                        $statusLabel = '';

                        switch ($status) {
                            case 'pending':
                                $badgeClass = 'badge-pending';
                                $statusIcon = 'fa-clock-o';
                                $statusLabel = 'Pending';
                                break;
                            case 'approved':
                                $badgeClass = 'badge-approved';
                                $statusIcon = 'fa-check';
                                $statusLabel = 'Approved';
                                break;
                            case 'rejected':
                                $badgeClass = 'badge-rejected';
                                $statusIcon = 'fa-times';
                                $statusLabel = 'Rejected';
                                break;
                        }
                ?>
                        <div class="request-card" data-status="<?php echo $status; ?>">
                            <div class="request-header">
                                <div class="title-badge-row">
                                    <h4 class="request-title"><?php echo $activityName; ?></h4>
                                    <span class="status-badge <?php echo $badgeClass; ?>">
                                        <i class="fa <?php echo $statusIcon; ?>"></i> <?php echo $statusLabel; ?>
                                    </span>
                                </div>
                                <div class="request-location">
                                    <i class="fa fa-map-marker" aria-hidden="true"></i>
                                    <?php echo $roomName; ?> - <?php echo $buildingName; ?>
                                </div>
                                <div class="request-date">
                                    <div class="request-date-value">
                                        <i class="fa fa-calendar" aria-hidden="true"></i>
                                        <?php echo $reservationDate; ?>
                                    </div>
                                    <div class="request-time">
                                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                                        <?php echo date('g:i A', strtotime($row['StartTime'])); ?> - <?php echo date('g:i A', strtotime($row['EndTime'])); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <span class="detail-value">ID: <?php echo $requestId; ?></span>
                                <button type="button" class="view-details-btn" onclick="showRequestDetails(<?php echo $requestId; ?>, '<?php echo htmlspecialchars($activityName, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($buildingName, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($roomName, ENT_QUOTES); ?>', '<?php echo $reservationDate; ?>', '<?php echo date('g:i A', strtotime($row['StartTime'])); ?>', '<?php echo date('g:i A', strtotime($row['EndTime'])); ?>', '<?php echo $participants; ?>', '<?php echo htmlspecialchars($purpose, ENT_QUOTES); ?>', '<?php echo $statusLabel; ?>', '<?php echo htmlspecialchars($rejectionReason, ENT_QUOTES); ?>', '<?php echo $status; ?>')">
                                    View Details <i class="fa fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    <?php
                    }
                } else {
                    ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fa fa-calendar-o"></i>
                        </div>
                        <div class="empty-state-text">No room requests yet</div>
                        <div class="empty-state-subtext">Your room reservation requests will appear here</div>
                        <a href="std_browse_room.php" class="btn-next">Make a Reservation</a>
                    </div>
                <?php
                }
                $stmt->close();
                $conn->close();
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Request Details Modal -->
<div class="modal" id="requestDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Request Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="requestDetailsContent">
                <!-- Content will be filled by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-action btn-print" id="printButton">
                    <i class="fa fa-file-pdf-o"></i> Export PDF
                </button>
                <div id="actionButtons">
                    <!-- Action buttons will be added dynamically based on status -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Replace the existing confirmation modal HTML with this simplified version -->
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
<script src="../public/js/user_scripts/room_status.js"></script>
<?php include "../partials/footer.php"; ?>