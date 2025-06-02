<?php
// get_training_types.php

// Database connection settings for SQL Server
$serverName = "192.168.100.240"; // Example: "localhost\SQLEXPRESS"
$database = "Complaint";
$username = "sa";
$password = "Intranet@123";

try {
    // Establishing connection using PDO for SQL Server
    $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to fetch nature of training values
    $sql = "SELECT DISTINCT nature_of_Training FROM [Complaint].[dbo].[Training_Types] WHERE flag = 1 ORDER BY nature_of_Training";
    $stmt = $conn->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set the content type to JSON
    header('Content-Type: application/json');

    // Encode the results as JSON and output them
    echo json_encode($results);

} catch (PDOException $e) {
    // Handle database errors
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

// Close connection
$conn = null;
?>