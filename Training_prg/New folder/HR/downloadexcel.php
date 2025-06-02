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

$location = isset($_POST['location']) ? $_POST['location'] : '';
// Filter the excel data 
function filterData(&$str){ 
    $str = preg_replace("/\t/", "\\t", $str); 
    $str = preg_replace("/\r?\n/", "\\n", $str); 
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
} 

//$selectedProgram = $_GET['program'];

// Excel file name for download 
$fileName = "Over_All_Approval_Training_Program" . date('Y-m-d') . ".xls"; 

// Column names 
$fields = array('Serial No', 'Name', 'Employee Number', 'Program Name', 'Nature of Training', 'Year', 'Remarks', 'Duration', 'Plant', 'Hostel Required', 'Status'); 

// Display column names as first row 
$excelData = implode("\t", array_values($fields)) . "\n"; 

// Initialize serial number counter
$serialNo = 1;

// Fetch all records from database
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
            r.flag = '8'  
        ORDER BY 
            r.id DESC";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Output each row of the data 
while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){ 
    // Check if tentative_date is not empty or null
    //$tentativeDate = !empty($row['tentative_date']) ? $row['tentative_date']->format('Y-m-d') : '';
    
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
        //$tentativeDate, // Tentative date
        ($row['hostel_book'] == 2 ? 'Yes' : 'No'),// Display 'Yes' for 1 and 'No' for 0
        ($row['flag'] == 8 ? 'Overall Approved' : 'Error')
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
