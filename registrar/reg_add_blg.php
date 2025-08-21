<?php
require '../auth/middleware.php';
checkAccess(['Registrar']);
include "includes/add_building.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Building</title>
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
    
    <!-- Scripts with defer attribute -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js" defer></script>
    <script src="../public/js/admin_scripts/main.min.js" defer></script>
    <script src="../public/js/admin_scripts/custom_alert.js" defer></script>
    <script src="../public/js/admin_scripts/add_building.js" defer></script>
    <?php include "layout/admin-scripts.js.php"; ?>
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
                            <p class="new-title">Building List</p>
                            <a href="javascript:void(0);" id="exportBtn" class="batch" style="display: inline-flex; align-items: center; padding: 8px 16px;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" style="margin-right: 8px;">
                                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                                </svg>
                                Export
                            </a>
                        </div>
                    </header>
                    <div class="card-content">
                        <table id="buildingTable" class="adminTable table is-fullwidth is-striped">
                            <thead>
                                <tr class="titles">
                                    <th>Building Name</th>
                                    <th>Department</th>
                                    <th>Number Of Floors</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($requests)): ?>
                                    <tr>
                                        <td colspan="4" class="has-text-centered">No buildings found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($requests as $row): ?>
                                        <tr>
                                            <td data-label="Building Name"><?= htmlspecialchars($row['building_name']) ?></td>
                                            <td data-label="Department"><?= htmlspecialchars($row['department']) ?></td>
                                            <td data-label="Number Of Floors"><?= htmlspecialchars($row['number_of_floors']) ?></td>
                                            <td data-label="Created At"><?= htmlspecialchars($row['created_at']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <section class="section main-section">
                <div class="card">
                    <header class="card-header">
                        <div class="new-title-container">
                            <p class="new-title">Add Building</p>
                        </div>
                    </header>
                    <div class="card-content">
                        <form id="buildingForm" method="POST">
                            <div class="field is-inline">
                                <div class="control">
                                    <label class="label">Building Name:</label>
                                    <input class="input" type="text" name="building_name" required>
                                </div>
                                <div class="control">
                                    <label class="label">Number Of Floors (Max 7):</label>
                                    <input class="input" type="number" name="number_of_floors" min="1" max="7" required>
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
                                    <button type="submit" name="add_building" class="styled-button">Add Building</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
    
    <!-- Add Modal for error/success messages -->
    <div id="messageModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeModal">&times;</span>
            <h2 id="modalTitle"></h2>
            <div id="modalMessage"></div>
            <button class="modal-button" onclick="closeModal()">OK</button>
        </div>
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
