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
    <link rel="stylesheet" href="../public/css/admin_styles/add_admin.css">
    <link rel="stylesheet" href="../public/css/admin_styles/ajax.css">

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" defer></script>
    <script src="../public/js/admin_scripts/main.min.js" defer></script>
    <script src="../public/js/admin_scripts/custom_alert.js" defer></script>
    <?php include "layout/admin-scripts.js.php"; ?>
    <script src="../public/js/admin_scripts/add_admin.js" defer></script>
</head>

<body>
    <div id="app">
        
        <?php 
        include 'layout/topnav.php'; 
        include 'layout/sidebar.php'; 
        ?>

        <div class="all_container">
            <div class="table-container">
                <div class="card">
                    <header class="card-header">
                        <div class="new-title-container dept-list">
                            <p class="new-title">Department Admin List</p>
                            <button id="exportButton" class="batch" style="display: inline-flex; align-items: center; padding: 8px 16px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                                </svg>
                                Export
                            </button>
                        </div>
                    </header>
                    <div class="card-content">
                        <table id="adminTable" class="adminTable table is-fullwidth is-striped">
                            <thead>
                                <tr class="titles">
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
                                        <td colspan="5" class="has-text-centered">No admins found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td data-label="FirstName"><?= htmlspecialchars($row['FirstName']) ?></td>
                                            <td data-label="LastName"><?= htmlspecialchars($row['LastName']) ?></td>
                                            <td data-label="Department"><?= htmlspecialchars($row['Department']) ?></td>
                                            <td data-label="Email"><?= htmlspecialchars($row['Email']) ?></td>
                                            <td class="action-buttons">
                                                <button class="button is-info styled-button" 
                                                    onclick="openEditModal('<?= htmlspecialchars($row['AdminID']) ?>', 
                                                    '<?= htmlspecialchars($row['FirstName']) ?>', 
                                                    '<?= htmlspecialchars($row['LastName']) ?>', 
                                                    '<?= htmlspecialchars($row['Department']) ?>', 
                                                    '<?= htmlspecialchars($row['Email']) ?>')">
                                                    <span class="icon">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" width="24" height="24" stroke-width="2">
                                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                        </svg>
                                                    </span>
                                                </button>
                                                <button class="button is-danger styled-button is-reset"
                                                    onclick="deleteAdmin(<?= htmlspecialchars($row['AdminID']) ?>)">
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
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <form id="importForm" class="form-data" method="post" enctype="multipart/form-data" style="display: flex;">
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
                                    <button id="importButton" class="container-btn-file" type="button" style="border-radius: 0 0.3em 0.3em 0;">
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
                        </div>
                    </header>
                    <div class="card-content">
                        <form id="adminForm" action="includes/add_admin_ajax.php" method="POST">
                            <input type="hidden" name="action" value="add">
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
                <div id="modalMessage"></div>
                <button class="modal-button" onclick="closeModal()">OK</button>
            </div>
        </div>

        <!-- Modal for editing admin -->
        <div id="editModal" class="modal">
            <div class="modal-content" style="width: 500px; max-width: 90%;">
                <span class="close" id="closeEditModal">&times;</span>
                <h2>Edit Administrator</h2>
                <form id="editAdminForm">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="admin_id" id="edit_admin_id">
                    
                    <div class="field">
                        <label class="label">First Name</label>
                        <div class="control">
                            <input class="input" type="text" name="first_name" id="edit_first_name" pattern="[A-Za-z\s]+" required>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Last Name</label>
                        <div class="control">
                            <input class="input" type="text" name="last_name" id="edit_last_name" pattern="[A-Za-z\s]+" required>
                        </div>
                    </div>
                    
                    <div class="field">
                        <label class="label">Department</label>
                        <div class="control">
                            <div class="select" style="width: 100%;">
                                <select name="department" id="edit_department" required style="width: 100%;">
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
                    
                    <div class="field">
                        <label class="label">Email</label>
                        <div class="control">
                            <input class="input" type="email" name="email" id="edit_email" required>
                        </div>
                    </div>
                    
                    <div class="field" style="margin-top: 20px; display: flex; justify-content: flex-end;">
                        <div class="control">
                            <button type="button" class="modal-button" style="background-color: #ccc; margin-right: 10px;" onclick="closeEditModal()">Cancel</button>
                            <button type="button" id="saveEditButton" class="modal-button" style="background-color: #ffc107;">Save Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php
        // Close the database connection
        $conn->close();
        ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const message = urlParams.get('msg');

            if (status && message) {
                const title = status === 'success' ? 'Success' : 'Error';
                showModal(title, decodeURIComponent(message), status);
            }
        });
    </script>
    
    <!-- AJAX Loader -->
    <div class="ajax-loader" id="ajaxLoader">
        <div class="ajax-loader-content">
            <div class="ajax-loader-spinner"></div>
            <div id="ajaxLoaderText">Processing...</div>
        </div>
    </div>
</body>

</html>