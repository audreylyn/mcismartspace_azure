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
                <div class="form-group" style="position:relative;">
                    <input
                        type="password"
                        class="form-control"
                        name="password"
                        id="password"
                        placeholder="Password"
                        required>
                    <button type="button" id="togglePassword" aria-label="Show password" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:transparent; border:none; outline:none; cursor:pointer; padding:0; display:flex; align-items:center;">
                        <svg id="eyeIcon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#666" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="transition:stroke 0.2s;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    </button>
                </div>
                <button type="submit" class="btn-login">Sign In</button>
            </form>
            <script>
                        const passwordInput = document.getElementById('password');
                        const togglePassword = document.getElementById('togglePassword');
                        const eyeIcon = document.getElementById('eyeIcon');
                        let passwordVisible = false;
                        togglePassword.addEventListener('click', function() {
                                passwordVisible = !passwordVisible;
                                passwordInput.type = passwordVisible ? 'text' : 'password';
                                eyeIcon.innerHTML = passwordVisible
                                    ? '<path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a21.07 21.07 0 0 1 5.06-7.06"/><path d="M9.53 9.53A3 3 0 0 1 12 15a3 3 0 0 1-2.47-5.47"/><path d="M1 1l22 22"/>'
                                    : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
                                eyeIcon.setAttribute('stroke', passwordVisible ? '#007bff' : '#666');
                        });
            </script>
        </div>
    </div>
    
    <script src="./public/js/alert.js"></script>
</body>

</html>