<?php
require 'vendor/autoload.php'; // Ensure this path is correct
//require '../vendor/Phpoffice/PhpSpreadsheet/IOFactory.php';
use phpoffice\PhpSpreadsheet\IOFactory;


$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);           
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $spreadsheet = IOFactory::load($fileTmpPath);
    $worksheet = $spreadsheet->getActiveSheet();

    foreach ($worksheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        $data = [];
        foreach ($cellIterator as $cell) {
            $data[] = $cell->getValue();
        }

        // Ensure correct number of columns (adjust as necessary)
        if (count($data) >= 11) {
            $query = "
                INSERT INTO [Complaint].[dbo].[Training_admin_excel] 
                ([srl_no], [Program_name], [nature_training], [duration], [faculty], [tentative_date], [year], [target_group], [upload_date], [ip_address], [Closed_date])
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ";
            $params = array_slice($data, 1, 11);
            $stmt = sqlsrv_query($conn, $query, $params);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
        }
    }

    echo "<script>
        alert('Excel file data saved successfully!');
        window.location.href = 'index.php';
    </script>";
} else {
    echo "<script>
        alert('Error uploading file.');
        window.location.href = 'index.php';
    </script>";
}

sqlsrv_close($conn);
?>
