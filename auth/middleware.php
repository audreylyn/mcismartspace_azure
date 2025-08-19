<?php
require_once __DIR__ . '/../middleware/session_manager.php';
require_once __DIR__ . '/dbh.inc.php';

$sessionManager = new SessionManager();

function checkAccess($allowedRoles)
{
    global $sessionManager;
    if (!$sessionManager->validateSession()) {
        // validateSession now handles the redirect for timeout
        exit();
    }

    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles)) {
        // Redirect to a generic access denied page or the login page
        header("Location: ../index.php?error=denied");
        exit();
    }
}
