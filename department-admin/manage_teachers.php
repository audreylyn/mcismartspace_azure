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

// Process add teacher form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_teacher'])) {
    // Validate and sanitize input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $department = $adminDepartment; // Use admin's department
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    $isValid = true;

    if (empty($first_name) || empty($last_name) || empty($department) || empty($email) || empty($password)) {
        $error_message = "All fields are required.";
        $isValid = false;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
        $isValid = false;
    } else {
        // Check for duplicate email
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM teacher WHERE Email = ?");
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

    // If validation passes, add the teacher
    if ($isValid) {
        // Prepare statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO teacher (FirstName, LastName, Department, Email, Password, AdminID) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt === false) {
            $error_message = "Prepare failed: " . htmlspecialchars($conn->error);
        } else {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Bind parameters
            $stmt->bind_param("sssssi", $first_name, $last_name, $department, $email, $hashed_password, $adminId);

            // Execute the statement
            if ($stmt->execute()) {
                $success_message = "Teacher added successfully!";
            } else {
                $error_message = "Error: " . htmlspecialchars($stmt->error);
            }

            // Close the statement
            $stmt->close();
        }
    }
}

// Process edit teacher form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_teacher'])) {
    // Get form data
    $teacherId = $_POST['teacher_id'];
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    
    // Optional password change
    $newPassword = trim($_POST['password']);
    $changePassword = !empty($newPassword);

    // Validation
    $isValid = true;

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $error_message = "All fields are required except password (only if changing).";
        $isValid = false;
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address.";
        $isValid = false;
    }

    // Check if teacher belongs to the same department
    $checkDeptSql = "SELECT Department FROM teacher WHERE TeacherID = ?";
    $checkStmt = $conn->prepare($checkDeptSql);
    $checkStmt->bind_param("i", $teacherId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $teacherData = $checkResult->fetch_assoc();
    $checkStmt->close();

    if ($teacherData['Department'] !== $adminDepartment) {
        $error_message = "You can only edit teachers in your department.";
        $isValid = false;
    }

    // Check if email is already in use by another teacher
    $checkEmailSql = "SELECT TeacherID FROM teacher WHERE Email = ? AND TeacherID != ?";
    $checkStmt = $conn->prepare($checkEmailSql);
    $checkStmt->bind_param("si", $email, $teacherId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        $error_message = "This email is already in use by another account.";
        $isValid = false;
    }
    $checkStmt->close();

    // If validation passes, update teacher information
    if ($isValid) {
        if ($changePassword) {
            // Update with new password
            $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $updateSql = "UPDATE teacher SET 
                          FirstName = ?, 
                          LastName = ?, 
                          Email = ?,
                          Password = ?
                          WHERE TeacherID = ? AND Department = ?";

            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param(
                "ssssis",
                $firstName,
                $lastName,
                $email,
                $hashed_password,
                $teacherId,
                $adminDepartment
            );
        } else {
            // Update without changing password
            $updateSql = "UPDATE teacher SET 
                          FirstName = ?, 
                          LastName = ?, 
                          Email = ?
                          WHERE TeacherID = ? AND Department = ?";

            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param(
                "sssss",
                $firstName,
                $lastName,
                $email,
                $teacherId,
                $adminDepartment
            );
        }

        if ($updateStmt->execute()) {
            $success_message = "Teacher information updated successfully!";
        } else {
            $error_message = "Error updating teacher information: " . $conn->error;
        }

        $updateStmt->close();
    }
}

// Process delete request
if (isset($_GET['delete_id']) && !empty($_GET['delete_id'])) {
    $deleteId = (int)$_GET['delete_id'];

    // Check if teacher belongs to the same department
    $checkDeptSql = "SELECT Department FROM teacher WHERE TeacherID = ?";
    $checkStmt = $conn->prepare($checkDeptSql);
    $checkStmt->bind_param("i", $deleteId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $teacherData = $checkResult->fetch_assoc();

        if ($teacherData['Department'] === $adminDepartment) {
            // Teacher belongs to admin's department, proceed with deletion
            $deleteSql = "DELETE FROM teacher WHERE TeacherID = ? AND Department = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("is", $deleteId, $adminDepartment);

            if ($deleteStmt->execute()) {
                $success_message = "Teacher deleted successfully!";
            } else {
                $error_message = "Error deleting teacher: " . $conn->error;
            }

            $deleteStmt->close();
        } else {
            $error_message = "You can only delete teachers in your department.";
        }
    } else {
        $error_message = "Teacher not found.";
    }

    $checkStmt->close();
}

// Fetch all teachers in the department
$sql = "SELECT * FROM teacher WHERE Department = ? ORDER BY TeacherID ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $adminDepartment);
$stmt->execute();
$teachersResult = $stmt->get_result();
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Teachers</title>
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_2.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">
    <link rel="stylesheet" href="../public/css/admin_styles/manage_accounts.css">
    
    <style>
        /* Only unique styles not in external CSS */
        .import-btn-acc {
            border-radius: 0 0.3em 0.3em 0;
            background-color: rgb(41, 114, 45);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .import-btn-acc:hover {
            background-color: rgb(5, 48, 7);
        }

        /* Enhanced Modal Styles */
        .modal-lg {
            max-width: 800px;
            width: 90%;
        }
        
        .modal-header {
            border-bottom: 1px solid #e0e0e0;
            padding: 15px 20px;
            background-color: #f8f9fa;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }
        
        .modal-title {
            display: flex;
            align-items: center;
            font-size: 1.25rem;
            font-weight: 600;
            color: #333;
        }
        
        .modal-title i {
            margin-right: 8px;
            color: #4a6fdc;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            border-top: 1px solid #e0e0e0;
            padding: 15px 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }
        
        /* Fix password field width - don't make it full width */
        .password-field-container {
            max-width: 400px;
            margin: 0 auto;
        }
        
        /* Form actions alignment */
        .form-actions-container {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        /* Batch upload info styles */
        .batch-upload-info {
            margin-top: 20px;
        }
        
        .info-card {
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
        }
        
        .info-card h4 {
            margin: 0 0 15px 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .info-card h4 i {
            color: #4a6fdc;
        }
        
        .csv-format {
            background-color: #e8f4fd;
            border: 1px solid #b3d9ff;
            border-radius: 4px;
            padding: 10px;
            margin: 10px 0;
            font-family: monospace;
            text-align: center;
        }
        
        .help-text {
            margin: 10px 0 0 0;
            color: #666;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }
        
        .help-text i {
            color: #ffa500;
            margin-top: 2px;
        }
        
        /* Excel button hover effect */
        .excel:hover {
            background-color: #1a5d39 !important;
        }
        
        #importButton:hover {
            background-color: #3a5dba !important;
        }
    </style>
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
                <button class="tab-btn active" data-tab="tab-list">Teacher List</button>
                <button class="tab-btn" data-tab="tab-add">Add New Teacher</button>
            </div>

            <!-- Teacher List Tab -->
            <div id="tab-list" class="tab-content active">
                <div class="table-container">
                    <div class="card">
                        <header class="card-header">
                            <div class="new-title-container">
                                <p class="new-title">TEACHER LIST</p>
                            </div>
                        </header>
                        <div class="card-content">
                            <table id="teacherTable" class="teacher-table display is-fullwidth">
                                <thead>
                                    <tr>
                                        <th>First Name</th>
                                        <th>Last Name</th>
                                        <th>Department</th>
                                        <th>Email</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($teachersResult->num_rows > 0): ?>
                                        <?php while ($teacher = $teachersResult->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($teacher['FirstName']) ?></td>
                                                <td><?= htmlspecialchars($teacher['LastName']) ?></td>
                                                <td><?= htmlspecialchars($teacher['Department']) ?></td>
                                                <td><?= htmlspecialchars($teacher['Email']) ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button type="button" class="btn-edit" onclick="openEditModal(
                                                            <?= $teacher['TeacherID'] ?>,
                                                            '<?= htmlspecialchars($teacher['FirstName']) ?>',
                                                            '<?= htmlspecialchars($teacher['LastName']) ?>',
                                                            '<?= htmlspecialchars($teacher['Email']) ?>',
                                                            '<?= htmlspecialchars($teacher['Department']) ?>'
                                                        )">
                                                            <i class="mdi mdi-pencil"></i> Edit
                                                        </button>
                                                        <button type="button" class="btn-delete" onclick="openDeleteModal(<?= $teacher['TeacherID'] ?>)">
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

            <!-- Add New Teacher Tab -->
            <div id="tab-add" class="tab-content">
                <div class="card add-teacher-card">
                    <header class="card-header">
                        <div class="new-title-container" style="width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 1rem;">
                            <p class="new-title"><i class="mdi mdi-account-plus"></i> ADD NEW TEACHER</p>
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <form id="importForm" method="post" action="includes/import_teachers.php" enctype="multipart/form-data" style="display: flex;">
                                    <input type="hidden" name="importSubmit" value="1">
                                    <button class="excel" type="button" onclick="document.getElementById('teacherFileInput').click();" style="border-radius: 0.3em 0 0 0.3em; display: flex; justify-content: center; width: 50px; padding: 0.5rem; background-color: #217346; border: none; cursor: pointer;">
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
                                        <input type="file" id="teacherFileInput" name="file" accept=".csv,.xlsx,.xls" style="display: none;" onchange="updateFileName()" />
                                    </button>
                                    <button id="importButton" type="submit" class="import-btn-acc">
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
                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="add-teacher-form">
                            <input type="hidden" name="add_teacher" value="1">
                            <div class="form-grid">
                                <!-- Left Column -->
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
                                </div>
                                
                                <!-- Right Column -->
                                <div class="form-grid-column">
                                    <div class="field">
                                        <label class="label">Email Address</label>
                                        <div class="control has-icons-left">
                                            <input class="input is-rounded" type="email" name="email" placeholder="teacher@example.com" required>
                                            <span class="icon is-small is-left">
                                                <i class="mdi mdi-email"></i>
                                            </span>
                                        </div>
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
                        <span>Add Teacher</span>
                    </button>
                    <button type="reset" class="reset-button">
                        <i class="mdi mdi-refresh"></i>
                        <span>Reset</span>
                    </button>
                </div>
            </div>                            <div class="divider">
                                <span>OR</span>
                            </div>
                            
                            <div class="batch-upload-info">
                                <div class="info-card">
                                    <h4><i class="mdi mdi-information"></i> Batch Upload Instructions</h4>
                                    <p>Upload a CSV file with the following column format:</p>
                                    <div class="csv-format">
                                        <strong>FirstName, LastName, Email, Password</strong>
                                    </div>
                                    <p class="help-text">
                                        <i class="mdi mdi-lightbulb"></i> 
                                        Make sure your CSV file has a header row and follows the exact column order shown above.
                                        All teachers will be added to the <strong><?php echo htmlspecialchars($adminDepartment); ?></strong> department.
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Teacher Modal -->
            <div id="editModal" class="modal">
                <div>
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="mdi mdi-account-edit"></i> Edit Teacher
                            </h5>
                            <button type="button" class="modal-close" onclick="closeModal('editModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="editForm" action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                                <input type="hidden" name="edit_teacher" value="1">
                                <input type="hidden" id="edit_teacher_id" name="teacher_id">
                                
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
                                    </div>
                                    
                                    <!-- Right Column -->
                                    <div class="form-grid-column">
                                        <div class="field">
                                            <label class="label">Email Address</label>
                                            <div class="control has-icons-left">
                                                <input class="input is-rounded" type="email" id="edit_email" name="email" required>
                                                <span class="icon is-small is-left">
                                                    <i class="mdi mdi-email"></i>
                                                </span>
                                            </div>
                                        </div>
                                        
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
                                        <i class="mdi mdi-check"></i> Update Teacher
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div id="deleteModal" class="modal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Confirm Deletion</h5>
                            <button type="button" class="modal-close" onclick="closeModal('deleteModal')">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p>Are you sure you want to delete this teacher? This action cannot be undone.</p>
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
        // Initialize DataTables
        $(document).ready(function() {
            $('#teacherTable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search teachers..."
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
        function openEditModal(teacherId, firstName, lastName, email, department) {
            document.getElementById('edit_teacher_id').value = teacherId;
            document.getElementById('edit_first_name').value = firstName;
            document.getElementById('edit_last_name').value = lastName;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_department').value = department;
            document.getElementById('hidden_department').value = department;
            document.getElementById('edit_password').value = '';
            
            document.getElementById('editModal').classList.add('show');
        }
        
        function openDeleteModal(teacherId) {
            document.getElementById('confirmDelete').href = '<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>?delete_id=' + teacherId;
            document.getElementById('deleteModal').classList.add('show');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }
        
        // File upload functions
        function updateFileName() {
            const fileInput = document.getElementById('teacherFileInput');
            const fileName = document.getElementById('fileName');
            
            if (fileInput.files.length > 0) {
                fileName.textContent = fileInput.files[0].name;
                fileName.style.color = '#4a6fdc';
            } else {
                fileName.textContent = 'No file selected';
                fileName.style.color = '#666';
            }
        }
        
        // Form validation before submit
        document.getElementById('importForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('teacherFileInput');
            
            if (!fileInput.files.length) {
                e.preventDefault();
                alert('Please select a file to upload.');
                return false;
            }
            
            const file = fileInput.files[0];
            const allowedTypes = ['.csv', '.xlsx', '.xls'];
            const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
            
            if (!allowedTypes.includes(fileExtension)) {
                e.preventDefault();
                alert('Please select a valid CSV or Excel file.');
                return false;
            }
            
            // Show loading state
            const importButton = document.getElementById('importButton');
            importButton.innerHTML = '<svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M12 4V2A10 10 0 0 0 2 12h2a8 8 0 0 1 8-8z"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></path></svg> Importing...';
            importButton.disabled = true;
            
            return true;
        });
        
        // Handle URL parameters for status messages
        document.addEventListener('DOMContentLoaded', function() {
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
