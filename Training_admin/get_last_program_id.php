<?php
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "Complaint",
    "Uid" => "sa",
    "PWD" => "Intranet@123"
);

$conn = sqlsrv_connect($serverName, $connectionOptions);
$lastProgramId = 20251001; // Default if no data found

if ($conn) {
    $sql = "SELECT TOP 1 program_id FROM [Complaint].[dbo].[attendance_records] WHERE flag = '2' ORDER BY program_id DESC";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $lastProgramId = intval($row['program_id']) + 1;
    }

    echo json_encode(['last_id' => $lastProgramId]);
} else {
    echo json_encode(['last_id' => $lastProgramId]); // fallback
}
?>
