<?php
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
    exit(); // Ensures script stops execution after redirect
}

$sessionemp = $_SESSION["emp_num"];

// Add '00' in front if session value has only 6 digits
if (strlen($sessionemp) == 6) {
    $sessionemp = '00' . $sessionemp;
}

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

if (isset($_POST['expiredIds']) && !empty($_POST['expiredIds'])) {
    $expiredIds = array_map('intval', $_POST['expiredIds']);
    $expiredIds = implode(',', $expiredIds);

    // Update the expired records
    $sql = "UPDATE [Complaint].[dbo].[request] 
            SET flag = '000', 
                aprroved_time = GETDATE(), 
                appr_empno = ?
            WHERE id IN ($expiredIds)";

    $params = array($sessionemp);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    } else {
        echo "<script>alert('Expired records processed successfully.');window.location.href = 'buh_approval.php';</script>";
    }
} else {
    echo "No expired records selected.";
}

sqlsrv_close($conn);
?>
