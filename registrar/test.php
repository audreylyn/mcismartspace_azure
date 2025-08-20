<?php
require '../auth/middleware.php';
checkAccess(['Registrar']);
?>

<?php include "includes/summary.php"; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facility Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/soft-ui-dashboard-tailwind.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_1.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">

    <style>
        .dataTables_wrapper {
            padding: 15px;
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
                                <li>Facility Summary</li>
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
                            <a class="navbar-item" href="../includes/logout.php">
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
                    <p>MC RMIS</p>
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
                                <a href="./reg_add_blg.php">
                                    <span>Add Building</span>
                                </a>
                            </li>


                            <li class="active">
                                <a href="#">
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
                            <li>
                                <a href="./reg_assign_equipt.php">
                                    <span>Assign Equipment</span>
                                </a>
                            </li>
                            <li>
                                <a href="./reg_audit_equipt.php">
                                    <span>Equipment Audit</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>

            </div>
        </aside>
        <div class="flex flex-wrap -mx-3">
            <div class="flex-none w-full max-w-full px-3">
                <div>
                    <div>
                        <div class="all_container">
                            <div class="table-container m-10">
                                <div class="card">
                                    <header class="card-header">
                                        <div class="new-title-container">
                                            <p class="new-title">
                                                Facility Management
                                            </p>
                                        </div>
                                    </header>
                                    <table id="facilityTable" class="table is-fullwidth is-striped">
                                        <thead>
                                            <tr>
                                                <th>Building Name</th>
                                                <th>Department</th>
                                                <th>Number of Floors</th>
                                                <th>Room Name</th>
                                                <th>Room Type</th>
                                                <th>Capacity</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            while ($row = $result->fetch_assoc()):
                                                // Sanitize all data before output
                                                $building_id = htmlspecialchars($row['building_id'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $building_name = htmlspecialchars($row['building_name'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $department = htmlspecialchars($row['department'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $floors = htmlspecialchars($row['number_of_floors'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $room_id = htmlspecialchars($row['room_id'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $room_name = htmlspecialchars($row['room_name'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $room_type = htmlspecialchars($row['room_type'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $capacity = htmlspecialchars($row['capacity'] ?? '', ENT_QUOTES, 'UTF-8');
                                            ?>
                                                <tr>
                                                    <td data-label="Building Name"><?php echo $building_name; ?></td>
                                                    <td data-label="Department"><?php echo $department; ?></td>
                                                    <td data-label="Number of Floors"><?php echo $floors; ?></td>
                                                    <td data-label="Room Name"><?php echo $room_name ?: 'N/A'; ?></td>
                                                    <td data-label="Room Type"><?php echo $room_type ?: 'N/A'; ?></td>
                                                    <td data-label="Capacity"><?php echo $capacity ?: 'N/A'; ?></td>
                                                    <td data-label="Actions">
                                                        <?php if ($room_id): ?>
                                                            <div style="display: flex; flex-wrap: nowrap; gap: 4px;">
                                                                <button class="button is-info styled-button" onclick='openEditModal(<?php echo json_encode([
                                                                                                                                        "room_id" => $row['room_id'],
                                                                                                                                        "room_name" => $row['room_name'],
                                                                                                                                        "room_type" => $row['room_type'],
                                                                                                                                        "capacity" => $row['capacity'],
                                                                                                                                        "building_id" => $row['building_id']
                                                                                                                                    ]); ?>)'>
                                                                    <span class="icon">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                                                            <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"></path>
                                                                            <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"></path>
                                                                            <path d="M16 5l3 3"></path>
                                                                        </svg>
                                                                    </span>
                                                                </button>
                                                                <button class="button is-danger styled-button is-reset"
                                                                    onclick="if(confirm('Are you sure you want to delete this room?')) window.location.href='?delete_room=<?php echo urlencode($room_id); ?>'">
                                                                    <span class="icon">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                                                            <path d="M4 7l16 0"></path>
                                                                            <path d="M10 11l0 6"></path>
                                                                            <path d="M14 11l0 6"></path>
                                                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"></path>
                                                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"></path>
                                                                        </svg>
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div id="editModal" class="modal">
            <div class="modal-background"></div>
            <div class="modal-card">
                <header class="modal-card-head">
                    <div class="new-title-container">
                        <p class="new-title">
                            Edit Room
                        </p>
                    </div>
                    <button class="delete" aria-label="close" onclick="closeModal()"></button>
                </header>
                <section class="modal-card-body">
                    <form id="editForm" method="POST">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="room_id" id="edit_room_id">

                        <div class="field">
                            <label class="label">Room Name:</label>
                            <div class="control">
                                <input class="input" type="text" name="room_name" id="edit_room_name" required maxlength="100">
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Room Type:</label>
                            <div class="control">
                                <div class="select">
                                    <select name="room_type" id="edit_room_type" required>
                                        <option value="">Select Room Type</option>
                                        <?php
                                        $rooms = ['Classroom', 'Gymnasium', 'Computer Lab'];
                                        foreach ($rooms as $room) {
                                            echo '<option value="' . htmlspecialchars($room) . '">' . htmlspecialchars($room) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Capacity:</label>
                            <div class="control">
                                <input class="input" type="number" name="capacity" id="edit_capacity" required min="1" max="1000">
                            </div>
                        </div>

                        <div class="field">
                            <label class="label">Building:</label>
                            <div class="select">
                                <select name="building_id" id="edit_building_id" required>
                                    <?php
                                    $building_sql = "SELECT id, building_name FROM buildings ORDER BY building_name ASC";
                                    $building_result = $conn->query($building_sql);
                                    while ($building = $building_result->fetch_assoc()) {
                                        echo "<option value=\"" . htmlspecialchars($building['id'], ENT_QUOTES, 'UTF-8') .
                                            "\">" . htmlspecialchars($building['building_name'], ENT_QUOTES, 'UTF-8') . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="field is-grouped">
                            <div class="control">
                                <button type="submit" class="button is-success styled-button">Save Changes</button>
                            </div>
                            <div class="control">
                                <button type="button" class="button styled-button is-reset" onclick="closeModal()">Cancel</button>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </div>

        <?php
        $stmt->close();
        $conn->close();
        ?>

    </div>
    <script type="text/javascript" src="../public/js/main.min.js"></script>
    <script type="text/javascript" src="../public/js/custom_alert.js"></script>
    <script type="text/javascript" src="../public/js/edit_modal.js"></script>

    <script>
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