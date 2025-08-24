<?php
// Get the current page name
$currentPage = basename($_SERVER['PHP_SELF']);

// Define the menu structure
$menuItems = [
    [
        'type' => 'single',
        'page' => 'dept-admin.php',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                <path d="M5 4h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1"></path>
                <path d="M5 16h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1"></path>
                <path d="M15 12h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1"></path>
                <path d="M15 4h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1"></path>
            </svg>',
        'title' => 'Dashboard'
    ],
    [
        'type' => 'single',
        'page' => 'dept_room_approval.php',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building2">
                <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                <path d="M10 6h4"></path>
                <path d="M10 10h4"></path>
                <path d="M10 14h4"></path>
                <path d="M10 18h4"></path>
            </svg>',
        'title' => 'Room Approval'
    ],
    [
        'type' => 'single',
        'page' => 'dept_room_activity_logs.php',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-list">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                <path d="M15 2H9a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"></path>
                <path d="M12 11h4"></path>
                <path d="M12 16h4"></path>
                <path d="M8 11h.01"></path>
                <path d="M8 16h.01"></path>
            </svg>',
        'title' => 'Room Activity Logs'
    ],
    [
        'type' => 'dropdown',
        'title' => 'Manage Accounts',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                <path d="M9 7m-4 0a4 4 0 1 0 8 0a4 4 0 1 0 -8 0"></path>
                <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                <path d="M21 21v-2a4 4 0 0 0 -3 -3.85"></path>
            </svg>',
        'children' => [
            [
                'page' => 'manage_teachers.php',
                'title' => 'Manage Teachers'
            ],
            [
                'page' => 'manage_students.php',
                'title' => 'Manage Students'
            ]
        ]
    ],
    [
        'type' => 'single',
        'page' => 'dept_equipment_report.php',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 0 0-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 0 0-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 0 0-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 0 0-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 0 0 1.066-2.573c-.94-1.543.826-3.31 2.37-2.37c.996.608 2.296.07 2.572-1.065z"></path>
                <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"></path>
            </svg>',
        'title' => 'Equipment Report'
    ],
    [
        'type' => 'single',
        'page' => 'qr_generator.php',
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code">
                <rect width="5" height="5" x="3" y="3" rx="1"></rect>
                <rect width="5" height="5" x="16" y="3" rx="1"></rect>
                <rect width="5" height="5" x="3" y="16" rx="1"></rect>
                <path d="M21 16h-3a2 2 0 0 0-2 2v3"></path>
                <path d="M21 21v.01"></path>
                <path d="M12 7v3a2 2 0 0 1-2 2H7"></path>
                <path d="M3 12h.01"></path>
                <path d="M12 3h.01"></path>
                <path d="M12 16v.01"></path>
                <path d="M16 12h1"></path>
                <path d="M21 12v.01"></path>
                <path d="M12 21v-1"></path>
            </svg>',
        'title' => 'QR Generator'
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
            <?php foreach ($menuItems as $menuItem): ?>
                <?php if ($menuItem['type'] === 'single'): ?>
                    <li <?php echo isMenuItemActive($menuItem, $currentPage) ? 'class="active"' : ''; ?>>
                        <a href="<?php echo $menuItem['page']; ?>">
                            <span class="icon"><?php echo $menuItem['icon']; ?></span>
                            <span class="#"><?php echo $menuItem['title']; ?></span>
                        </a>
                    </li>
                <?php elseif ($menuItem['type'] === 'dropdown'): ?>
                    <?php $isActive = isMenuItemActive($menuItem, $currentPage); ?>
                    <li <?php echo $isActive ? 'class="active"' : ''; ?>>
                        <a class="dropdown <?php echo $isActive ? 'active' : ''; ?>" onclick="toggleIcon(this)">
                            <span class="icon"><?php echo $menuItem['icon']; ?></span>
                            <span class="#"><?php echo $menuItem['title']; ?></span>
                            <span class="icon toggle-icon">
                                <i class="mdi <?php echo $isActive ? 'mdi-minus' : 'mdi-plus'; ?>"></i>
                            </span>
                        </a>
                        <ul style="<?php echo $isActive ? 'display: block;' : 'display: none;'; ?>">
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

<script>
// Function to toggle dropdown menus
function toggleIcon(element) {
    // Toggle active class on the clicked dropdown
    element.classList.toggle('active');
    
    // Toggle the plus/minus icon
    const icon = element.querySelector('.toggle-icon i');
    icon.classList.toggle('mdi-plus');
    icon.classList.toggle('mdi-minus');
    
    // Toggle the submenu visibility
    const submenu = element.nextElementSibling;
    if (submenu.style.display === 'block') {
        submenu.style.display = 'none';
    } else {
        // Close all other dropdowns first
        const allDropdowns = document.querySelectorAll('.menu-list .dropdown');
        allDropdowns.forEach(dropdown => {
            if (dropdown !== element) {
                dropdown.classList.remove('active');
                const dropdownIcon = dropdown.querySelector('.toggle-icon i');
                dropdownIcon.classList.remove('mdi-minus');
                dropdownIcon.classList.add('mdi-plus');
                const dropdownSubmenu = dropdown.nextElementSibling;
                dropdownSubmenu.style.display = 'none';
            }
        });
        
        submenu.style.display = 'block';
    }
}

// Add click event listener to the document
document.addEventListener('DOMContentLoaded', function() {
    // Initial dropdown state is set via PHP, but we need to ensure the toggle works
    // regardless of which page we're on
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });
});
</script>
