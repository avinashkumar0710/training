<?php
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);

// Establishes the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $program_files = $_FILES["program_files"];
    $year = $_POST["year"];
    
    // Get current date and IP address
    $uploaded_date = date("Y-m-d H:i:s");
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // File upload handling
    $targetDirectory = "uploads/";
    $targetFile = $targetDirectory . basename($program_files["name"]);

    // Move the uploaded file to the specified directory
    if (move_uploaded_file($program_files["tmp_name"], $targetFile)) {
        // File uploaded successfully, now insert data into the database
        $sql = "INSERT INTO [Complaint].[dbo].[excel] (program_files, year, uploaded_date, ip_address) VALUES (?, ?, ?, ?)";
        $params = array($targetFile, $year, $uploaded_date, $ip_address);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        } else {
            echo "Data inserted successfully!";
        }
    } else {
        echo "Error uploading file.";
    }
}

// Close the connection
sqlsrv_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Data</title>
</head>
<body>
    <h2>Insert Data</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
        <label for="program_files">Program Files:</label>
        <input type="file" name="program_files" required><br>

        <label for="year">Year:</label>
        <select name="year" required>
            <option value="2023">2023</option>
            <option value="2024">2024</option>
        </select><br>

        <input type="submit" value="Insert Data">
    </form>
</body>
</html>
