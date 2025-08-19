<?php
require_once '../config/database.php';
require_once '../middleware/session_manager.php';

// Initialize session manager
$sessionManager = new SessionManager();

// Handle session extension request
$sessionManager->handleAjaxExtension();
?>
