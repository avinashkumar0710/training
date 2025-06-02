<?php
require('fpdf/fpdf.php');

// Your database connection code goes here
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

// Get the selected program flag from POST request
//$selectedProgram = isset($_POST['selected_program']) ? intval($_POST['selected_program']) : 0;
$selectedProgram = isset($_POST['selected_program']) ? $_POST['selected_program'] : 'all';

// Query to fetch data based on the selected flag
if ($selectedProgram === 'all') {
    // If no specific program is selected, fetch all data
    $query = "SELECT r.id, r.PROGRAM_NAME, r.year, r.tentative_date, r.nature_training, r.duration, e.loc_desc, e.name, e.empno, r.remarks, r.hostel_book, r.flag  
              FROM [Complaint].[dbo].[request_TNI] r  
              JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno  
              ORDER BY r.id DESC";
    $params = array();
} else {
    // If a specific program is selected, fetch data for that program
    $query = "SELECT r.id, r.PROGRAM_NAME, r.year, r.tentative_date, r.nature_training, r.duration, e.loc_desc, e.name, e.empno, r.remarks, r.hostel_book, r.flag  
              FROM [Complaint].[dbo].[request_TNI] r  
              JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno  
              WHERE r.flag = ? 
              ORDER BY r.id DESC";
    $params = array($selectedProgram);
}

$result = sqlsrv_query($conn, $query, $params);

if ($result === false) {
    die("Error fetching data: " . print_r(sqlsrv_errors(), true));
}

// Instantiate FPDF with landscape mode
$pdf = new FPDF('L', 'pt', 'letter');
$pdf->AddPage();

// Set background color and font for header cells
$pdf->SetFillColor(200, 200, 200); // Set background color (light gray)
$pdf->SetFont('helvetica', 'B', 6); // Set font to bold

// Output header cells
$pdf->Cell(20, 30, 'ID', 1, 0, 'C', true); // Set fill parameter to true
$pdf->Cell(200, 30, 'Program Name', 1, 0, 'C', true); // Set fill parameter to true
$pdf->Cell(30, 30, 'Year', 1, 0, 'C', true); // Set fill parameter to true
$pdf->Cell(100, 30, 'Tentative Date', 1, 0, 'C', true); // Set fill parameter to true
$pdf->Cell(80, 30, 'Nature of Training', 1, 0, 'C', true); // Set fill parameter to true
$pdf->Cell(30, 30, 'Duration', 1, 0, 'C', true); // Set fill parameter to true
$pdf->Cell(80, 30, 'Employee Name', 1, 0, 'C', true); // Set fill parameter to true
$pdf->Cell(40, 30, 'Plant', 1, 0, 'C', true); // Set fill parameter to true
$pdf->Cell(40, 30, 'Hostel Book', 1, 0, 'C', true); // Set fill parameter to true
$pdf->Cell(100, 30, 'Status', 1, 1, 'C', true); // Set fill parameter to true

// Reset fill color and font
$pdf->SetFillColor(255, 255, 255); // Reset background color (white)
$pdf->SetFont('helvetica', '', 6); // Reset font to regular

// Function to determine the status based on the flag value
function getStatus($flag) {
    switch ($flag) {
        case 0:
            return 'Pending at Reporting Officer';
        case 1:
            return 'Reject by Reporting Officer';
        case 2:
            return 'Pending at HOD';
        case 3:
            return 'Reject by HOD';
        case 4:
            return 'TNI Approved';
        default:
            return 'Unknown';
    }
}

// Output fetched data in PDF
while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $hostelValue = ($row['hostel_book'] == 1) ? 'Yes' : 'No';
    $status = getStatus($row['flag']); // Get status based on the flag
    // Output data with border lines
    $pdf->Cell(20, 20, $row['id'], 1, 0, 'C');
    $pdf->Cell(200, 20, $row['PROGRAM_NAME'], 1, 0, 'C');
    $pdf->Cell(30, 20, $row['year'], 1, 0, 'C');
    $pdf->Cell(100, 20, $row['tentative_date'], 1, 0, 'C');
    $pdf->Cell(80, 20, $row['nature_training'], 1, 0, 'C');
    $pdf->Cell(30, 20, $row['duration'], 1, 0, 'C');
    $pdf->Cell(80, 20, $row['name'], 1, 0, 'L');
    $pdf->Cell(40, 20, $row['loc_desc'], 1, 0, 'L');
    $pdf->Cell(40, 20, $hostelValue, 1, 0, 'C');
    $pdf->Cell(100, 20, $status, 1, 1, 'C');
}

// Generate a filename with the current date
$filename = 'Overall_Approved_TNI_' . date('Y-m-d') . '.pdf';

// Output the PDF to the browser for download with the generated filename
$pdf->Output($filename, 'D');

// Clean up
sqlsrv_free_stmt($result);
sqlsrv_close($conn);
?>
