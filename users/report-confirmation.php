<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if we have a success message
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : "Your report has been submitted successfully!";

// Get the reference number if available
$referenceNumber = isset($_SESSION['report_reference']) ? $_SESSION['report_reference'] : 'EQ' . rand(100000, 999999);

// Clear the messages to prevent them from showing again on refresh
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['report_reference'])) {
    unset($_SESSION['report_reference']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Confirmation</title>
    <link href="../public/css/user_styles/report_confirmation.css" rel="stylesheet">
</head>

<body>
    <!-- Page content -->
    <div class="right_col" role="main">
        <div class="confirmation-container">
            <div class="confirmation-card">
                <div class="success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#0f4228" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="success-svg">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>

                <h2 class="confirmation-title">Report Submitted Successfully!</h2>
                <p class="confirmation-message"><?php echo $successMessage; ?></p>

                <div class="reference-number">
                    <div class="reference-label">Reference Number</div>
                    <div class="reference-value"><?php echo $referenceNumber; ?></div>
                </div>

                <div class="btn-container">
                    <a href="qr-scan.php" class="btn btn-primary">
                        <i class="fa fa-qrcode"></i> Scan Another QR Code
                    </a>
                    <a href="equipment_report_status.php" class="btn btn-secondary">
                        Return to Report Status
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>

</html>