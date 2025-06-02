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
} // include your DB connection logic

$sql = "SELECT unique_id, id, nature_of_Training, Training_Subtype 
        FROM [Complaint].[dbo].[Training_Types] 
        WHERE flag = 1 
        ORDER BY id";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $uniqueId = $row['unique_id'];
echo "<tr>

<td>
<input type='hidden' id='id_$uniqueId' value='{$row['unique_id']}' />
<input type='text' value='{$row['id']}'  />
</td>

   
    <td><input type='text' id='nature_$uniqueId' value=\"" . htmlspecialchars($row['nature_of_Training']) . "\" /></td>
    <td><input type='text' id='subtype_$uniqueId' value=\"" . htmlspecialchars($row['Training_Subtype']) . "\" /></td>
    <td><button class='btn btn-primary' onclick=\"updateRow('$uniqueId')\">Update</button></td>
    <td><button class='btn btn-danger' onclick=\"deleteRow('$uniqueId')\">Delete</button></td>
</tr>";

}

?>
