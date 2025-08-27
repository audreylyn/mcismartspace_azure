<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

// Get user info from session
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Determine ID field based on user role
$idField = ($userRole === 'Student') ? 'student_id' : 'teacher_id';

// Fetch all equipment reports made by this user
$sql = "SELECT ei.*, e.name as equipment_name, r.room_name, b.building_name
        FROM equipment_issues ei
        JOIN equipment_units eu ON ei.unit_id = eu.unit_id
        JOIN equipment e ON eu.equipment_id = e.id
        JOIN rooms r ON eu.room_id = r.id
        JOIN buildings b ON r.building_id = b.id
        WHERE ei.$idField = ?
        ORDER BY ei.reported_at DESC";

// First, check if we need to add reference_number column
$checkRefNumColumnSql = "SHOW COLUMNS FROM equipment_issues LIKE 'reference_number'";
$refNumColumnExists = $conn->query($checkRefNumColumnSql)->num_rows > 0;

if (!$refNumColumnExists) {
    // Add reference_number column to the table
    $alterTableSql = "ALTER TABLE equipment_issues ADD COLUMN reference_number VARCHAR(15) DEFAULT NULL";
    $conn->query($alterTableSql);
    
    // Update existing records with reference numbers
    $updateExistingRecordsSql = "UPDATE equipment_issues SET reference_number = CONCAT('EQ', LPAD(id, 6, '0')) WHERE reference_number IS NULL";
    $conn->query($updateExistingRecordsSql);
}

// Check if we need to add rejection_reason column
$checkRejectionColumnSql = "SHOW COLUMNS FROM equipment_issues LIKE 'rejection_reason'";
$rejectionColumnExists = $conn->query($checkRejectionColumnSql)->num_rows > 0;

if (!$rejectionColumnExists) {
    // Add rejection_reason column to the table
    $alterTableSql = "ALTER TABLE equipment_issues ADD COLUMN rejection_reason TEXT DEFAULT NULL";
    $conn->query($alterTableSql);
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
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

    .rejection-reason {
        background-color: #fff3f3;
        border-left: 4px solid #dc3545;
        padding: 12px 15px;
        margin-top: 8px;
        border-radius: 4px;
    }

    .rejection-reason p {
        margin: 0;
        color: #721c24;
    }

    .report-id {
        font-weight: bold;
        color: #0056b3;
        font-size: 1.1em;
        letter-spacing: 0.5px;
    }

    @media screen and (max-width: 768px) {
        .search-wrapper-inner {
            flex-wrap: wrap;
        }
    }
</style>

<?php include "layout/sidebar.php"; ?>
<?php include "layout/topnav.php"; ?>

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
                            <div class="report-id">
                                <?php 
                                    // Display reference number if it exists, otherwise generate one from ID
                                    $refNumber = !empty($report['reference_number']) ? 
                                        $report['reference_number'] : 
                                        'EQ' . str_pad($report['id'], 6, '0', STR_PAD_LEFT);
                                    echo $refNumber;
                                ?>
                            </div>
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
                                    <div class="info-value"><?php echo date('M d, Y h:i A', strtotime($report['reported_at']) + 8*3600); ?></div>
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
                                            <div class="response-date">Responded on <?php echo date('M d, Y h:i A', strtotime($report['resolved_at']) + 8*3600); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($report['status'] === 'rejected' && !empty($report['rejection_reason'])): ?>
                                <div class="details-section">
                                    <h3 class="details-title">Rejection Reason</h3>
                                    <div class="rejection-reason">
                                        <p><?php echo nl2br(htmlspecialchars($report['rejection_reason'])); ?></p>
                                        <?php if (!empty($report['resolved_at'])): ?>
                                            <div class="response-date">Rejected on <?php echo date('M d, Y h:i A', strtotime($report['resolved_at']) + 8*3600); ?></div>
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