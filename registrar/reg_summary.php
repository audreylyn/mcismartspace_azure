<?php
require '../auth/middleware.php';
checkAccess(['Registrar']);

// Include the logic for handling summary, add, update, and delete actions
include "includes/summary.php"; 
include "includes/add_room.php"; 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facility Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />
    <link rel="stylesheet" href="https://cdn.materialdesignicons.com/4.9.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main.css">
    <link rel="stylesheet" href="../public/css/admin_styles/main_2.css">
    <link rel="stylesheet" href="../public/css/admin_styles/style-all.css">
    <link rel="stylesheet" href="../public/css/admin_styles/form_1.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="../public/css/admin_styles/datatables.css">

    <style>
        .card-content { padding: 1.5rem; }
        .action-buttons button { margin-right: 5px; }
        
        /* Updated modal styles */
        #roomModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            padding: 20px;
        }
        
        .modal-container {
            background-color: #fff;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            position: relative;
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #999;
            position: absolute;
            right: 0;
            top: 0;
        }
        
        /* Form styles */
        .field {
            margin-bottom: 15px;
        }
        
        .label {
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
        }
        
        .input, .select select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
        }
        
        .field .control {
            margin-bottom: 10px;
        }
        
        /* Button styles */
        .modal-footer {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .button {
            padding: 8px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .button.is-cancel {
            background-color: #f0f0f0;
            border: none;
            color: #333;
            margin-right: 10px;
        }
        
        .button.is-primary, .button.is-save {
            background-color: #D4AF37;
            border: none;
            color: white;
        }
        
        /* Import button styles */
        .excel {
            position: relative;
            cursor: pointer;
            background-color: #ffc107;
            border: none;
            color: white;
        }
        
        .excel input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .container-btn-file {
            display: flex;
            align-items: center;
            gap: 5px;
            background-color: #ffc107;
            border: none;
            color: white;
            padding: 0.5rem;
        }
        
        /* Import button disabled state */
        .excel.disabled, .container-btn-file.disabled {
            background-color: #e0e0e0;
            cursor: not-allowed;
        }

        /* Spinner for loading state */
        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 5px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Modal for displaying messages */
        .modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 25px;
            border: none;
            width: 80%;
            max-width: 550px;
            border-radius: 8px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            transition: color 0.2s;
        }

        .close:hover,
        .close:focus {
            color: #333;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-button {
            background-color: #D4AF37;
            color: white;
            border: none;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 15px 0 5px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .modal-button:hover {
            background-color: #c19b26;
        }
        
        #modalTitle {
            margin-top: 0;
            font-size: 1.5rem;
            color: #333;
            font-weight: 600;
        }
        
        #modalMessage {
            margin-top: 15px;
            line-height: 1.5;
            color: #555;
        }
        
        #modalMessage ul {
            margin-bottom: 0;
        }
        
        #modalMessage li {
            margin-bottom: 5px;
        }

    </style>

</head>

<body>
    <div id="app">

        <?php 
        include 'layout/topnav.php'; 
        include 'layout/sidebar.php'; 
        ?>

        <section class="section main-section">
            <div class="card">
                <header class="card-header">
                    <p class="card-header-title">
                        <span class="icon"><i class="mdi mdi-office-building"></i></span>
                        Facility Management
                    </p>
                    <div class="card-header-icon" style="display: flex; gap: 10px;">
                        <button class="button is-primary" id="addRoomBtn">
                            <span class="icon"><i class="mdi mdi-plus"></i></span>
                            <span>Add Room</span>
                        </button>
                        <form id="importForm" action="includes/import_rooms.php" class="form-data" method="post" enctype="multipart/form-data" style="display: flex;">
                            <button type="button" class="excel" style="border-radius: 0.3em 0 0 0.3em; display: flex; justify-content: center; width: 50px; padding: 0.5rem;">
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
                                <input type="file" name="file" id="csvFile" class="file" accept=".csv" required />
                            </button>
                            <button id="importButton" class="container-btn-file" type="submit" name="importSubmit" style="border-radius: 0 0.3em 0.3em 0;">
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
                    <table id="facilityTable" class="adminTable table is-fullwidth is-striped is-hoverable">
                        <thead>
                            <tr>
                                <th>Building Name</th>
                                <th>Department</th>
                                <th>Floors</th>
                                <th>Room Name</th>
                                <th>Room Type</th>
                                <th>Capacity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = $result->fetch_assoc()):
                                $building_name = htmlspecialchars($row['building_name'] ?? '', ENT_QUOTES, 'UTF-8');
                                $department = htmlspecialchars($row['department'] ?? '', ENT_QUOTES, 'UTF-8');
                                $floors = htmlspecialchars($row['number_of_floors'] ?? '', ENT_QUOTES, 'UTF-8');
                                $room_id = htmlspecialchars($row['room_id'] ?? '', ENT_QUOTES, 'UTF-8');
                                $room_name = htmlspecialchars($row['room_name'] ?? '', ENT_QUOTES, 'UTF-8');
                                $room_type = htmlspecialchars($row['room_type'] ?? '', ENT_QUOTES, 'UTF-8');
                                $capacity = htmlspecialchars($row['capacity'] ?? '', ENT_QUOTES, 'UTF-8');
                            ?>
                                <tr>
                                    <td data-label="Building Name"><?= $building_name ?></td>
                                    <td data-label="Department"><?= $department ?></td>
                                    <td data-label="Floors"><?= $floors ?></td>
                                    <td data-label="Room Name"><?= $room_name ?: 'N/A' ?></td>
                                    <td data-label="Room Type"><?= $room_type ?: 'N/A' ?></td>
                                    <td data-label="Capacity"><?= $capacity ?: 'N/A' ?></td>
                                    <td data-label="Actions" class="action-buttons">
                                        <?php if ($room_id): ?>
                                            <button class="button is-info is-small" onclick='openEditModal(<?= json_encode($row) ?>)'>
                                                <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                            </button>
                                            <button class="button is-danger is-small" onclick="if(confirm('Are you sure you want to delete this room?')) window.location.href='?delete_room=<?= urlencode($room_id) ?>'">
                                                <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- Add/Edit Modal -->
        <div id="roomModal">
            <div class="modal-container">
                <div class="modal-header">
                    <h2 class="modal-title" id="modalTitle">Add Room</h2>
                    <button class="modal-close" id="modalClose">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="roomForm" method="POST">
                        <input type="hidden" name="room_id" id="room_id">
                        
                        <div class="field">
                            <label class="label">Room Name</label>
                            <input class="input" type="text" name="room_name" id="room_name" required>
                        </div>

                        <div class="field">
                            <label class="label">Room Type</label>
                            <div class="select is-fullwidth">
                                <select name="room_type" id="room_type" required>
                                    <option value="">Select Room Type</option>
                                    <?php
                                    $room_types = ['Classroom', 'Gymnasium', 'Auditorium', 'Lecture Hall'];
                                    foreach ($room_types as $type) {
                                        echo '<option value="' . htmlspecialchars($type) . '">' . htmlspecialchars($type) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="field">
                            <label class="label" id="capacityLabel">Capacity (max 500)</label>
                            <input class="input" type="number" name="capacity" id="capacity" min="1" max="500" required>
                        </div>

                        <div class="field">
                            <label class="label">Building</label>
                            <div class="select is-fullwidth">
                                <select name="building_id" id="building_id" required>
                                    <option value="">Select Building</option>
                                    <?php
                                    $building_sql = "SELECT id, building_name FROM buildings ORDER BY building_name ASC";
                                    $building_result_modal = $conn->query($building_sql);
                                    while ($building = $building_result_modal->fetch_assoc()) {
                                        echo "<option value=\"" . htmlspecialchars($building['id']) . "\">" . htmlspecialchars($building['building_name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="button is-cancel" id="modalCancel">Cancel</button>
                            <button type="submit" class="button is-save" id="formSubmitBtn" name="add_room">Save Changes</button>
                        </div>
                    </form>
                </div>
                
            </div>
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

        <?php
        $stmt->close();
        $conn->close();
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/main.min.js"></script>
    <script type="text/javascript" src="../public/js/admin_scripts/custom_alert.js"></script>

    <!-- Enhanced Modal JavaScript -->
    <script>
        // Function to show the message modal
        function showModal(title, message, type = 'success') {
            const modal = document.getElementById('messageModal');
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalMessage').innerHTML = message;

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
            } else if (event.target == document.getElementById('editModal')) {
                closeEditModal();
            }
        }

        // Function to close message modal with animation
        function closeModal() {
            const modal = document.getElementById('messageModal');
            modal.classList.remove('show');

            // Wait for animation to complete before hiding
            setTimeout(() => {
                modal.style.display = "none";
                modal.classList.remove('success', 'error');
            }, 300);
        }
    </script>

    <script>
        $(document).ready(function() {
            $('#facilityTable').DataTable({
                responsive: true,
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search facilities..."
                },
                dom: 'lfrtip',
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                pageLength: 10,
                ordering: true,
                columnDefs: [{ targets: -1, orderable: false }]
            });
            
            // Handle file input display
            $('.excel input[type="file"]').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $(this).closest('form').find('.container-btn-file').html('<svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"></path></svg> ' + fileName);
                } else {
                    $(this).closest('form').find('.container-btn-file').html('<svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"><path d="M19 9h-4V3H9v6H5l7 7 7-7zM5 18v2h14v-2H5z"></path></svg> Import');
                }
            });

            // Handle CSV import with form validation
            $('#importForm').on('submit', function(e) {
                e.preventDefault();
                
                // Validate file input
                const fileInput = $('#csvFile')[0];
                if (!fileInput.files.length) {
                    showModal('Error', 'Please select a CSV file to import.', 'error');
                    return false;
                }
                
                const file = fileInput.files[0];
                if (file.size === 0) {
                    showModal('Error', 'The selected file is empty.', 'error');
                    return false;
                }
                
                const fileExtension = file.name.split('.').pop().toLowerCase();
                if (fileExtension !== 'csv') {
                    showModal('Error', 'Only CSV files are allowed.', 'error');
                    return false;
                }
                
                // Show loading state
                const $importButton = $('#importButton');
                const originalButtonText = $importButton.html();
                $importButton.html('<div class="spinner"></div> Importing...');
                $importButton.addClass('disabled');
                $('.excel').addClass('disabled');
                
                // Prepare form data
                const formData = new FormData(this);
                // Add the importSubmit parameter that would normally come from the submit button
                formData.append('importSubmit', 'true');
                
                // Submit using AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            showModal('Success', response.message, 'success');
                            
                            // Add new rooms to the table
                            const table = $('#facilityTable').DataTable();
                            if (response.new_rooms && response.new_rooms.length > 0) {
                                response.new_rooms.forEach(function(room) {
                                    // Create action buttons
                                    const actionButtons = `
                                        <button class="button is-info is-small" onclick='openEditModal(${JSON.stringify(room)})'>
                                            <span class="icon"><i class="mdi mdi-pencil"></i></span>
                                        </button>
                                        <button class="button is-danger is-small" onclick="if(confirm('Are you sure you want to delete this room?')) window.location.href='?delete_room=${room.room_id}'">
                                            <span class="icon"><i class="mdi mdi-trash-can"></i></span>
                                        </button>
                                    `;
                                    
                                    // Add the row to the table
                                    table.row.add([
                                        room.building_name || 'N/A',
                                        room.department || 'N/A',
                                        room.number_of_floors || 'N/A',
                                        room.room_name || 'N/A',
                                        room.room_type || 'N/A',
                                        room.capacity || 'N/A',
                                        actionButtons
                                    ]).draw(false);
                                });
                            }
                        } else {
                            showModal('Error', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', error);
                        let errorMsg = 'An error occurred while importing the file.';
                        
                        // Try to parse the response if it's JSON
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response && response.message) {
                                errorMsg = response.message;
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                        }
                        
                        showModal('Error', errorMsg, 'error');
                    },
                    complete: function() {
                        // Reset form and button state
                        $('#importForm').trigger("reset");
                        $importButton.html(originalButtonText);
                        $importButton.removeClass('disabled');
                        $('.excel').removeClass('disabled');
                    }
                });
            });

            // Simple jQuery modal implementation
            $("#addRoomBtn").click(function() {
                $('#modalTitle').text('Add Room');
                $('#roomForm').trigger("reset");
                $('#room_id').val('');
                $('#formSubmitBtn').text('Save Changes').attr('name', 'add_room');
                $("#roomModal").fadeIn(300);
                updateCapacityLimit();
                return false; // Prevent default action
            });
            
            // Update capacity limits based on room type
            $('#room_type').on('change', function() {
                updateCapacityLimit();
            });
            
            function updateCapacityLimit() {
                const roomType = $('#room_type').val();
                const capacityInput = $('#capacity');
                const capacityLabel = $('#capacityLabel');
                
                if (roomType === 'Classroom') {
                    capacityInput.attr('max', 50);
                    capacityLabel.text('Capacity (max 50 for classrooms)');
                } else {
                    capacityInput.attr('max', 500);
                    capacityLabel.text('Capacity (max 500)');
                }
            }

            // Add form validation before submission
            $("#roomForm").on('submit', function(e) {
                const capacity = parseInt($('#capacity').val());
                const roomType = $('#room_type').val();
                
                if (roomType === 'Classroom' && capacity > 50) {
                    e.preventDefault();
                    showModal('Error', 'Classroom capacity cannot exceed 50 people.', 'error');
                    return false;
                } else if (capacity > 500) {
                    e.preventDefault();
                    showModal('Error', 'Room capacity cannot exceed 500 people.', 'error');
                    return false;
                }
            });

            $("#modalClose, #modalCancel").click(function() {
                $("#roomModal").fadeOut(300);
                return false; // Prevent default action
            });

            // Close modal when clicking outside the modal content
            $(document).mouseup(function(e) {
                var container = $(".modal-container");
                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    $("#roomModal").fadeOut(300);
                }
            });
        });

        function openEditModal(data) {
            $('#modalTitle').text('Edit Room');
            $('#room_id').val(data.room_id);
            $('#room_name').val(data.room_name);
            $('#room_type').val(data.room_type);
            $('#capacity').val(data.capacity);
            $('#building_id').val(data.building_id);
            $('#formSubmitBtn').attr('name', 'update_room');
            $("#roomModal").fadeIn(300);
            updateCapacityLimit(); // Update the capacity limit when editing
            return false; // Prevent any default action
        }

        function closeModal() {
            $('#messageModal').fadeOut(300);
        }

        window.onload = function() {
            <?php
            if (isset($_SESSION['success_message'])) {
                echo 'showModal("Success", "' . addslashes($_SESSION['success_message']) . '", "success");';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo 'showModal("Error", "' . addslashes($_SESSION['error_message']) . '", "error");';
                unset($_SESSION['error_message']);
            }
            ?>
        }
    </script>

</body>
</html>