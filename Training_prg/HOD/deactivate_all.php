<?php
// Database connection
$serverName = "192.168.100.240";
$connectionInfo = array("Database" => "Complaint", "UID" => "sa", "PWD" => "Intranet@123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    $error = "Database connection failed: " . print_r(sqlsrv_errors(), true);
    echo "<script>alert('$error'); window.location.href='upload_External_trg_calender.php';</script>";
    exit();
}

// Update all active files to flag=0
$updateSql = "UPDATE [Complaint].[dbo].[upload_External_trg_calender] SET flag = 0 WHERE flag = 1";
$updateStmt = sqlsrv_query($conn, $updateSql);

if ($updateStmt) {
    $message = "All files have been successfully deactivated!";
} else {
    $message = "Error deactivating files: " . print_r(sqlsrv_errors(), true);
}

sqlsrv_close($conn);

// Show alert and redirect back
echo "<script>
    alert('$message');
    window.location.href = 'upload_External_trg_calender.php';
</script>";
exit();
?>