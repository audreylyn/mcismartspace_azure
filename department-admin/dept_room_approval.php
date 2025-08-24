<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

// Include room status handler to automatically update room statuses
require_once '../auth/room_status_handler.php';

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'includes/approve-reject.php';
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
    
    <style>
        /* Additional modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 80%;
            max-width: 600px;
            position: relative;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
        
        .modal-title {
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .modal-footer {
            margin-top: 20px;
            text-align: right;
        }
        
        /* Styles for the filter buttons */
        #clearFilters {
            cursor: pointer;
            padding: 8px 16px;
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        #clearFilters:hover {
            background-color: #e0e0e0;
        }

        .predefined-reasons {
            margin-bottom: 15px;
        }

        .reason-options {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
            margin-bottom: 10px;
        }

        .reason-option {
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 6px 12px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .reason-option:hover {
            background-color: #e6e6e6;
            border-color: #999;
        }
    </style>

</head>

<body>
    <div id="app">
        <?php include 'layout/topnav.php'; ?>
        <?php include 'layout/sidebar.php'; ?>


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
                        // Get the department of the logged-in admin
                        $admin_department = $_SESSION['department'] ?? '';

                        if (empty($admin_department)) {
                            // Optional: Handle cases where department is not set for the admin
                            echo "<div class='no-results'>Department not configured for this admin.</div>";
                            $requests = [];
                            $requestCount = 0;
                        } else {
                            // Query to get room requests with room and user info, filtered by department
                            $sql = "SELECT rr.*, r.room_name, r.capacity, r.room_type, b.building_name,
                                    CASE 
                                        WHEN rr.StudentID IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
                                        WHEN rr.TeacherID IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
                                    END as RequesterName,
                                    CASE 
                                        WHEN rr.StudentID IS NOT NULL THEN 'Student'
                                        WHEN rr.TeacherID IS NOT NULL THEN 'Teacher'
                                    END as RequesterType,
                                    s.Department as StudentDepartment,
                                    t.Department as TeacherDepartment,
                                    DATEDIFF(DATE(rr.StartTime), CURDATE()) as DaysUntilReservation,
                                    rr.RequestDate as RequestDate
                                    FROM room_requests rr
                                    LEFT JOIN rooms r ON rr.RoomID = r.id
                                    LEFT JOIN buildings b ON r.building_id = b.id
                                    LEFT JOIN student s ON rr.StudentID = s.StudentID
                                    LEFT JOIN teacher t ON rr.TeacherID = t.TeacherID
                                    HAVING StudentDepartment = ? OR TeacherDepartment = ?
                                    ORDER BY 
                                        CASE WHEN rr.Status = 'pending' THEN 0
                                             WHEN rr.Status = 'approved' THEN 1
                                             WHEN rr.Status = 'rejected' THEN 2
                                        END ASC,
                                        CASE WHEN rr.TeacherID IS NOT NULL THEN 0 ELSE 1 END, /* Teachers first */
                                        DATEDIFF(DATE(rr.StartTime), CURDATE()) ASC, /* Prioritize by how soon the date is */
                                        rr.RequestDate ASC";

                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ss", $admin_department, $admin_department);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
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
                                    $priorityScore -= 500;
                                    $priorityLabel = "Today";
                                    $priorityClass = "priority-urgent";
                                } else if ($daysUntil <= 1) {
                                    $priorityScore -= 400;
                                    $priorityLabel = "Tomorrow";
                                    $priorityClass = "priority-high";
                                } else if ($daysUntil <= 3) {
                                    $priorityScore -= 300;
                                    $priorityLabel = "Soon";
                                    $priorityClass = "priority-medium";
                                } else if ($daysUntil <= 7) {
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
                                $statusA = $a['Status'] == 'pending' ? 0 : ($a['Status'] == 'approved' ? 1 : 2);
                                $statusB = $b['Status'] == 'pending' ? 0 : ($b['Status'] == 'approved' ? 1 : 2);
                                if ($statusA != $statusB) {
                                    return $statusA - $statusB;
                                }
                                return $a['PriorityScore'] - $b['PriorityScore'];
                            });
                        }

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
                                        
                                        <?php if ($status == 'approved' && (!empty($row['ApproverFirstName']) || !empty($row['ApproverLastName']))): ?>
                                            <div class="request-detail-item">
                                                <i class="mdi mdi-check-circle" style="color: var(--success-color);"></i>
                                                <span>Approved by: <?php echo htmlspecialchars($row['ApproverFirstName'] . ' ' . $row['ApproverLastName']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($status == 'rejected' && (!empty($row['RejecterFirstName']) || !empty($row['RejecterLastName']))): ?>
                                            <div class="request-detail-item">
                                                <i class="mdi mdi-cancel" style="color: var(--danger-color);"></i>
                                                <span>Rejected by: <?php echo htmlspecialchars($row['RejecterFirstName'] . ' ' . $row['RejecterLastName']); ?></span>
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
                        <div class="predefined-reasons">
                            <label>Select a reason or enter your own:</label>
                            <div class="reason-options">
                                <button type="button" class="reason-option" onclick="selectReason('Room unavailable due to maintenance')">Room unavailable</button>
                                <button type="button" class="reason-option" onclick="selectReason('Scheduling conflict with another event')">Scheduling conflict</button>
                                <button type="button" class="reason-option" onclick="selectReason('Insufficient information provided')">Insufficient info</button>
                                <button type="button" class="reason-option" onclick="selectReason('Exceeds room capacity')">Exceeds capacity</button>
                                <button type="button" class="reason-option" onclick="selectReason('Request does not meet department policy')">Policy violation</button>
                            </div>
                        </div>
                        <label for="rejection_reason">Reason for rejection:</label>
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
                    <span class="close" onclick="closeDetailsModal()">&times;</span>
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

        // When the DOM is loaded, initialize everything
        document.addEventListener('DOMContentLoaded', function() {
            // Auto fade-out alerts after 3 seconds
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
            
            // Initialize dropdown menus
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(function(dropdown) {
                const next = dropdown.nextElementSibling;
                if (next) {
                    next.style.display = 'none';
                }
            });

            // Initialize filters
            document.getElementById('searchInput').addEventListener('keyup', filterRequests);
            document.getElementById('statusFilter').addEventListener('change', filterRequests);
            document.getElementById('dateFilter').addEventListener('change', filterRequests);
            document.getElementById('priorityFilter').addEventListener('change', filterRequests);
            document.getElementById('clearFilters').addEventListener('click', clearFilters);
            
            // Show request count
            document.getElementById('requestCount').textContent = "Showing <?php echo $requestCount; ?> of <?php echo $requestCount; ?> requests";
        });

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
        
        // Function to select a predefined rejection reason
        function selectReason(reason) {
            document.getElementById('rejection_reason').value = reason;
        }

        // Request details modal functions
        function showRequestDetails(requestId) {
            // Prepare the data for the modal
            <?php
            $detailsScript = '';
            // Use the requests array we already have instead of querying again
            foreach ($requests as $row) {
                $id = $row['RequestID'];
                $purpose = htmlspecialchars($row['Purpose'] ?? '', ENT_QUOTES);
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
                
                // Add approved by information if the request is approved
                if ($row['Status'] === 'approved' && (!empty($row['ApproverFirstName']) || !empty($row['ApproverLastName']))) {
                    $approverFullName = htmlspecialchars($row['ApproverFirstName'] . ' ' . $row['ApproverLastName'], ENT_QUOTES);
                    $detailsScript .= "<p style=\"color: var(--success-color);\"><strong>Approved by:</strong> <span>" . $approverFullName . "</span></p>";
                }

                if ($row['Status'] === 'rejected') {
                    if (!empty($row['RejectionReason'])) {
                        $detailsScript .= "<p style=\"color: var(--danger-color);\"><strong>Rejection Reason:</strong> <span>" . htmlspecialchars($row['RejectionReason'], ENT_QUOTES) . "</span></p>";
                    }
                    
                    if (!empty($row['RejecterFirstName']) || !empty($row['RejecterLastName'])) {
                        $rejecterFullName = htmlspecialchars($row['RejecterFirstName'] . ' ' . $row['RejecterLastName'], ENT_QUOTES);
                        $detailsScript .= "<p style=\"color: var(--danger-color);\"><strong>Rejected by:</strong> <span>" . $rejecterFullName . "</span></p>";
                    }
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

        function filterRequests() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const statusValue = document.getElementById('statusFilter').value;
            const dateValue = document.getElementById('dateFilter').value;
            const priorityValue = document.getElementById('priorityFilter').value;

            const requestCards = document.querySelectorAll('.request-card');
            let visibleCount = 0;

            requestCards.forEach(card => {
                let show = true;
                const cardText = card.textContent.toLowerCase();
                const cardStatus = card.getAttribute('data-status');
                const cardDate = new Date(card.getAttribute('data-reservation-date'));
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

                    const cardDateObj = new Date(cardDate);
                    cardDateObj.setHours(0, 0, 0, 0);

                    switch (dateValue) {
                        case 'today':
                            if (cardDateObj.toDateString() !== today.toDateString()) {
                                show = false;
                            }
                            break;
                        case 'tomorrow':
                            if (cardDateObj.toDateString() !== tomorrow.toDateString()) {
                                show = false;
                            }
                            break;
                        case 'week':
                            if (cardDateObj < today || cardDateObj > weekEnd) {
                                show = false;
                            }
                            break;
                        case 'month':
                            if (cardDateObj < today || cardDateObj > monthEnd) {
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