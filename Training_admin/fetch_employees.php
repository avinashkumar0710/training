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
    die(print_r(sqlsrv_errors(), true));
}

// Get the plant value from POST data
$data = json_decode(file_get_contents("php://input"), true);
$plant = $data['plant'];

// Prepare SQL query to fetch employee name based on plant
$query = "SELECT emp_name FROM [Complaint].[dbo].[EA_webuser_tstpp] WHERE plant = ?";
$params = array($plant);

// Prepare and execute statement
$stmt = sqlsrv_query($conn, $query, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch the data
if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Prepare response as JSON
    $response = array(
        'emp_name' => $row['emp_name']
    );
    // Output JSON response
    echo json_encode($response);
} else {
    echo json_encode(array()); // Return empty array if no data found
}

// Free statement and close connection
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
