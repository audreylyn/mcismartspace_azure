<?php
require_once __DIR__ . '/../../auth/middleware.php';
$db = db();

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="dept_admins.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['AdminID', 'FirstName', 'LastName', 'Department', 'Email']); // Header row

$query = $db->query("SELECT AdminID, FirstName, LastName, Department, Email FROM dept_admin");
while ($row = $query->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
