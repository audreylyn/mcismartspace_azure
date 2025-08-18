<?php

//include the database connection
require_once __DIR__ . '/dbh.inc.php';

function checkAccess($allowedRoles)
{
    if (!isset($_SESSION['role'])) {
        header("Location: ../index.php");
        exit();
    }

    if (!in_array($_SESSION['role'], $allowedRoles)) {
        echo "You can't access this page.";
        exit();
    }
}
