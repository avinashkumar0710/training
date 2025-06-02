<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $year = $_POST['year'];
    $programName = $_POST['program_name'];
    $fileName = $_POST['fileName'];

    // Define the file path
    $filePath = "uploads/" . $year . "/" . $programName . "/" . $fileName;

    // Check if the file exists and delete it
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            echo "File deleted successfully.";
        } else {
            echo "Error deleting file.";
        }
    } else {
        echo "File not found.";
    }
} else {
    echo "Invalid request method.";
}
?>
