<?php
require_once __DIR__ . '/../middleware/session_manager.php';

$sessionManager = new SessionManager();
$sessionManager->handleAjaxExtension();
