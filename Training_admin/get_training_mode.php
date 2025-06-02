<?php
$serverName = "192.168.100.240";
$connectionInfo = array("Database" => "Complaint", "UID" => "sa", "PWD" => "Intranet@123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(json_encode(["error" => "Database connection failed."]));
}

$sql = "SELECT DISTINCT nature_of_Training FROM [Complaint].[dbo].[Training_Types] WHERE flag = 1 ORDER BY nature_of_Training";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(json_encode(["error" => "Error fetching training types."]));
}

$trainingTypes = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $trainingTypes[] = $row['nature_of_Training'];
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);

echo json_encode($trainingTypes);
?>