<?php
require_once __DIR__ . '/../../auth/middleware.php';
$conn = db();

// Initialize message variables - first check for flash messages from session
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear flash messages after retrieving them
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);

// $conn provided by middleware's db()

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $department = $_SESSION['department'] ?? ''; // Get department from session
    $program = trim($_POST['program']);
    $yearsection = trim($_POST['yearsection']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($department) || empty($program) || empty($yearsection) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
    } else {
        // Check for duplicate email
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM student WHERE Email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->bind_result($email_count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($email_count > 0) {
            $_SESSION['error_message'] = "Email already exists. Please use a different email.";
        } else {
            // Inside the form submission logic
            $admin_id = $_SESSION['user_id']; // Get the logged-in admin's ID


            // Prepare statement to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO student (FirstName, LastName, Department, Program, YearSection, Email, Password, AdminID) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt === false) {
                $_SESSION['error_message'] = "Prepare failed: " . htmlspecialchars($conn->error);
            } else {
                // Hash the password for security
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Bind parameters
                $stmt->bind_param("sssssssi", $first_name, $last_name, $department, $program, $yearsection, $email, $hashed_password, $admin_id);

                // Execute the statement
                if ($stmt->execute()) {
                } else {
                    $_SESSION['error_message'] = "Error: " . htmlspecialchars($stmt->error);
                }

                // Close the statement
                $stmt->close();
            }
        }
    }

    // Redirect to prevent form resubmission on refresh
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Delete functionality
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $delete_stmt = $conn->prepare("DELETE FROM student WHERE StudentId = ?");

    if ($delete_stmt === false) {
        $_SESSION['error_message'] = "Prepare failed: " . htmlspecialchars($conn->error);
    } else {
        $delete_stmt->bind_param("i", $id);

        if ($delete_stmt->execute()) {
        } else {
            $_SESSION['error_message'] = "Error deleting student: " . htmlspecialchars($delete_stmt->error);
        }
        $delete_stmt->close();
    }

    // Redirect to prevent refresh issues
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all students created by the logged-in admin and in the same department
$admin_id = $_SESSION['user_id']; // Get the logged-in admin's ID
$admin_department = $_SESSION['department'] ?? '';

$sql = "SELECT StudentId, FirstName, LastName, Department, Program, YearSection, Email FROM student WHERE AdminID = ? AND Department = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $admin_id, $admin_department);
$stmt->execute();
$result = $stmt->get_result();
