<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $link_id = $_POST['link_id'];
    $close_time = $_POST['close_time'];

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

    $sql = "UPDATE [Complaint].[dbo].[link_show] SET [close_time] = ? WHERE [user_id] = ? AND [link_id] = ? AND [close_time] IS NULL";
    $params = array($close_time, $user_id, $link_id);

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
}
?>
