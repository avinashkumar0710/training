<?php
// Database connection
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

// Check if the 'id' parameter is provided
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // SQL query to delete the link
    $sql = "update [Complaint].[dbo].[link_tracking] set flag='D' WHERE id = ?";
    $params = array($id);
    
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    } else {
        echo "Link deleted successfully.";
    }

    // Free the statement and close the connection
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
} else {
    echo "No link ID provided.";
}
?>
