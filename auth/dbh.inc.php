<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone to UTC
date_default_timezone_set('UTC');

// Centralized database connection (singleton)
function db(): mysqli
{
    static $conn = null;

    if ($conn instanceof mysqli) {
        // Ensure the connection is alive; reconnect if needed
        if (@$conn->ping()) {
            return $conn;
        }
    }

    // Allow env overrides but keep sensible defaults
    $host = getenv('DB_HOST') ?: 'mcismartdb.mysql.database.azure.com';
    $user = getenv('DB_USER') ?: 'adminuser@mcismartdb';
    $pass = getenv('DB_PASS') ?: 'SmartDb2025!';
    $name = getenv('DB_NAME') ?: 'mcismartdb';

    $conn = new mysqli($host, $user, $pass, $name);
    if ($conn->connect_error) {
        error_log('Database connection failed: ' . $conn->connect_error);
        http_response_code(500);
        die('Database connection error.');
    }

    // Ensure proper charset
    @$conn->set_charset('utf8mb4');

    // Expose a global for legacy code expecting $conn
    $GLOBALS['conn'] = $conn;
    return $conn;
}

// Initialize connection early for files expecting $conn immediately
db();
