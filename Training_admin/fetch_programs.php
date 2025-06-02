<?php
// Database connection parameters
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);

// Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(json_encode(array("error" => "Database connection failed.")));
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == 'fetch_programs') {
    $query = "SELECT DISTINCT Program_name FROM [Complaint].[dbo].[training_mast]";
    $stmt = sqlsrv_query($conn, $query);
    if ($stmt === false) {
        die(json_encode(array("error" => "Error fetching program names.")));
    }

    $programs = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $programs[] = $row['Program_name'];
    }

    echo json_encode($programs);

} elseif ($action == 'fetch_program_details') {
    $programName = isset($_POST['Program_name']) ? $_POST['Program_name'] : '';
   // error_log("Received Program Name: " . $program_name);

    if (empty($programName)) {
        echo json_encode(array("error" => "Program name is required."));
        exit;
    }

    $query = "SELECT srl_no, faculty, nature_training, year, duration, tentative_date FROM [Complaint].[dbo].[training_mast] WHERE Program_name = ?";
    $stmt = sqlsrv_prepare($conn, $query, array(&$programName));

    if (sqlsrv_execute($stmt)) {
        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $response = array(
                'srl_no' => $row['srl_no'] ?? null,  // Ensure 'srl_no' is set
                'faculty' => $row['faculty'] ?? null,
                'nature_training' => $row['nature_training'] ?? null,
                'year' => $row['year'] ?? null,
                'duration' => $row['duration'] ?? null,
                'tentative_date' => $row['tentative_date'] ?? null
            );
            echo json_encode($response);
        } else {
            echo json_encode(array("error" => "No details found for the selected program."));
        }
    } else {
        echo json_encode(array("error" => "Error fetching program details."));
    }
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
