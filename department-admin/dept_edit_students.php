<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get department admin's department - Use from session instead of querying a non-existent table
// The admin's department should be stored in the session when they log in
$adminId = $_SESSION['user_id'];
$adminDepartment = $_SESSION['department'] ?? '';

// If department is not in session, you can fallback to another table or show an error
if (empty($adminDepartment)) {
    die("Error: Department information not available in session. Please log out and log in again.");
}

// Initialize variables for error/success messages
$successMsg = "";
$errorMsg = "";

// Process edit form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_student'])) {
    // Get form data
    $studentId = $_POST['student_id'];
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);
    $program = trim($_POST['program']);
    $yearSection = trim($_POST['year_section']);

    // Validation
    $isValid = true;

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($department) || empty($program) || empty($yearSection)) {
        $errorMsg = "All fields are required.";
        $isValid = false;
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Please enter a valid email address.";
        $isValid = false;
    }

    // Check if student belongs to admin's department
    $checkDeptSql = "SELECT AdminID FROM student WHERE StudentID = ?";
    $checkStmt = $conn->prepare($checkDeptSql);
    $checkStmt->bind_param("i", $studentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $studentData = $checkResult->fetch_assoc();
    $checkStmt->close();

    if ($studentData['AdminID'] !== $adminId) {
        $errorMsg = "You can only edit students that you have added.";
        $isValid = false;
    }

    // Check if email is already in use by another student
    $checkEmailSql = "SELECT StudentID FROM student WHERE Email = ? AND StudentID != ?";
    $checkStmt = $conn->prepare($checkEmailSql);
    $checkStmt->bind_param("si", $email, $studentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        $errorMsg = "This email is already in use by another account.";
        $isValid = false;
    }
    $checkStmt->close();

    // If validation passes, update student information
    if ($isValid) {
        $updateSql = "UPDATE student SET 
                      FirstName = ?, 
                      LastName = ?, 
                      Email = ?,
                      Department = ?,
                      Program = ?,
                      YearSection = ?
                      WHERE StudentID = ? AND AdminID = ?";

        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param(
            "ssssssii",
            $firstName,
            $lastName,
            $email,
            $department,
            $program,
            $yearSection,
            $studentId,
            $adminId
        );

        if ($updateStmt->execute()) {
            $successMsg = "Student information updated successfully!";
        } else {
            $errorMsg = "Error updating student information: " . $conn->error;
        }

        $updateStmt->close();
    }
}

// Process delete request
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];

    // Check if student belongs to admin's department
    $checkDeptSql = "SELECT AdminID FROM student WHERE StudentID = ?";
    $checkStmt = $conn->prepare($checkDeptSql);
    $checkStmt->bind_param("i", $deleteId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $studentData = $checkResult->fetch_assoc();

        if ($studentData['AdminID'] === $adminId) {
            // Student belongs to admin's department, proceed with deletion
            $deleteSql = "DELETE FROM student WHERE StudentID = ? AND AdminID = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("ii", $deleteId, $adminId);

            if ($deleteStmt->execute()) {
                $successMsg = "Student deleted successfully!";
            } else {
                $errorMsg = "Error deleting student: " . $conn->error;
            }

            $deleteStmt->close();
        } else {
            $errorMsg = "You can only delete students that you have added.";
        }
    } else {
        $errorMsg = "Student not found.";
    }

    $checkStmt->close();
}

// Fetch students in admin's department
$sql = "SELECT * FROM student WHERE AdminID = ? ORDER BY StudentID ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$studentsResult = $stmt->get_result();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Students</title>
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_1.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">
    <style>
        .card-content {
            padding: 1.5rem;
        }

        /* Card styling */
        .card {
            border-radius: 10px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: none;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eaeaea;
            padding: 20px;
        }

        .new-title {
            font-weight: 700;
            font-size: 1.3rem;
            margin: 0;
            color: #1e5631;
        }

        .card-content {
            padding: 20px;
        }

        /* Modern table styling */
        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .table thead tr {
            background-color: #f5f5f5;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #eaeaea;
        }

        .table td {
            padding: 15px;
            border-bottom: 1px solid #eaeaea;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f9f9f9;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 12px;
        }

        .styled-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 22px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            text-decoration: none;
            margin: 0;
            min-width: 90px;
            width: auto;
        }

        .styled-button.is-edit {
            background: linear-gradient(90deg, #b99309 0%, #f2c94c 100%);
            color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        .styled-button.is-edit:hover {
            background: linear-gradient(90deg, #a07d08 0%, #e1b93b 100%);
            transform: translateY(-2px) scale(1.04);
        }

        .styled-button.is-delete {
            background: linear-gradient(90deg, #c0392b 0%, #e74c3c 100%);
            color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        .styled-button.is-delete:hover {
            background: linear-gradient(90deg, #a93226 0%, #c0392b 100%);
            transform: translateY(-2px) scale(1.04);
        }

        .success-message {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 15px 30px;
            margin: 15px auto;
            margin-bottom: 15px;
            border-radius: 6px;
            border-left: 4px solid #3c763d;
            display: inline-flex;
            align-items: center;
            width: fit-content;
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
            margin-bottom: 0;
        }

        .success-message:before {
            content: '✓';
            font-weight: bold;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .error-message {
            background-color: #f2dede;
            color: #a94442;
            padding: 15px 30px;
            margin: 15px auto;
            margin-bottom: 0;
            border-radius: 6px;
            border-left: 4px solid #a94442;
            display: inline-flex;
            align-items: center;
            width: fit-content;
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .error-message:before {
            content: '!';
            font-weight: bold;
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Modal and Form Styling - Updated for better UI/UX */
        .modal {
            z-index: 1050;
        }

        .modal-backdrop {
            z-index: 1040;
            background-color: rgba(0, 0, 0, 0.5) !important;
            opacity: 1 !important;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100%;
            height: 100%;
        }

        /* Add animation to backdrop for smoother appearance */
        .modal-backdrop.fade {
            opacity: 0 !important;
            transition: opacity 0.15s linear;
        }

        .modal-backdrop.show {
            opacity: 0.5 !important;
        }

        /* Ensure modal appears on top of backdrop */
        .modal.fade.show {
            background-color: rgba(0, 0, 0, 0.5);
            padding-right: 0 !important;
        }

        .modal-dialog {
            max-width: 600px;
            margin: 1.75rem auto;
            z-index: 1060;
            transition: transform 0.3s ease-out;
        }

        .modal.show .modal-dialog {
            transform: translate(0, 0);
        }

        .modal-content {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
            background-color: #fff;
            position: relative;
            z-index: 1070;
            border: none;
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .modal-content::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(145deg, #d1a300, #9e8700);
            border-radius: 8px 8px 0 0;
        }

        .modal-header {
            background: #fff;
            padding: 1.5rem 1.75rem 1.25rem;
            border-bottom: 1px solid #e8edf2;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
        }

        .modal-header-content {
            flex: 1;
            padding-right: 2rem;
        }

        .modal-title {
            font-size: 1.6rem;
            font-weight: 600;
            color: #333;
            margin: 0 0 0.3rem 0;
        }

        .modal-subtitle {
            font-size: 0.95rem;
            color: #64748b;
            margin: 0;
            line-height: 1.4;
        }

        .modal-header .close {
            position: absolute;
            right: 1.25rem;
            top: 1.25rem;
            padding: 0.5rem;
            font-size: 1.25rem;
            opacity: 0.5;
            transition: opacity 0.2s ease;
            background: none;
            border: none;
            cursor: pointer;
            line-height: 1;
            z-index: 1080;
        }

        .modal-header .close:hover {
            opacity: 0.75;
        }

        .modal-body {
            padding: 1.75rem;
            background-color: #fff;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            padding: 1.25rem 1.75rem;
            background-color: #f8fafc;
            border-top: 1px solid #e8edf2;
        }

        /* Form styling */
        .form-row {
            display: flex;
            margin-right: -10px;
            margin-left: -10px;
            flex-wrap: wrap;
        }

        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
            padding-right: 10px;
            padding-left: 10px;
            box-sizing: border-box;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group:last-child {
            margin-bottom: 0.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #dbe0e8;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            background-color: #fff;
            color: #333;
            height: auto;
        }

        .form-control:focus {
            border-color: #1e5631;
            box-shadow: 0 0 0 3px rgba(30, 86, 49, 0.15);
            outline: none;
        }

        /* Disabled input styling */
        .form-control:disabled,
        .form-control[readonly] {
            background-color: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
            border-color: #e2e8f0;
        }

        select.form-control {
            padding-right: 30px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23495057' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        /* Button styling */
        .btn-primary {
            background-color: #1e5631;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .btn-primary:hover {
            background-color: #174526;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #f3f4f6;
            color: #4b5563;
            border: 1px solid #e5e7eb;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-secondary:hover {
            background-color: #e5e7eb;
            color: #374151;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.06);
        }

        .btn-secondary:active {
            transform: translateY(-1px);
        }

        /* Delete modal styles */
        .delete-modal-content {
            text-align: center;
            padding: 2rem;
        }

        .delete-warning {
            color: #dc3545;
            font-weight: 500;
            background-color: #f8d7da;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 1rem;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        }

        .btn-danger:hover {
            background-color: #bb2d3b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
        }

        .btn-danger:active {
            transform: translateY(-1px);
        }

        .center-modal {
            max-width: 600px;
            margin: 10rem auto;
        }
    </style>
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
                                <li>Edit Students</li>
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
                    <li>
                        <a href="dept_room_approval.php">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2 flex-shrink-0">
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
                            <li class="active">
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
            <!-- Message Container for Success/Error Messages -->
            <?php if (!empty($successMsg)): ?>
                <div class="success-message"><i class="fa fa-check-circle"></i> <?php echo $successMsg; ?></div>
            <?php endif; ?>

            <?php if (!empty($errorMsg)): ?>
                <div class="error-message"><i class="fa fa-exclamation-circle"></i> <?php echo $errorMsg; ?></div>
            <?php endif; ?>

            <div class="table-container">
                <div class="card">
                    <header class="card-header">
                        <div class="new-title-container">
                            <p class="new-title">STUDENTS</p>
                        </div>
                    </header>
                    <div class="card-content">
                        <table id="studentTable" class="table is-fullwidth is-striped">
                            <thead>
                                <tr class="titles">
                                    <th>Student ID</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Department</th>
                                    <th>Program</th>
                                    <th>Year & Section</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($studentsResult->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="8" class="has-text-centered">No students found in your department.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while ($student = $studentsResult->fetch_assoc()): ?>
                                        <tr>
                                            <td data-label="Student ID"><?= htmlspecialchars($student['StudentID']) ?></td>
                                            <td data-label="First Name"><?= htmlspecialchars($student['FirstName']) ?></td>
                                            <td data-label="Last Name"><?= htmlspecialchars($student['LastName']) ?></td>
                                            <td data-label="Department"><?= htmlspecialchars($student['Department']) ?></td>
                                            <td data-label="Program"><?= htmlspecialchars($student['Program']) ?></td>
                                            <td data-label="Year & Section"><?= htmlspecialchars($student['YearSection']) ?></td>
                                            <td data-label="Email"><?= htmlspecialchars($student['Email']) ?></td>
                                            <td class="action-buttons">
                                                <button class="styled-button is-edit" onclick="openEditModal(<?= $student['StudentID'] ?>, '<?= htmlspecialchars($student['FirstName']) ?>', '<?= htmlspecialchars($student['LastName']) ?>', '<?= htmlspecialchars($student['Email']) ?>', '<?= htmlspecialchars($student['Department']) ?>', '<?= htmlspecialchars($student['Program']) ?>', '<?= htmlspecialchars($student['YearSection']) ?>')">EDIT</button>
                                                <button class="styled-button is-delete" onclick="openDeleteModal(<?= $student['StudentID'] ?>)">DELETE</button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Close the database connection
        $conn->close();
        ?>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1" role="dialog" aria-labelledby="editStudentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="modal-header-content">
                        <h4 class="modal-title" id="editStudentModalLabel">Edit Student</h4>
                        <p class="modal-subtitle">Update student information</p>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="post" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="editStudentForm">
                    <div class="modal-body">
                        <input type="hidden" id="edit_student_id" name="student_id">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="first_name">First Name</label>
                                <input type="text" id="edit_first_name" name="first_name" class="form-control" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="last_name">Last Name</label>
                                <input type="text" id="edit_last_name" name="last_name" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="department">Department</label>
                                <select id="edit_department" name="department" class="form-control" required>
                                    <option value="Accountancy">Accountancy</option>
                                    <option value="Computer Science">Computer Science</option>
                                    <option value="Engineering">Engineering</option>
                                    <option value="Business Administration">Business Administration</option>
                                </select>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="program">Program</label>
                                <input type="text" id="edit_program" name="program" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="year_section">Year & Section</label>
                                <input type="text" id="edit_year_section" name="year_section" class="form-control" required>
                            </div>

                            <div class="form-group col-md-6">
                                <label for="student_id_display">Student ID</label>
                                <input type="text" id="student_id_display" class="form-control" disabled>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="edit_email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="edit_student" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="center-modal">
        <div class="modal fade" id="deleteStudentModal" tabindex="-1" role="dialog" aria-labelledby="deleteStudentModalLabel" aria-hidden="true">
            <div class="modal-dialog center-modal" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-header-content">
                            <h4 class="modal-title" id="deleteStudentModalLabel">Delete Student</h4>
                            <p class="modal-subtitle">Are you sure you want to delete this student?</p>
                        </div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="delete-warning">
                            Warning: This action cannot be undone. All this student's data will be permanently deleted.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-secondary" data-dismiss="modal">Cancel</button>
                        <a href="#" id="confirmDeleteLink" class="btn-danger">Delete</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        // Initialize DataTables
        $(document).ready(function() {
            $('#studentTable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search students..."
                },
                dom: '<"top"lf>rt<"bottom"ip><"clear">',
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "All"]
                ],
                pageLength: 10,
                ordering: true,
                columnDefs: [{
                    targets: -1,
                    orderable: false
                }]
            });
        });

        // Function to handle dropdown toggle
        function toggleIcon(element) {
            element.classList.toggle('active');
            const icon = element.querySelector('.toggle-icon i');
            icon.classList.toggle('mdi-plus');
            icon.classList.toggle('mdi-minus');

            // Toggle visibility of the submenu
            const submenu = element.nextElementSibling;
            if (submenu) {
                submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
            }
        }

        // Function to open the edit modal
        function openEditModal(studentId, firstName, lastName, email, department, program, yearSection) {
            // Populate the modal with student info
            document.getElementById('edit_student_id').value = studentId;
            document.getElementById('student_id_display').value = studentId;
            document.getElementById('edit_first_name').value = firstName;
            document.getElementById('edit_last_name').value = lastName;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_department').value = department;
            document.getElementById('edit_program').value = program;
            document.getElementById('edit_year_section').value = yearSection;

            // Show the modal
            $('#editStudentModal').modal('show');
        }

        // Function to open the delete confirmation modal
        function openDeleteModal(studentId) {
            // Set the delete confirmation link
            document.getElementById('confirmDeleteLink').href = 'dept_edit_students.php?delete_id=' + studentId;

            // Show the modal
            $('#deleteStudentModal').modal('show');
        }

        // Show success message for 3 seconds then fade out
        document.addEventListener('DOMContentLoaded', function() {
            const successMessage = document.querySelector('.success-message');
            if (successMessage) {
                setTimeout(function() {
                    successMessage.style.opacity = '0';
                    successMessage.style.transition = 'opacity 1s ease';

                    setTimeout(function() {
                        successMessage.style.display = 'none';
                    }, 1000);
                }, 3000);
            }

            // Initialize Bootstrap components
            if (typeof $.fn.modal !== 'undefined') {
                // Show modals if needed (for example if there was a validation error)
                if (document.getElementById('editStudentModal').querySelector('.is-invalid')) {
                    $('#editStudentModal').modal('show');
                }
            }
        });
    </script>
</body>

</html>