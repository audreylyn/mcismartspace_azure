<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

// Get current user ID and role
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Initialize variables for error/success messages
$successMsg = "";
$errorMsg = "";

// Fetch user information based on role
if ($userRole == 'Student') {
    $sql = "SELECT * FROM student WHERE StudentID = ?";
    $tableName = "student";
    $idField = "StudentID";
} else if ($userRole == 'Teacher') {
    $sql = "SELECT * FROM teacher WHERE TeacherID = ?";
    $tableName = "teacher";
    $idField = "TeacherID";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

// Include room status handler to automatically update room statuses
require_once '../auth/room_status_handler.php';
?>

<?php include "../partials/header.php"; ?>
<link href="../public/css/user_styles/edit_profile.css" rel="stylesheet">
<style>
    .main-content {
        margin-top: 30px;
    }

    /* Delete Account Modal specific styles for mobile */
    @media (max-width: 768px) {
        /* Existing mobile styles... */

        #deleteAccountModal .alert-actions {
            flex-direction: row;
            justify-content: space-between;
            width: 100%;
        }

        #deleteAccountModal .alert-actions .btn-action {
            flex: 1;
            width: 48%;
            margin: 0;
            justify-content: center;
            text-align: center;
        }
    }

    @media (max-width: 576px) {
        /* Existing small mobile styles... */

        #deleteAccountModal .alert-actions {
            flex-direction: row;
            gap: 8px;
        }

        #deleteAccountModal .alert-actions .btn-action {
            font-size: 13px;
            min-height: 44px;
            padding: 8px 6px;
        }
    }

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
</style>

<?php include "layout/sidebar.php"; ?>
<?php include "layout/topnav.php"; ?>

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
                            <label for="firstName">First name</label>
                            <div class="input-with-icon">
                                <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($userData['FirstName'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="lastName">Last name</label>
                            <div class="input-with-icon">
                                <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($userData['LastName'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-with-icon">
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userData['Email'] ?? ''); ?>" readonly>
                                <span class="verified-badge">
                                    <i class="fa fa-check-circle"></i> Verified
                                </span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="department">Department</label>
                            <div class="input-with-icon">
                                <input type="text" id="department" name="department" value="<?php echo htmlspecialchars($userData['Department'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <?php if ($userRole == 'Student'): ?>
                        <div class="form-group">
                            <label for="program">Program</label>
                            <div class="input-with-icon">
                                <input type="text" id="program" name="program" value="<?php echo htmlspecialchars($userData['Program'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="yearSection">Year & Section</label>
                            <div class="input-with-icon">
                                <input type="text" id="yearSection" name="yearSection" value="<?php echo htmlspecialchars($userData['YearSection'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                        <?php elseif ($userRole == 'Teacher'): ?>
                        <div class="form-group">
                            <label for="position">Position</label>
                            <div class="input-with-icon">
                                <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($userData['Position'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="specialization">Specialization</label>
                            <div class="input-with-icon">
                                <input type="text" id="specialization" name="specialization" value="<?php echo htmlspecialchars($userData['Specialization'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                        <?php endif; ?>
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

        // Handle error messages from redirects
        if (errorType === 'password_change' && errorMsg) {
            // Show password change modal with error
            $('#changePasswordModal').modal('show');
            const errorElement = document.getElementById('passwordChangeError');
            errorElement.textContent = decodeURIComponent(errorMsg);
            errorElement.style.display = 'block';
        } else if (errorType === 'delete_account' && errorMsg) {
            // Show delete account modal with error
            $('#deleteAccountModal').modal('show');
            const errorElement = document.getElementById('deleteAccountError');
            errorElement.textContent = decodeURIComponent(errorMsg);
            errorElement.style.display = 'block';
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

        // Call the function when showing error messages
        if (errorType === 'password_change' && errorMsg || errorType === 'delete_account' && errorMsg) {
            setupErrorMessageFade();
        }
    });
</script>