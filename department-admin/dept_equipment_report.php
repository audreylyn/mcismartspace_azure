<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

include 'includes/equipment_report.php'
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Reports Management</title>
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">
    <link rel="stylesheet" href="../public/css/admin_styles/equipment_report.css">
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
                                <li>Equipment Issue Reports</li>
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
                    <li>
                        <a href="dept_room_approval.php">
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
                    <li class="active">
                        <a href="#">
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

        <section class="section main-section">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-wrench"></i></span>
                        Equipment Issue Reports
                    </p>
                </header>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <?php if (!$viewReportId): ?>
                    <!-- Dashboard Summary -->
                    <div class="card-content">
                        <div class="dashboard-tiles">
                            <div class="tile tile-pending">
                                <div class="tile-count"><?php echo $countData['pending_count']; ?></div>
                                <div class="tile-label">Pending</div>
                            </div>
                            <div class="tile tile-in-progress">
                                <div class="tile-count"><?php echo $countData['in_progress_count']; ?></div>
                                <div class="tile-label">In Progress</div>
                            </div>
                            <div class="tile tile-resolved">
                                <div class="tile-count"><?php echo $countData['resolved_count']; ?></div>
                                <div class="tile-label">Resolved</div>
                            </div>
                            <div class="tile tile-rejected">
                                <div class="tile-count"><?php echo $countData['rejected_count']; ?></div>
                                <div class="tile-label">Rejected</div>
                            </div>
                            <div class="tile tile-total">
                                <div class="tile-count"><?php echo $countData['total_count']; ?></div>
                                <div class="tile-label">Total Reports</div>
                            </div>
                        </div>

                        <!-- Reports List with DataTables -->
                        <div class="table-container">
                            <!-- Custom filters above the table -->
                            <div style="background-color: #f8fafc; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1rem;">
                                    <div>
                                        <label style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.375rem; display: block; font-weight: 500;">Status</label>
                                        <select id="status-filter" class="form-select" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem;">
                                            <option value="">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="resolved">Resolved</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.375rem; display: block; font-weight: 500;">Date Range</label>
                                        <select id="date-filter" class="form-select" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem;">
                                            <option value="">All Time</option>
                                            <option value="7" selected>Last 7 days</option>
                                            <option value="30">Last 30 days</option>
                                            <option value="90">Last 90 days</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.375rem; display: block; font-weight: 500;">Location</label>
                                        <select id="location-filter" class="form-select" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem;">
                                            <option value="">All Locations</option>
                                            <?php
                                            // Generate unique locations from dataset
                                            $locations = [];
                                            mysqli_data_seek($reportsResult, 0);
                                            while ($row = $reportsResult->fetch_assoc()) {
                                                $location = $row['building_name'] . ' - ' . $row['room_name'];
                                                if (!empty($row['building_name']) && !empty($row['room_name']) && !in_array($location, $locations)) {
                                                    $locations[] = $location;
                                                    echo '<option value="' . htmlspecialchars($location) . '">' . htmlspecialchars($location) . '</option>';
                                                }
                                            }
                                            // Reset the result pointer to beginning
                                            mysqli_data_seek($reportsResult, 0);
                                            ?>
                                        </select>
                                    </div>
                                    <div style="display: flex; align-items: flex-end;">
                                        <button id="reset-filters" class="back-btn" style="width: 100%; justify-content: center; border: none; background-color: #f1f5f9; padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 500; cursor: pointer;">Reset</button>
                                    </div>
                                </div>

                                <!-- Standalone search bar -->
                                <div style="width: 100%;">
                                    <label style="font-size: 0.875rem; color: #64748b; margin-bottom: 0.375rem; display: block; font-weight: 500;">Search</label>
                                    <input type="text" id="customSearch" class="form-select" style="width: 100%; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem;" placeholder="Search by equipment, room, student...">
                                </div>
                            </div>

                            <!-- Show entries dropdown -->
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <div style="display: flex; align-items: center;">
                                    <span style="margin-right: 0.5rem; font-weight: 500;">Show</span>
                                    <select id="entries-filter" class="form-select" style="width: auto; min-width: 70px; padding: 0.5rem; border: 1px solid #e2e8f0; border-radius: 0.375rem;">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                        <option value="-1">All</option>
                                    </select>
                                    <span style="margin-left: 0.5rem; font-weight: 500;">entries</span>
                                </div>
                            </div>

                            <table id="equipmentReportTable" class="table is-fullwidth is-striped">
                                <thead>
                                    <tr class="titles">
                                        <th>ID</th>
                                        <th>Equipment</th>
                                        <th>Location</th>
                                        <th>Issue Type</th>
                                        <th>Reported By</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Condition</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($reportsResult->num_rows > 0): ?>
                                        <?php while ($row = $reportsResult->fetch_assoc()): ?>
                                            <tr>
                                                <td data-label="ID">#<?php echo $row['id']; ?></td>
                                                <td data-label="Equipment"><?php echo htmlspecialchars($row['equipment_name']); ?></td>
                                                <td data-label="Location"><?php echo htmlspecialchars($row['room_name'] ?? 'N/A') . ' (' . htmlspecialchars($row['building_name'] ?? 'N/A') . ')'; ?></td>
                                                <td data-label="Issue Type"><?php echo htmlspecialchars($row['issue_type']); ?></td>
                                                <td data-label="Reported By">
                                                    <?php echo htmlspecialchars($row['reporter_name'] ?? 'Unknown'); ?>
                                                    <?php if (!empty($row['reporter_type'])): ?>
                                                        <span class="reporter-badge"><?php echo $row['reporter_type']; ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td data-label="Date"><?php echo date('M d, Y', strtotime($row['reported_at'])); ?></td>
                                                <td data-label="Status">
                                                    <span class="status-badge status-<?php echo $row['status']; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?>
                                                    </span>
                                                </td>
                                                <td data-label="Condition">
                                                    <span class="condition-badge condition-<?php echo $row['statusCondition']; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $row['statusCondition'])); ?>
                                                    </span>
                                                </td>
                                                <td class="action-buttons">
                                                    <a href="?view=<?php echo $row['id']; ?>" class="view-btn" title="View Details">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="has-text-centered">No reports found matching your criteria.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Report Detail View -->
                    <?php if ($reportDetail): ?>
                        <div class="card-content">
                            <div class="detail-header">
                                <a href="dept_equipment_report.php" class="back-btn">
                                    <i class="mdi mdi-arrow-left"></i> Back to List
                                </a>
                                <span class="status-badge status-<?php echo $reportDetail['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $reportDetail['status'])); ?>
                                </span>
                            </div>

                            <div class="detail-grid">
                                <div>
                                    <div class="detail-section">
                                        <h3 class="section-title">Equipment Information</h3>
                                        <div class="info-group">
                                            <div class="info-label">Name</div>
                                            <div class="info-value"><?php echo htmlspecialchars($reportDetail['equipment_name']); ?></div>
                                        </div>
                                        <div class="info-group">
                                            <div class="info-label">Location</div>
                                            <div class="info-value"><?php echo htmlspecialchars($reportDetail['room_name'] ?? 'N/A') . ', ' . htmlspecialchars($reportDetail['building_name'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="info-group">
                                            <div class="info-label">Equipment ID</div>
                                            <div class="info-value">#<?php echo $reportDetail['equipment_id']; ?></div>
                                        </div>
                                    </div>

                                    <div class="detail-section">
                                        <h3 class="section-title">
                                            <?php echo $reportDetail['reporter_type'] ?? 'Reporter'; ?> Information
                                        </h3>
                                        <div class="info-group">
                                            <div class="info-label">Name</div>
                                            <div class="info-value"><?php echo htmlspecialchars($reportDetail['reporter_name'] ?? 'N/A'); ?></div>
                                        </div>
                                        <div class="info-group">
                                            <div class="info-label">
                                                <?php echo $reportDetail['reporter_type'] == 'Student' ? 'Student ID' : 'Teacher ID'; ?>
                                            </div>
                                            <div class="info-value">
                                                <?php echo $reportDetail['reporter_type'] == 'Student' ?
                                                    $reportDetail['StudentID'] :
                                                    $reportDetail['TeacherID']; ?>
                                            </div>
                                        </div>
                                        <div class="info-group">
                                            <div class="info-label">Email</div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($reportDetail['reporter_type'] == 'Student' ?
                                                    $reportDetail['student_email'] :
                                                    $reportDetail['teacher_email']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="detail-section">
                                        <h3 class="section-title">Issue Information</h3>
                                        <div class="info-group">
                                            <div class="info-label">Issue Type</div>
                                            <div class="info-value"><?php echo htmlspecialchars($reportDetail['issue_type']); ?></div>
                                        </div>
                                        <div class="info-group">
                                            <div class="info-label">Description</div>
                                            <div class="info-value"><?php echo nl2br(htmlspecialchars($reportDetail['description'])); ?></div>
                                        </div>
                                        <div class="info-group">
                                            <div class="info-label">Equipment Condition</div>
                                            <div class="info-value">
                                                <span class="condition-badge condition-<?php echo $reportDetail['statusCondition']; ?>">
                                                    <?php echo ucfirst(str_replace('_', ' ', $reportDetail['statusCondition'])); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="info-group">
                                            <div class="info-label">Reported Date</div>
                                            <div class="info-value"><?php echo date('F d, Y - h:i A', strtotime($reportDetail['reported_at'])); ?></div>
                                        </div>
                                        <?php if ($reportDetail['resolved_at']): ?>
                                            <div class="info-group">
                                                <div class="info-label">Resolved Date</div>
                                                <div class="info-value"><?php echo date('F d, Y - h:i A', strtotime($reportDetail['resolved_at'])); ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($reportDetail['admin_response']): ?>
                                        <div class="detail-section">
                                            <h3 class="section-title">Admin Response</h3>
                                            <div class="info-value"><?php echo nl2br(htmlspecialchars($reportDetail['admin_response'])); ?></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($reportDetail['image_path']): ?>
                                <div class="image-container">
                                    <h3 class="section-title">Attached Image</h3>
                                    <img src="<?php echo htmlspecialchars($reportDetail['image_path']); ?>" alt="Issue Image" class="issue-image">
                                </div>
                            <?php endif; ?>

                            <form class="status-form" method="POST" action="">
                                <input type="hidden" name="report_id" value="<?php echo $reportDetail['id']; ?>">
                                <div class="form-group">
                                    <label class="form-label">Update Status</label>
                                    <select name="status" class="form-select">
                                        <option value="pending" <?php echo $reportDetail['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="in_progress" <?php echo $reportDetail['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="resolved" <?php echo $reportDetail['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                        <option value="rejected" <?php echo $reportDetail['status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Equipment Condition</label>
                                    <select name="statusCondition" class="form-select">
                                        <option value="working" <?php echo $reportDetail['statusCondition'] == 'working' ? 'selected' : ''; ?>>Working</option>
                                        <option value="needs_repair" <?php echo $reportDetail['statusCondition'] == 'needs_repair' ? 'selected' : ''; ?>>Needs Repair</option>
                                        <option value="maintenance" <?php echo $reportDetail['statusCondition'] == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                        <option value="missing" <?php echo $reportDetail['statusCondition'] == 'missing' ? 'selected' : ''; ?>>Missing</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Admin Response</label>
                                    <textarea name="admin_response" class="form-textarea" placeholder="Provide details about resolution, next steps, or rejection reason..."><?php echo htmlspecialchars($reportDetail['admin_response']); ?></textarea>
                                </div>
                                <div class="form-group">
                                    <button type="submit" name="update_status" class="submit-btn">Update Report</button>
                                </div>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="card-content">
                            <div class="notification is-danger">
                                Report not found. <a href="dept_equipment_report.php">Return to report list</a>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/equipment_report.js"></script>

</body>

</html>