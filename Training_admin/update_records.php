<?php
// Database Connection
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Check if records are selected for update
if (isset($_POST['update_selected']) && !empty($_POST['selected_records'])) {
    foreach ($_POST['selected_records'] as $record_id) {
        // Get updated values
        $attendance = $_POST['attendance'][$record_id];
        $new_faculty = $_POST['faculty'][$record_id];
        $new_mandays = $_POST['mandays'][$record_id];

        // Ensure total_attendance matches mandays
        $new_total_attendance = $new_mandays;

        // Update Query (removes flag update)
        $update_sql = "UPDATE [Complaint].[dbo].[attendance_records]
                       SET attendance = ?, faculty = ?, mandays = ?, total_attendance = ?
                       WHERE record_id = ?";
        $params = array($attendance, $new_faculty, $new_mandays, $new_total_attendance, $record_id);
        $update_stmt = sqlsrv_query($conn, $update_sql, $params);
        
        if ($update_stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    }
    echo "<script>alert('✅ Selected records updated successfully!'); window.location='edit_future_programs.php';</script>";
} else {
    echo "<script>alert('⚠️ No records selected for update.'); window.location='edit_future_programs.php';</script>";
}

// Close connection
sqlsrv_close($conn);
?>
