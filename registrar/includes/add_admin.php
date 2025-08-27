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
        header("Location: ../reg_add_admin.php?status=error&msg=" . urlencode("All fields are required."));
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../reg_add_admin.php?status=error&msg=" . urlencode("Invalid email format."));
        exit;
    } else {
        // Check for duplicate email
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM dept_admin WHERE Email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->bind_result($email_count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($email_count > 0) {
            // Email already exists - redirect with error
            header("Location: ../reg_add_admin.php?status=error&msg=" . urlencode("Email already exists. Please use a different email."));
            exit;
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
                    $admin_id = $stmt->insert_id;
                    $stmt->close();
                    header('Location: ../reg_add_admin.php?status=success&admin_id=' . $admin_id . '&username=' . $email . '&password=' . $password);
                    exit;
                } else {
                    $stmt->close();
                    header("Location: ../reg_add_admin.php?status=error&msg=" . urlencode("Error: " . htmlspecialchars($stmt->error)));
                    exit;
                }
            }
        }
    }

    // Redirect to prevent form resubmission on refresh
    header("Location: ../reg_add_admin.php");
    exit;
}

// Delete functionality
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $delete_stmt = $conn->prepare("DELETE FROM dept_admin WHERE AdminID = ?");

    if ($delete_stmt === false) {
        header("Location: ../reg_add_admin.php?status=error&msg=" . urlencode("Prepare failed: " . htmlspecialchars($conn->error)));
        exit;
    } else {
        $delete_stmt->bind_param("i", $id);

        if ($delete_stmt->execute()) {
            $delete_stmt->close();
            header("Location: ../reg_add_admin.php?status=success&msg=" . urlencode("Administrator deleted successfully!"));
            exit;
        } else {
            $delete_stmt->close();
            header("Location: ../reg_add_admin.php?status=error&msg=" . urlencode("Error deleting admin: " . htmlspecialchars($delete_stmt->error)));
            exit;
        }
    }
}

// Fetch all admins
$sql = "SELECT AdminID, FirstName, LastName, Department, Email FROM dept_admin";
$result = $conn->query($sql);
