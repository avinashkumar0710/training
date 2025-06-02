<?php
$serverName = "NSPCL-AD\SQLEXPRESS";
$connectionOptions = array(
    "Database" => "complaint",
    "UID" => "",
    "PWD" => ""
);

// Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    // Get the id and perform the deletion
    $idToDelete = $_POST['idToDelete'];

    // Perform the update query
    $sql = "UPDATE [Complaint].[dbo].[excel] SET flag = 'N' WHERE id = ?";
    $params = array($idToDelete);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    } else {
        // Redirect back to index.php after successful deletion
        header("Location: index.php");
    }
}

// Close the connection
sqlsrv_close($conn);
?>

