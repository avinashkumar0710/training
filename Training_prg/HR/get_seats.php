<?php
header('Content-Type: application/json');

$serverName = "192.168.100.240";
$connectionInfo = array("Database" => "Complaint", "UID" => "sa", "PWD" => "Intranet@123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Get the most recent seats configuration
$sql = "SELECT TOP 1 available_seats as seats 
        FROM [Complaint].[dbo].[employee_seats] 
        ORDER BY created_at DESC";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt && sqlsrv_fetch($stmt)) {
    $seats = sqlsrv_get_field($stmt, 0);
    echo json_encode(['seats' => $seats]);
} else {
    echo json_encode(['seats' => null]);
}

sqlsrv_close($conn);
?>