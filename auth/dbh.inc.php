<?php
function db() {
    // Database settings
    $host = "mcismartdb.mysql.database.azure.com";
    $database = "mcismartdb";
    
    // Detect environment
    if (strpos($_SERVER['HTTP_HOST'], 'azurewebsites.net') !== false) {
        // Running on Azure App Service
        $username = "adminuser@mcismartdb";   // Must include @servername
        $password = "SmartDb2025!";
    } else {
        // Running locally (e.g., XAMPP + MySQL Workbench)
        $username = "adminuser";              // Just the username
        $password = "";
    }

    // Create connection
    $conn = new mysqli($host, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
$conn = db();
?>
