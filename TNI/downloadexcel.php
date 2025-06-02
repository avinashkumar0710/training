<?php
// Your database connection code goes here
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

// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if (strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
}

$selectedProgram = isset($_POST['selected_program']) ? $_POST['selected_program'] : 'all';
$fileName = "Over_All_Approval_" . date('Y-m-d') . ".xls"; 

// Column names 
$fields = array('Serial No', 'Name', 'Employee Number', 'Program Name', 'Nature of Training', 'Year', 'Remarks', 'Duration', 'Plant', 'Hostel Required', 'Status'); 

// Display column names as first row 
$excelData = implode("\t", array_values($fields)) . "\n"; 

// Initialize serial number counter
$serialNo = 1;

// Fetch records from database
if ($selectedProgram === 'all') {
    $sql = "SELECT r.id, r.PROGRAM_NAME, r.year, r.tentative_date, r.nature_training, r.duration, e.loc_desc, e.name, e.empno, r.remarks, r.hostel_book, r.flag
            FROM  [Complaint].[dbo].[request_TNI] r  
            JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno 
            ORDER BY r.id DESC";
    $params = array();
} else {
    $sql = "SELECT r.id, r.PROGRAM_NAME, r.year, r.tentative_date, r.nature_training, r.duration, e.loc_desc, e.name, e.empno, r.remarks, r.hostel_book, r.flag
            FROM  [Complaint].[dbo].[request_TNI] r  
            JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno 
            WHERE r.flag = ? 
            ORDER BY r.id DESC";
    $params = array($selectedProgram);
}

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Function to determine the status based on the flag value
function getStatus($flag) {
    switch ($flag) {
        case 0:
            return 'Pending at Reporting Officer';
        case 1:
            return 'Reject by Reporting Officer';
        case 2:
            return 'Pending at HOD';
        case 3:
            return 'Reject by HOD';
        case 4:
            return 'TNI Approved';
        default:
            return 'Unknown';
    }
}

// Output each row of the data 
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { 
    $status = getStatus($row['flag']); // Get status based on the flag
    
    $lineData = array(
        $serialNo++, // Serial number
        $row['name'],
        $row['empno'], 
        $row['PROGRAM_NAME'], 
        $row['nature_training'], 
        $row['year'], 
        $row['remarks'], 
        $row['duration'], 
        $row['loc_desc'],
        ($row['hostel_book'] == 1 ? 'Yes' : 'No'), // Display 'Yes' for 1 and 'No' for 0
        $status // Use the status obtained from getStatus function
    ); 
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
