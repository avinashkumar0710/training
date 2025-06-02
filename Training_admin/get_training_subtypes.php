<?php
// get_training_subtypes.php

// Database connection settings for SQL Server
$serverName = "192.168.100.240"; // Example: "localhost\SQLEXPRESS"
$database = "Complaint";
$username = "sa";
$password = "Intranet@123";

// Get the selected nature of training from the query parameter
$natureOfTraining = isset($_GET['nature']) ? $_GET['nature'] : null;

if ($natureOfTraining === null) {
    // If no nature is provided, return an empty JSON array
    header('Content-Type: application/json');
    echo json_encode([]);
    exit();
}

try {
    // Establishing connection using PDO for SQL Server
    $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL query to fetch training subtypes based on the selected nature
    $sql = "SELECT DISTINCT Training_Subtype
            FROM [Complaint].[dbo].[Training_Types]
            WHERE nature_of_Training = :nature
              AND flag = 1
            ORDER BY Training_Subtype";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nature', $natureOfTraining);
    $stmt->execute();
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