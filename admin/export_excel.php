<?php 
// Load the database configuration file 
//include_once 'conn.php'; 
$serverName = "NSPCL-AD\SQLEXPRESS";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "",
    "PWD" => ""
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
$fileName = "members-data_" . date('Y-m-d') . ".xls"; 
 
// Column names 
$fields = array('userid', 'Program_name','year','duration', 'tentative_date', 'remarks', 'Device', 'IP_address', 'Make_model', 'IT_sr', 'PC_sr','Issued_date','replace_pc','remarks1', 'remarks2'); 
 
// Display column names as first row 
$excelData = implode("\t", array_values($fields)) . "\n"; 
 
// Fetch records from database 
$query = $conn->query("SELECT * FROM hp_aio ORDER BY userid ASC"); 
if($query->num_rows > 0){ 
    // Output each row of the data 
    while($row = $query->fetch_assoc()){ 
        
        $lineData = array($row['userid'], $row['plant'], $row['empid'], $row['IssuedTo'], $row['dept'], $row['Location'], $row['Device'], $row['IP_address'], $row['Make_model'], $row['IT_sr'], $row['PC_sr'], $row['Issued_date'], $row['replace_pc'], $row['remarks1'],$row['remarks2']); 
        array_walk($lineData, 'filterData'); 
        $excelData .= implode("\t", array_values($lineData)) . "\n"; 
    } 
}else{ 
    $excelData .= 'No records found...'. "\n"; 
} 
 
// Headers for download 
header("Content-Type: application/vnd.ms-excel"); 
header("Content-Disposition: attachment; filename=\"$fileName\""); 
 
// Render excel data 
echo $excelData; 
 
exit;