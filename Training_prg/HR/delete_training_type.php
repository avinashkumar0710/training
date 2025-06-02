<?php
ob_start(); // Start output buffering
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp= $_SESSION["emp_num"];
    //echo 'empno' .$sessionemp;

    // Add '00' in front if session value has only 6 digits
    if(strlen($sessionemp) == 6) {
        $sessionemp = '00' . $sessionemp;
    }
    //echo 'empno' .$sessionemp;

  // Database connection
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

// Check if the connection failed
if ($conn === false) {
    die("Connection Error: " . print_r(sqlsrv_errors(), true));
} // your DB connection file

// Sanitize and retrieve POST data
$unique_id = $_POST['unique_id'];  // ✅ Make sure there's no space in the key
$nature = $_POST['nature'];
$subtype = $_POST['subtype'];

// Validate input (optional but recommended)
if (empty($unique_id) || empty($nature) || empty($subtype)) {
    echo "All fields are required.";
    exit;
}

// SQL to update
$sql = "UPDATE [Complaint].[dbo].[Training_Types]
SET flag = 0
WHERE unique_id = ?";

$params = array($unique_id);

// Execute query
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo "Update failed: ";
    print_r(sqlsrv_errors());
} else {
    echo "Record Deleted successfully";
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
