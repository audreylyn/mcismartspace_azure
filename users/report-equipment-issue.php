<?php
require '../auth/middleware.php';
checkAccess(['Student', 'Teacher']);

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get user role and ID from session
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Get equipment details from query parameters or sessionStorage
$equipmentId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
$equipmentName = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '';
$roomName = isset($_GET['room']) ? htmlspecialchars($_GET['room']) : '';
$buildingName = isset($_GET['building']) ? htmlspecialchars($_GET['building']) : '';

// Initialize variables for equipment type (will be filled from database)
$equipmentType = '';

$conn = db();

// Check if we need to add reference_number column
$checkRefNumColumnSql = "SHOW COLUMNS FROM equipment_issues LIKE 'reference_number'";
$refNumColumnExists = $conn->query($checkRefNumColumnSql)->num_rows > 0;

if (!$refNumColumnExists) {
    // Add reference_number column to the table
    $alterTableSql = "ALTER TABLE equipment_issues ADD COLUMN reference_number VARCHAR(15) DEFAULT NULL";
    $conn->query($alterTableSql);
    
    // Update existing records with reference numbers
    $updateExistingRecordsSql = "UPDATE equipment_issues SET reference_number = CONCAT('EQ', LPAD(id, 6, '0')) WHERE reference_number IS NULL";
    $conn->query($updateExistingRecordsSql);
}

// Check if we need to add rejection_reason column
$checkRejectionColumnSql = "SHOW COLUMNS FROM equipment_issues LIKE 'rejection_reason'";
$rejectionColumnExists = $conn->query($checkRejectionColumnSql)->num_rows > 0;

if (!$rejectionColumnExists) {
    // Add rejection_reason column to the table
    $alterTableSql = "ALTER TABLE equipment_issues ADD COLUMN rejection_reason TEXT DEFAULT NULL";
    $conn->query($alterTableSql);
}

// Attempt to get equipment ID if not provided but name is available
if (empty($equipmentId) && !empty($equipmentName) && !empty($roomName)) {
    // Try to find the equipment ID based on name and location
    $findIdSql = "SELECT e.id FROM equipment e 
                  JOIN room_equipment re ON e.id = re.equipment_id
                  JOIN rooms r ON re.room_id = r.id
                  JOIN buildings b ON r.building_id = b.id
                  WHERE e.name LIKE ? AND r.room_name = ? AND b.building_name = ?
                  LIMIT 1";

    $stmt = $conn->prepare($findIdSql);
    $searchName = "%$equipmentName%";
    $stmt->bind_param("sss", $searchName, $roomName, $buildingName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $equipmentId = $row['id'];
    }
    $stmt->close();
}

// Function to process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_report'])) {
    // Get form data
    $issueType = $_POST['issue_type'];
    $condition = $_POST['condition'];
    $description = $_POST['description'];
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $userRole = $_SESSION['role']; // Get user role from session
    $imagePath = null;
    
    // Get equipment_id from POST data if it exists (for manual entry), otherwise use the one from URL
    $formEquipmentId = isset($_POST['equipment_id']) ? intval($_POST['equipment_id']) : null;
    $finalEquipmentId = $formEquipmentId ?: $equipmentId;

    // Check if there's already an open report for this equipment
    $checkReportSql = "SELECT ei.id, ei.status, re.status AS equipment_status 
                      FROM equipment_issues ei 
                      JOIN room_equipment re ON ei.equipment_id = re.equipment_id 
                      WHERE ei.equipment_id = ? AND (ei.status = 'pending' OR ei.status = 'in_progress')";
    $checkReportStmt = $conn->prepare($checkReportSql);
    $checkReportStmt->bind_param("i", $finalEquipmentId);
    $checkReportStmt->execute();
    $reportResult = $checkReportStmt->get_result();
    
    // Check if equipment has non-working status
    $checkEquipmentSql = "SELECT status FROM room_equipment WHERE equipment_id = ? AND status IN ('needs_repair', 'maintenance', 'missing')";
    $checkEquipmentStmt = $conn->prepare($checkEquipmentSql);
    $checkEquipmentStmt->bind_param("i", $finalEquipmentId);
    $checkEquipmentStmt->execute();
    $equipmentResult = $checkEquipmentStmt->get_result();
    
    // Prevent submission if equipment already has an open report or is in non-working state
    if ($reportResult->num_rows > 0 || $equipmentResult->num_rows > 0) {
        $reportData = $reportResult->fetch_assoc();
        $equipmentData = $equipmentResult->fetch_assoc();
        
        if ($reportResult->num_rows > 0) {
            $error_message = "This equipment already has an open report that needs to be resolved by the department admin. Please check the status of your existing report.";
        } else {
            $status = $equipmentData['status'];
            $readableStatus = str_replace('_', ' ', $status);
            $error_message = "This equipment is currently marked as '$readableStatus'. It needs to be resolved by the department admin before new reports can be submitted.";
        }
        $checkReportStmt->close();
        $checkEquipmentStmt->close();
    } else {
        $checkReportStmt->close();
        $checkEquipmentStmt->close();

        // Verify the user ID exists in the database
        if ($userId) {
            // Use different table based on user role
        if ($userRole === 'Student') {
            $checkUserSql = "SELECT StudentID FROM student WHERE StudentID = ?";
            $userIdField = "StudentID";
            $errorMsg = "student";
        } else { // Teacher
            $checkUserSql = "SELECT TeacherID FROM teacher WHERE TeacherID = ?";
            $userIdField = "TeacherID";
            $errorMsg = "teacher";
        }
        
        $checkStmt = $conn->prepare($checkUserSql);
        $checkStmt->bind_param("i", $userId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            // User ID doesn't exist in the database
            $error_message = "Error: Your $errorMsg ID is not found in the database. Please contact support.";
            $userId = null; // Set to null since it's invalid
        }
        $checkStmt->close();
    } else {
        $error_message = "Error: User ID not found in session. Please try logging in again.";
    }

    // Handle image upload and compression if provided
    if (!empty($_FILES['image']['tmp_name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/equipment_issues/';

        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $timestamp = time();
        $filename = "issue_" . $userId . "_" . $timestamp . ".jpg";
        $uploadPath = $uploadDir . $filename;

        // Simple file upload without image processing
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $imagePath = $uploadPath;
        } else {
            $error_message = "Failed to upload image.";
        }
    }

    // Only proceed with insert if we have a valid user ID and no errors
    if ($userId && !isset($error_message)) {
        // Generate a unique reference number with current timestamp
        $timestamp = time();
        $referenceNumber = 'EQ' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        
        // Prepare SQL statement to insert report with image path based on user role
        if ($userRole === 'Student') {
            $sql = "INSERT INTO equipment_issues (equipment_id, student_id, issue_type, description, image_path, status, reported_at, reference_number) 
                    VALUES (?, ?, ?, ?, ?, 'pending', NOW(), ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissss", $finalEquipmentId, $userId, $issueType, $description, $imagePath, $referenceNumber);
        } else { // Teacher
            $sql = "INSERT INTO equipment_issues (equipment_id, teacher_id, issue_type, description, image_path, status, reported_at, reference_number) 
                    VALUES (?, ?, ?, ?, ?, 'pending', NOW(), ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissss", $finalEquipmentId, $userId, $issueType, $description, $imagePath, $referenceNumber);
        }

        // Execute the statement
        try {
            if ($stmt->execute()) {
                // Also update the equipment status in room_equipment table
                $updateEquipment = "UPDATE room_equipment SET status = ?, last_updated = NOW() 
                                    WHERE equipment_id = ?";
                $updateStmt = $conn->prepare($updateEquipment);
                $updateStmt->bind_param("si", $condition, $finalEquipmentId);
                $updateStmt->execute();

                // Create an audit log entry
                $auditSql = "INSERT INTO equipment_audit (equipment_id, action, notes) 
                            VALUES (?, 'Issue Reported', ?)";
                $auditStmt = $conn->prepare($auditSql);
                $roleText = ($userRole === 'Student') ? 'student' : 'teacher';
                $auditNotes = "Issue reported by $roleText ID: $userId - Type: $issueType";
                $auditStmt->bind_param("is", $finalEquipmentId, $auditNotes);
                $auditStmt->execute();

                // Set success message
                $_SESSION['success_message'] = "Your report has been submitted successfully! The issue will be addressed by the maintenance team.";
                
                // Store the reference number in session for the confirmation page
                $_SESSION['report_reference'] = $referenceNumber;

                // Redirect to confirmation page
                header("Location: report-confirmation.php");
                exit;
            } else {
                $error_message = "Error submitting report: " . $stmt->error;
            }
        } catch (Exception $e) {
            $error_message = "Database error: " . $e->getMessage();
        }

        $stmt->close();
    }
}

// Check if the equipment_issues table exists, create if not
$checkTableSql = "SHOW TABLES LIKE 'equipment_issues'";
$tableExists = $conn->query($checkTableSql)->num_rows > 0;

if (!$tableExists) {
    // Create the equipment_issues table
    $createTableSql = "CREATE TABLE IF NOT EXISTS equipment_issues (
        id INT AUTO_INCREMENT PRIMARY KEY,
        equipment_id INT NOT NULL,
        student_id INT DEFAULT NULL,
        teacher_id INT DEFAULT NULL,
        issue_type VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        image_path VARCHAR(255) DEFAULT NULL,
        status ENUM('pending', 'in_progress', 'resolved', 'rejected') DEFAULT 'pending',
        statusCondition ENUM('working', 'needs_repair', 'maintenance', 'missing') DEFAULT 'working',
        admin_response TEXT DEFAULT NULL,
        reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        resolved_at TIMESTAMP NULL DEFAULT NULL,
        FOREIGN KEY (equipment_id) REFERENCES equipment(id),
        FOREIGN KEY (student_id) REFERENCES student(StudentID) ON DELETE CASCADE,
        FOREIGN KEY (teacher_id) REFERENCES teacher(TeacherID) ON DELETE CASCADE
    )";
    $conn->query($createTableSql);
} else {
    // Check if image_path column exists, add it if not
    $checkColumnSql = "SHOW COLUMNS FROM equipment_issues LIKE 'image_path'";
    $columnExists = $conn->query($checkColumnSql)->num_rows > 0;

    if (!$columnExists) {
        // Add image_path column to existing table
        $addColumnSql = "ALTER TABLE equipment_issues ADD COLUMN image_path VARCHAR(255) DEFAULT NULL";
        $conn->query($addColumnSql);
    }
} // End of database structure check

// Close the if block for form submission
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Equipment Issue</title>
    <link href="../public/css/user_styles/report_equipment_issue.css" rel="stylesheet">

</head>

<body>
    <!-- Page content -->
    <div class="right_col" role="main">
        <div class="issue-report-container">
            <h3 class="title">Report Equipment Issue</h3>
            <p class="subtitle">Submit a report for any malfunctioning equipment</p>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">Equipment Information</div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Equipment ID</div>
                            <div class="info-value"><?php echo $equipmentId ?: 'N/A'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Equipment Type</div>
                            <div class="info-value"><?php echo $equipmentName ?: 'N/A'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Location</div>
                            <div class="info-value"><?php echo $roomName ?: 'N/A'; ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Building</div>
                            <div class="info-value"><?php echo $buildingName ?: 'N/A'; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="card">
                    <div class="card-header">Issue Details</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="issue_type" class="form-label">Issue Type</label>
                            <select id="issue_type" name="issue_type" class="form-select" required>
                                <option value="">Select issue type</option>
                                <option value="Hardware Failure">Hardware Failure</option>
                                <option value="Software Problem">Software Problem</option>
                                <option value="Connectivity Issue">Connectivity Issue</option>
                                <option value="Power Problem">Power Problem</option>
                                <option value="Display Issue">Display Issue</option>
                                <option value="Audio Problem">Audio Problem</option>
                                <option value="Peripheral Not Working">Peripheral Not Working</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="condition" class="form-label">Equipment Condition</label>
                            <select id="condition" name="condition" class="form-select" required>
                                <option value="needs_repair">Needs Repair</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="missing">Missing</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" placeholder="Provide details about the issue..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Attach Image (Optional)</label>
                            <div class="upload-container">
                                <label for="image_upload" class="upload-label">
                                    <i class="fa fa-cloud-upload upload-icon"></i>
                                    <div class="upload-text">Click to upload an image</div>
                                    <div class="upload-subtext">JPG, PNG or GIF (max. 5MB)</div>
                                    <input type="file" id="image_upload" name="image" class="upload-input" accept="image/*">
                                </label>
                            </div>
                            <div id="image-preview" style="display: none; margin-top: 1rem;">
                                <img id="preview-img" src="" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 0.5rem;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="btn-container">
                    <a href="qr-scan.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" name="submit_report" class="btn btn-primary">Submit Report</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if equipment info is empty and try to load from sessionStorage
            const equipmentId = '<?php echo $equipmentId; ?>';
            const equipmentName = '<?php echo $equipmentName; ?>';
            const roomName = '<?php echo $roomName; ?>';
            const buildingName = '<?php echo $buildingName; ?>';
            
            // If any equipment data is missing, try to load from sessionStorage
            if (!equipmentId || !equipmentName || !roomName || !buildingName) {
                const scannedEquipment = sessionStorage.getItem('scannedEquipment');
                if (scannedEquipment) {
                    try {
                        const equipmentData = JSON.parse(scannedEquipment);
                        
                        // Update the display elements
                        const infoValues = document.querySelectorAll('.info-value');
                        if (infoValues.length >= 4) {
                            if (!equipmentId && equipmentData.equipment_id) {
                                infoValues[0].textContent = equipmentData.equipment_id;
                            }
                            if (!equipmentName && equipmentData.name) {
                                infoValues[1].textContent = equipmentData.name;
                            }
                            if (!roomName && equipmentData.room) {
                                infoValues[2].textContent = equipmentData.room;
                            }
                            if (!buildingName && equipmentData.building) {
                                infoValues[3].textContent = equipmentData.building;
                            }
                        }
                        
                        // Store equipment data in hidden inputs for form submission
                        const form = document.querySelector('form');
                        if (form && equipmentData.equipment_id) {
                            // Create hidden input for equipment_id if it doesn't exist
                            let hiddenEquipmentId = form.querySelector('input[name="equipment_id"]');
                            if (!hiddenEquipmentId) {
                                hiddenEquipmentId = document.createElement('input');
                                hiddenEquipmentId.type = 'hidden';
                                hiddenEquipmentId.name = 'equipment_id';
                                form.appendChild(hiddenEquipmentId);
                            }
                            hiddenEquipmentId.value = equipmentData.equipment_id;
                        }
                        
                    } catch (e) {
                        console.error('Error parsing equipment data from sessionStorage:', e);
                    }
                }
            }

            // Image preview functionality
            const imageUpload = document.getElementById('image_upload');
            const imagePreview = document.getElementById('image-preview');
            const previewImg = document.getElementById('preview-img');

            imageUpload.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.style.display = 'block';
                    }

                    reader.readAsDataURL(file);
                } else {
                    imagePreview.style.display = 'none';
                }
            });
        });
    </script>
</body>

</html>