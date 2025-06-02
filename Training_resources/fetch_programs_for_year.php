<?php
if (isset($_POST['year'])) {
    $year = $_POST['year'];

    $serverName = "192.168.100.240";
    $connectionOptions = array(
        "Database" => "Complaint",
        "UID" => "sa",
    "PWD" => "Intranet@123"
    );
    $conn = sqlsrv_connect($serverName, $connectionOptions);

    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $query = "SELECT distinct PROGRAM_NAME 
              FROM [Complaint].[dbo].[training_mast] 
              WHERE YEAR = ?";
    $params = array($year);
    $stmt = sqlsrv_query($conn, $query, $params);

    $options = "<option value=''>Select Program</option>";
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $options .= "<option value='{$row['id']}'>{$row['PROGRAM_NAME']}</option>";
    }

    echo $options;

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
}
?>
