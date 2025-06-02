<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $year = $_POST['year'];
    $program_name = $_POST['program_name'];

    // Establish database connection (if needed)
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

    // Close the database connection (as no query is needed here)
    sqlsrv_close($conn);

    // Define the upload directory
    $uploadDir = "uploads/" . $year . "/" . $program_name . "/";
    
    // Ensure the directory exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Handle multiple file uploads
    if (isset($_FILES['file'])) {
        $fileCount = count($_FILES['file']['name']); // Count number of uploaded files
        $uploadSuccess = true; // Flag to check if all files are uploaded successfully

        for ($i = 0; $i < $fileCount; $i++) {
            $fileTmpPath = $_FILES['file']['tmp_name'][$i];
            $fileName = $_FILES['file']['name'][$i];
            $fileError = $_FILES['file']['error'][$i];

            // Check for upload errors
            if ($fileError === 0) {
                // Sanitize the file name to prevent unwanted characters
                $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);

                // Define the destination path
                $destPath = $uploadDir . $fileName;

                // Move the uploaded file to the desired directory
                if (!move_uploaded_file($fileTmpPath, $destPath)) {
                    $uploadSuccess = false; // If a file fails to upload
                    echo "<script>alert('Error moving file $fileName.');</script>";
                }
            } else {
                $uploadSuccess = false;
                echo "<script>alert('Error uploading file $fileName.');</script>";
            }
        }

        // Check if all files were uploaded successfully
        if ($uploadSuccess) {
            echo "<script>
                alert('Files successfully uploaded!');
                window.location.href = 'Upload_Employee_Review.php';
            </script>";
        } else {
            echo "<script>alert('Some files failed to upload.');</script>";
        }
    } else {
        echo "<script>alert('No files uploaded.');</script>";
    }
} else {
    echo "<script>alert('Invalid request method.');</script>";
}
?>
