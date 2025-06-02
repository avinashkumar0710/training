<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
}

$hodempno = $_SESSION["emp_num"];
echo 'HOD Emp No: ' . $hodempno;

// Ensure $hodempno has 8 digits (prepend "00" if needed)
$hodempno = str_pad($hodempno, 8, "0", STR_PAD_LEFT);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selectedIds']) && !empty($_POST['selectedIds'])) {
    $selectedIds = $_POST['selectedIds'];

    echo '<pre>';
    print_r($selectedIds);  // Debugging - check if data is correct
    echo '</pre>';

    // Database Connection
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

    // Loop through selected IDs and update flag
    foreach ($selectedIds as $id => $flag) {  // Use key as ID, value as flag
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
        $flag = filter_var($flag, FILTER_SANITIZE_NUMBER_INT);

        if ($id && ($flag == 3 || $flag == 4)) {  // Ensure valid values
            $sql = "UPDATE [Complaint].[dbo].[request] 
            SET flag = ?, aprroved_time = GETDATE(), appr_empno = ? 
            WHERE id = ?";
            $params = array($flag, $hodempno, $id);
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
        }
    }

    sqlsrv_close($conn);

    echo '<script>alert("Form submitted successfully!"); window.location.href = "Training_HOD.php";</script>';
} else {
    echo '<script>alert("Please select at least one request."); window.location.href = "Training_HOD.php";</script>';
}
?>
