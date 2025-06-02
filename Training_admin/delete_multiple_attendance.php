<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_records'])) {
    $recordIds = $_POST['selected_records'];

    if (is_array($recordIds) && count($recordIds) > 0) {
        foreach ($recordIds as $recordId) {
            $recordId = intval($recordId);
            $query = "UPDATE [Complaint].[dbo].[attendance_records] SET act_Nact_flag = '0' WHERE record_id = ?";
            $stmt = sqlsrv_prepare($conn, $query, [$recordId]);

            if ($stmt) {
                sqlsrv_execute($stmt);
                sqlsrv_free_stmt($stmt);
            }
        }

        echo "<script>alert('Selected records marked as deleted.'); window.location.href='view_attendance.php';</script>";
        exit;
    } else {
        echo "<script>alert('No records selected.'); window.location.href='view_attendance.php';</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='view_attendance.php';</script>";
    exit;
}
?>
