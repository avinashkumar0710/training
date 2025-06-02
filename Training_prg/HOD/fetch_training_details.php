<?php
// Database connection
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// Get the srl_no from the request
$srl_no = $_GET['srl_no'];

// Fetch training details from the database
$query = "SELECT faculty, Program_name, nature_training, year, remarks, duration, tentative_date, target_group 
          FROM [Complaint].[dbo].[training_mast] 
          WHERE srl_no = ?";
$params = array($srl_no);
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch the result as an associative array
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// Return the result as JSON
header('Content-Type: application/json');
echo json_encode($row);

// Close the connection
sqlsrv_close($conn);
?>