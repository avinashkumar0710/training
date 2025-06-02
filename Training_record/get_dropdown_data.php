<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["emp_num"])) {
    echo json_encode([]);
    exit;
}

$serverName = "192.168.100.240";
$connectionOptions = [
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    echo json_encode([]);
    exit;
}

$data = [];

// Fetch distinct employee numbers
$sqlEmpno = "SELECT DISTINCT empno FROM [Complaint].[dbo].[emp_mas_sap] ORDER BY empno";
$stmtEmpno = sqlsrv_query($conn, $sqlEmpno);

$data['empno'] = [];

while ($row = sqlsrv_fetch_array($stmtEmpno, SQLSRV_FETCH_ASSOC)) {
    $empno = $row['empno'];

    if (strlen($empno) == 8 && substr($empno, 0, 2) === '00') {
        $empno = substr($empno, 2); // Remove first two characters '00'
    }

    $data['empno'][] = $empno;
}

sqlsrv_free_stmt($stmtEmpno);


// Fetch distinct grades
$sqlGrade = " SELECT distinct grade
FROM [Complaint].[dbo].[emp_mas_sap] ";
$stmtGrade = sqlsrv_query($conn, $sqlGrade);
$data['grade'] = [];
while ($row = sqlsrv_fetch_array($stmtGrade, SQLSRV_FETCH_ASSOC)) {
    $data['grade'][] = trim($row['grade']); // Trim to remove potential whitespace
}
sqlsrv_free_stmt($stmtGrade);

// Fetch distinct departments
$sqlDept = "SELECT DISTINCT dept FROM [Complaint].[dbo].[emp_mas_sap] ORDER BY dept";
$stmtDept = sqlsrv_query($conn, $sqlDept);
$data['dept'] = [];
while ($row = sqlsrv_fetch_array($stmtDept, SQLSRV_FETCH_ASSOC)) {
    $data['dept'][] = trim($row['dept']); // Trim to remove potential whitespace
}
sqlsrv_free_stmt($stmtDept);

// Fetch distinct plants with mapping from loc_desc
$sqlPlant = "SELECT DISTINCT loc_desc FROM [Complaint].[dbo].[emp_mas_sap] ORDER BY loc_desc";
$stmtPlant = sqlsrv_query($conn, $sqlPlant);
$data['plant'] = [];
$plantMapping = [
    'NS04' => 'Bhilai',
    'NS03' => 'Rourkela',
    'NS02' => 'Durgapur',
    'NS01' => 'Corporate Center'
];
while ($row = sqlsrv_fetch_array($stmtPlant, SQLSRV_FETCH_ASSOC)) {
    $locDesc = trim($row['loc_desc']);
    if (isset($plantMapping[$locDesc])) {
        $data['plant'][] = $plantMapping[$locDesc];
    } elseif (in_array($locDesc, $plantMapping)) {
        // If the loc_desc is already a mapped value, use it directly
        $data['plant'][] = $locDesc;
    }
}
// Remove duplicate plant names and sort
$data['plant'] = array_unique($data['plant']);
sort($data['plant']);

sqlsrv_free_stmt($stmtPlant);

// Fetch distinct durations

$data['duration'] = [];
for ($i = 0; $i <= 20; $i++) {
    $data['duration'][] = $i;
}

sqlsrv_close($conn);

echo json_encode($data);
?>