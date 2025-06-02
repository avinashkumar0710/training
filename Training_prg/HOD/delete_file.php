<?php
// Database connection
$serverName = "192.168.100.240";
$connectionInfo = array("Database" => "Complaint", "UID" => "sa", "PWD" => "Intranet@123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // First get file path
    $sql = "SELECT pdf_file_path FROM [Complaint].[dbo].[upload_External_trg_calender] WHERE id = ?";
    $stmt = sqlsrv_query($conn, $sql, array($id));
    
    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $filePath = $row['pdf_file_path'];
        
        // Delete from database
        $deleteSql = "UPDATE [Complaint].[dbo].[upload_External_trg_calender] SET flag = 0 WHERE id = ?";
        $deleteStmt = sqlsrv_query($conn, $deleteSql, array($id));
        
        if ($deleteStmt) {
            // Delete physical file
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            header("Location: upload_External_trg_calender.php?success=File deleted successfully");
        } else {
            header("Location: upload_External_trg_calender.php?error=Database delete failed");
        }
    } else {
        header("Location: upload_External_trg_calender.php?error=File not found in database");
    }
} else {
    header("Location: upload_External_trg_calender.php?error=Invalid request");
}

sqlsrv_close($conn);
exit();
?>