<?php
require_once __DIR__ . '/middleware.php';
$conn = db();

// Check connection
// Connection handled in db()

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Basic validation
    if (empty($email) || empty($password)) {
        die("Email and password are required.");
    }

    // Check registrar
    $stmt = $conn->prepare("SELECT regid, Reg_Password FROM registrar WHERE Reg_Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Direct password comparison for registrar
        if ($password === $row['Reg_Password']) {
            $_SESSION['user_id'] = $row['regid'];
            $_SESSION['role'] = 'Registrar';
            header("Location: ../registrar/reg_add_admin.php");
            exit();
        }
    }

    // Check dept_admin
    $stmt = $conn->prepare("SELECT AdminID, FirstName, Password, Department FROM dept_admin WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $row['Password'])) {
            $_SESSION['user_id'] = $row['AdminID'];
            $_SESSION['role'] = 'Department Admin';
            $_SESSION['first_name'] = $row['FirstName'];
            $_SESSION['department'] = $row['Department'];
            header("Location: ../department-admin/dept-admin.php");
            exit();
        }
    }

    // Check teacher
    $stmt = $conn->prepare("SELECT TeacherID, FirstName, Password FROM teacher WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $row['Password'])) {
            $_SESSION['user_id'] = $row['TeacherID'];
            $_SESSION['role'] = 'Teacher';
            $_SESSION['first_name'] = $row['FirstName'];
            header("Location: ../teacher/tc_browse_room.php");
            exit();
        }
    }

    // Check student
    $stmt = $conn->prepare("SELECT StudentId, FirstName, Password FROM student WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verify the password
        if (password_verify($password, $row['Password'])) {
            $_SESSION['user_id'] = $row['StudentId'];
            $_SESSION['role'] = 'Student';
            $_SESSION['first_name'] = $row['FirstName'];
            header("Location: ../student/std_browse_room.php");
            exit();
        }
    }

    echo "Invalid credentials";
}

// Connection kept open for reuse; do not close here
