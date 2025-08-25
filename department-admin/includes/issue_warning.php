<?php
require '../../auth/middleware.php';
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Department Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$conn = db();
$student_id = $_POST['student_id'] ?? null;
$reason = $_POST['reason'] ?? '';
$desc = $_POST['description'] ?? '';
if (!$student_id || !$reason || !$desc) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}
// Check current warnings
$stmt = $conn->prepare("SELECT COUNT(*) FROM penalty WHERE student_id = ? AND type = 'warning' AND (expires_at IS NULL OR expires_at > NOW())");
$stmt->bind_param('i', $student_id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();
if ($count >= 3) {
    echo json_encode(['success' => false, 'message' => 'Student already has 3 active warnings.']);
    exit;
}
// Insert warning
$expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
// Add issued_by (adminId)
$adminId = $_SESSION['user_id'];
$stmt = $conn->prepare("INSERT INTO penalty (student_id, type, reason, description, issued_at, expires_at, issued_by) VALUES (?, 'warning', ?, ?, NOW(), ?, ?)");
$stmt->bind_param('issssi', $student_id, $reason, $desc, $expires_at, $adminId);
if ($stmt->execute()) {
    $stmt->close();
    // Optionally update student status if warnings == 3
    $stmt2 = $conn->prepare("SELECT COUNT(*) FROM penalty WHERE student_id = ? AND type = 'warning' AND (expires_at IS NULL OR expires_at > NOW())");
    $stmt2->bind_param('i', $student_id);
    $stmt2->execute();
    $stmt2->bind_result($newCount);
    $stmt2->fetch();
    $stmt2->close();
    if ($newCount >= 3) {
        $stmt3 = $conn->prepare("UPDATE student SET PenaltyStatus = 'banned' WHERE StudentID = ?");
        $stmt3->bind_param('i', $student_id);
        $stmt3->execute();
        $stmt3->close();
    } else {
        $stmt3 = $conn->prepare("UPDATE student SET PenaltyStatus = 'warning' WHERE StudentID = ?");
        $stmt3->bind_param('i', $student_id);
        $stmt3->execute();
        $stmt3->close();
    }
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to issue warning. Error: ' . $stmt->error]);
}
$conn->close();
