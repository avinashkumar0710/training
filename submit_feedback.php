<?php
// Establish connection to the database
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

// Check if connection is established
if ($conn === false) {
    die( print_r( sqlsrv_errors(), true));
}

// Retrieve form data
$empNo = $_POST['empNo'];
$plant = $_POST['plant']; // Changed from location to plant
$feedback = $_POST['feedback'];

echo '<br>' .$empNo ;
echo '<br>' .$plant ;
echo '<br>' .$feedback ;

// SQL query to insert data into training_feedback table
$sql = "INSERT INTO training_feedback (empno, plant, Feedback) VALUES (?, ?, ?)";
$params = array($empNo, $plant, $feedback);
$stmt = sqlsrv_query($conn, $sql, $params);

// Check if the query executed successfully
if ($stmt === false) {
    echo "Error: " . print_r(sqlsrv_errors(), true); // Print detailed error information
} else {
    echo "Feedback submitted successfully.";
}

// Close the connection
sqlsrv_close($conn);
?>
