<?php
// Error handling settings
function setupErrorHandling() {
    // For production environment - hide all errors
    if ($_SERVER['SERVER_NAME'] !== 'localhost') {
        error_reporting(0);
        ini_set('display_errors', 0);
    } else {
        // For development environment - show all errors except notices
        error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 1);
    }
}

// Call the function to set up error handling
setupErrorHandling();
