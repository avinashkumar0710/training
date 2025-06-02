<?php
$serverName = "192.168.100.240";
$connectionInfo = array("Database" => "Complaint", "UID" => "sa", "PWD" => "Intranet@123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(json_encode(["error" => "Database connection failed."]));
}

if (isset($_POST['dept_code']) && isset($_POST['plant'])) {
    $dept_code = $_POST['dept_code'];
    $plant = $_POST['plant'];

    $sql = "SELECT empno, name FROM [emp_mas_sap] WHERE dept = ? and location = ? and status='A' order by name";
    $params = array($dept_code, $plant);
    $stmt = sqlsrv_query($conn, $sql, $params);

    $employees = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $employees[] = ["emp_num" => $row["empno"], "emp_name" => $row["name"]];
    }

    echo json_encode($employees);
}
?>