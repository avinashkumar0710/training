<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
    exit();
}

$sessionemp = $_SESSION["emp_num"];

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

require '../vendor/autoload.php'; // Load PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    // Load the Excel file
    $spreadsheet = IOFactory::load($file);
    $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    // Prepare the SQL statement
    $sql = "INSERT INTO TrainingPrograms (srl_no, Program_name, program_pdf, nature_training, duration, faculty, 
        training_mode, tentative_date, Internal_external, year, target_group, venue, hostel_reqd, 
        coordinator, remarks, upload_date, ip_address, Closed_date) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    foreach ($sheetData as $row) {
        // Skip the header row if necessary
        if ($row['A'] == 'srl_no') continue; // Adjust this check based on your Excel file's header

        // Prepare the data to be inserted
        $params = array(
            $row['A'], // srl_no
            $row['B'], // Program_name
            $row['C'], // program_pdf
            $row['D'], // nature_training
            $row['E'], // duration
            $row['F'], // faculty
            $row['G'], // training_mode
            $row['H'], // tentative_date
            $row['I'], // Internal_external
            $row['J'], // year
            $row['K'], // target_group
            $row['L'], // venue
            $row['M'], // hostel_reqd
            $row['N'], // coordinator
            $row['O'], // remarks
            $row['P'], // upload_date
            $_SERVER['REMOTE_ADDR'], // IP address
            null // Closed_date
        );

        // Execute the SQL statement
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    }

    echo "Data uploaded successfully.";
    
    // Free the statement
    sqlsrv_free_stmt($stmt);
}

// Close the connection
sqlsrv_close($conn);
?>
