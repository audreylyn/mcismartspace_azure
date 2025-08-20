<?php
require '../auth/middleware.php';
checkAccess(['Student']);

// Get student ID from session
$studentId = $_SESSION['user_id'];

// Process password change
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $currentPassword = $_POST['currentPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Initialize error message
    $errorMsg = "";

    // Validate inputs
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $errorMsg = "All fields are required.";
    } elseif ($newPassword !== $confirmPassword) {
        $errorMsg = "New password and confirmation do not match.";
    } elseif (strlen($newPassword) < 8) {
        $errorMsg = "New password must be at least 8 characters long.";
    }

    // If no validation errors, proceed
    if (empty($errorMsg)) {
        // First, get the current hashed password
        $sql = "SELECT Password FROM student WHERE StudentID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            $hashed_password = $user['Password'];

            // Verify the current password against the stored hash
            if (password_verify($currentPassword, $hashed_password)) {
                // Hash the new password
                $new_hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);

                // Update the password with the new hash
                $updateSql = "UPDATE student SET Password = ? WHERE StudentID = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("si", $new_hashed_password, $studentId);

                if ($updateStmt->execute()) {
                    $_SESSION['success_message'] = "Password changed successfully!";
                    header("Location: edit_profile.php");
                    exit();
                } else {
                    $errorMsg = "Error updating password: " . $conn->error;
                }

                $updateStmt->close();
            } else {
                $errorMsg = "Current password is incorrect.";
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
        header("Location: edit_profile.php?error_type=password_change&error_msg=" . urlencode($errorMsg));
        exit();
    }
}

// If we get here without redirecting, something went wrong
$_SESSION['error_message'] = "An unexpected error occurred.";
header("Location: edit_profile.php");
exit();
