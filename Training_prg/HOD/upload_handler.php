<?php
// Database connection
$serverName = "192.168.100.240";
$connectionInfo = array("Database" => "Complaint", "UID" => "sa", "PWD" => "Intranet@123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// File upload directory - adjust this path as needed
$uploadDir = "uploads/external_training/";

// Create directory if it doesn't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$uploadedFiles = [];

// Process each uploaded file
foreach ($_FILES['pdfFiles']['tmp_name'] as $key => $tmpName) {
    $fileName = $_FILES['pdfFiles']['name'][$key];
    $fileSize = $_FILES['pdfFiles']['size'][$key];
    $fileType = $_FILES['pdfFiles']['type'][$key];
    $fileError = $_FILES['pdfFiles']['error'][$key];
    
    // Validate PDF file
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    if ($fileExt != 'pdf') {
        die("Error: Only PDF files are allowed. File $fileName is not a PDF.");
    }
    
    // Check file size (max 5MB)
    if ($fileSize > 5000000) {
        die("Error: File $fileName is too large. Maximum size is 5MB.");
    }
    
    // Generate unique filename
    $originalFileName = $_FILES['pdfFiles']['name'][$key];
    $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $originalFileName);
    $uploadPath = $uploadDir . $newFileName;
    
    // Move uploaded file
    if (move_uploaded_file($tmpName, $uploadPath)) {
        // Insert into database with flag=1
        $sql = "INSERT INTO [upload_External_trg_calender] 
        ([pdf_file_path], [original_name], [flag]) 
        VALUES (?, ?, 1)";
        $params = array($uploadPath, $originalFileName);
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt === false) {
            die("Database error: " . print_r(sqlsrv_errors(), true));
        }
        
        $uploadedFiles[] = $fileName;
    } else {
        die("Error uploading file $fileName");
    }
}

// Close connection
sqlsrv_close($conn);

// Redirect back with success message
header("Location: upload_External_trg_calender.php?success=" . urlencode(count($uploadedFiles) . " files uploaded successfully"));
exit();
?>