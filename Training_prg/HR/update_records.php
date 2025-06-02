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

$data = json_decode(file_get_contents('php://input'), true);
$ids = $data['ids'] ?? [];
$action = $data['action'] ?? '';

if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'No IDs received']);
    exit;
}

$idList = implode(',', array_map('intval', $ids));
$flagValue = ($action === 'reactivate') ? 1 : 0;

// Update both tables
$sql1 = "UPDATE Complaint.dbo.training_mast SET flag = $flagValue WHERE id IN ($idList)";
$sql2 = "UPDATE Complaint.dbo.training_mast_com SET flag = $flagValue WHERE srl_no IN (
            SELECT srl_no FROM Complaint.dbo.training_mast WHERE id IN ($idList)
        )";

$success = sqlsrv_query($conn, $sql1) && sqlsrv_query($conn, $sql2);

echo json_encode([
    'success' => $success,
    'message' => $success 
        ? count($ids) . ' record(s) ' . ($flagValue ? 'reactivated' : 'deactivated') . ' successfully'
        : 'Error updating records'
]);

sqlsrv_close($conn);
?>