<?php
function db() {
    $host = "mcismartdb.mysql.database.azure.com";
    $username = "adminuser@mcismartdb";  // must include @servername
    $password = "SmartDb2025!";
    $database = "mcismartdb";

    $conn = new mysqli($host, $username, $password, $database);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>
