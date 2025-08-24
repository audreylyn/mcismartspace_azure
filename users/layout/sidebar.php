<?php
// Get current page file name
$currentPage = basename($_SERVER['PHP_SELF']);

// Define sidebar menu items
$menuItems = [
    [
        'page' => 'users_browse_room.php',
        'title' => 'Browse Room',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2 flex-shrink-0">
                    <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                    <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                    <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                    <path d="M10 6h4"></path>
                    <path d="M10 10h4"></path>
                    <path d="M10 14h4"></path>
                    <path d="M10 18h4"></path>
                </svg>'
    ],
    [
        'page' => 'users_reservation_history.php',
        'title' => 'Reservation History',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8v4l3 3"></path>
                    <circle cx="12" cy="12" r="10"></circle>
                </svg>'
    ],
    [
        'page' => 'equipment_report_status.php',
        'title' => 'Equipment Reports',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>'
    ]
];
?>

<div class="col-md-3 left_col menu_fixed">
    <div class="left_col scroll-view">
        <div class="navbar nav_title" style="border: 0;">
            <div class="logo-container">
                <a href="#" class="site-branding">
                    <img class="meyclogo" src="../public/assets/logo.webp" alt="meyclogo">
                    <span class="title-text">MCiSmartSpace</span>
                </a>
            </div>
        </div>

        <div class="clearfix"></div>

        <br />

        <!-- sidebar menu -->
        <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
            <div class="menu_section">
                <ul class="nav side-menu" class="navbar nav_title" style="border: 0;">
                    <?php foreach ($menuItems as $item): ?>
                        <li class="<?php echo ($currentPage === $item['page']) ? 'active' : ''; ?>">
                            <a href="<?php echo $item['page']; ?>">
                                <div class="icon">
                                    <?php echo $item['icon']; ?>
                                </div>
                                <div class="menu-text">
                                    <span><?php echo $item['title']; ?></span>
                                    <span class="fa fa-chevron-down" style="opacity: 0;"></span>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <!-- /sidebar menu -->
    </div>
</div>
