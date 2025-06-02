<?php
session_start(); // Start session if not already started

if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
    exit(); // Stop further execution after redirect
}

$sessionemp = $_SESSION["emp_num"];

// Database connection
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(json_encode(["status" => "error", "message" => sqlsrv_errors()]));
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    $id = intval($_POST['id']); // Ensure it's an integer to prevent SQL injection

    $sql1 = "UPDATE Complaint.dbo.training_mast SET flag = 0 WHERE id = ?";
    $sql2 = "UPDATE Complaint.dbo.training_mast_com SET flag = 0 WHERE id = ?"; 

    $params = array($id);

    $stmt1 = sqlsrv_query($conn, $sql1, $params);
    $stmt2 = sqlsrv_query($conn, $sql2, $params);

    if ($stmt1 && $stmt2) {
        echo json_encode(["status" => "success", "message" => "Entry deleted successfully"] );
        // window.parent.closeModal();
        // window.parent.location.reload();
    } else {
        echo json_encode(["status" => "error", "message" => sqlsrv_errors()]);
    }
    
    sqlsrv_close($conn); // Close connection after execution
}
?>
