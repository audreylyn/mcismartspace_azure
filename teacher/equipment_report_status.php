<?php
require '../auth/middleware.php';
checkAccess(['Teacher']);

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get current teacher ID
$teacherId = $_SESSION['user_id'];

// Fetch all equipment reports made by this teacher
$sql = "SELECT ei.*, e.name as equipment_name, r.room_name, b.building_name 
        FROM equipment_issues ei
        JOIN equipment e ON ei.equipment_id = e.id
        JOIN room_equipment re ON e.id = re.equipment_id
        JOIN rooms r ON re.room_id = r.id
        JOIN buildings b ON r.building_id = b.id
        WHERE ei.teacher_id = ?
        ORDER BY ei.reported_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();
$reports = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Helper function for status badge
function getStatusBadge($status)
{
    switch ($status) {
        case 'pending':
            return '<span class="status-tag pending">PENDING</span>';
        case 'in_progress':
            return '<span class="status-tag in-progress">IN PROGRESS</span>';
        case 'resolved':
            return '<span class="status-tag resolved">RESOLVED</span>';
        case 'rejected':
            return '<span class="status-tag rejected">REJECTED</span>';
        default:
            return '<span class="status-tag">' . strtoupper($status) . '</span>';
    }
}

// Helper function for condition badge
function getConditionBadge($condition)
{
    switch ($condition) {
        case 'working':
            return '<span class="condition-tag working">WORKING</span>';
        case 'needs_repair':
            return '<span class="condition-tag needs-repair">NEEDS REPAIR</span>';
        case 'maintenance':
            return '<span class="condition-tag maintenance">MAINTENANCE</span>';
        case 'missing':
            return '<span class="condition-tag missing">MISSING</span>';
        default:
            return '<span class="condition-tag">' . strtoupper($condition) . '</span>';
    }
}

// Include room status handler to automatically update room statuses
require_once '../auth/room_status_handler.php';
?>

<?php include "../partials/header.php"; ?>
<link href="../public/css/user_styles/equipment_report_status.css" rel="stylesheet">

<style>
    .search-wrapper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        width: 100%;
        max-width: 100%;
        flex-wrap: wrap;
    }

    .search-wrapper-inner {
        display: flex;
        gap: 15px;
        flex-wrap: nowrap;
    }

    @media screen and (max-width: 768px) {
        .search-wrapper-inner {
            flex-wrap: wrap;
        }
    }
</style>

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
                        <a href="tc_browse_room.php">
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
                        <a href="tc_room_status.php">
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
                        <a href="tc_reservation_history.php">
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
                    <li class="active">
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
    <div class="main-content">
        <h1 class="page-title-report">My Equipment Reports</h1>
        <p class="page-subtitle">Track the status of your equipment issue reports</p>

        <div class="wrap-report">
            <div class="search-wrapper">
                <div class="search-wrapper-inner">
                    <div class="search-box">
                        <i class="fa fa-search search-icon"></i>
                        <input type="text" id="searchInput" placeholder="Search equipment, location...">
                    </div>
                    <div class="status-filter">
                        <select id="statusFilter" class="filter-select">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="status-filter">
                        <select id="conditionFilter" class="filter-select">
                            <option value="">All Conditions</option>
                            <option value="working">Working</option>
                            <option value="needs_repair">Needs Repair</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="missing">Missing</option>
                        </select>
                    </div>
                </div>
                <a href="qr-scan.php" class="btn-primary">Report an Issue</a>
            </div>
        </div>


        <?php if (empty($reports)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fa fa-clipboard-list"></i>
                </div>
                <h4 class="empty-title">No Reports Found</h4>
                <p class="empty-text">You haven't submitted any equipment issue reports yet.</p>
            </div>
        <?php else: ?>
            <div class="reports-list">
                <?php foreach ($reports as $report): ?>
                    <div class="report-card" data-status="<?php echo $report['status']; ?>" data-condition="<?php echo $report['statusCondition']; ?>">
                        <div class="report-card-header">
                            <div class="report-id">Report #<?php echo $report['id']; ?></div>
                            <div class="status-badges">
                                <?php echo getStatusBadge($report['status']); ?>

                            </div>
                        </div>
                        <div class="report-card-body">
                            <div class="report-info-grid">
                                <div class="info-item">
                                    <div class="info-label">Equipment:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($report['equipment_name']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Location:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($report['room_name'] . ', ' . $report['building_name']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Issue Type:</div>
                                    <div class="info-value"><?php echo htmlspecialchars($report['issue_type']); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Condition:</div>
                                    <div class="info-value"><?php echo ucfirst(str_replace('_', ' ', $report['statusCondition'])); ?></div>
                                </div>
                                <div class="info-item">
                                    <div class="info-label">Reported:</div>
                                    <div class="info-value"><?php echo date('M d, Y h:i A', strtotime($report['reported_at'])); ?></div>
                                </div>
                            </div>
                            <a href="javascript:void(0)" class="view-details-btn" onclick="toggleDetails(this, <?php echo $report['id']; ?>)">
                                View Details <i class="fa fa-chevron-right"></i>
                            </a>
                        </div>
                        <div id="details-<?php echo $report['id']; ?>" class="report-details">
                            <div class="details-section">
                                <h3 class="details-title">Issue Description</h3>
                                <p class="details-content"><?php echo nl2br(htmlspecialchars($report['description'])); ?></p>
                            </div>

                            <?php if (!empty($report['image_path'])): ?>
                                <div class="details-section">
                                    <h3 class="details-title">Attached Image</h3>
                                    <div class="image-container">
                                        <img src="<?php echo htmlspecialchars($report['image_path']); ?>" alt="Issue Image" class="report-image">
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($report['admin_response'])): ?>
                                <div class="details-section">
                                    <h3 class="details-title">Administrator Response</h3>
                                    <div class="admin-response">
                                        <p><?php echo nl2br(htmlspecialchars($report['admin_response'])); ?></p>
                                        <?php if (!empty($report['resolved_at'])): ?>
                                            <div class="response-date">Responded on <?php echo date('M d, Y h:i A', strtotime($report['resolved_at'])); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
<?php include "../partials/footer.php"; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        const reportCards = document.querySelectorAll('.report-card');

        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();

            reportCards.forEach(card => {
                const cardText = card.textContent.toLowerCase();
                if (cardText.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        // Status filter functionality
        const statusFilter = document.getElementById('statusFilter');

        statusFilter.addEventListener('change', function() {
            filterReports();
        });

        // Condition filter functionality
        const conditionFilter = document.getElementById('conditionFilter');

        conditionFilter.addEventListener('change', function() {
            filterReports();
        });

        // Combined filter function
        function filterReports() {
            const statusValue = statusFilter.value.toLowerCase();
            const conditionValue = conditionFilter.value.toLowerCase();

            reportCards.forEach(card => {
                const cardStatus = card.dataset.status;
                const cardCondition = card.dataset.condition;

                // Show card if both filters match or are empty
                const statusMatch = statusValue === '' || cardStatus === statusValue;
                const conditionMatch = conditionValue === '' || cardCondition === conditionValue;

                if (statusMatch && conditionMatch) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Call this when the document is ready
        if (typeof updateValidationIcons === 'function') {
            updateValidationIcons();
        }
    });

    // Toggle report details function remains the same
    function toggleDetails(button, reportId) {
        const detailsSection = document.getElementById('details-' + reportId);
        const icon = button.querySelector('i');

        if (detailsSection.style.display === 'none' || !detailsSection.style.display) {
            detailsSection.style.display = 'block';
            icon.classList.remove('fa-chevron-right');
            icon.classList.add('fa-chevron-down');
            button.innerHTML = 'Hide Details <i class="fa fa-chevron-down"></i>';
        } else {
            detailsSection.style.display = 'none';
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-right');
            button.innerHTML = 'View Details <i class="fa fa-chevron-right"></i>';
        }
    }
</script>