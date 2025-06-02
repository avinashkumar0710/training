<?php
require 'vendor/autoload.php'; // Include PhpSpreadsheet

use phpOffice\PhpSpreadsheet\IOFactory;
use phpOffice\PhpSpreadsheet\Spreadsheet;

// Database connection
$serverName = "192.168.100.240";
$connectionInfo = array("Database" => "complaint", "UID" => "sa", "PWD" => "Intranet@123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

// Check if the connection was successful
if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

if (isset($_POST['submit'])) {
    // Get the uploaded file
    $file = $_FILES['file']['tmp_name'];

    // Load the spreadsheet
    $spreadsheet = IOFactory::load($file);

    // Select the active sheet
    $sheet = $spreadsheet->getActiveSheet();
    
    // Get the highest row and column numbers referenced in the worksheet
    $highestRow = $sheet->getHighestDataRow(); 
    $highestColumn = $sheet->getHighestDataColumn();

    // Prepare the SQL insert query (excluding ID, which auto-increments)
    $sql = "INSERT INTO training_mas (emp_no, emp_name, dept, grade, design, loc, tr_name, institute, venue, mandays, tr_date_fr, tr_date_to, upld_by, half_day) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // Iterate through each row in the spreadsheet (assuming the first row is the header)
    for ($row = 2; $row <= $highestRow; ++$row) {
        $data = [
            $sheet->getCellByColumnAndRow(1, $row)->getValue(),
            $sheet->getCellByColumnAndRow(2, $row)->getValue(),
            $sheet->getCellByColumnAndRow(3, $row)->getValue(),
            $sheet->getCellByColumnAndRow(4, $row)->getValue(),
            $sheet->getCellByColumnAndRow(5, $row)->getValue(),
            $sheet->getCellByColumnAndRow(6, $row)->getValue(),
            $sheet->getCellByColumnAndRow(7, $row)->getValue(),
            $sheet->getCellByColumnAndRow(8, $row)->getValue(),
            $sheet->getCellByColumnAndRow(9, $row)->getValue(),
            $sheet->getCellByColumnAndRow(10, $row)->getValue(),
            date('Y-m-d', strtotime($sheet->getCellByColumnAndRow(11, $row)->getValue())), // Convert date format
            date('Y-m-d', strtotime($sheet->getCellByColumnAndRow(12, $row)->getValue())), // Convert date format
            $sheet->getCellByColumnAndRow(13, $row)->getValue(),
            $sheet->getCellByColumnAndRow(14, $row)->getValue()
        ];

        // Execute the insert query
        $stmt = sqlsrv_query($conn, $sql, $data);
        if (!$stmt) {
            echo "Error inserting row $row: " . print_r(sqlsrv_errors(), true);
        }
    }

    echo "Data successfully inserted!";
}
?>