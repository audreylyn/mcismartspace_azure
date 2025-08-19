<?php
require_once __DIR__ . '/../middleware/rate_limiter.php';
require_once __DIR__ . '/../middleware/session_manager.php';
require_once __DIR__ . '/dbh.inc.php';

$conn = db();
$rateLimiter = new RateLimiter($conn);
$sessionManager = new SessionManager();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $check = $rateLimiter->isAllowed();
    if (!$check['allowed']) {
        header("Location: ../index.php?error=locked");
        exit();
    }

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        die("Email and password are required.");
    }

    $loginSuccess = false;
    $userData = [];

    // Check registrar
    $stmt = $conn->prepare("SELECT regid, Reg_Password, 'Registrar' as Role, 'Registrar' as FirstName FROM registrar WHERE Reg_Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($password === $row['Reg_Password']) {
            $loginSuccess = true;
            $userData = [
                'user_id' => $row['regid'],
                'role' => 'Registrar',
                'email' => $email,
                'name' => 'Registrar'
            ];
            $redirectUrl = "../registrar/reg_add_admin.php";
        }
    }

    // Check dept_admin
    if (!$loginSuccess) {
        $stmt = $conn->prepare("SELECT AdminID, FirstName, LastName, Password, Department, 'Department Admin' as Role FROM dept_admin WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['Password'])) {
                $loginSuccess = true;
                $userData = [
                    'user_id' => $row['AdminID'],
                    'role' => 'Department Admin',
                    'email' => $email,
                    'name' => $row['FirstName'],
                    'firstname' => $row['FirstName'],
                    'lastname' => $row['LastName'],
                    'department' => $row['Department']
                ];
                $redirectUrl = "../department-admin/dept-admin.php";
            }
        }
    }

    // Check teacher
    if (!$loginSuccess) {
        $stmt = $conn->prepare("SELECT TeacherID, FirstName, Password, 'Teacher' as Role FROM teacher WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['Password'])) {
                $loginSuccess = true;
                $userData = [
                    'user_id' => $row['TeacherID'],
                    'role' => 'Teacher',
                    'email' => $email,
                    'name' => $row['FirstName']
                ];
                $redirectUrl = "../teacher/tc_browse_room.php";
            }
        }
    }

    // Check student
    if (!$loginSuccess) {
        $stmt = $conn->prepare("SELECT StudentID, FirstName, Password, 'Student' as Role FROM student WHERE Email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['Password'])) {
                $loginSuccess = true;
                $userData = [
                    'user_id' => $row['StudentID'],
                    'role' => 'Student',
                    'email' => $email,
                    'name' => $row['FirstName']
                ];
                $redirectUrl = "../student/std_browse_room.php";
            }
        }
    }

    if ($loginSuccess) {
        $rateLimiter->recordSuccessfulAttempt($email);
        $sessionManager->createSession($userData);
        header("Location: " . $redirectUrl);
        exit();
    } else {
        $rateLimiter->recordFailedAttempt($email);
        $remaining = $rateLimiter->getRemainingAttempts();
        header("Location: ../index.php?error=invalid&attempts_left=" . $remaining);
        exit();
    }
}
?>