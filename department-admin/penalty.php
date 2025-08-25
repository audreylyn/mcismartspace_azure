<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);
$conn = db();

$adminId = $_SESSION['user_id'];
$adminDepartment = $_SESSION['department'] ?? '';

// Fetch students in this department
$students = [];
if (!empty($adminDepartment)) {
    $stmt = $conn->prepare("SELECT StudentID, FirstName, LastName, YearSection, Email, PenaltyStatus FROM student WHERE Department = ? ORDER BY StudentID ASC");
    $stmt->bind_param("s", $adminDepartment);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
}

// Fetch penalties for students
$penalties = [];
if (!empty($students)) {
    $studentIds = array_column($students, 'StudentID');
    $in = str_repeat('?,', count($studentIds) - 1) . '?';
    $types = str_repeat('i', count($studentIds));
    $sql = "SELECT student_id, type, reason, issued_at, expires_at FROM penalty WHERE student_id IN ($in) ORDER BY issued_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$studentIds);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $penalties[$row['student_id']][] = $row;
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator | MCiSmartSpace</title>
    <!-- Include QR Code library -->
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/penalty.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">
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
                                <li>QR Code Generator</li>
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
                        <a href="dept_room_activity_logs.php">
                            <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-list">
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                <path d="M15 2H9a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"></path>
                                <path d="M12 11h4"></path>
                                <path d="M12 16h4"></path>
                                <path d="M8 11h.01"></path>
                                <path d="M8 16h.01"></path>
                            </svg>
                            </span>
                            <span>Activity Logs</span>
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
                                <a href="manage_teachers.php">
                                    <span>Manage Teachers</span>
                                </a>
                            </li>
                            <li>
                                <a href="manage_students.php">
                                    <span>Manage Students</span>
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
                    <li class="active">
                        <a href="#S">
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
            <div class="card">
                <header class="card-header">
                    <h2>STUDENT LIST</h2>
                </header>
                <div class="card-content">
                    <div class="table-container">
                        <table id="studentTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>NAME</th>
                                    <th>YEAR &amp; SECTION</th>
                                    <th>EMAIL</th>
                                    <th>STATUS</th>
                                    <th>PENALTIES</th>
                                    <th>ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($students as $student): ?>
                                <?php
                                    $sid = $student['StudentID'];
                                    $status = $student['PenaltyStatus'];
                                    $penaltyCount = isset($penalties[$sid]) ? count($penalties[$sid]) : 0;
                                    $activeWarnings = isset($penalties[$sid]) ? count(array_filter($penalties[$sid], function($p){ return $p['type']==='warning' && (is_null($p['expires_at']) || strtotime($p['expires_at'])>time()); })) : 0;
                                    $statusLabel = $status==='active' ? '<span class="status-active">Active</span>' : ($status==='warning' ? '<span class="status-warning">Warning</span>' : ($status==='banned' ? '<span class="status-banned">Perm. Banned</span>' : '<span class="status-active">Active</span>'));
                                    $fullName = htmlspecialchars($student['FirstName']) . ' ' . htmlspecialchars($student['LastName']);
                                ?>
                                <tr>
                                    <td><strong><?= $fullName ?></strong></td>
                                    <td><?= htmlspecialchars($student['YearSection']) ?></td>
                                    <td><?= htmlspecialchars($student['Email']) ?></td>
                                    <td><?= $statusLabel ?></td>
                                    <td><?= $penaltyCount ?><?= $activeWarnings ? " (<span class='active-warnings'>{$activeWarnings} active</span>)" : '' ?></td>
                                    <td>
                                        <button class="btn-penalty btn btn-warning" onclick="openPenaltyModal(<?= $sid ?>, '<?= $fullName ?>', '<?= htmlspecialchars($student['Email']) ?>', <?= $activeWarnings ?>)"><i class="mdi mdi-alert"></i> Penalty</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Penalty Modal -->
            <div id="penaltyModal" class="popover-modal" style="display:none;">
                <div class="popover-content">
                    <div class="popover-header">
                        <h3>Issue Warning</h3>
                        <button class="popover-close" onclick="closeModal('penaltyModal')">&times;</button>
                    </div>
                    <div class="popover-body">
                        <div id="penaltyStudentInfo" class="penalty-student-info"></div>
                        <div class="warning-count">
                            <span class="mdi mdi-alert warning-alert-icon"></span>
                            <span>Current Warnings: <span id="currentWarnings">0</span>/3</span>
                            <span class="warning-indicators">
                                <span class="dot" id="dot1"></span>
                                <span class="dot" id="dot2"></span>
                                <span class="dot" id="dot3"></span>
                            </span>
                        </div>
                        <div id="previousWarnings" class="previous-warnings"></div>
                        <form id="penaltyForm">
                            <div class="field penalty-field">
                                <label for="violation_reason" class="penalty-label">Violation Reason *</label>
                                <select id="violation_reason" name="violation_reason" required class="penalty-select">
                                    <option value="">Select a reason</option>
                                    <option value="Equipment Misuse">Equipment Misuse</option>
                                    <option value="Repeated Cancellations">Repeated Cancellations</option>
                                    <option value="Equipment Damage">Equipment Damage</option>
                                    <option value="Inappropriate Behavior">Inappropriate Behavior</option>
                                    <option value="Violation of Lab Rules">Violation of Lab Rules</option>
                                    <option value="Unauthorized Access">Unauthorized Access</option>
                                    <option value="Late Return of Equipment">Late Return of Equipment</option>
                                    <option value="Noise Disturbance">Noise Disturbance</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="field penalty-field">
                                <label for="description" class="penalty-label">Detailed Description *</label>
                                <textarea id="description" name="description" required class="penalty-textarea" placeholder="Provide detailed information about the incident or violation..."></textarea>
                                <small class="penalty-desc-help">Be specific about what happened and when it occurred.</small>
                            </div>
                            <div class="popover-footer">
                                <button type="button" class="btn-cancel penalty-btn-cancel" onclick="closeModal('penaltyModal')">Cancel</button>
                                <button type="submit" class="btn-issue penalty-btn-issue">Issue Warning</button>
                            </div>
                        </form>
                    </div>
                </div>
        // Close the database connection
        $conn->close();
        ?>
    </div>
    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/custom_alert.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.11.5/api/fnPagingInfo.js"></script>
    <script>
        $(document).ready(function() {
            $('#studentTable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search students...",
                    lengthMenu: '<span class="show-entries-label">Show</span> _MENU_ <span class="show-entries-label">entries</span>',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    paginate: {
                        previous: 'Prev',
                        next: 'Next'
                    }
                },
                dom: '<"top"lf>rt<"bottom"ip><"clear">',
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                pageLength: 10,
                ordering: true,
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });
            // Style the show entries dropdown
            $('.dataTables_length').css({ 'margin-bottom': '16px', 'display': 'flex', 'align-items': 'center', 'gap': '8px' });
            $('.show-entries-label').css({ 'font-weight': '500', 'color': '#888' });
        });

        // Store previous warnings for each student
        var studentWarnings = {};
        <?php
        // Prepare JS object for previous warnings
        foreach ($students as $student) {
            $sid = $student['StudentID'];
            $warnings = [];
            if (isset($penalties[$sid])) {
                foreach ($penalties[$sid] as $idx => $p) {
                    if ($p['type'] === 'warning' && (is_null($p['expires_at']) || strtotime($p['expires_at']) > time())) {
                        $warnings[] = [
                            'reason' => $p['reason'],
                            'issued_at' => $p['issued_at'],
                            'idx' => $idx + 1
                        ];
                    }
                }
            }
            echo "studentWarnings[$sid] = " . json_encode($warnings) . ";\n";
        }
        ?>

        var currentStudentId = null;
        function openPenaltyModal(studentId, fullName, email, activeWarnings) {
            currentStudentId = studentId;
            var modal = document.getElementById('penaltyModal');
            modal.style.display = 'block';
            document.getElementById('penaltyStudentInfo').textContent = fullName + ' • ' + email;
            document.getElementById('currentWarnings').textContent = activeWarnings;
            // Set warning dots
            for (let i = 1; i <= 3; i++) {
                document.getElementById('dot'+i).classList.remove('active');
            }
            for (let i = 1; i <= activeWarnings && i <= 3; i++) {
                document.getElementById('dot'+i).classList.add('active');
            }
            // Render previous warnings
            var prevDiv = document.getElementById('previousWarnings');
            prevDiv.innerHTML = '';
            var warnings = studentWarnings[studentId] || [];
            if (warnings.length > 0) {
                warnings.forEach(function(w, i) {
                    var date = new Date(w.issued_at);
                    var dateStr = date.toLocaleDateString('en-US', { year: 'numeric', month: 'numeric', day: 'numeric' });
                    prevDiv.innerHTML += `<div class="prev-warning"><strong>Warning #${i+1}: ${w.reason}</strong> <span class="prev-date">${dateStr}</span><br><span class="prev-desc">${w.reason}</span></div>`;
                });
            }
        }
        function closeModal(modalId) {
            var modal = document.getElementById(modalId);
            modal.style.display = 'none';
        }
        // Handle penalty form submission
        document.getElementById('penaltyForm').onsubmit = function(e) {
            e.preventDefault();
            var reason = document.getElementById('violation_reason').value;
            var desc = document.getElementById('description').value;
            if (!reason || !desc) {
                alert('Please fill out all required fields.');
                return;
            }
            // AJAX to backend
            $.ajax({
                url: 'includes/issue_warning.php',
                method: 'POST',
                data: {
                    student_id: currentStudentId,
                    reason: reason,
                    description: desc
                },
                success: function(resp) {
                    try {
                        var data = JSON.parse(resp);
                        if (data.success) {
                            showCustomAlert('Warning issued!', 'success');
                            closeModal('penaltyModal');
                            setTimeout(function(){ location.reload(); }, 1200);
                        } else {
                            showCustomAlert(data.message || 'Failed to issue warning.', 'error');
                        }
                    } catch(e) {
                        showCustomAlert('Unexpected error.', 'error');
                    }
                },
                error: function() {
                    showCustomAlert('Server error.', 'error');
                }
            });
        };
    </script>
    <style>
    /* Simple popover modal styles */
    .popover-modal {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.3);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .popover-modal[style*="display: block"] {
        display: flex !important;
    }
    .popover-content {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 16px rgba(0,0,0,0.18);
        padding: 32px 24px 24px 24px;
        min-width: 340px;
        max-width: 95vw;
        margin: auto;
        position: relative;
    }
    .popover-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .popover-close {
        background: none;
        border: none;
        font-size: 1.8rem;
        cursor: pointer;
        color: #888;
        margin-left: 12px;
    }
    .popover-body {
        margin-bottom: 0;
    }
    .popover-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 18px;
    }
    /* Previous warnings styles */
    .previous-warnings {
        margin: 18px 0 8px 0;
    }
    .prev-warning {
        background: #fffbe6;
        border: 1px solid #ffe58f;
        border-radius: 6px;
        padding: 8px 12px;
        margin-bottom: 8px;
        font-size: 0.98rem;
        color: #856404;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    }
    .prev-warning strong {
        color: #856404;
    }
    .prev-date {
        float: right;
        color: #b8860b;
        font-size: 0.95em;
        margin-left: 8px;
    }
    .prev-desc {
        display: block;
        margin-top: 2px;
        color: #856404;
        font-size: 0.97em;
    }
    </style>


    <script>
        // Show alerts on page load if messages exist
        window.onload = function() {
            <?php
            if (isset($_SESSION['success_message'])) {
                echo 'showCustomAlert("' . addslashes($_SESSION['success_message']) . '", "success");';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo 'showCustomAlert("' . addslashes($_SESSION['error_message']) . '", "error");';
                unset($_SESSION['error_message']);
            }
            ?>
        }
    </script>


</body>

</html>