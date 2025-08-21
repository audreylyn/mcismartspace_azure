<?php
// Include required files and check access
require '../auth/middleware.php';
checkAccess(['Registrar']);

// Set page variables
$pageTitle = 'Registrar Dashboard';
$pageSubTitle = 'Facility Summary';
$includeChartJs = true;

// Load dashboard data
include 'includes/dashboard_data.php';
$dashboardData = loadDashboardData();

// Extract data for use in templates
extract($dashboardData);

// Include layout files
include 'layout/header.php';
include 'layout/topnav.php';
include 'layout/sidebar.php';

// Load dashboard specific styles
include 'layout/dashboard.css.php';

// Include dashboard content
include 'layout/dashboard-content.php';

// Add the dashboard charts JavaScript
include 'layout/dashboard-charts.js.php';

// Include footer
include 'layout/footer.php';
?>