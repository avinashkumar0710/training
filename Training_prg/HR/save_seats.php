<?php
header('Content-Type: application/json');

$serverName = "192.168.100.240";
$connectionInfo = array("Database" => "Complaint", "UID" => "sa", "PWD" => "Intranet@123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$seats = $data['seats'] ?? 0;

// Insert new seats configuration (keeps history of all changes)
$sql = "INSERT INTO [Complaint].[dbo].[employee_seats] (available_seats) VALUES (?)";
$params = array($seats);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . print_r(sqlsrv_errors(), true)]);
}

sqlsrv_close($conn);
?>