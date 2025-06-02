<?php
// Include your database connection here
// Example:
// include('db_connection.php');
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empno = $_POST['empno'];
    $action = $_POST['action'];

  // Remove leading '00' if the employee number is 8 digits and starts with '00'
  if (strlen($empno) == 8 && substr($empno, 0, 2) === '00') {
    $empno = substr($empno, 2); // Remove the first two characters
}     

    // Determine the access value based on the action
    $access = ($action === 'activate') ? 1 : 0;

    // SQL query to insert or update the employee's status
    $sql = "IF EXISTS (SELECT 1 FROM [Complaint].[dbo].[Training_HR_User] WHERE empno = ?)
            BEGIN
                UPDATE [Complaint].[dbo].[Training_HR_User]
                SET access = ?
                WHERE empno = ?;
            END
            ELSE
            BEGIN
                INSERT INTO [Complaint].[dbo].[Training_HR_User] (empno, access)
                VALUES (?, ?);
            END";

    // Prepare and execute the SQL statement
   // Prepare and execute the SQL statement
    $params = array($empno, $access, $empno, $empno, $access);
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    } else {
        echo "Employee status has been successfully updated!";
    }

    // Redirect back to the original page (you can specify your original page here)
    //header("Location: permissionaccess.php");
    exit();
}
?>
