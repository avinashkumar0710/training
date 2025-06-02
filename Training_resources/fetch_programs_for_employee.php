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

    $query = "SELECT Program_name 
              FROM [Complaint].[dbo].[training_mast]
              WHERE year = ?";
    $params = array($year);
    $stmt = sqlsrv_query($conn, $query, $params);

    $options = "<option value=''>Select Program</option>";
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $options .= "<option value='{$row['Program_name']}'>{$row['Program_name']}</option>";
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

    echo $options;
}
?>