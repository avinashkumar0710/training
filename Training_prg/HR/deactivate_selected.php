<?php
header('Content-Type: application/json');

// Database connection
$serverName = "192.168.100.240";
$connectionInfo = array("Database" => "Complaint", "UID" => "sa", "PWD" => "Intranet@123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
$ids = $data['ids'] ?? [];

if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'No IDs received']);
    exit;
}

// Prepare ID list for SQL
$idList = implode(',', array_map('intval', $ids));

// Update both tables
$sql1 = "UPDATE Complaint.dbo.training_mast SET flag = 0 WHERE id IN ($idList)";
$sql2 = "UPDATE Complaint.dbo.training_mast_com SET flag = 0 WHERE id IN ($idList)";

$success = true;
$message = '';

if (!sqlsrv_query($conn, $sql1) || !sqlsrv_query($conn, $sql2)) {
    $success = false;
    $message = 'Error updating records: ' . print_r(sqlsrv_errors(), true);
} else {
    $message = count($ids) . ' record(s) deactivated successfully';
}

sqlsrv_close($conn);
echo json_encode(['success' => $success, 'message' => $message]);
?>