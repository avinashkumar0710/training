<?php
$serverName = "NSPCL-AD\SQLEXPRESS";
$connectionOptions = array(
    "Database" => "complaint",
    "UID" => "",
    "PWD" => ""
);

// Establish the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// Initialize an empty array to store the results
$results = array();

// Check if the search query is set in the request
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];

    // Log the searchQuery to a dedicated log file
    $logFile = 'search_query_log.txt';
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $searchQuery . PHP_EOL, FILE_APPEND);
    // Log the searchQuery to the PHP error log
    error_log('Search Query: ' . $searchQuery);

    // Fetch data based on the exact match of the search query
   $sql = "SELECT * FROM [Complaint].[dbo].[training_mast] WHERE Program_name LIKE ?";
    $params = array("%$searchQuery%");
} else {
    // Fetch all data if the search query is not set
    $sql = "SELECT * FROM [Complaint].[dbo].[training_mast]";
    $params = array();
}

// Execute the query
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch data into an associative array
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $results[] = $row;
}

// Close the connection
sqlsrv_close($conn);

// Return the results as JSON
echo json_encode($results);
?>
