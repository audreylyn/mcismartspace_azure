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
</body>

</html>