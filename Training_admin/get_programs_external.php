<?php
// Replace with your actual database connection details
$serverName = "192.168.100.240";
$database = "Complaint";
$uid = "sa";
$pwd = "Intranet@123";

try {
    $conn = sqlsrv_connect($serverName, array("Database" => $database, "UID" => $uid, "PWD" => $pwd));
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $sql = "SELECT DISTINCT program_id, program_name FROM [Complaint].[dbo].[attendance_records] where flag='3'";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $programs = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $programs[] = array(
            'program_id' => $row['program_id'],
            'program_name' => $row['program_name']
        );
    }

    header('Content-Type: application/json');
    echo json_encode($programs);

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(array('error' => $e->getMessage()));
}
?>