<?php
// SQL Server connection
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$nature = $_POST['nature'];
$sql = "SELECT Training_Subtype FROM [Complaint].[dbo].[Training_Types] 
        WHERE nature_of_Training = ?";
$params = array($nature);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$options = '<option value="">Select Training Subtype</option>';
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $options .= '<option value="' . htmlspecialchars($row['Training_Subtype']) . '">' . 
               htmlspecialchars($row['Training_Subtype']) . '</option>';
}

echo $options;

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>