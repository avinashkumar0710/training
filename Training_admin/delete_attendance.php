<?php
if (!isset($_GET['record_id'])) {
    die("Invalid request.");
}

$recordId = $_GET['record_id'];

// Database connection
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

// Update query for soft delete
$sql = "UPDATE [Complaint].[dbo].[attendance_records] SET act_Nact_flag = '0' WHERE record_id = ?";
$params = array($recordId);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Close resources
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>

<!-- JavaScript for alert and redirect -->
<script>
    alert("Record deleted successfully.");
    window.location.href = "edit.php"; // redirect to edit.php after alert
</script>
