<?php
// Include error handling configuration
require_once __DIR__ . '/middleware/error_handler.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="public/assets/logo.webp" type="image/webp" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/login.css">
    <title>SmartSpace | Room Management System</title>
    <style>
        .alert {
            transition: opacity 1s ease-in-out;
        }
        .fade-out {
            opacity: 0;
        }
    </style>
</head>

<body>
    <div class="login-image">
        <div class="image-overlay-text"></div>
    </div>
    <div class="login-form">
        <div class="form-container">
            <div class="branding">
                <img src="public/assets/logo.webp" alt="Logo" class="logo">
                <h1 class="brand-title">MCiSmartSpace</h1>
                <div class="college-name">Meycauayan College</div>
            </div>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger" role="alert" style="padding: 10px; margin-bottom: 15px; border-radius: 5px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;">
                    <?php
                    switch ($_GET['error']) {
                        case 'locked':
                            echo "Too many failed login attempts. Please try again in 15 minutes.";
                            break;
                        case 'invalid':
                            echo "Invalid email or password.";
                            break;
                        case 'timeout':
                            echo "Your session has expired. Please log in again.";
                            break;
                        case 'denied':
                             echo "Access denied.";
                             break;
                        default:
                            echo "An unknown error occurred.";
                            break;
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['attempts_left'])): ?>
                <div class="alert alert-warning" role="alert" style="padding: 10px; margin-bottom: 15px; border-radius: 5px; background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba;">
                    You have <?php echo htmlspecialchars($_GET['attempts_left']); ?> login attempts remaining.
                </div>
            <?php endif; ?>

            <form action="auth/login.php" method="POST">
                <div class="form-group">
                    <input
                        type="email"
                        class="form-control"
                        name="email"
                        placeholder="Email"
                        required>
                </div>
                <div class="form-group">
                    <input
                        type="password"
                        class="form-control"
                        name="password"
                        placeholder="Password"
                        required>
                </div>
                <button type="submit" class="btn-login">Sign In</button>
            </form>
        </div>
    </div>
    
    <script src="./public/js/alert.js"></script>
</body>

</html>