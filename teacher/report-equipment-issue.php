<?php
require '../auth/middleware.php';
checkAccess(['Teacher']);

// Initialize session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get equipment details from query parameters
$equipmentId = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
$equipmentName = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '';
$roomName = isset($_GET['room']) ? htmlspecialchars($_GET['room']) : '';
$buildingName = isset($_GET['building']) ? htmlspecialchars($_GET['building']) : '';

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
    $teacherId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $imagePath = null;

    // Verify the teacher ID exists in the database
    if ($teacherId) {
        $checkTeacherSql = "SELECT TeacherID FROM teacher WHERE TeacherID = ?";
        $checkStmt = $conn->prepare($checkTeacherSql);
        $checkStmt->bind_param("i", $teacherId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            // Teacher ID doesn't exist in the database
            $error_message = "Error: Your teacher ID is not found in the database. Please contact support.";
            $teacherId = null; // Set to null since it's invalid
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
        $filename = "issue_" . $teacherId . "_" . $timestamp . ".jpg";
        $uploadPath = $uploadDir . $filename;

        // Simple file upload without image processing
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $imagePath = $uploadPath;
        } else {
            $error_message = "Failed to upload image.";
        }
    }

    // Only proceed with insert if we have a valid teacher ID and no errors
    if ($teacherId && !isset($error_message)) {
        // Prepare SQL statement to insert report with image path
        $sql = "INSERT INTO equipment_issues (equipment_id, teacher_id, issue_type, description, image_path, status, statusCondition, reported_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iissss", $equipmentId, $teacherId, $issueType, $description, $imagePath, $condition);

        // Execute the statement
        try {
            if ($stmt->execute()) {
                // Also update the equipment status in room_equipment table
                $updateEquipment = "UPDATE room_equipment SET status = ?, statusCondition = ?, last_updated = NOW() 
                                   WHERE equipment_id = ?";
                $updateStmt = $conn->prepare($updateEquipment);
                $updateStmt->bind_param("ssi", $condition, $condition, $equipmentId);
                $updateStmt->execute();

                // Create an audit log entry
                $auditSql = "INSERT INTO equipment_audit (equipment_id, action, notes) 
                            VALUES (?, 'Issue Reported', ?)";
                $auditStmt = $conn->prepare($auditSql);
                $auditNotes = "Issue reported by teacher ID: $teacherId - Type: $issueType";
                $auditStmt->bind_param("is", $equipmentId, $auditNotes);
                $auditStmt->execute();

                // Set success message
                $_SESSION['success_message'] = "Your report has been submitted successfully! The issue will be addressed by the maintenance team.";

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
    // Create the equipment_issues table with teacher_id instead of student_id
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
            <h3 class="page-title">Report Equipment Issue</h3>
            <p class="page-subtitle">Submit a report for any malfunctioning equipment</p>

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