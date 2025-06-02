<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'fetch_program_details') {
    $programId = $_POST['program_id'];

    $conn = sqlsrv_connect("192.168.100.240", array(
        "Database" => "Complaint",
        "UID" => "sa",
        "PWD" => "Intranet@123"
    ));

    if ($conn === false) {
        echo json_encode(["error" => "Connection failed"]);
        exit;
    }

    $sql = "SELECT Program_name, duration, Faculty FROM [Complaint].[dbo].[attendance_records] WHERE program_id = ?";
    $stmt = sqlsrv_query($conn, $sql, array($programId));

    if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo json_encode([
            "Program_name" => $row['Program_name'],
            "program_duration" => $row['duration'],
            "faculty" => $row['Faculty']
        ]);
    } else {
        echo json_encode(["error" => "No data found"]);
    }

    sqlsrv_close($conn);
}
?>
