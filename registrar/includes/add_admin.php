<?php
require_once __DIR__ . '/../../auth/middleware.php';
$conn = db();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $department = trim($_POST['department']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($department) || empty($email) || empty($password)) {
        $_SESSION['error_message'] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format.";
    } else {
        // Check for duplicate email
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM dept_admin WHERE Email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->bind_result($email_count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($email_count > 0) {
            $_SESSION['error_message'] = "Email already exists. Please use a different email.";
        } else {
            // Prepare statement to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO dept_admin (FirstName, LastName, Department, Email, Password) VALUES (?, ?, ?, ?, ?)");
            if ($stmt === false) {
                $_SESSION['error_message'] = "Prepare failed: " . htmlspecialchars($conn->error);
            } else {
                // Hash the password for security
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Bind parameters
                $stmt->bind_param("sssss", $first_name, $last_name, $department, $email, $hashed_password);

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
    $delete_stmt = $conn->prepare("DELETE FROM dept_admin WHERE AdminID = ?");

    if ($delete_stmt === false) {
        $_SESSION['error_message'] = "Prepare failed: " . htmlspecialchars($conn->error);
    } else {
        $delete_stmt->bind_param("i", $id);

        if ($delete_stmt->execute()) {
        } else {
            $_SESSION['error_message'] = "Error deleting admin: " . htmlspecialchars($delete_stmt->error);
        }
        $delete_stmt->close();
    }

    // Redirect to prevent refresh issues
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all admins
$sql = "SELECT AdminID, FirstName, LastName, Department, Email FROM dept_admin";
$result = $conn->query($sql);
