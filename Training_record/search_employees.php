<?php
// Database Connection
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);           
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Get the search term from the AJAX request
$term = $_GET['term'];

// Fetch Employee Data
$sql = "SELECT DISTINCT ar.EMPNO, ea.emp_name
        FROM [Complaint].[dbo].[attendance_records] AS ar
        JOIN [Complaint].[dbo].[EA_webuser_tstpp] AS ea ON ar.EMPNO = ea.emp_num
        WHERE ea.emp_name LIKE ?";
$params = array("%$term%");
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$employees = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $employees[] = [
        'label' => $row['emp_name'] . ' (' . $row['EMPNO'] . ')', // Display text
        'value' => $row['emp_name'], // Value to insert into the input field
        'empno' => $row['EMPNO'] // Employee number for redirection
    ];
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($employees);
?>