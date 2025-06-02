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

// Step 1: Fetch location from emp_mas_sap based on sessionemp
$sqlLocation = "SELECT location FROM emp_mas_sap WHERE empno = ?";
$params = array($sessionemp);
$stmtLocation = sqlsrv_query($conn, $sqlLocation, $params);

if ($stmtLocation === false) {
    die(print_r(sqlsrv_errors(), true));
}

$rowLocation = sqlsrv_fetch_array($stmtLocation, SQLSRV_FETCH_ASSOC);
if (!$rowLocation) {
    die("No location found for the employee.");
}

$location = $rowLocation['location'];

// Step 2: Fetch empno, name, and design from emp_mas_sap based on location
$sqlEmployees = "SELECT empno, name, design 
                 FROM [Complaint].[dbo].[emp_mas_sap] 
                 WHERE location = ? 
                 AND (design LIKE '%BUH%' OR design LIKE '%Chief%') 
                 AND status = 'A'";
$paramsEmployees = array($location);
$stmtEmployees = sqlsrv_query($conn, $sqlEmployees, $paramsEmployees);

if ($stmtEmployees === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch and display the results
while ($rowEmployee = sqlsrv_fetch_array($stmtEmployees, SQLSRV_FETCH_ASSOC)) {
    echo "Empno: " . $rowEmployee['empno'] . " - Name: " . $rowEmployee['name'] . " - Designation: " . $rowEmployee['design'] . "<br>";
    
    // Store the empno of the last fetched employee (or use logic to determine the correct empno)
    $empno = $rowEmployee['empno'];
}


// Check if any checkboxes are selected
if(isset($_POST['selectedIds']) && !empty($_POST['selectedIds'])) {
    // Sanitize the input to prevent SQL injection
    $selectedIds = array_map('intval', $_POST['selectedIds']);
    $selectedIds = implode(',', $selectedIds);

    // Update the records in the database
    $sql = "UPDATE [Complaint].[dbo].[request] 
            SET flag = '6', 
                aprroved_time = GETDATE(), 
                appr_empno = ?
            WHERE id IN ($selectedIds)";

            $params = array($empno);
    $stmt = sqlsrv_query($conn, $sql, $params);

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