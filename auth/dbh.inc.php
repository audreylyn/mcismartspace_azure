<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('UTC');

function db(): mysqli
{
    static $conn = null;

    if ($conn instanceof mysqli && @$conn->ping()) {
        return $conn;
    }

    // Azure MySQL connection settings
    $host = getenv('DB_HOST') ?: 'mcismartdb.mysql.database.azure.com';
    $user = getenv('DB_USER') ?: 'adminuser'; 
    $pass = getenv('DB_PASS') ?: 'SmartDb2025!';
    $name = getenv('DB_NAME') ?: 'mcismartdb';
    $port = 3306;

    // Path to SSL certificate
    $ssl_ca = __DIR__ . '/../DigiCertGlobalRootCA.crt.pem';

    $conn = mysqli_init();
    if (!$conn) {
        die('mysqli_init failed');
    }

    // ✅ Attach SSL CA cert
    mysqli_ssl_set($conn, NULL, NULL, $ssl_ca, NULL, NULL);

    // ✅ Use SSL mode
    mysqli_real_connect($conn, $host, $user, $pass, $name, $port, NULL, MYSQLI_CLIENT_SSL);

    if (mysqli_connect_errno()) {
        die('Database connection failed: ' . mysqli_connect_error());
    }

    $conn->set_charset('utf8mb4');
    $GLOBALS['conn'] = $conn;
    return $conn;
}

db();
