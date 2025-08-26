<?php
session_start();

// Check if user is logged in and has a valid role
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Student', 'Teacher'])) {
    // Redirect to login page if not authenticated or role is not allowed
    header('Location: /index.php?error=unauthorized_access');
    exit();
}

// The destination page for reporting equipment issues
$reportPage = '/users/report-equipment-issue.php';

// Get query parameters from the current URL
$queryParams = $_SERVER['QUERY_STRING'];

// Construct the final URL, ensuring query parameters are passed along
if (!empty($queryParams)) {
    $redirectUrl = $reportPage . '?' . $queryParams;
} else {
    $redirectUrl = $reportPage;
}

// Perform the redirect
header('Location: ' . $redirectUrl);
exit();
?>
