<?php

$serverName = "192.168.100.240"; // Note: double backslashes to escape the backslash in the server name
$connectionInfo = array( "Database"=>"Complaint");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn) {
   echo "Connection established.<br />";
} else {
     echo "Connection could not be established.<br />";
     die( print_r( sqlsrv_errors(), true));
}

sqlsrv_close($conn);

?>
