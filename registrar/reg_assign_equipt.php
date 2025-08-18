<?php
require '../auth/middleware.php';
checkAccess(['Registrar']);
?>
<?php include "includes/assign_equipt.php"; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Management</title>
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_1.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">

    <style>
        .card-content {
            padding: 1.5rem;
        }
    </style>

</head>

<body>
    <div id="app">
        <nav id="navbar-main" class="navbar is-fixed-top">
            <div class="navbar-brand">
                <a class="navbar-item mobile-aside-button">
                    <span class="icon"><i class="mdi mdi-forwardburger mdi-24px"></i></span>
                </a>
                <div class="navbar-item">
                    <section class="is-title-bar">
                        <div class="flex flex-col md:flex-row items-center justify-between space-y-6 md:space-y-0">
                            <ul>
                                <li>Registrar</li>
                                <li>Assign Equipment</li>
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
                    <div class="navbar-item dropdown has-divider">
                        <a class="navbar-link">

                            <span>Hello, Registrar</span>
                            <span class="icon">
                                <i class="mdi mdi-chevron-down"></i>
                            </span>
                        </a>
                        <div class="navbar-dropdown">
                            <a class="navbar-item" href="../auth/logout.php">
                                <span class="icon"><i class="mdi mdi-logout"></i></span>
                                <span>Log Out</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <aside class="aside is-placed-left is-expanded">
            <div class="aside-tools">
                <div class="logo">
                    <a href="#"><img class="meyclogo" src="../public/assets/logo.webp" alt="logo"></a>
                    <p>MCiSmartSpace</p>
                </div>
            </div>
            <div class="menu is-menu-main">
                <ul class="menu-list">
                    <!-- <li>
                        <a href="../registrar.php">
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                    <path d="M5 4h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1"></path>
                                    <path d="M5 16h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1"></path>
                                    <path d="M15 12h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1"></path>
                                    <path d="M15 4h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1"></path>
                                </svg> </span>
                            <span class="#">Dashboard</span>
                        </a>
                    </li> -->
                </ul>
                <ul class="menu-list">

                    <li>
                        <a href="reg_add_admin.php">
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"></path>
                                    <path d="M16 19h6"></path>
                                    <path d="M19 16v6"></path>
                                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4"></path>
                                </svg></span>
                            <span class="#">Add Admin</span>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown" onclick="toggleIcon(this)">
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                    <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path>
                                    <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"></path>
                                    <path d="M16 5l3 3"></path>
                                </svg></span>
                            <span class="#">Manage Rooms</span>
                            <span class="icon toggle-icon"><i class="mdi mdi-plus"></i></span>
                        </a>
                        <ul>
                            <li>
                                <a href="#">
                                    <span>Add Building</span>
                                </a>
                            </li>
                            <li>
                                <a href="./reg_add_room.php">
                                    <span>Add Rooms</span>
                                </a>
                            </li>
                            <li>
                                <a href="./reg_summary.php">
                                    <span>Facility Management</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a class="dropdown" onclick="toggleIcon(this)">
                            <span class="icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                    <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path>
                                    <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"></path>
                                    <path d="M16 5l3 3"></path>
                                </svg></span>
                            <span class="#">Manage Equipment</span>
                            <span class="icon toggle-icon"><i class="mdi mdi-plus"></i></span>
                        </a>
                        <ul>
                            <li>
                                <a href="./reg_add_equipt.php">
                                    <span>Add Equipment</span>
                                </a>
                            </li>
                            <li class="active">
                                <a href="#">
                                    <span>Assign Equipment</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                </ul>
            </div>
        </aside>

        <div class="all_container">
            <?php if ($success_message): ?>
                <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="table-container">
                <div class="card">
                    <header class="card-header">
                        <div class="new-title-container">
                            <p class="new-title">Equipment List</p>
                        </div>
                    </header>
                    <div class="card-content">
                        <table id="assignTable" class="table is-fullwidth is-striped">
                            <thead>
                                <tr class="titles">
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                    <th>Room Assignment</th>
                                    <th>Building</th>
                                    <th>Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($equipment_list)): ?>
                                    <tr>
                                        <td colspan="6" class="has-text-centered">No equipment found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($equipment_list as $equipment): ?>
                                        <tr>
                                            <td data-label="Name"><?= htmlspecialchars($equipment['name']) ?></td>
                                            <td data-label="Description"><?= htmlspecialchars($equipment['description']) ?></td>
                                            <td data-label="Category"><?= htmlspecialchars($equipment['category']) ?></td>
                                            <td data-label="Room"><?= htmlspecialchars($equipment['room_name']) ?></td>
                                            <td data-label="Building"><?= htmlspecialchars($equipment['building_name']) ?></td>
                                            <td data-label="Quantity"><?= htmlspecialchars($equipment['quantity']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <section class="section main-section">
                <div class="card mt-6">
                    <header class="card-header">
                        <div class="new-title-container">
                            <p class="new-title">Assign Equipment to Room</p>
                        </div>
                    </header>
                    <div class="card-content">
                        <form method="POST">
                            <div class="field">
                                <div class="control" style="width: 100%; margin-bottom: 1rem;">
                                    <label class="label">Select Room:</label>
                                    <select class="input" name="room_id" required style="width: 100%;">
                                        <option value="">Select a room</option>
                                        <?php foreach ($rooms_list as $room): ?>
                                            <option value="<?= htmlspecialchars($room['id']) ?>">
                                                <?= htmlspecialchars($room['room_name']) ?> (<?= htmlspecialchars($room['building_name']) ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="control" style="width: 100%; margin-bottom: 1rem;">
                                    <label class="label">Select Equipment:</label>
                                    <select class="input" name="equipment_id" required style="width: 100%;">
                                        <option value="">Select equipment</option>
                                        <?php foreach ($equipment_dropdown as $equipment): ?>
                                            <option value="<?= htmlspecialchars($equipment['id']) ?>">
                                                <?= htmlspecialchars($equipment['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="control" style="width: 100%; margin-bottom: 1rem;">
                                    <label class="label">Quantity:</label>
                                    <input class="input" type="number" name="quantity" value="1" min="1" required style="width: 100%;">
                                </div>
                            </div>

                            <div class="field">
                                <div class="control">
                                    <button type="submit" name="assign_equipment" class="styled-button">Assign Equipment</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/custom_alert.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#assignTable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search assignments..."
                },
                dom: '<"top"lf>rt<"bottom"ip><"clear">',
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "All"]
                ],
                pageLength: 10,
                ordering: true
            });
        });

        window.onload = function() {
            <?php
            if (isset($_SESSION['success_message'])) {
                echo 'showCustomAlert("' . addslashes($_SESSION['success_message']) . '", "success");';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo 'showCustomAlert("' . addslashes($_SESSION['error_message']) . '", "error");';
                unset($_SESSION['error_message']);
            }
            ?>
        }
    </script>
</body>

</html>