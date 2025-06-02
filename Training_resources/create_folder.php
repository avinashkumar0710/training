<?php
if (isset($_POST['empno']) && isset($_POST['program_id'])) {
    $empno = $_POST['empno'];
    $programId = $_POST['program_id'];

    // Fetch the program name from the database
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

    $query = "SELECT Program_name FROM [Complaint].[dbo].[request] WHERE id = ?";
    $params = array($programId);
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $programName = '';
    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $programName = $row['Program_name'];
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

    // Create directories
    $empnoDir = "uploads/" . $empno;
    $programDir = $empnoDir . "/" . $programName;

    if (!file_exists($empnoDir)) {
        mkdir($empnoDir, 0777, true);
    }

    if (!file_exists($programDir)) {
        mkdir($programDir, 0777, true);
        echo "Folder created successfully.";
    } else {
        echo "Folder already exists.";
    }
} else {
    echo "Employee number or program ID not provided.";
}
?>
