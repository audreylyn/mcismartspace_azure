<?php
require '../auth/middleware.php';
checkAccess(['Department Admin']);

// Get current admin ID
$adminId = $_SESSION['user_id'];

// Initialize variables for error/success messages
$successMsg = "";
$errorMsg = "";

// Fetch admin information
$sql = "SELECT * FROM dept_admin WHERE AdminID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $adminId);
$stmt->execute();
$result = $stmt->get_result();
$adminData = $result->fetch_assoc();
$stmt->close();

require_once '../auth/room_status_handler.php';
?>

<?php include "../partials/header.php"; ?>
<link href="../public/css/user_styles/edit_profile.css" rel="stylesheet">

<?php include 'layout/topnav.php'; ?>
<?php include 'layout/sidebar.php'; ?>

<div class="right_col" role="main">
	<div class="main-content">
		<div class="profile-container">
			<div class="message-container">
				<?php
				if (isset($_SESSION['error_message'])) {
					echo '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' . htmlspecialchars($_SESSION['error_message']) . '</div>';
					unset($_SESSION['error_message']);
				}
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
				<div class="admin-notice alert alert-info">
					<i class="fa fa-info-circle"></i> To update your profile information, please contact the system administrator.
				</div>
				<div class="profile-form">
					<div class="form-row">
						<div class="form-group">
							<label for="firstName">First name</label>
							<div class="input-with-icon">
								<input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($adminData['FirstName'] ?? ''); ?>" readonly>
							</div>
						</div>
						<div class="form-group">
							<label for="lastName">Last name</label>
							<div class="input-with-icon">
								<input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($adminData['LastName'] ?? ''); ?>" readonly>
							</div>
						</div>
					</div>
					<div class="form-row">
						<div class="form-group">
							<label for="email">Email</label>
							<div class="input-with-icon">
								<input type="email" id="email" name="email" value="<?php echo htmlspecialchars($adminData['Email'] ?? ''); ?>" readonly>
								<span class="verified-badge">
									<i class="fa fa-check-circle"></i> Verified
								</span>
							</div>
						</div>
						<div class="form-group">
							<label for="department">Department</label>
							<div class="input-with-icon">
								<input type="text" id="department" name="department" value="<?php echo htmlspecialchars($adminData['Department'] ?? ''); ?>" readonly>
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
				<div id="passwordChangeError" class="modal-error-message" style="display: none;"></div>
				<form id="passwordChangeForm" action="change_password_process.php" method="post">
					<div class="password-form-group">
						<label for="currentPassword">Current Password</label>
						<div class="input-with-icon">
							<input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
							<span class="toggle-password" data-target="currentPassword">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M2 10C3.5 6 7 3 10 3C13 3 16.5 6 18 10C16.5 14 13 17 10 17C7 17 3.5 14 2 10Z" stroke="#888" stroke-width="2" fill="none"/>
									<circle cx="10" cy="10" r="3" stroke="#888" stroke-width="2" fill="none"/>
								</svg>
							</span>
						</div>
					</div>
					<div class="password-form-group">
						<label for="newPassword">New Password</label>
						<div class="input-with-icon">
							<input type="password" class="form-control" id="newPassword" name="newPassword" required>
							<span class="toggle-password" data-target="newPassword">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M2 10C3.5 6 7 3 10 3C13 3 16.5 6 18 10C16.5 14 13 17 10 17C7 17 3.5 14 2 10Z" stroke="#888" stroke-width="2" fill="none"/>
									<circle cx="10" cy="10" r="3" stroke="#888" stroke-width="2" fill="none"/>
								</svg>
							</span>
						</div>
						<small class="password-hint">Password must be at least 8 characters long</small>
					</div>
					<div class="password-form-group">
						<label for="confirmPassword">Confirm New Password</label>
						<div class="input-with-icon">
							<input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
							<span class="toggle-password" data-target="confirmPassword">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M2 10C3.5 6 7 3 10 3C13 3 16.5 6 18 10C16.5 14 13 17 10 17C7 17 3.5 14 2 10Z" stroke="#888" stroke-width="2" fill="none"/>
									<circle cx="10" cy="10" r="3" stroke="#888" stroke-width="2" fill="none"/>
								</svg>
							</span>
						</div>
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
				<div id="deleteAccountError" class="modal-error-message" style="display: none;"></div>
				<form id="deleteAccountForm" action="delete_account_process.php" method="post">
					<div class="password-form-group">
						<label for="verifyPassword">Enter your password to confirm</label>
						<div class="input-with-icon">
							<input type="password" class="form-control" id="verifyPassword" name="verifyPassword" required>
							<span class="toggle-password" data-target="verifyPassword">
								<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M2 10C3.5 6 7 3 10 3C13 3 16.5 6 18 10C16.5 14 13 17 10 17C7 17 3.5 14 2 10Z" stroke="#888" stroke-width="2" fill="none"/>
									<circle cx="10" cy="10" r="3" stroke="#888" stroke-width="2" fill="none"/>
								</svg>
							</span>
						</div>
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
	.input-with-icon {
		position: relative;
	}
	.toggle-password {
		position: absolute;
		right: 10px;
		top: 50%;
		transform: translateY(-50%);
		cursor: pointer;
		color: #888;
		transition: color 0.2s;
		z-index: 2;
	}
	.toggle-password.active svg path,
	.toggle-password.active svg circle {
		stroke: #007bff;
	}
</style>

<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Password match validation
		const passwordForm = document.getElementById('passwordChangeForm');
		const newPassword = document.getElementById('newPassword');
		const confirmPassword = document.getElementById('confirmPassword');
		const currentPasswordInput = document.getElementById('currentPassword');

		if (passwordForm) {
			passwordForm.addEventListener('submit', function(event) {
				// Get values
				const currentVal = currentPasswordInput.value;
				const newVal = newPassword.value;
				const confirmVal = confirmPassword.value;
				const errorElement = document.getElementById('passwordChangeError');
				errorElement.style.display = 'none';
				errorElement.textContent = '';

				// Check if new password matches confirmation
				if (newVal !== confirmVal) {
					event.preventDefault();
					errorElement.textContent = 'New password and confirmation do not match.';
					errorElement.style.display = 'block';
					return;
				}

				// Check password length
				if (newVal.length < 8) {
					event.preventDefault();
					errorElement.textContent = 'Password must be at least 8 characters long.';
					errorElement.style.display = 'block';
					return;
				}

				// Check if new password is same as current
				if (currentVal === newVal) {
					event.preventDefault();
					errorElement.textContent = 'New password must be different from your current password.';
					errorElement.style.display = 'block';
					return;
				}

				// Check if new password is too similar (e.g., only 1 char different)
				let diffCount = 0;
				for (let i = 0; i < Math.max(currentVal.length, newVal.length); i++) {
					if (currentVal[i] !== newVal[i]) diffCount++;
				}
				if (diffCount <= 1 && currentVal.length === newVal.length) {
					event.preventDefault();
					errorElement.textContent = 'New password is too similar to your current password.';
					errorElement.style.display = 'block';
					return;
				}

				// Optionally, check for common passwords or add more rules here
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
			$('#changePasswordModal').modal('show');
			const errorElement = document.getElementById('passwordChangeError');
			errorElement.textContent = decodeURIComponent(errorMsg);
			errorElement.style.display = 'block';
		}

		else if (errorType === 'delete_account' && errorMsg) {
			$('#deleteAccountModal').modal('show');
			const errorElement = document.getElementById('deleteAccountError');
			errorElement.textContent = decodeURIComponent(errorMsg);
			errorElement.style.display = 'block';
		}

		// Current password focus handling
		if (currentPasswordInput) {
			$('#changePasswordModal').on('shown.bs.modal', function() {
				currentPasswordInput.focus();
				document.getElementById('passwordChangeError').style.display = 'none';
			});
		}

		// Verify password focus handling
		const verifyPasswordInput = document.getElementById('verifyPassword');
		if (verifyPasswordInput) {
			$('#deleteAccountModal').on('shown.bs.modal', function() {
				verifyPasswordInput.focus();
				document.getElementById('deleteAccountError').style.display = 'none';
			});
		}

		// Add this function to your existing script
		function setupErrorMessageFade() {
			const errorMessages = document.querySelectorAll('.modal-error-message');
			errorMessages.forEach(message => {
				if (message.style.display !== 'none') {
					setTimeout(function() {
						message.classList.add('fade-out');
						setTimeout(function() {
							message.style.display = 'none';
							message.classList.remove('fade-out');
						}, 1000);
					}, 5000);
				}
			});
		}
		if (errorType === 'password_change' && errorMsg || errorType === 'delete_account' && errorMsg) {
			setupErrorMessageFade();
		}

		// Show/hide password toggle
		document.querySelectorAll('.toggle-password').forEach(function(toggle) {
			toggle.addEventListener('click', function() {
				const targetId = toggle.getAttribute('data-target');
				const input = document.getElementById(targetId);
				if (input) {
					if (input.type === 'password') {
						input.type = 'text';
						toggle.classList.add('active');
					} else {
						input.type = 'password';
						toggle.classList.remove('active');
					}
				}
			});
		});
	});
</script>
