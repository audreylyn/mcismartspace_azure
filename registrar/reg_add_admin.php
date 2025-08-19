<?php
// Include error handling configuration
require_once __DIR__ . '/../middleware/error_handler.php';

// Authentication checks
require '../auth/middleware.php';
checkAccess(['Registrar']);

// Include page-specific logic
include "includes/add_admin.php";
include "includes/message.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Admin Registration</title>
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
                                <li>Add Admin</li>
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
                    <li class="active">
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
                            <li>
                                <a href="./reg_assign_equipt.php">
                                    <span>Assign Equipment</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                </ul>

            </div>
        </aside>


        <div class="all_container">
            <div class="table-container">
                <div class="card">
                    <header class="card-header">
                        <div class="new-title-container dept-list">
                            <p class="new-title">Department Admin List</p>
                            <a href="includes/export_admin.php" class="batch" style="display: inline-flex; align-items: center; padding: 8px 16px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                                </svg>
                                Export
                            </a>
                        </div>
                    </header>
                    <div class="card-content">
                        <table id="adminTable" class="table is-fullwidth is-striped">
                            <thead>
                                <tr class="titles">
                                    <th>AdminID</th>
                                    <th>FirstName</th>
                                    <th>LastName</th>
                                    <th>Department</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="6" class="has-text-centered">No admins found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td data-label="AdminID"><?= htmlspecialchars($row['AdminID']) ?></td>
                                            <td data-label="FirstName"><?= htmlspecialchars($row['FirstName']) ?></td>
                                            <td data-label="LastName"><?= htmlspecialchars($row['LastName']) ?></td>
                                            <td data-label="Department"><?= htmlspecialchars($row['Department']) ?></td>
                                            <td data-label="Email"><?= htmlspecialchars($row['Email']) ?></td>
                                            <td class="action-buttons">
                                                <button class="button is-danger styled-button is-reset"
                                                    onclick="if(confirm('Are you sure you want to delete this admin?')) window.location.href='?id=<?= htmlspecialchars($row['AdminID']) ?>'">
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
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <section class="section main-section">
                <div class="card">
                    <header class="card-header">
                        <div class="new-title-container" style="width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 5px 0 5px 20px;">
                            <p class="new-title">Add Admin</p>
                            <form action="includes/import_admin.php" class="form-data" method="post" enctype="multipart/form-data" style="display: flex;">
                                <button class="excel" style="border-radius: 0.3em 0 0 0.3em; display: flex; justify-content: center; width: 50px; padding: 0.5rem;">
                                    <svg
                                        fill="#fff"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="20"
                                        height="20"
                                        viewBox="0 0 50 50"
                                        style="margin: 0;">
                                        <path
                                            d="M28.8125 .03125L.8125 5.34375C.339844 
                                        5.433594 0 5.863281 0 6.34375L0 43.65625C0 
                                        44.136719 .339844 44.566406 .8125 44.65625L28.8125 
                                        49.96875C28.875 49.980469 28.9375 50 29 50C29.230469 
                                        50 29.445313 49.929688 29.625 49.78125C29.855469 49.589844 
                                        30 49.296875 30 49L30 1C30 .703125 29.855469 .410156 29.625 
                                        .21875C29.394531 .0273438 29.105469 -.0234375 28.8125 .03125ZM32 
                                        6L32 13L34 13L34 15L32 15L32 20L34 20L34 22L32 22L32 27L34 27L34 
                                        29L32 29L32 35L34 35L34 37L32 37L32 44L47 44C48.101563 44 49 
                                        43.101563 49 42L49 8C49 6.898438 48.101563 6 47 6ZM36 13L44 
                                        13L44 15L36 15ZM6.6875 15.6875L11.8125 15.6875L14.5 21.28125C14.710938 
                                        21.722656 14.898438 22.265625 15.0625 22.875L15.09375 22.875C15.199219 
                                        22.511719 15.402344 21.941406 15.6875 21.21875L18.65625 15.6875L23.34375 
                                        15.6875L17.75 24.9375L23.5 34.375L18.53125 34.375L15.28125 
                                        28.28125C15.160156 28.054688 15.035156 27.636719 14.90625 
                                        27.03125L14.875 27.03125C14.8125 27.316406 14.664063 27.761719 
                                        14.4375 28.34375L11.1875 34.375L6.1875 34.375L12.15625 25.03125ZM36 
                                        20L44 20L44 22L36 22ZM36 27L44 27L44 29L36 29ZM36 35L44 35L44 37L36 37Z"></path>
                                    </svg>
                                    <input type="file" name="file" class="file" accept=".csv" required />
                                </button>
                                <button class="container-btn-file" type="submit" name="importSubmit" style="border-radius: 0 0.3em 0.3em 0;">
                                    <svg
                                        fill="#fff"
                                        xmlns="http://www.w3.org/2000/svg"
                                        width="20"
                                        height="20"
                                        viewBox="0 0 24 24">
                                        <path
                                            d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"></path>
                                    </svg>
                                    Import
                                </button>
                            </form>
                        </div>
                    </header>
                    <div class="card-content">
                        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                            <div class="field is-inline">
                                <div class="control">
                                    <label class="label">First Name:</label>
                                    <input class="input" type="text" name="first_name" pattern="[A-Za-z\s]+" required>
                                </div>
                                <div class="control">
                                    <label class="label">Last Name:</label>
                                    <input class="input" type="text" name="last_name" pattern="[A-Za-z\s]+" required>
                                </div>
                            </div>

                            <div class="field is-inline">
                                <div class="control">
                                    <label class="label">Department:</label>
                                    <div class="select">
                                        <select name="department" required>
                                            <option value="">Select Department</option>
                                            <?php
                                            $departments = ['Accountancy', 'Business Administration', 'Hospitality Management', 'Education and Arts', 'Criminal Justice'];
                                            foreach ($departments as $dept) {
                                                echo '<option value="' . htmlspecialchars($dept) . '">' . htmlspecialchars($dept) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="field is-inline">
                                <div class="control">
                                    <label class="label">Email:</label>
                                    <input class="input" type="email" name="email" required>
                                </div>
                            </div>

                            <div class="field is-inline">
                                <div class="control">
                                    <label class="label">Password:</label>
                                    <input class="input" type="password" name="password" minlength="8" required>
                                </div>
                            </div>

                            <div class="field is-inline">
                                <div class="control">
                                    <button type="submit" class="styled-button">Register</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>

        <!-- Modal for displaying messages -->
        <div id="messageModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeModal">&times;</span>
                <h2 id="modalTitle"></h2>
                <p id="modalMessage"></p>
                <button class="modal-button" onclick="closeModal()">OK</button>
            </div>
        </div>


        <?php
        // Close the database connection
        $conn->close();
        ?>
    </div>
    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/custom_alert.js"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#adminTable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search admins..."
                },
                dom: '<"top"lf>rt<"bottom"ip><"clear">',
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "All"]
                ],
                pageLength: 10,
                ordering: true,
                columnDefs: [{
                    targets: -1,
                    orderable: false
                }]
            });
        });

        // Show alerts on page load if messages exist
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

    <!-- Enhanced Modal JavaScript -->
    <script>
        // Function to show the modal with the message
        function showModal(title, message, type = 'success') {
            const modal = document.getElementById('messageModal');
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMessage').textContent = message;

            // Add the type class for specific styling
            modal.classList.add(type);

            // Display the modal and add show class for animation
            modal.style.display = "block";

            // Trigger reflow for animation to work
            void modal.offsetWidth;

            modal.classList.add('show');
        }

        // Close the modal when the user clicks on <span> (x)
        document.getElementById('closeModal').onclick = function() {
            closeModal();
        }

        // Close the modal when the user clicks anywhere outside of the modal
        window.onclick = function(event) {
            if (event.target == document.getElementById('messageModal')) {
                closeModal();
            }
        }

        // Function to close modal with animation
        function closeModal() {
            const modal = document.getElementById('messageModal');
            modal.classList.remove('show');

            // Wait for animation to complete before hiding
            setTimeout(() => {
                modal.style.display = "none";
                modal.classList.remove('success', 'error');
            }, 300);
        }

        // Show the modal if there are messages
        <?php if ($success_message): ?>
            showModal("Success", "<?php echo addslashes($success_message); ?>", "success");
        <?php endif; ?>

        <?php if ($error_message): ?>
            showModal("Error", "<?php echo addslashes($error_message); ?>", "error");
        <?php endif; ?>
    </script>
</body>

</html>