<?php
require '../auth/middleware.php';
checkAccess(['Teacher']);

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get current teacher ID
$teacherId = $_SESSION['user_id'];

// Initialize variables for error/success messages
$successMsg = "";
$errorMsg = "";

// Fetch teacher information
$sql = "SELECT * FROM teacher WHERE TeacherID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $teacherId);
$stmt->execute();
$result = $stmt->get_result();
$teacher = $result->fetch_assoc();
$stmt->close();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $department = trim($_POST['department']);

    // Validation
    $isValid = true;

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email)) {
        $errorMsg = "First name, last name, and email are required fields.";
        $isValid = false;
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Please enter a valid email address.";
        $isValid = false;
    }

    // Check if email already exists (but belongs to another teacher)
    $checkEmailSql = "SELECT TeacherID FROM teacher WHERE Email = ? AND TeacherID != ?";
    $checkStmt = $conn->prepare($checkEmailSql);
    $checkStmt->bind_param("si", $email, $teacherId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        $errorMsg = "This email is already in use by another account.";
        $isValid = false;
    }
    $checkStmt->close();

    // If validation passes, update the teacher information
    if ($isValid) {
        $updateSql = "UPDATE teacher SET 
                      FirstName = ?, 
                      LastName = ?, 
                      Email = ?,
                      Department = ?
                      WHERE TeacherID = ?";

        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param(
            "ssssi",
            $firstName,
            $lastName,
            $email,
            $department,
            $teacherId
        );

        if ($updateStmt->execute()) {
            $successMsg = "Profile updated successfully!";

            // Refresh teacher data after update
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $teacherId);
            $stmt->execute();
            $result = $stmt->get_result();
            $teacher = $result->fetch_assoc();
            $stmt->close();
        } else {
            $errorMsg = "Error updating profile: " . $conn->error;
        }

        $updateStmt->close();
    }
}

// Include room status handler to automatically update room statuses
require_once '../auth/room_status_handler.php';
?>

<?php include "../partials/header.php"; ?>
<link href="../public/css/user_styles/equipment_report_status.css" rel="stylesheet">
<link href="../public/css/user_styles/edit_profile.css" rel="stylesheet">

<style>
    @media (max-width: 768px) {
        .security-actions {
            flex-direction: column;
            gap: 10px;
            width: 100%;
        }

        .security-actions .btn {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .btn-secondary,
        .security-actions .btn-danger {
            width: 100%;
            min-height: 44px;
            padding: 8px 12px;
        }
    }


    .main-content {
        margin-top: 30px;
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
    <div class="main-content">
        <div class="profile-container">
            <!-- Update message container to exclude password/delete errors -->
            <div class="message-container">
                <?php
                // Only display error messages that aren't related to password change or account deletion
                if (
                    isset($_SESSION['error_message']) &&
                    (!isset($_GET['error_type']) || ($_GET['error_type'] !== 'password_change' && $_GET['error_type'] !== 'delete_account'))
                ) {
                    echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                    unset($_SESSION['error_message']);
                }

                // Display success messages from session
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success alert-auto-fade"><i class="fa fa-check-circle"></i> ' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                    unset($_SESSION['success_message']);
                }
                ?>
            </div>

            <div class="profile-card">
                <div class="card-header bg-modal">
                    <h2 class="bold-personal">Personal Information</h2>
                    <p>View your profile information</p>
                </div>

                <!-- Notice about contacting department admin -->
                <div class="admin-notice alert alert-info">
                    <i class="fa fa-info-circle"></i> To update your profile information, please contact your department administrator.
                </div>

                <div class="profile-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <div class="input-with-icon">
                                <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($teacher['FirstName']); ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <div class="input-with-icon">
                                <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($teacher['LastName']); ?>" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-with-icon">
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($teacher['Email']); ?>" readonly>
                                <span class="verified-badge"><i class="fa fa-check-circle"></i> Verified</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="department">Department</label>
                            <div class="input-with-icon">
                                <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($teacher['Department']); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-card">
                <div class="card-header bg-modal">
                    <h2 class="bold-personal">Password & Security</h2>
                    <p>Update your password and security settings</p>
                </div>
                <div class="security-section">
                    <div class="security-actions">
                        <button type="button" class="btn btn-action btn-secondary" data-toggle="modal" data-target="#changePasswordModal">
                            <i class="fa fa-lock mr-2"></i> Change Password
                        </button>
                        <button type="button" class="btn btn-action btn-danger" data-toggle="modal" data-target="#deleteAccountModal">
                            <i class="fa fa-trash mr-2"></i> Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal" id="changePasswordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="alert-style-modal">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5>Change Password</h5>
                <!-- Add error message display -->
                <div id="passwordChangeError" class="modal-error-message" style="display: none;"></div>
                <form id="passwordChangeForm" action="change_password_process.php" method="post">
                    <div class="password-form-group">
                        <label for="currentPassword">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                    </div>
                    <div class="password-form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                        <small class="password-hint">Password must be at least 8 characters long</small>
                    </div>
                    <div class="password-form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                    </div>
                    <div class="alert-actions">
                        <button type="button" class="btn-action btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-action btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal" id="deleteAccountModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="alert-style-modal delete-modal">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5>Delete Account</h5>
                <p class="delete-warning">Warning: This action cannot be undone. All your data will be permanently deleted.</p>
                <!-- Add error message display -->
                <div id="deleteAccountError" class="modal-error-message" style="display: none;"></div>
                <form id="deleteAccountForm" action="delete_account_process.php" method="post">
                    <div class="password-form-group">
                        <label for="verifyPassword">Enter your password to confirm</label>
                        <input type="password" class="form-control" id="verifyPassword" name="verifyPassword" required>
                    </div>
                    <div class="alert-actions">
                        <button type="button" class="btn-action btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn-action btn-danger">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../partials/footer.php"; ?>

<style>
    .admin-notice {
        margin: 20px;
        padding: 15px;
        border-radius: 5px;
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password match validation
        const passwordForm = document.getElementById('passwordChangeForm');
        const newPassword = document.getElementById('newPassword');
        const confirmPassword = document.getElementById('confirmPassword');

        if (passwordForm) {
            passwordForm.addEventListener('submit', function(event) {
                if (newPassword.value !== confirmPassword.value) {
                    event.preventDefault();
                    alert('New password and confirmation do not match.');
                }

                if (newPassword.value.length < 8) {
                    event.preventDefault();
                    alert('Password must be at least 8 characters long.');
                }
            });
        }

        // Delete account confirmation
        const deleteAccountForm = document.getElementById('deleteAccountForm');
        if (deleteAccountForm) {
            deleteAccountForm.addEventListener('submit', function(event) {
                if (!confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.')) {
                    event.preventDefault();
                }
            });
        }

        // Check URL for error parameters
        const urlParams = new URLSearchParams(window.location.search);
        const errorType = urlParams.get('error_type');
        const errorMsg = urlParams.get('error_msg');

        // Add this function to your existing script
        function setupErrorMessageFade() {
            // Get all error message elements
            const errorMessages = document.querySelectorAll('.modal-error-message');

            errorMessages.forEach(message => {
                if (message.style.display !== 'none') {
                    // Set a timeout to fade the message after 5 seconds
                    setTimeout(function() {
                        // Add fade-out class
                        message.classList.add('fade-out');

                        // Hide after animation completes
                        setTimeout(function() {
                            message.style.display = 'none';
                            message.classList.remove('fade-out');
                        }, 1000);
                    }, 5000);
                }
            });
        }

        // Handle error messages from redirects
        if (errorType === 'password_change' && errorMsg) {
            // Show password change modal with error
            $('#changePasswordModal').modal('show');
            const errorElement = document.getElementById('passwordChangeError');
            errorElement.textContent = decodeURIComponent(errorMsg);
            errorElement.style.display = 'block';
            setupErrorMessageFade();
        } else if (errorType === 'delete_account' && errorMsg) {
            // Show delete account modal with error
            $('#deleteAccountModal').modal('show');
            const errorElement = document.getElementById('deleteAccountError');
            errorElement.textContent = decodeURIComponent(errorMsg);
            errorElement.style.display = 'block';
            setupErrorMessageFade();
        }

        // Current password focus handling
        const currentPasswordInput = document.getElementById('currentPassword');
        if (currentPasswordInput) {
            $('#changePasswordModal').on('shown.bs.modal', function() {
                currentPasswordInput.focus();
                // Clear any previous error
                document.getElementById('passwordChangeError').style.display = 'none';
            });
        }

        // Verify password focus handling
        const verifyPasswordInput = document.getElementById('verifyPassword');
        if (verifyPasswordInput) {
            $('#deleteAccountModal').on('shown.bs.modal', function() {
                verifyPasswordInput.focus();
                // Clear any previous error
                document.getElementById('deleteAccountError').style.display = 'none';
            });
        }
    });
</script>