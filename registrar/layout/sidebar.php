<?php
// This file contains the sidebar HTML for registrar pages
$currentPage = basename($_SERVER['PHP_SELF']);

// Define menu structure
$menuItems = [
    [
        'type' => 'single',
        'page' => 'registrar.php',
        'title' => 'Dashboard',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                    <path d="M5 4h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1"></path>
                    <path d="M5 16h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1"></path>
                    <path d="M15 12h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1"></path>
                    <path d="M15 4h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1"></path>
                </svg>'
    ],
    [
        'type' => 'single',
        'page' => 'reg_add_admin.php',
        'title' => 'Add Admin',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path>
                    <path d="M16 19h6"></path>
                    <path d="M19 16v6"></path>
                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4"></path>
                </svg>'
    ],
    [
        'type' => 'dropdown',
        'title' => 'Manage Rooms',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                    <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path>
                    <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"></path>
                    <path d="M16 5l3 3"></path>
                </svg>',
        'children' => [
            [
                'page' => 'reg_add_blg.php',
                'title' => 'Add Building'
            ],
            [
                'page' => 'reg_summary.php',
                'title' => 'Facility Management'
            ]
        ]
    ],
    [
        'type' => 'dropdown',
        'title' => 'Manage Equipment',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                    <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path>
                    <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"></path>
                    <path d="M16 5l3 3"></path>
                </svg>',
        'children' => [
            [
                'page' => 'reg_add_equipt.php',
                'title' => 'Add Equipment'
            ],
            [
                'page' => 'reg_assign_equipt.php',
                'title' => 'Assign Equipment'
            ],
        ]
    ]
];

// Function to check if a dropdown menu should be open
function isDropdownActive($menuItem, $currentPage) {
    if ($menuItem['type'] !== 'dropdown') return false;
    
    foreach ($menuItem['children'] as $child) {
        if ($child['page'] === $currentPage) {
            return true;
        }
    }
    return false;
}

// Function to check if a menu item is active
function isMenuItemActive($menuItem, $currentPage) {
    if ($menuItem['type'] === 'single' && $menuItem['page'] === $currentPage) {
        return true;
    } elseif ($menuItem['type'] === 'dropdown') {
        foreach ($menuItem['children'] as $child) {
            if ($child['page'] === $currentPage) {
                return true;
            }
        }
    }
    return false;
}
?>
<aside class="aside is-placed-left is-expanded">
    <div class="aside-tools">
        <div class="logo">
            <a href="#"><img class="meyclogo" src="../public/assets/logo.webp" alt="logo"></a>
            <p>MCiSmartSpace</p>
        </div>
    </div>
    <div class="menu is-menu-main">
        <ul class="menu-list">
            <?php foreach ($menuItems as $index => $menuItem): ?>
                <?php if ($index === 1): ?>
                    </ul><ul class="menu-list">
                <?php endif; ?>
                
                <?php if ($menuItem['type'] === 'single'): ?>
                    <li <?php echo isMenuItemActive($menuItem, $currentPage) ? 'class="active"' : ''; ?>>
                        <a href="<?php echo $menuItem['page']; ?>">
                            <span class="icon"><?php echo $menuItem['icon']; ?></span>
                            <span class="#"><?php echo $menuItem['title']; ?></span>
                        </a>
                    </li>
                <?php elseif ($menuItem['type'] === 'dropdown'): ?>
                    <?php $isActive = isMenuItemActive($menuItem, $currentPage); ?>
                    <?php $isOpen = isDropdownActive($menuItem, $currentPage); ?>
                    <li <?php echo $isActive ? 'class="active"' : ''; ?>>
                        <a class="dropdown <?php echo $isOpen ? 'active' : ''; ?>" onclick="toggleIcon(this)">
                            <span class="icon"><?php echo $menuItem['icon']; ?></span>
                            <span class="#"><?php echo $menuItem['title']; ?></span>
                            <span class="icon toggle-icon">
                                <i class="mdi <?php echo $isOpen ? 'mdi-minus' : 'mdi-plus'; ?>"></i>
                            </span>
                        </a>
                        <ul style="<?php echo $isOpen ? 'display: block;' : ''; ?>">
                            <?php foreach ($menuItem['children'] as $child): ?>
                                <?php $isChildActive = ($child['page'] === $currentPage); ?>
                                <li <?php echo $isChildActive ? 'class="active"' : ''; ?>>
                                    <a href="<?php echo $child['page']; ?>" <?php echo $isChildActive ? 'class="active"' : ''; ?>>
                                        <span><?php echo $child['title']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
</aside>
