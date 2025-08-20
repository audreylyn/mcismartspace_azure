<?php
session_start();
require_once __DIR__ . '/../../auth/middleware.php';
checkAccess(['Registrar']);

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the database connection
    $conn = db();
    
    // Get form data and sanitize
    $admin_id = filter_input(INPUT_POST, 'admin_id', FILTER_SANITIZE_NUMBER_INT);
    $first_name = trim(filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS));
    $last_name = trim(filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS));
    $department = trim(filter_input(INPUT_POST, 'department', FILTER_SANITIZE_SPECIAL_CHARS));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    
    // Validate inputs
    if (empty($admin_id) || empty($first_name) || empty($last_name) || empty($department) || empty($email)) {
        $_SESSION['error_message'] = "All fields are required.";
        header("Location: ../reg_add_admin.php");
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
        header("Location: ../reg_add_admin.php");
        exit;
    }
    
    // Check if the email already exists (for a different admin)
    $check_email_stmt = $conn->prepare("SELECT AdminID FROM dept_admin WHERE Email = ? AND AdminID != ?");
    $check_email_stmt->bind_param("si", $email, $admin_id);
    $check_email_stmt->execute();
    $check_email_result = $check_email_stmt->get_result();
    
    if ($check_email_result->num_rows > 0) {
        $_SESSION['error_message'] = "Email already exists. Please use a different email.";
        header("Location: ../reg_add_admin.php");
        exit;
    }
    $check_email_stmt->close();
    
    // Update the admin information
    $update_stmt = $conn->prepare("UPDATE dept_admin SET FirstName = ?, LastName = ?, Department = ?, Email = ? WHERE AdminID = ?");
    $update_stmt->bind_param("ssssi", $first_name, $last_name, $department, $email, $admin_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Administrator updated successfully.";
    } else {
        $_SESSION['error_message'] = "Error updating administrator: " . $conn->error;
    }
    
    $update_stmt->close();
    $conn->close();
    
    // Redirect back to the admin page
    header("Location: ../reg_add_admin.php");
    exit;
} else {
    // If not a POST request, redirect to the admin page
    header("Location: ../reg_add_admin.php");
    exit;
}
