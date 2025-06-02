<?php
$serverName = "192.168.100.240";
$connectionInfo = array("Database" => "Complaint", "UID" => "sa", "PWD" => "Intranet@123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(json_encode(["error" => "Database connection failed."]));
}

if (isset($_POST['plant'])) {
    $plant = $_POST['plant'];

    $sql = "SELECT Distinct Dept FROM [emp_mas_sap] WHERE location = ? order by Dept";
    $params = array($plant);
    $stmt = sqlsrv_query($conn, $sql, $params);

    $departments = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $departments[] = ["id" => $row["Dept"],"name" => $row["Dept"]];
    }

    echo json_encode($departments);
}
?>
