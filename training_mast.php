<?php

// Function to handle file upload
function uploadFile($uploadDir, $fileInputName)
{
    $uploadedFilePath = null;

    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $fileName = $_FILES[$fileInputName]['name'];
        $uploadedFilePath = $uploadDir . '/' . $fileName;

        // Move the uploaded file to the destination directory
        move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $uploadedFilePath);
    }

    return $uploadedFilePath;
}

// Connect to SQL Server
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// Handle file upload
$uploadDir = 'uploads'; // Directory to store uploaded files
$fileInputName = 'excelFile'; // Name attribute of the file input in the HTML form
$excelFilePath = uploadFile($uploadDir, $fileInputName);

if ($excelFilePath) {
    // Read the Excel file using basic file handling
    $excelData = [];
    if (($handle = fopen($excelFilePath, 'r')) !== false) {
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $excelData[] = $data;
        }
        fclose($handle);
    }

    // Insert data into SQL Server table
    foreach ($excelData as $row) {
        // Use the $row array to construct your SQL insert statement
        $insertSql = "INSERT INTO [dbo].[training_excel] 
                      ([id], [program_files], [year], [duration], [faculty], [venue]) 
                      VALUES (?, ?, ?, ?, ?, ?)";

        // Execute the prepared statement
        $query = sqlsrv_query($conn, $insertSql, $row);

        if (!$query) {
            die(print_r(sqlsrv_errors(), true));
        }
    }

    // Close the SQL Server connection
    sqlsrv_close($conn);

    echo "Data imported successfully.";
} else {
    echo "File upload failed.";
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Upload and Data Import</title>
</head>
<body>

<form action="" method="post" enctype="multipart/form-data">
    <label for="excelFile">Choose Excel File:</label>
    <input type="file" name="excelFile" id="excelFile" accept=".csv, .xls, .xlsx" required>

    <button type="submit">Upload and Import Data</button>
</form>

</body>
</html>
