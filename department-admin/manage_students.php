<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

// Initialize variables
$success_message = '';
$error_message = '';
$conn = db();

// Get department admin's department from session
$adminId = $_SESSION['user_id'];
$adminDepartment = $_SESSION['department'] ?? '';

// If department is not in session, show an error
if (empty($adminDepartment)) {
    $error_message = "Error: Department information not available. Please log out and log in again.";
}

// Process add student form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_student'])) {
    // Validate and sanitize input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $department = $adminDepartment; // Use admin's department
    $program = trim($_POST['program']);
    $yearsection = trim($_POST['year_section']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    $isValid = true;

    if (empty($first_name) || empty($last_name) || empty($department) || empty($program) || empty($yearsection) || empty($email) || empty($password)) {
        $error_message = "All fields are required.";
        $isValid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
        $isValid = false;
    } else {
        // Check for duplicate email
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM student WHERE Email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->bind_result($email_count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($email_count > 0) {
            $error_message = "Email already exists. Please use a different email.";
            $isValid = false;
        }
    }

    // If validation passes, add the student
    if ($isValid) {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO student (FirstName, LastName, Department, Program, YearSection, Email, Password, AdminID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $error_message = "Prepare failed: " . htmlspecialchars($conn->error);
        } else {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Bind parameters
            $stmt->bind_param("sssssssi", $first_name, $last_name, $department, $program, $yearsection, $email, $hashed_password, $adminId);

            // Execute the statement
            if ($stmt->execute()) {
                $success_message = "Student added successfully!";
            } else {
                $error_message = "Error: " . htmlspecialchars($stmt->error);
            }

            // Close the statement
            $stmt->close();
        }
    }
}

// Process edit student form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_student'])) {
    // Get form data
    $studentId = $_POST['student_id'];
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $program = trim($_POST['program']);
    $yearSection = trim($_POST['year_section']);
    
    // Optional password change
    $newPassword = trim($_POST['password']);
    $changePassword = !empty($newPassword);

    // Validation
    $isValid = true;

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($program) || empty($yearSection)) {
        $error_message = "All fields are required except password (only if changing).";
        $isValid = false;
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
        $isValid = false;
    }

    // Check if student belongs to the same department
    $checkDeptSql = "SELECT Department FROM student WHERE StudentID = ?";
    $checkStmt = $conn->prepare($checkDeptSql);
    $checkStmt->bind_param("i", $studentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $studentData = $checkResult->fetch_assoc();
    $checkStmt->close();

    if ($studentData['Department'] !== $adminDepartment) {
        $error_message = "You can only edit students in your department.";
        $isValid = false;
    }

    // Check if email is already in use by another student
    $checkEmailSql = "SELECT StudentID FROM student WHERE Email = ? AND StudentID != ?";
    $checkStmt = $conn->prepare($checkEmailSql);
    $checkStmt->bind_param("si", $email, $studentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        $error_message = "This email is already in use by another account.";
        $isValid = false;
    }
    $checkStmt->close();

    // If validation passes, update student information
    if ($isValid) {
        if ($changePassword) {
            // Update with new password
            $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $updateSql = "UPDATE student SET 
                          FirstName = ?, 
                          LastName = ?, 
                          Email = ?,
                          Program = ?,
                          YearSection = ?,
                          Password = ?
                          WHERE StudentID = ? AND Department = ?";

            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param(
                "ssssssis",
                $firstName,
                $lastName,
                $email,
                $program,
                $yearSection,
                $hashed_password,
                $studentId,
                $adminDepartment
            );
        } else {
            // Update without changing password
            $updateSql = "UPDATE student SET 
                          FirstName = ?, 
                          LastName = ?, 
                          Email = ?,
                          Program = ?,
                          YearSection = ?
                          WHERE StudentID = ? AND Department = ?";

            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param(
                "sssssis",
                $firstName,
                $lastName,
                $email,
                $program,
                $yearSection,
                $studentId,
                $adminDepartment
            );
        }

        if ($updateStmt->execute()) {
            $success_message = "Student information updated successfully!";
        } else {
            $error_message = "Error updating student information: " . $conn->error;
        }

        $updateStmt->close();
    }
}

// Process delete request
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];

    // Check if student belongs to the same department
    $checkDeptSql = "SELECT Department FROM student WHERE StudentID = ?";
    $checkStmt = $conn->prepare($checkDeptSql);
    $checkStmt->bind_param("i", $deleteId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $studentData = $checkResult->fetch_assoc();

        if ($studentData['Department'] === $adminDepartment) {
            // Student belongs to admin's department, proceed with deletion
            $deleteSql = "DELETE FROM student WHERE StudentID = ? AND Department = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("is", $deleteId, $adminDepartment);

            if ($deleteStmt->execute()) {
                $success_message = "Student deleted successfully!";
            } else {
                $error_message = "Error deleting student: " . $conn->error;
            }

            $deleteStmt->close();
        } else {
            $error_message = "You can only delete students in your department.";
        }
    } else {
        $error_message = "Student not found.";
    }

    $checkStmt->close();
}

// Fetch all students in the department
$sql = "SELECT * FROM student WHERE Department = ? ORDER BY StudentID ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $adminDepartment);
$stmt->execute();
$studentsResult = $stmt->get_result();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_2.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">
    <link rel="stylesheet" href="../public/css/admin_styles/manage_accounts.css">
    

</head>

<body>
    <div id="app">


        <?php include 'layout/topnav.php'; ?>
        <?php include 'layout/sidebar.php'; ?>

        <div class="main-container">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="mdi mdi-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="mdi mdi-alert-circle"></i> <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Tab navigation -->
            <div class="tab-nav">
                <button class="tab-btn active" data-tab="tab-list">Student List</button>
                <button class="tab-btn" data-tab="tab-add">Add New Student</button>
            </div>

            <!-- Student List Tab -->
            <div id="tab-list" class="tab-content active">
                <div class="table-container">
                    <div class="card">
                        <header class="card-header">
                            <div class="new-title-container">
                                <p class="new-title">STUDENT LIST</p>
                            </div>
                        </header>
                        <div class="card-content">
                            <table id="studentTable" class="student-table display is-fullwidth">
                                <thead>
                                    <tr>
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
                                    <?php if ($studentsResult->num_rows > 0): ?>
                                        <?php while ($student = $studentsResult->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($student['FirstName']) ?></td>
                                                <td><?= htmlspecialchars($student['LastName']) ?></td>
                                                <td><?= htmlspecialchars($student['Department']) ?></td>
                                                <td><?= htmlspecialchars($student['Program']) ?></td>
                                                <td><?= htmlspecialchars($student['YearSection']) ?></td>
                                                <td><?= htmlspecialchars($student['Email']) ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button type="button" class="btn-edit" onclick="openEditModal(
                                                            <?= $student['StudentID'] ?>,
                                                            '<?= htmlspecialchars($student['FirstName']) ?>',
                                                            '<?= htmlspecialchars($student['LastName']) ?>',
                                                            '<?= htmlspecialchars($student['Email']) ?>',
                                                            '<?= htmlspecialchars($student['Department']) ?>',
                                                            '<?= htmlspecialchars($student['Program']) ?>',
                                                            '<?= htmlspecialchars($student['YearSection']) ?>'
                                                        )">
                                                            <i class="mdi mdi-pencil"></i> Edit
                                                        </button>
                                                        <button type="button" class="btn-delete" onclick="openDeleteModal(<?= $student['StudentID'] ?>)">
                                                            <i class="mdi mdi-delete"></i> Delete
                                                        </button>
                                                    </div>
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

            <!-- Add New Student Tab -->
            <div id="tab-add" class="tab-content">
                <div class="card add-student-card">
                    <header class="card-header">
                        <div class="new-title-container" style="width: 100%; display: flex; justify-content: space-between; align-items: center;">
                            <p class="new-title"><i class="mdi mdi-account-plus"></i> ADD NEW STUDENT</p>
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <form id="importForm" method="post" action="includes/import_students.php" enctype="multipart/form-data" style="display: flex;">
                                    <input type="hidden" name="importSubmit" value="1">
                                    <button class="excel" type="button" onclick="document.getElementById('studentFileInput').click();" style="border-radius: 0.3em 0 0 0.3em; display: flex; justify-content: center; width: 50px; padding: 0.5rem; background-color: #217346; border: none; cursor: pointer;">
                                        <svg
                                            fill="#fff"
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="20"
                                            height="20"
                                            viewBox="0 0 50 50"
                                            style="margin: 0;">
                                            <path
                                                d="M28.8125 .03125L.8125 5.34375C.339844 
                                            5.433594 0 5.863281 0 6.34375L0 43.65625C0 
                                            44.136719 .339844 44.566406 .8125 44.65625L28.8125 
                                            49.96875C28.875 49.980469 28.9375 50 29 50C29.230469 
                                            50 29.445313 49.929688 29.625 49.78125C29.855469 49.589844 
                                            30 49.296875 30 49L30 1C30 .703125 29.855469 .410156 29.625 
                                            .21875C29.394531 .0273438 29.105469 -.0234375 28.8125 .03125ZM32 
                                            6L32 13L34 13L34 15L32 15L32 20L34 20L34 22L32 22L32 27L34 27L34 
                                            29L32 29L32 35L34 35L34 37L32 37L32 44L47 44C48.101563 44 49 
                                            43.101563 49 42L49 8C49 6.898438 48.101563 6 47 6ZM36 13L44 
                                            13L44 15L36 15ZM6.6875 15.6875L11.8125 15.6875L14.5 21.28125C14.710938 
                                            21.722656 14.898438 22.265625 15.0625 22.875L15.09375 22.875C15.199219 
                                            22.511719 15.402344 21.941406 15.6875 21.21875L18.65625 15.6875L23.34375 
                                            15.6875L17.75 24.9375L23.5 34.375L18.53125 34.375L15.28125 
                                            28.28125C15.160156 28.054688 15.035156 27.636719 14.90625 
                                            27.03125L14.875 27.03125C14.8125 27.316406 14.664063 27.761719 
                                            14.4375 28.34375L11.1875 34.375L6.1875 34.375L12.15625 25.03125ZM36 
                                            20L44 20L44 22L36 22ZM36 27L44 27L44 29L36 29ZM36 35L44 35L44 37L36 37Z"></path>
                                        </svg>
                                        <input type="file" id="studentFileInput" name="file" accept=".csv,.xlsx,.xls" style="display: none;" onchange="handleFileSelect(this)" />
                                    </button>
                                    <button id="importButton" type="submit" disabled style="border-radius: 0 0.3em 0.3em 0; background-color: rgb(41, 114, 45); color: white; border: none; padding: 0.5rem 1rem; cursor: not-allowed; opacity: 0.5; display: flex; align-items: center; gap: 5px;">
                                        <svg
                                            fill="#fff"
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="20"
                                            height="20"
                                            viewBox="0 0 24 24">
                                            <path
                                                d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"></path>
                                        </svg>
                                        Import
                                    </button>
                                </form>
                                <small id="fileName" style="margin-top: 5px; color: #666; font-size: 12px;">No file selected</small>
                            </div>
                        </div>
                    </header>
                    <div class="card-content">
                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="add-student-form">
                            <input type="hidden" name="add_student" value="1">
                            <div class="form-grid">
                                <div class="form-grid-column">
                                    <div class="field">
                                        <label class="label">First Name</label>
                                        <div class="control has-icons-left">
                                            <input class="input is-rounded" type="text" name="first_name" placeholder="First Name" pattern="[A-Za-z\s]+" required>
                                            <span class="icon is-small is-left">
                                                <i class="mdi mdi-account"></i>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="field">
                                        <label class="label">Last Name</label>
                                        <div class="control has-icons-left">
                                            <input class="input is-rounded" type="text" name="last_name" placeholder="Last Name" pattern="[A-Za-z\s]+" required>
                                            <span class="icon is-small is-left">
                                                <i class="mdi mdi-account-card-details"></i>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="field">
                                        <label class="label">Email Address</label>
                                        <div class="control has-icons-left">
                                            <input class="input is-rounded" type="email" name="email" placeholder="student@example.com" required>
                                            <span class="icon is-small is-left">
                                                <i class="mdi mdi-email"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-grid-column">
                                    <div class="field">
                                        <label class="label">Program</label>
                                        <div class="control has-icons-left">
                                            <div class="select is-fullwidth is-rounded">
                                                <select name="program" id="program_select" required>
                                                    <option value="">Select Program</option>
                                                </select>
                                            </div>
                                            <span class="icon is-small is-left">
                                                <i class="mdi mdi-book-open-variant"></i>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="field">
                                        <label class="label">Year & Section</label>
                                        <div class="control has-icons-left">
                                            <input class="input is-rounded year-section-input" type="text" name="year_section" id="year_section" placeholder="Program will appear here, add year-section (e.g., 4-1)" readonly required>
                                            <span class="icon is-small is-left">
                                                <i class="mdi mdi-school"></i>
                                            </span>
                                        </div>
                                        <p class="year-section-help">Select a program first, then add year-section (e.g., 4-1 for 4th year section 1)</p>
                                    </div>
                                    
                                    <div class="field">
                                        <label class="label">Department</label>
                                        <div class="control has-icons-left">
                                            <input class="input is-rounded" type="text" value="<?php echo htmlspecialchars($adminDepartment); ?>" disabled>
                                            <span class="icon is-small is-left">
                                                <i class="mdi mdi-domain"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="divider">
                                <span>Security</span>
                            </div>
                            
                            <div class="password-field-container">
                                <div class="field">
                                    <label class="label">Password</label>
                                    <div class="control has-icons-left has-icons-right">
                                        <input class="input is-rounded" type="password" name="password" id="add_password" placeholder="Minimum 8 characters" minlength="8" required>
                                        <span class="icon is-small is-left">
                                            <i class="mdi mdi-lock"></i>
                                        </span>
                                        <span class="icon is-small is-right toggle-password" onclick="togglePasswordVisibility('add_password')">
                                            <i class="mdi mdi-eye"></i>
                                        </span>
                                    </div>
                                    <p class="help">Password must be at least 8 characters long</p>
                                </div>
                            </div>
                            
                            <div class="form-actions-container">
                                <div class="form-actions">
                                    <button type="submit" class="submit-button">
                                        <i class="mdi mdi-account-plus"></i>
                                        <span>Add Student</span>
                                    </button>
                                    <button type="reset" class="reset-button">
                                        <i class="mdi mdi-refresh"></i>
                                        <span>Reset</span>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="divider">
                                <span>OR</span>
                            </div>
                            
                            <div class="batch-upload-info">
                                <div class="info-card">
                                    <h4><i class="mdi mdi-information"></i> Batch Upload Instructions</h4>
                                    <p>Upload a CSV file with the following column format:</p>
                                    <div class="csv-format">
                                        <strong>FirstName, LastName, Email, Program, YearSection, Password</strong>
                                    </div>
                                    <p class="help-text">
                                        <i class="mdi mdi-lightbulb"></i> 
                                        Make sure your CSV file has a header row and follows the exact column order shown above.
                                        All students will be added to the <strong><?php echo htmlspecialchars($adminDepartment); ?></strong> department.
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Student Modal -->
            <div id="editModal" class="modal">
                <div>
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="mdi mdi-account-edit"></i> Edit Student
                            </h5>
                            <button type="button" class="modal-close" onclick="closeModal('editModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="editForm" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                <input type="hidden" name="edit_student" value="1">
                                <input type="hidden" id="edit_student_id" name="student_id">
                                
                                <div class="form-grid">
                                    <!-- Left Column -->
                                    <div class="form-grid-column">
                                        <div class="field">
                                            <label class="label">First Name</label>
                                            <div class="control has-icons-left">
                                                <input class="input is-rounded" type="text" id="edit_first_name" name="first_name" pattern="[A-Za-z\s]+" required>
                                                <span class="icon is-small is-left">
                                                    <i class="mdi mdi-account"></i>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="field">
                                            <label class="label">Last Name</label>
                                            <div class="control has-icons-left">
                                                <input class="input is-rounded" type="text" id="edit_last_name" name="last_name" pattern="[A-Za-z\s]+" required>
                                                <span class="icon is-small is-left">
                                                    <i class="mdi mdi-account-card-details"></i>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="field">
                                            <label class="label">Email Address</label>
                                            <div class="control has-icons-left">
                                                <input class="input is-rounded" type="email" id="edit_email" name="email" required>
                                                <span class="icon is-small is-left">
                                                    <i class="mdi mdi-email"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Right Column -->
                                    <div class="form-grid-column">
                                        <div class="field">
                                            <label class="label">Department</label>
                                            <div class="control has-icons-left">
                                                <input class="input is-rounded" type="text" id="edit_department" disabled>
                                                <span class="icon is-small is-left">
                                                    <i class="mdi mdi-domain"></i>
                                                </span>
                                                <input type="hidden" id="hidden_department" name="department">
                                            </div>
                                        </div>
                                        
                                        <div class="field">
                                            <label class="label">Program</label>
                                            <div class="control has-icons-left">
                                                <div class="select is-fullwidth is-rounded">
                                                    <select id="edit_program_select" name="program" required>
                                                        <option value="">Select Program</option>
                                                    </select>
                                                </div>
                                                <span class="icon is-small is-left">
                                                    <i class="mdi mdi-book-open-variant"></i>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="field">
                                            <label class="label">Year & Section</label>
                                            <div class="control has-icons-left">
                                                <input class="input is-rounded year-section-input" type="text" id="edit_year_section" name="year_section" placeholder="Program will appear here, add year-section (e.g., 4-1)" readonly required>
                                                <span class="icon is-small is-left">
                                                    <i class="mdi mdi-school"></i>
                                                </span>
                                            </div>
                                            <p class="year-section-help">Select a program first, then add year-section (e.g., 4-1 for 4th year section 1)</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="divider">
                                    <span>Security (Optional)</span>
                                </div>
                                
                                <div class="password-field-container">
                                    <div class="field">
                                        <label class="label">New Password (Leave blank to keep current password)</label>
                                        <div class="control has-icons-left has-icons-right">
                                            <input class="input is-rounded" type="password" id="edit_password" name="password" minlength="8">
                                            <span class="icon is-small is-left">
                                                <i class="mdi mdi-lock"></i>
                                            </span>
                                            <span class="icon is-small is-right toggle-password" onclick="togglePasswordVisibility('edit_password')">
                                                <i class="mdi mdi-eye"></i>
                                            </span>
                                        </div>
                                        <p class="help">Only fill this if you want to change the password</p>
                                    </div>
                                </div>
                            
                                <div class="modal-footer">
                                    <button type="button" class="reset-button" onclick="closeModal('editModal')">
                                        <i class="mdi mdi-close"></i> Cancel
                                    </button>
                                    <button type="submit" class="submit-button">
                                        <i class="mdi mdi-check"></i> Update Student
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div id="deleteModal" class="modal">
                <div>
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Deletion</h5>
                            <button type="button" class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this student? This action cannot be undone.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="reset-button" onclick="closeModal('deleteModal')">Cancel</button>
                            <a href="#" id="confirmDelete" class="submit-button" style="background-color: #dc3545;">Delete</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php $conn->close(); ?>
    </div>

    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/custom_alert.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        // Programs data organized by department (global scope)
        const programsByDepartment = {
            'Accountancy': [
                'BSA',
                'BSAT', 
                'BSLM'
            ],
            'Business Administration': [
                'BSBA-FM',
                'BSBA-M',
                'BSBA-MKT'
            ],
            'Hospitality Management': [
                'BSHM',
                'BSTrM'
            ],
            'Education and Arts': [
                'BEEd-PSE',
                'BEEd-GEN',
                'BSE-BIO',
                'BSE-ENG', 
                'BSE-FIL',
                'BSE-MATH',
                'AB-PSYCH',
                'BSSW',
                'BLIS',
                'BPE-SPE',
                'BPE-SWM',
                'CPTE'
            ],
            'Criminal Justice': [
                'BSCrim'
            ]
        };

        // Get current admin department (global scope)
        const adminDepartment = '<?php echo htmlspecialchars($adminDepartment); ?>';
        
        // Function to populate program dropdown (global scope)
        function populateProgramDropdown(selectElement, department, selectedValue = '') {
            const programs = programsByDepartment[department] || [];
            
            // Clear existing options except the first one
            selectElement.innerHTML = '<option value="">Select Program</option>';
            
            // Add programs for the department
            programs.forEach(program => {
                const option = document.createElement('option');
                option.value = program;
                option.textContent = program;
                if (program === selectedValue) {
                    option.selected = true;
                }
                selectElement.appendChild(option);
            });
        }

        // Initialize DataTables
        $(document).ready(function() {

            // Populate the add form program dropdown on page load
            const addProgramSelect = document.getElementById('program_select');
            if (addProgramSelect && adminDepartment) {
                populateProgramDropdown(addProgramSelect, adminDepartment);
                
                // Add change event to prefill program and focus on year section
                addProgramSelect.addEventListener('change', function() {
                    const yearSectionInput = document.getElementById('year_section');
                    if (this.value && yearSectionInput) {
                        // Set the program as the base value
                        yearSectionInput.value = this.value + ' ';
                        yearSectionInput.readOnly = false;
                        yearSectionInput.placeholder = 'Add year-section (e.g., 4-1)';
                        
                        setTimeout(() => {
                            yearSectionInput.focus();
                            // Set cursor at the end after the program name
                            yearSectionInput.setSelectionRange(yearSectionInput.value.length, yearSectionInput.value.length);
                            yearSectionInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 100);
                    } else if (yearSectionInput) {
                        // Reset if no program selected
                        yearSectionInput.value = '';
                        yearSectionInput.readOnly = true;
                        yearSectionInput.placeholder = 'Program will appear here, add year-section (e.g., 4-1)';
                    }
                });
            }

            // Add year section validation
            function setupYearSectionValidation() {
                const yearSectionInputs = document.querySelectorAll('.year-section-input');
                
                yearSectionInputs.forEach(input => {
                    input.addEventListener('input', function() {
                        validateYearSection(this);
                    });
                    
                    input.addEventListener('blur', function() {
                        validateYearSection(this);
                    });
                });
            }

            function validateYearSection(input) {
                const value = input.value.trim();
                const helpElement = input.closest('.field').querySelector('.year-section-help');
                
                // Check if it contains a program name followed by year-section pattern
                const programYearPattern = /^.+\s[1-4]-[1-9]$/;
                
                if (value && !programYearPattern.test(value)) {
                    input.classList.add('invalid');
                    if (helpElement) {
                        helpElement.textContent = 'Invalid format! Should be: Program Year-Section (e.g., BSA 4-1)';
                        helpElement.classList.add('error');
                    }
                    return false;
                } else if (value) {
                    input.classList.remove('invalid');
                    if (helpElement) {
                        helpElement.textContent = 'Correct format: Program + Year-Section';
                        helpElement.classList.remove('error');
                    }
                    return true;
                } else {
                    input.classList.remove('invalid');
                    if (helpElement) {
                        helpElement.textContent = 'Select a program first, then add year-section (e.g., 4-1 for 4th year section 1)';
                        helpElement.classList.remove('error');
                    }
                    return false;
                }
            }

            // Initialize year section validation
            setupYearSectionValidation();

            // Add form validation before submission
            const addForm = document.querySelector('.add-student-form');
            if (addForm) {
                addForm.addEventListener('submit', function(e) {
                    const yearSectionInput = this.querySelector('#year_section');
                    if (yearSectionInput && !validateYearSection(yearSectionInput)) {
                        e.preventDefault();
                        yearSectionInput.focus();
                        return false;
                    }
                });
            }

            const editForm = document.getElementById('editForm');
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    const yearSectionInput = this.querySelector('#edit_year_section');
                    if (yearSectionInput && !validateYearSection(yearSectionInput)) {
                        e.preventDefault();
                        yearSectionInput.focus();
                        return false;
                    }
                });
            }

            $('#studentTable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search students..."
                },
                dom: '<"top"lf>rt<"bottom"ip><"clear">',
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                pageLength: 10,
                ordering: true,
                columnDefs: [
                    { orderable: false, targets: -1 } // Disable sorting on the actions column
                ]
            });
            
            // Set up tab navigation
            $('.tab-btn').click(function() {
                const tabId = $(this).data('tab');
                
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                
                $('.tab-content').removeClass('active');
                $('#' + tabId).addClass('active');
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
        
        // Toggle password visibility
        function togglePasswordVisibility(inputId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = passwordInput.parentNode.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('mdi-eye');
                eyeIcon.classList.add('mdi-eye-off');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('mdi-eye-off');
                eyeIcon.classList.add('mdi-eye');
            }
        }
        
        // Function to toggle dropdown menus
        function toggleIcon(element) {
            element.classList.toggle('active');
            const icon = element.querySelector('.toggle-icon i');
            icon.classList.toggle('mdi-plus');
            icon.classList.toggle('mdi-minus');
            
            const submenu = element.nextElementSibling;
            submenu.style.display = submenu.style.display === 'block' ? 'none' : 'block';
        }
        
        // Modal functions
        function openEditModal(studentId, firstName, lastName, email, department, program, yearSection) {
            document.getElementById('edit_student_id').value = studentId;
            document.getElementById('edit_first_name').value = firstName;
            document.getElementById('edit_last_name').value = lastName;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_department').value = department;
            document.getElementById('hidden_department').value = department;
            document.getElementById('edit_password').value = '';
            
            // Populate program dropdown for edit form
            const editProgramSelect = document.getElementById('edit_program_select');
            const editYearSectionInput = document.getElementById('edit_year_section');
            
            if (editProgramSelect && department) {
                populateProgramDropdown(editProgramSelect, department, program);
                
                // Set the year section input with the full value
                if (editYearSectionInput) {
                    editYearSectionInput.value = yearSection;
                    editYearSectionInput.readOnly = false;
                }
                
                // Add change event to prefill program when program is changed
                editProgramSelect.addEventListener('change', function() {
                    if (this.value && editYearSectionInput) {
                        // Extract just the year-section part if it exists
                        const currentValue = editYearSectionInput.value;
                        const yearSectionMatch = currentValue.match(/\s([1-4]-[1-9])$/);
                        const yearSectionPart = yearSectionMatch ? yearSectionMatch[1] : '';
                        
                        // Set new program with existing year-section or just program
                        editYearSectionInput.value = this.value + (yearSectionPart ? ' ' + yearSectionPart : ' ');
                        editYearSectionInput.readOnly = false;
                        editYearSectionInput.placeholder = 'Add year-section (e.g., 4-1)';
                        
                        setTimeout(() => {
                            editYearSectionInput.focus();
                            editYearSectionInput.setSelectionRange(editYearSectionInput.value.length, editYearSectionInput.value.length);
                            editYearSectionInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 100);
                    } else if (editYearSectionInput) {
                        editYearSectionInput.value = '';
                        editYearSectionInput.readOnly = true;
                        editYearSectionInput.placeholder = 'Program will appear here, add year-section (e.g., 4-1)';
                    }
                });
            }
            
            document.getElementById('editModal').classList.add('show');
        }
        
        function openDeleteModal(studentId) {
            document.getElementById('confirmDelete').href = '<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>?delete_id=' + studentId;
            document.getElementById('deleteModal').classList.add('show');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }
        
        // Batch upload functionality
        function triggerFileUpload() {
            document.getElementById('studentFileInput').click();
        }

        function handleFileSelect(input) {
            const fileName = input.files[0] ? input.files[0].name : '';
            const fileInfo = document.getElementById('fileName');
            
            if (fileName) {
                // Validate file type
                const allowedTypes = ['.csv', '.xlsx', '.xls'];
                const fileExtension = fileName.toLowerCase().substring(fileName.lastIndexOf('.'));
                
                if (!allowedTypes.includes(fileExtension)) {
                    alert('Please select a valid CSV or Excel file (.csv, .xlsx, .xls)');
                    input.value = '';
                    fileInfo.textContent = 'No file selected';
                    fileInfo.style.color = '#666';
                    return;
                }
                
                fileInfo.innerHTML = `
                    <span style="color: #4caf50;">
                        <i class="mdi mdi-file-check"></i>
                        Selected: <strong>${fileName}</strong>
                    </span>
                `;
                
                // Enable the import button
                const importButton = document.getElementById('importButton');
                importButton.style.opacity = '1';
                importButton.style.cursor = 'pointer';
                importButton.disabled = false;
            } else {
                fileInfo.textContent = 'No file selected';
                fileInfo.style.color = '#666';
                
                // Disable the import button
                const importButton = document.getElementById('importButton');
                importButton.style.opacity = '0.5';
                importButton.style.cursor = 'not-allowed';
                importButton.disabled = true;
            }
        }

        // Ensure form submission works
        document.addEventListener('DOMContentLoaded', function() {
            const importForm = document.getElementById('importForm');
            if (importForm) {
                importForm.addEventListener('submit', function(e) {
                    const fileInput = document.getElementById('studentFileInput');
                    if (!fileInput.files || fileInput.files.length === 0) {
                        e.preventDefault();
                        alert('Please select a file before uploading.');
                        return false;
                    }
                    
                    // Show loading state
                    const importButton = document.getElementById('importButton');
                    importButton.innerHTML = '<svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M12 4V2A10 10 0 0 0 2 12h2a8 8 0 0 1 8-8z"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></path></svg> Importing...';
                    importButton.disabled = true;
                    
                    return true;
                });
            }
            
            // Handle URL parameters for status messages
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const message = urlParams.get('msg');

            if (status && message) {
                const alertType = status === 'success' ? 'alert-success' : 'alert-danger';
                const icon = status === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle';
                
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert ${alertType}`;
                alertDiv.innerHTML = `<i class="mdi ${icon}"></i> ${decodeURIComponent(message)}`;
                
                const mainContainer = document.querySelector('.main-container');
                const firstChild = mainContainer.firstElementChild;
                mainContainer.insertBefore(alertDiv, firstChild);
                
                // Auto-hide after 8 seconds
                setTimeout(function() {
                    alertDiv.style.opacity = '0';
                    setTimeout(function() {
                        if (alertDiv.parentNode) {
                            alertDiv.parentNode.removeChild(alertDiv);
                        }
                    }, 300);
                }, 8000);
                
                // Clear URL parameters
                const url = new URL(window.location);
                url.searchParams.delete('status');
                url.searchParams.delete('msg');
                window.history.replaceState({}, document.title, url);
            }
        });
    </script>
</body>

</html>
