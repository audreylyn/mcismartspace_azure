<?php
// This file contains the top navigation HTML for registrar pages

// Get user data - in a real application, this would come from your session
// You can modify this to pull actual user data from your session
$userData = [
    'name' => $_SESSION['user_name'] ?? 'Registrar',
    'role' => $_SESSION['user_role'] ?? 'Registrar',
    'profileImage' => $_SESSION['profile_image'] ?? null
];

// Get breadcrumb information
$moduleName = 'Registrar';
$currentPage = basename($_SERVER['PHP_SELF']);

// Map page filenames to readable titles for breadcrumbs
$pageTitles = [
    'registrar.php' => 'Dashboard',
    'reg_add_admin.php' => 'Add Admin',
    'reg_add_blg.php' => 'Add Building',
    'reg_summary.php' => 'Facility Management',
    'reg_add_equipt.php' => 'Add Equipment',
    'reg_assign_equipt.php' => 'Assign Equipment'
];

// Determine the current page title
$currentPageTitle = $pageTitles[$currentPage] ?? $pageSubTitle ?? 'Dashboard';

// Define dropdown menu items for user profile
$userMenuItems = [
    [
        'title' => 'Profile',
        'url' => '../users/edit_profile.php',
        'icon' => 'account'
    ],
    [
        'title' => 'Settings',
        'url' => '../users/settings.php',
        'icon' => 'cog-outline'
    ],
    [
        'title' => 'Log Out',
        'url' => '../auth/logout.php',
        'icon' => 'logout'
    ]
];

// Notifications - this would be dynamic in a real application
$notifications = [];
// Example of how you might populate notifications
// $notifications = fetchUserNotifications($userId);
?>
<nav id="navbar-main" class="navbar is-fixed-top">
    <div class="navbar-brand">
        <a class="navbar-item mobile-aside-button">
            <span class="icon"><i class="mdi mdi-forwardburger mdi-24px"></i></span>
        </a>
        <div class="navbar-item">
            <section class="is-title-bar">
                <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
                    <ul>
                        <li><?php echo htmlspecialchars($moduleName); ?></li>
                        <li><?php echo htmlspecialchars($currentPageTitle); ?></li>
                    </ul>
                </div>
            </section>
        </div>
    </div>
    <div class="navbar-brand is-right">
        <a class="navbar-item --jb-navbar-menu-toggle" data-target="navbar-menu">
            <span class="icon"><i class="mdi mdi-dots-vertical mdi-24px"></i></span>
        </a>
    </div>
    <div class="navbar-menu" id="navbar-menu">
        <div class="navbar-end">
            <?php if (!empty($notifications)): ?>
            <div class="navbar-item dropdown has-divider has-user-avatar">
                <a class="navbar-link">
                    <span class="icon"><i class="mdi mdi-bell"></i></span>
                    <?php if (count($notifications) > 0): ?>
                    <span class="notification-badge"><?php echo count($notifications); ?></span>
                    <?php endif; ?>
                </a>
                <div class="navbar-dropdown">
                    <div class="navbar-dropdown-header">
                        <span>Notifications</span>
                    </div>
                    <?php if (count($notifications) > 0): ?>
                        <?php foreach ($notifications as $notification): ?>
                        <a class="navbar-item">
                            <div class="navbar-item-text">
                                <p><?php echo htmlspecialchars($notification['message']); ?></p>
                                <small><?php echo htmlspecialchars($notification['time']); ?></small>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="navbar-item">
                            <p class="has-text-centered">No new notifications</p>
                        </div>
                    <?php endif; ?>
                    <hr class="navbar-divider">
                    <a class="navbar-item">
                        <span>View all notifications</span>
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="navbar-item dropdown has-divider">
                <a class="navbar-link">
                    <?php if (!empty($userData['profileImage'])): ?>
                    <div class="user-avatar">
                        <img src="<?php echo htmlspecialchars($userData['profileImage']); ?>" alt="User profile">
                    </div>
                    <?php endif; ?>
                    <span>Hello, <?php echo htmlspecialchars($userData['name']); ?></span>
                    <span class="icon">
                        <i class="mdi mdi-chevron-down"></i>
                    </span>
                </a>
                <div class="navbar-dropdown">
                    <?php foreach ($userMenuItems as $item): ?>
                    <a class="navbar-item" href="<?php echo $item['url']; ?>">
                        <span class="icon"><i class="mdi mdi-<?php echo $item['icon']; ?>"></i></span>
                        <span><?php echo htmlspecialchars($item['title']); ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</nav>
