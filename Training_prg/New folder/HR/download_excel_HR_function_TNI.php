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
    if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"'; 
} 

// Excel file name for download 
$fileName = "approved-tni-requests_" . date('Y-m-d') . ".xls"; 

// Column names 
$fields = array('Name','Empno', 'Program Name', 'Nature of Training', 'Year', 'Remarks', 'Duration', 'Tentative Date', 'Hostel Required'); 

// Display column names as first row 
$excelData = implode("\t", array_values($fields)) . "\n"; 

// Fetch records from database
$selected_year = $_POST['year'];
$sql = "SELECT r.empno, r.Program_name, r.nature_training, r.year, r.remarks, r.duration, r.tentative_date, a.name, r.hostel_book
    FROM [Complaint].[dbo].[request_TNI] r 
    JOIN [Complaint].[dbo].[emp_mas_sap] a on r.empno = a.empno  
    WHERE flag = '4' and r.year = '$selected_year'   ORDER BY r.Program_name";

$params = array($selected_year);
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Output each row of the data 
while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){ 
    $lineData = array(
        $row['name'],
        $row['empno'], 
        $row['Program_name'], 
        $row['nature_training'], 
        $row['year'], 
        $row['remarks'], 
        $row['duration'], 
        $row['tentative_date'], // Format date if necessary
        ($row['hostel_book'] == 1 ? 'Yes' : 'No') // Display 'Yes' for 1 and 'No' for 0

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
