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
$location = isset($_POST['location']) ? $_POST['location'] : '';
$query = "SELECT 
            r.id, 
            r.PROGRAM_NAME, 
            r.year, 
            r.tentative_date,
            r.nature_training,
            r.duration,
            e.loc_desc,
            e.name, 
            e.empno, 
            r.remarks, 
            r.hostel_book,
            r.flag,
            e.location
        FROM 
            [Complaint].[dbo].[request] r
        JOIN 
            [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
        WHERE 
            r.flag = '8' 
        ORDER BY 
            r.id DESC";

$result = sqlsrv_query($conn, $query);

if ($result === false) {
    die("Error fetching data: " . sqlsrv_errors());
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


while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $hostelValue = ($row['hostel_book'] == 2) ? 'Yes' : 'No';
    $flag = ($row['flag'] == 8) ? 'OverAll_Approve' : 'Error';

    $programName = $row['PROGRAM_NAME'];
    $employeeName = $row['name'];
    $plant = $row['loc_desc'];

    // Set fixed row height
    $fixedHeight = 50; // Height of the row
    $lineHeight = 10;  // Line height for MultiCell to allow wrapping

    // Output data with fixed row height and border lines
    $pdf->Cell(20, $fixedHeight, $row['id'], 1, 0, 'C');

    // PROGRAM_NAME column with wrapped text inside a fixed-height box
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(200, $lineHeight, $programName, 1, 'C');
    $pdf->SetXY($x + 200, $y); // Move to the next cell after MultiCell()

    // Other columns with fixed height
    $pdf->Cell(30, $fixedHeight, $row['year'], 1, 0, 'C');
    $pdf->Cell(100, $fixedHeight, $row['tentative_date'], 1, 0, 'C');
    $pdf->Cell(80, $fixedHeight, $row['nature_training'], 1, 0, 'C');
    $pdf->Cell(30, $fixedHeight, $row['duration'], 1, 0, 'C');

    // Employee Name column with wrapped text inside a fixed-height box
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(80, $lineHeight, $employeeName, 1, 'L');
    $pdf->SetXY($x + 80, $y); // Move to the next cell after MultiCell()

    // Plant column with wrapped text inside a fixed-height box
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    $pdf->MultiCell(40, $lineHeight, $plant, 1, 'L');
    $pdf->SetXY($x + 40, $y); // Move to the next cell after MultiCell()

    // Remaining columns with fixed height
    $pdf->Cell(40, $fixedHeight, $hostelValue, 1, 0, 'C');
    $pdf->Cell(100, $fixedHeight, $flag, 1, 1, 'C'); // End of the row
}


// Generate a filename with the current date
$filename = 'Overall_Approved_Training_Program' . date('Y-m-d') . '.pdf';

// Output the PDF to the browser for download with the generated filename
$pdf->Output($filename, 'D');


// Clean up
sqlsrv_free_stmt($result);
sqlsrv_close($conn);
?>
