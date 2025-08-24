<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

// Get user ID and role from session
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Process account deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get verification password
    $verifyPassword = $_POST['verifyPassword'];

    // Initialize error message
    $errorMsg = "";

    // Validate input
    if (empty($verifyPassword)) {
        $errorMsg = "Password is required to delete your account.";
    }

    // If no validation errors, proceed
    if (empty($errorMsg)) {
        // Determine which table and ID field to use based on user role
        if ($userRole == 'Student') {
            $table = 'student';
            $idField = 'StudentID';
        } else if ($userRole == 'Teacher') {
            $table = 'teacher';
            $idField = 'TeacherID';
        }
        
        // Get the current hashed password
        $sql = "SELECT Password FROM $table WHERE $idField = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $hashed_password = $user['Password'];

            // Verify the password against the stored hash
            if (password_verify($verifyPassword, $hashed_password)) {
                // Delete the user account based on role
                $deleteAccountSql = "DELETE FROM $table WHERE $idField = ?";
                $deleteAccountStmt = $conn->prepare($deleteAccountSql);
                $deleteAccountStmt->bind_param("i", $userId);

                if ($deleteAccountStmt->execute()) {
                    // Destroy the session
                    session_destroy();

                    // Set a temporary message in a cookie
                    setcookie("account_deleted", "Your account has been successfully deleted.", time() + 60, "/");

                    // Redirect to login page
                    header("Location: ../index.php");
                    exit();
                } else {
                    $errorMsg = "Error deleting account: " . $conn->error;
                }

                $deleteAccountStmt->close();
            } else {
                $errorMsg = "Incorrect password.";
            }
        } else {
            $errorMsg = "User not found.";
        }

        $stmt->close();
    }

    // If there was an error, redirect back with the error message
    if (!empty($errorMsg)) {
        $_SESSION['error_message'] = $errorMsg;
        // Add error_type and error_msg parameters to the URL
        header("Location: edit_profile.php?error_type=delete_account&error_msg=" . urlencode($errorMsg));
        exit();
    }
}

// If we get here without redirecting, something went wrong
$_SESSION['error_message'] = "An unexpected error occurred.";
header("Location: edit_profile.php");
exit();
