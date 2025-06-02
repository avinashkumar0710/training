<?php
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);           

$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Check if any checkboxes are selected
if(isset($_POST['selectedIds']) && !empty($_POST['selectedIds'])) {
    // Sanitize the input to prevent SQL injection
    $selectedIds = array_map('intval', $_POST['selectedIds']);
    $selectedIds = implode(',', $selectedIds);

    // Update the records in the database
    $sql = "UPDATE [Complaint].[dbo].[request] SET flag = '6' WHERE id IN ($selectedIds)";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    } else {
        //echo "Selected records updated successfully.";
        echo "<script>alert('Selected records updated successfully.');window.location.href = 'buh_approval.php';</script>";
    }
} else {
    echo "No records selected for update.";
}

// Close the database connection
sqlsrv_close($conn);
?>
