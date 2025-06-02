<?php
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
    exit();
}

$sessionemp = $_SESSION["emp_num"] ?? '';

// Database Connection
$serverName = "192.168.100.240";
$connectionInfo = [
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Get filters from POST request
$selectedProgram = isset($_POST['selected_program']) ? $_POST['selected_program'] : '';
$location = isset($_POST['location']) ? $_POST['location'] : '';

// Function to clean data for Excel
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
} 

// File name for download
$fileName = "Over_All_Approval_Training_Program_" . date('Y-m-d') . ".xls"; 

// Column names
$fields = ['Serial No', 'Name', 'Employee Number', 'Program Name', 'Nature of Training', 'Year', 'Remarks', 'Duration', 'Plant', 'Hostel Required', 'Status']; 

// Display column names as first row
$excelData = implode("\t", array_values($fields)) . "\n"; 

// Initialize serial number counter
$serialNo = 1;

// SQL Query with filters
$sql = "SELECT 
            r.id, 
            r.PROGRAM_NAME, 
            r.year, 
            r.tentative_date,
            r.nature_training,
            r.duration,
            e.loc_desc,
            e.name, 
            e.empno, 
            r.remarks, 
            r.hostel_book,
            r.flag,
            e.location
        FROM 
            [Complaint].[dbo].[request] r
        JOIN 
            [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
        WHERE 
            r.flag = '7'";

$params = [];

if (!empty($selectedProgram)) {
    $sql .= " AND r.PROGRAM_NAME = ?";
    $params[] = $selectedProgram;
}

if (!empty($location) && $location !== 'NS04') {
    $sql .= " AND e.location = ?";
    $params[] = $location;
}

$sql .= " ORDER BY r.id DESC";

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Output each row of the data
while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){ 
    $lineData = [
        $serialNo++, // Serial number
        $row['name'],
        $row['empno'], 
        $row['PROGRAM_NAME'], 
        $row['nature_training'], 
        $row['year'], 
        $row['remarks'], 
        $row['duration'], 
        $row['loc_desc'],
        ($row['hostel_book'] == 2 ? 'Yes' : 'No'), // Display 'Yes' for 2 and 'No' otherwise
        ($row['flag'] == 7 ? 'Overall Approved' : 'Error')
    ]; 
    array_walk($lineData, 'filterData'); 
    $excelData .= implode("\t", array_values($lineData)) . "\n"; 
} 

// Headers for download
header("Content-Type: application/vnd.ms-excel"); 
header("Content-Disposition: attachment; filename=\"$fileName\""); 

// Render excel data
echo $excelData; 

exit;
?>
