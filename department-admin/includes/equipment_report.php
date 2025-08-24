<?php
require_once __DIR__ . '/../../auth/middleware.php';
$conn = db();

// Process status update if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $reportId = $_POST['report_id'];
    $newStatus = $_POST['status'];
    $newCondition = $_POST['statusCondition'];
    $adminResponse = $_POST['admin_response'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Update the equipment issue report
        $updateSql = "UPDATE equipment_issues SET 
                    status = ?, 
                    admin_response = ?,
                    resolved_at = CASE WHEN ? = 'resolved' THEN NOW() ELSE resolved_at END
                    WHERE id = ?";

        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("sssi", $newStatus, $adminResponse, $newStatus, $reportId);
        $stmt->execute();

        // Get the equipment_id from the report
        $getEquipmentSql = "SELECT equipment_id FROM equipment_issues WHERE id = ?";
        $equipStmt = $conn->prepare($getEquipmentSql);
        $equipStmt->bind_param("i", $reportId);
        $equipStmt->execute();
        $equipResult = $equipStmt->get_result();
        $equipData = $equipResult->fetch_assoc();
        $equipmentId = $equipData['equipment_id'];

        // Update the room_equipment table to reflect the new condition
        $updateEquipSql = "UPDATE room_equipment 
                          SET status = ?, last_updated = NOW() 
                          WHERE equipment_id = ?";
        $equipUpdateStmt = $conn->prepare($updateEquipSql);
        $equipUpdateStmt->bind_param("si", $newCondition, $equipmentId);
        $equipUpdateStmt->execute();

        // Create audit log entry
        $auditSql = "INSERT INTO equipment_audit (equipment_id, action, notes) 
                    VALUES (?, CONCAT('Status Updated: ', ?, ' Condition: ', ?), ?)";
        $auditStmt = $conn->prepare($auditSql);
        $auditNotes = "Admin response: " . $adminResponse;
        $auditStmt->bind_param("isss", $equipmentId, $newStatus, $newCondition, $auditNotes);
        $auditStmt->execute();

        // Commit the transaction
        $conn->commit();

        $success_message = "Report status and equipment condition updated successfully!";
    } catch (Exception $e) {
        // Roll back the transaction in case of error
        $conn->rollback();
        $error_message = "Error updating report: " . $e->getMessage();
    }
}

// Fetch summary counts for dashboard
$countsSql = "SELECT 
              SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
              SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
              SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count,
              SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
              COUNT(*) as total_count
              FROM equipment_issues";
$countsResult = $conn->query($countsSql);
$countData = $countsResult->fetch_assoc();

// Set up filtering options
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$dateRange = isset($_GET['date_range']) ? $_GET['date_range'] : '30'; // Default to last 30 days
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Build query based on filters
$filterConditions = [];
$params = [];
$types = '';

// Department-Based Access Control
$admin_department = $_SESSION['department'] ?? '';
if (!empty($admin_department)) {
    $filterConditions[] = "(s.Department = ? OR t.Department = ?)";
    $params[] = $admin_department;
    $params[] = $admin_department;
    $types .= 'ss';
}

if ($statusFilter != 'all') {
    $filterConditions[] = "ei.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if ($dateRange != 'all') {
    $filterConditions[] = "ei.reported_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
    $params[] = $dateRange;
    $types .= 'i';
}

if (!empty($searchTerm)) {
    $filterConditions[] = "(e.name LIKE ? OR r.room_name LIKE ? OR b.building_name LIKE ? OR s.FirstName LIKE ? OR s.LastName LIKE ?)";
    for ($i = 0; $i < 5; $i++) {
        $params[] = "%" . $searchTerm . "%";
        $types .= 's';
    }
}

// Construct WHERE clause
$whereClause = !empty($filterConditions) ? "WHERE " . implode(" AND ", $filterConditions) : "";

// Get reports with detailed information
$reportsSql = "SELECT ei.*, e.name as equipment_name, r.room_name as room_name, b.building_name as building_name,
              re.status as equipment_condition,
              CASE 
                WHEN ei.student_id IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
                WHEN ei.teacher_id IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
              END as reporter_name,
              CASE
                WHEN ei.student_id IS NOT NULL THEN 'Student'
                WHEN ei.teacher_id IS NOT NULL THEN 'Teacher'
              END as reporter_type,
              s.StudentID, s.Email as student_email,
              t.TeacherID, t.Email as teacher_email
              FROM equipment_issues ei
              JOIN equipment e ON ei.equipment_id = e.id
              LEFT JOIN (
                  SELECT equipment_id, MIN(room_id) as room_id, status
                  FROM room_equipment 
                  GROUP BY equipment_id
              ) re ON ei.equipment_id = re.equipment_id
              LEFT JOIN rooms r ON re.room_id = r.id
              LEFT JOIN buildings b ON r.building_id = b.id
              LEFT JOIN student s ON ei.student_id = s.StudentID
              LEFT JOIN teacher t ON ei.teacher_id = t.TeacherID
              $whereClause
              ORDER BY 
              CASE 
                WHEN ei.status = 'pending' THEN 1
                WHEN ei.status = 'in_progress' THEN 2
                WHEN ei.status = 'resolved' THEN 3
                WHEN ei.status = 'rejected' THEN 4
                ELSE 5
              END,
              ei.reported_at DESC";

$stmt = $conn->prepare($reportsSql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$reportsResult = $stmt->get_result();

// Check if viewing a specific report
$viewReportId = isset($_GET['view']) ? intval($_GET['view']) : 0;
$reportDetail = null;

if ($viewReportId > 0) {
    $detailSql = "SELECT ei.*, e.name as equipment_name, r.room_name as room_name, b.building_name as building_name,
                 re.status as equipment_condition,
                 CASE 
                   WHEN ei.student_id IS NOT NULL THEN CONCAT(s.FirstName, ' ', s.LastName)
                   WHEN ei.teacher_id IS NOT NULL THEN CONCAT(t.FirstName, ' ', t.LastName)
                 END as reporter_name,
                 CASE
                   WHEN ei.student_id IS NOT NULL THEN 'Student'
                   WHEN ei.teacher_id IS NOT NULL THEN 'Teacher'
                 END as reporter_type,
                 s.StudentID, s.Email as student_email,
                 t.TeacherID, t.Email as teacher_email
                 FROM equipment_issues ei
                 JOIN equipment e ON ei.equipment_id = e.id
                 LEFT JOIN (
                     SELECT equipment_id, MIN(room_id) as room_id, status
                     FROM room_equipment 
                     GROUP BY equipment_id
                 ) re ON ei.equipment_id = re.equipment_id
                 LEFT JOIN rooms r ON re.room_id = r.id
                 LEFT JOIN buildings b ON r.building_id = b.id
                 LEFT JOIN student s ON ei.student_id = s.StudentID
                 LEFT JOIN teacher t ON ei.teacher_id = t.TeacherID
                 WHERE ei.id = ?";
    $detailStmt = $conn->prepare($detailSql);
    $detailStmt->bind_param("i", $viewReportId);
    $detailStmt->execute();
    $detailResult = $detailStmt->get_result();

    if ($detailResult->num_rows > 0) {
        $reportDetail = $detailResult->fetch_assoc();
    }
}
