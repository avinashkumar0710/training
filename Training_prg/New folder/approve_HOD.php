<?php 
// Start a new session
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
}

$hodempno = $_SESSION["emp_num"];
 echo ''.$hodempno;

// Ensure $hodempno has 8 digits (prepend 00 if it starts with 0)
$hodempno = str_pad($hodempno, 8, "0", STR_PAD_LEFT);
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selectedIds']) && !empty($_POST['selectedIds'])) {
    $selectedIds = $_POST['selectedIds'];
    $empNo = $_SESSION['emp_num'];  // Assuming emp_num is stored in the session
    echo '<pre>';
    print_r($selectedIds);
    echo '</pre>';

    // Connect to the database
    $serverName = "192.168.100.240";
    $connectionInfo = array(
        "Database" => "complaint",
        "UID" => "sa",
        "PWD" => "Intranet@123"  // Add your password if necessary
    );

    $conn = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Loop through each selected ID
    foreach ($selectedIds as $id) {
        // Validate and sanitize the input as needed
        $id = filter_var($id, FILTER_SANITIZE_NUMBER_INT);
echo 'hello' .$id;
        // Update query
        $sql = "UPDATE [Complaint].[dbo].[request] set flag = '4', aprroved_time = GETDATE(), appr_empno = '$hodempno' WHERE id = '$id'";
        $params = array($hodempno, $id);
        $stmt = sqlsrv_query($conn, $sql, $params);

        // Check if the query was successful
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    }

    // Close the database connection
    sqlsrv_close($conn);

    echo '<script>alert("Form submitted successfully!"); window.location.href = "Training_HOD.php";</script>';
} else {
    // Handle the case where no checkboxes were selected
    //echo '<script>alert("Please select at least one TNI request to approve."); window.location.href = "TNI_approval.php";</script>';
}
?>
