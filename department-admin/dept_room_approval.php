<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

// Include room status handler to automatically update room statuses
require_once '../auth/room_status_handler.php';

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Process approve/reject actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve_request'])) {
        $requestId = intval($_POST['request_id']);
        $sql = "UPDATE room_requests SET Status = 'approved' WHERE RequestID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $requestId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Request approved successfully";
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['reject_request'])) {
        $requestId = intval($_POST['request_id']);
        $rejectionReason = trim($_POST['rejection_reason']);

        $sql = "UPDATE room_requests SET Status = 'rejected', RejectionReason = ? WHERE RequestID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $rejectionReason, $requestId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Request rejected successfully";
        } else {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    // Redirect to prevent form resubmission
    header("Location: dept_room_approval.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Approval</title>
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/room_approval.css">

</head>

<body>
    <div id="app">
        <nav id="navbar-main" class="navbar is-fixed-top">
            <div class="navbar-brand">
                <a class="navbar-item mobile-aside-button">
                    <span class="icon"><i class="mdi mdi-forwardburger mdi-24px"></i></span>
                </a>
                <div class="navbar-item">
                    <section class="is-title-bar">
                        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
                            <ul>
                                <li>Department Admin</li>
                                <li>Room Approval</li>
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
                        <a class="navbar-link">

                            <span>Hello, <?php echo $_SESSION['first_name']; ?></span>
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
                    <p>MCiSmartSpace</p>
                </div>
            </div>
            <div class="menu is-menu-main">
                <ul class="menu-list">
                    <li>
                        <a href="dept-admin.php">
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                    <path d="M5 4h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1"></path>
                                    <path d="M5 16h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1"></path>
                                    <path d="M15 12h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1"></path>
                                    <path d="M15 4h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1"></path>
                                </svg> </span>
                            <span>Dashboard</span>
                        </a>
                    </li>
                </ul>
                <ul class="menu-list">
                    <li class="active">
                        <a href="#">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2 flex-shrink-0" data-lov-id="src/components/layout/Sidebar.tsx:89:20" data-lov-name="Icon" data-component-path="src/components/layout/Sidebar.tsx" data-component-line="89" data-component-file="Sidebar.tsx" data-component-name="Icon" data-component-content="%7B%22className%22%3A%22flex-shrink-0%22%7D">
                                    <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                                    <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                                    <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                                    <path d="M10 6h4"></path>
                                    <path d="M10 10h4"></path>
                                    <path d="M10 14h4"></path>
                                    <path d="M10 18h4"></path>
                                </svg>
                            </span>
                            <span>Room Approval</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown" onclick="toggleIcon(this)">
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                    <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                                    <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path>
                                </svg></span>
                            <span class="#">Manage Accounts</span>
                            <span class="icon toggle-icon"><i class="mdi mdi-plus"></i></span>
                        </a>
                        <ul>
                            <li>
                                <a href="dept_add_teacher.php">
                                    <span>Add Teacher</span>
                                </a>
                            </li>
                            <li>
                                <a href="dept_add_student.php">
                                    <span>Add Student</span>
                                </a>
                            </li>
                            <li>
                                <a href="dept_edit_teachers.php">
                                    <span>Edit Teachers</span>
                                </a>
                            </li>
                            <li>
                                <a href="dept_edit_students.php">
                                    <span>Edit Students</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="dept_equipment_report.php">
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                    <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37c.996.608 2.296.07 2.572-1.065z"></path>
                                    <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"></path>
                                </svg></span>
                            <span>Equipment Report</span>
                        </a>
                    </li>
                    <li>
                        <a href="qr_generator.php">
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code">
                                    <rect width="5" height="5" x="3" y="3" rx="1"></rect>
                                    <rect width="5" height="5" x="16" y="3" rx="1"></rect>
                                    <rect width="5" height="5" x="3" y="16" rx="1"></rect>
                                    <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                                    <path d="M21 21v.01"></path>
                                    <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
                                    <path d="M3 12h.01"></path>
                                    <path d="M12 3h.01"></path>
                                    <path d="M12 16v.01"></path>
                                    <path d="M16 12h1"></path>
                                    <path d="M21 12v.01"></path>
                                    <path d="M12 21v-1"></path>
                                </svg></span>
                            <span>QR Generator</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>


        <div class="main-container">
            <div>
                <div class="card-content">
                    <!-- Display success/error messages -->
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <?php
                        // Determine if this is a rejection success message
                        $alertClass = (strpos($_SESSION['success_message'], 'rejected') !== false) ? 'alert-reject' : 'alert-success';
                        ?>
                        <div class="alert <?php echo $alertClass; ?> fade-alert">
                            <?php
                            echo $_SESSION['success_message'];
                            unset($_SESSION['success_message']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger fade-alert">
                            <?php
                            echo $_SESSION['error_message'];
                            unset($_SESSION['error_message']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <!-- Search and Filters -->
                    <div class="search-container">
                        <input type="text" id="searchInput" class="search-input" placeholder="Search by activity, room, or requester...">
                        <i class="mdi mdi-magnify search-icon"></i>
                    </div>

                    <div class="filters">
                        <div class="filter-item">
                            <label class="filter-label">Status</label>
                            <select id="statusFilter" class="filter-control">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label class="filter-label">Date</label>
                            <select id="dateFilter" class="filter-control">
                                <option value="">All Dates</option>
                                <option value="today">Today</option>
                                <option value="tomorrow">Tomorrow</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label class="filter-label">Building</label>
                            <select id="buildingFilter" class="filter-control">
                                <option value="">All Buildings</option>
                                <?php
                                // Get unique buildings
                                $buildingSql = "SELECT DISTINCT b.building_name 
                                               FROM room_requests rr
                                               LEFT JOIN rooms r ON rr.RoomID = r.id
                                               LEFT JOIN buildings b ON r.building_id = b.id
                                               WHERE b.building_name IS NOT NULL
                                               ORDER BY b.building_name";
                                $buildingResult = $conn->query($buildingSql);
                                if ($buildingResult && $buildingResult->num_rows > 0) {
                                    while ($buildingRow = $buildingResult->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($buildingRow['building_name']) . '">' .
                                            htmlspecialchars($buildingRow['building_name']) .
                                            '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="filter-item">
                            <label class="filter-label">Priority</label>
                            <select id="priorityFilter" class="filter-control">
                                <option value="">All Priorities</option>
                                <option value="teacher">Teacher Requests</option>
                                <option value="urgent">Urgent (Today/Tomorrow)</option>
                                <option value="week">This Week</option>
                            </select>
                        </div>
                        <button id="clearFilters">Clear Filters</button>
                    </div>

                    <div class="results-count" id="requestCount"></div>

                    <!-- Room Request Cards -->
                    <div id="requestsContainer">
                        <?php
                        // Query to get room requests with room and user info
                        $sql = "SELECT rr.*, r.room_name, r.capacity, r.room_type, b.building_name,
                                CASE 
                                    WHEN rr.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
                                    WHEN rr.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
                                END as RequesterName,
                                CASE 
                                    WHEN rr.StudentID IS NOT NULL THEN 'Student'
                                    WHEN rr.TeacherID IS NOT NULL THEN 'Teacher'
                                END as RequesterType,
                                DATEDIFF(DATE(rr.StartTime), CURDATE()) as DaysUntilReservation,
                                rr.RequestDate as RequestDate
                                FROM room_requests rr
                                LEFT JOIN rooms r ON rr.RoomID = r.id
                                LEFT JOIN buildings b ON r.building_id = b.id
                                LEFT JOIN student s ON rr.StudentID = s.StudentID
                                LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
                                ORDER BY 
                                    CASE WHEN rr.Status = 'pending' THEN 0
                                         WHEN rr.Status = 'approved' THEN 1
                                         WHEN rr.Status = 'rejected' THEN 2
                                    END ASC,
                                    CASE WHEN rr.TeacherID IS NOT NULL THEN 0 ELSE 1 END, /* Teachers first */
                                    DATEDIFF(DATE(rr.StartTime), CURDATE()) ASC, /* Prioritize by how soon the date is */
                                    rr.RequestDate ASC"; /* Older requests come first for same dates */

                        $result = $conn->query($sql);
                        $requestCount = $result->num_rows;

                        // Calculate priority for sorting and display
                        $requests = [];
                        while ($row = $result->fetch_assoc()) {
                            // Calculate priority score (lower = higher priority)
                            $priorityScore = 0;

                            // Teacher requests get higher priority (subtract 1000 to ensure they're always first)
                            if ($row['RequesterType'] == 'Teacher') {
                                $priorityScore -= 1000;
                            }

                            // Urgent requests (within next 3 days) get higher priority
                            $daysUntil = $row['DaysUntilReservation'];
                            if ($daysUntil <= 0) {
                                // Today's reservations are highest priority
                                $priorityScore -= 500;
                                $priorityLabel = "Today";
                                $priorityClass = "priority-urgent";
                            } else if ($daysUntil <= 1) {
                                // Tomorrow's reservations
                                $priorityScore -= 400;
                                $priorityLabel = "Tomorrow";
                                $priorityClass = "priority-high";
                            } else if ($daysUntil <= 3) {
                                // Within 3 days
                                $priorityScore -= 300;
                                $priorityLabel = "Soon";
                                $priorityClass = "priority-medium";
                            } else if ($daysUntil <= 7) {
                                // Within a week
                                $priorityScore -= 200;
                                $priorityLabel = "This Week";
                                $priorityClass = "priority-normal";
                            } else {
                                $priorityLabel = "Scheduled";
                                $priorityClass = "priority-low";
                            }

                            // Store priority information in the row
                            $row['PriorityScore'] = $priorityScore;
                            $row['PriorityLabel'] = $priorityLabel;
                            $row['PriorityClass'] = $priorityClass;
                            $row['DaysUntil'] = $daysUntil;

                            $requests[] = $row;
                        }

                        // Sort requests by priority score
                        usort($requests, function ($a, $b) {
                            // First compare status (pending first)
                            $statusA = $a['Status'] == 'pending' ? 0 : ($a['Status'] == 'approved' ? 1 : 2);
                            $statusB = $b['Status'] == 'pending' ? 0 : ($b['Status'] == 'approved' ? 1 : 2);

                            if ($statusA != $statusB) {
                                return $statusA - $statusB;
                            }

                            // Then sort by priority score (lower score = higher priority)
                            return $a['PriorityScore'] - $b['PriorityScore'];
                        });

                        // Display the count
                        $requestCount = count($requests);

                        if ($requestCount > 0):
                            foreach ($requests as $row):
                                $requestId = $row['RequestID'];
                                $activityName = htmlspecialchars($row['ActivityName']);
                                $roomName = htmlspecialchars($row['room_name']);
                                $buildingName = htmlspecialchars($row['building_name']);
                                $reservationDate = date('M j, Y', strtotime($row['StartTime']));
                                $startTime = date('g:i A', strtotime($row['StartTime']));
                                $endTime = date('g:i A', strtotime($row['EndTime']));
                                $timeRange = "$startTime - $endTime";
                                $participants = $row['NumberOfParticipants'];
                                $requesterName = htmlspecialchars($row['RequesterName']);
                                $requesterType = $row['RequesterType'];
                                $status = $row['Status'];
                                $priorityLabel = $row['PriorityLabel'];
                                $priorityClass = $row['PriorityClass'];
                                $daysUntil = $row['DaysUntil'];

                                // Set info icon class based on status
                                $iconClass = 'info-icon-' . $status;
                        ?>
                                <div class="request-card"
                                    data-status="<?php echo $status; ?>"
                                    data-reservation-date="<?php echo date('Y-m-d', strtotime($row['StartTime'])); ?>"
                                    data-building="<?php echo htmlspecialchars($buildingName); ?>"
                                    data-requester-type="<?php echo $requesterType; ?>"
                                    data-days-until="<?php echo $daysUntil; ?>"
                                    data-priority-score="<?php echo $row['PriorityScore']; ?>">

                                    <div class="request-title">
                                        <h3><?php echo $activityName; ?></h3>
                                        <div class="card-indicators">
                                            <i class="mdi mdi-information-outline info-icon <?php echo $iconClass; ?>" onclick="showRequestDetails(<?php echo $requestId; ?>)"></i>
                                        </div>
                                    </div>

                                    <div class="request-details">
                                        <div class="request-detail-item"><i class="mdi mdi-domain"></i><?php echo $roomName . ', ' . $buildingName; ?></div>
                                        <div class="request-detail-item"><i class="mdi mdi-calendar"></i><?php echo $reservationDate; ?></div>
                                        <div class="request-detail-item"><i class="mdi mdi-clock-outline"></i><?php echo $timeRange; ?></div>
                                        <div class="request-detail-item">
                                            <i class="mdi mdi-account"></i>
                                            <?php echo $requesterName; ?>
                                            <span class="requester-badge <?php echo strtolower($requesterType); ?>-badge"><?php echo $requesterType; ?></span>
                                        </div>
                                        <div class="request-detail-item"><i class="mdi mdi-account-group"></i><?php echo $participants; ?> participants</div>

                                        <?php if ($status == 'pending'): ?>
                                            <div class="request-detail-item priority-item">
                                                <i class="mdi mdi-clock-alert-outline"></i>
                                                <span class="priority-badge <?php echo $priorityClass; ?>"><?php echo $priorityLabel; ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($status == 'pending'): ?>
                                        <div class="action-buttons">
                                            <form method="POST" style="flex: 1;">
                                                <input type="hidden" name="request_id" value="<?php echo $requestId; ?>">
                                                <button type="submit" name="approve_request" class="btn-approve">Approve</button>
                                            </form>
                                            <button type="button" class="btn-reject" onclick="showRejectModal(<?php echo $requestId; ?>)">Reject</button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="no-results">No room requests found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejection Modal -->
        <div id="rejectModal" class="modal">
            <div class="modal-content">
                <h3 class="modal-title">Rejection Reason
                    <span class="close" onclick="closeRejectModal()">&times;</span>
                </h3>
                <div class="modal-body">
                    <form id="rejectForm" method="POST">
                        <input type="hidden" id="rejectRequestId" name="request_id">
                        <label for="rejection_reason">Please provide a reason for rejecting this request:</label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="4" required></textarea>
                        <div class="modal-footer">
                            <button type="button" onclick="closeRejectModal()" class="modal-btn modal-btn-cancel">Cancel</button>
                            <button type="submit" name="reject_request" class="modal-btn modal-btn-reject">Confirm Rejection</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Request Details Modal -->
        <div id="detailsModal" class="modal">
            <div class="modal-content">
                <h3 class="modal-title">Request Details
                </h3>
                <div class="modal-body" id="detailsModalContent">
                    <!-- Details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeDetailsModal()" class="btn-approve">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script>
        // Function to toggle dropdown menus
        function toggleIcon(e) {
            e.classList.toggle("active");
            var next = e.nextElementSibling;
            if (next.style.display === "block") {
                next.style.display = "none";
                e.querySelector('.toggle-icon i').className = "mdi mdi-plus";
            } else {
                next.style.display = "block";
                e.querySelector('.toggle-icon i').className = "mdi mdi-minus";
            }
        }

        // Auto fade-out alerts after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.fade-alert');
            if (alerts.length > 0) {
                setTimeout(function() {
                    alerts.forEach(function(alert) {
                        alert.classList.add('fade-out');
                        setTimeout(function() {
                            alert.style.display = 'none';
                        }, 500); // Wait for fade animation to complete
                    });
                }, 3000); // 3 seconds
            }
        });

        // Show request count
        document.getElementById('requestCount').textContent = "Showing <?php echo $requestCount; ?> of <?php echo $requestCount; ?> requests";

        // Rejection modal functions
        function showRejectModal(requestId) {
            document.getElementById('rejectRequestId').value = requestId;
            document.getElementById('rejectModal').style.display = 'block';
            document.getElementById('rejection_reason').focus();
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').style.display = 'none';
            document.getElementById('rejection_reason').value = '';
        }

        // Request details modal functions
        function showRequestDetails(requestId) {
            // In a real implementation, you would fetch details via AJAX
            // For now, we'll use PHP to show the request details stored in data attributes
            <?php
            $detailsScript = '';
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $id = $row['RequestID'];
                $purpose = htmlspecialchars($row['Purpose'], ENT_QUOTES);
                $statusClass = '';
                switch ($row['Status']) {
                    case 'pending':
                        $statusClass = 'status-pending';
                        break;
                    case 'approved':
                        $statusClass = 'status-approved';
                        break;
                    case 'rejected':
                        $statusClass = 'status-rejected';
                        break;
                }

                $detailsScript .= "if(requestId === $id) {\n";
                $detailsScript .= "  let detailsHtml = `
                    <p><strong>Request ID:</strong> <span>$id</span></p>
                    <p><strong>Activity:</strong> <span>" . htmlspecialchars($row['ActivityName'], ENT_QUOTES) . "</span></p>
                    <p><strong>Purpose:</strong> <span>$purpose</span></p>
                    <p><strong>Room:</strong> <span>" . htmlspecialchars($row['room_name'], ENT_QUOTES) . ", " . htmlspecialchars($row['building_name'], ENT_QUOTES) . "</span></p>
                    <p><strong>Date:</strong> <span>" . date('F j, Y', strtotime($row['StartTime'])) . "</span></p>
                    <p><strong>Time:</strong> <span>" . date('g:i A', strtotime($row['StartTime'])) . " - " . date('g:i A', strtotime($row['EndTime'])) . "</span></p>
                    <p><strong>Participants:</strong> <span>" . $row['NumberOfParticipants'] . "</span></p>
                    <p><strong>Status:</strong> <span class=\"status-badge $statusClass\">" . ucfirst($row['Status']) . "</span></p>";

                if ($row['Status'] === 'rejected' && !empty($row['RejectionReason'])) {
                    $detailsScript .= "<p style=\"color: var(--danger-color);\"><strong>Rejection Reason:</strong> <span>" . htmlspecialchars($row['RejectionReason'], ENT_QUOTES) . "</span></p>";
                }

                $detailsScript .= "`;\n";
                $detailsScript .= "  document.getElementById('detailsModalContent').innerHTML = detailsHtml;\n";
                $detailsScript .= "}\n";
            }
            echo $detailsScript;
            ?>

            document.getElementById('detailsModal').style.display = 'block';
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', filterRequests);
        document.getElementById('statusFilter').addEventListener('change', filterRequests);
        document.getElementById('dateFilter').addEventListener('change', filterRequests);
        document.getElementById('buildingFilter').addEventListener('change', filterRequests);
        document.getElementById('priorityFilter').addEventListener('change', filterRequests);
        document.getElementById('clearFilters').addEventListener('click', clearFilters);

        function filterRequests() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const statusValue = document.getElementById('statusFilter').value;
            const dateValue = document.getElementById('dateFilter').value;
            const buildingValue = document.getElementById('buildingFilter').value;
            const priorityValue = document.getElementById('priorityFilter').value;

            const requestCards = document.querySelectorAll('.request-card');
            let visibleCount = 0;

            requestCards.forEach(card => {
                let show = true;
                const cardText = card.textContent.toLowerCase();
                const cardStatus = card.getAttribute('data-status');
                const cardDate = new Date(card.getAttribute('data-reservation-date'));
                const cardBuilding = card.getAttribute('data-building');
                const cardRequesterType = card.getAttribute('data-requester-type');
                const cardDaysUntil = parseInt(card.getAttribute('data-days-until'));

                // Filter by search text
                if (searchValue && !cardText.includes(searchValue)) {
                    show = false;
                }

                // Filter by status
                if (statusValue && cardStatus !== statusValue) {
                    show = false;
                }

                // Filter by building
                if (buildingValue && cardBuilding !== buildingValue) {
                    show = false;
                }

                // Filter by priority
                if (priorityValue) {
                    switch (priorityValue) {
                        case 'teacher':
                            if (cardRequesterType !== 'Teacher') {
                                show = false;
                            }
                            break;
                        case 'urgent':
                            if (cardDaysUntil > 1) { // Today or tomorrow
                                show = false;
                            }
                            break;
                        case 'soon':
                            if (cardDaysUntil > 3) { // Within 3 days
                                show = false;
                            }
                            break;
                        case 'week':
                            if (cardDaysUntil > 7) { // Within a week
                                show = false;
                            }
                            break;
                    }
                }

                // Filter by date
                if (dateValue) {
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);

                    const tomorrow = new Date(today);
                    tomorrow.setDate(tomorrow.getDate() + 1);

                    const weekEnd = new Date(today);
                    weekEnd.setDate(weekEnd.getDate() + 7);

                    const monthEnd = new Date(today);
                    monthEnd.setMonth(monthEnd.getMonth() + 1);

                    switch (dateValue) {
                        case 'today':
                            const cardDay = new Date(cardDate);
                            cardDay.setHours(0, 0, 0, 0);
                            if (cardDay.getTime() !== today.getTime()) {
                                show = false;
                            }
                            break;
                        case 'tomorrow':
                            const nextDay = new Date(cardDate);
                            nextDay.setHours(0, 0, 0, 0);
                            if (nextDay.getTime() !== tomorrow.getTime()) {
                                show = false;
                            }
                            break;
                        case 'week':
                            if (cardDate < today || cardDate > weekEnd) {
                                show = false;
                            }
                            break;
                        case 'month':
                            if (cardDate < today || cardDate > monthEnd) {
                                show = false;
                            }
                            break;
                    }
                }

                // Show or hide card
                if (show) {
                    card.style.display = '';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Show no results message if no cards are visible
            const noResultsMsg = document.querySelector('.no-results');
            if (visibleCount === 0) {
                if (!noResultsMsg) {
                    const noResults = document.createElement('div');
                    noResults.className = 'no-results';
                    noResults.textContent = 'No matching requests found';
                    document.getElementById('requestsContainer').appendChild(noResults);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }

            // Update displayed count
            document.getElementById('requestCount').textContent = `Showing ${visibleCount} of <?php echo $requestCount; ?> requests`;
        }

        function clearFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = '';
            document.getElementById('dateFilter').value = '';
            document.getElementById('buildingFilter').value = '';
            document.getElementById('priorityFilter').value = '';

            const requestCards = document.querySelectorAll('.request-card');
            requestCards.forEach(card => {
                card.style.display = '';
            });

            // Remove no results message if it exists
            const noResultsMsg = document.querySelector('.no-results');
            if (noResultsMsg) {
                noResultsMsg.remove();
            }

            document.getElementById('requestCount').textContent = "Showing <?php echo $requestCount; ?> of <?php echo $requestCount; ?> requests";
        }

        // Close modals when clicking outside the content
        window.onclick = function(event) {
            const rejectModal = document.getElementById('rejectModal');
            const detailsModal = document.getElementById('detailsModal');

            if (event.target == rejectModal) {
                closeRejectModal();
            }

            if (event.target == detailsModal) {
                closeDetailsModal();
            }
        }
    </script>
</body>

</html>