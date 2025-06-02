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
}

$id = $_POST['id'];
$nature = $_POST['nature'];
$subtype = $_POST['subtype'];

$sql = "INSERT INTO [Complaint].[dbo].[Training_Types] (id, nature_of_Training, Training_Subtype, flag)
        VALUES (?, ?, ?, 1)";
$params = array($id, $nature, $subtype);

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo "Insert failed: " . print_r(sqlsrv_errors(), true);
} else {
    echo "Record added successfully";
}
?>
