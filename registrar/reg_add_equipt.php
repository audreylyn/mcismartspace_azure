<?php
require '../auth/middleware.php';
checkAccess(['Registrar']);
?>
<?php include "includes/add_equipt.php"; ?>

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

        <?php 
        include 'layout/topnav.php'; 
        include 'layout/sidebar.php'; 
        ?>

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
                        <table id="equipmentTable" class="table is-fullwidth is-striped">
                            <thead>
                                <tr class="titles">
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($equipment_list)): ?>
                                    <tr>
                                        <td colspan="4" class="has-text-centered">No equipment found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($equipment_list as $equipment): ?>
                                        <tr>
                                            <td data-label="ID"><?= htmlspecialchars($equipment['id']) ?></td>
                                            <td data-label="Name"><?= htmlspecialchars($equipment['name']) ?></td>
                                            <td data-label="Description"><?= htmlspecialchars($equipment['description']) ?></td>
                                            <td data-label="Category"><?= htmlspecialchars($equipment['category']) ?></td>
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
                            <p class="new-title">Add Equipment</p>
                        </div>
                    </header>
                    <div class="card-content">
                        <form method="POST">
                            <div class="field is-inline">
                                <div class="field" style="width: 100%;">
                                    <label class="label">Equipment Name:</label>
                                    <div class="control">
                                        <input class="input" type="text" name="name" required style="width: 100%;">
                                    </div>
                                </div>
                            </div>

                            <div class="field is-inline">
                                <div class="field" style="width: 100%;">
                                    <label class="label">Category:</label>
                                    <div class="control">
                                        <div class="select" style="width: 100%;">
                                            <select name="category" required style="width: 100%;">
                                                <option value="">Select Category</option>
                                                <?php
                                                $categories = ['Furniture', 'Electronics', 'Teaching Materials', 'Office Supplies', 'Laboratory Equipment'];
                                                foreach ($categories as $cat) {
                                                    echo '<option value="' . htmlspecialchars($cat) . '">' . htmlspecialchars($cat) . '</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="field is-inline">
                                <div class="control">
                                    <label class="label">Description:</label>
                                    <textarea class="input" name="description" required></textarea>
                                </div>
                            </div>

                            <div class="field is-inline">
                                <div class="control">
                                    <button type="submit" name="add_equipment" class="styled-button">Add Equipment</button>
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
            $('#equipmentTable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search equipment..."
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