<?php
require('fpdf/fpdf.php');

// Database connection
$serverName = "192.168.100.240";
$connectionInfo = [
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
];

$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Get filters from POST request
$selectedProgram = isset($_POST['selected_program']) ? $_POST['selected_program'] : '';
$location = isset($_POST['location']) ? $_POST['location'] : '';

// SQL Query with filters
$query = "SELECT 
            r.id, 
            r.PROGRAM_NAME, 
            r.year, 
            r.nature_training, 
            r.duration,  
            e.loc_desc,  
            e.name,  
            e.empno, 
            r.remarks, 
            r.hostel_book, 
            r.flag, 
            e.location, 
            t.day_from, 
            t.day_to 
          FROM  
            [Complaint].[dbo].[request] r  
          JOIN 
            [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
          LEFT JOIN 
            [Complaint].[dbo].[training_mast] t ON r.srl_no = t.srl_no
          WHERE 
            r.flag = '7'";

$params = [];

if (!empty($selectedProgram)) {
    $query .= " AND r.PROGRAM_NAME = ?";
    $params[] = $selectedProgram;
}

if (!empty($location) && $location !== 'NS04') {
    $query .= " AND e.location = ?";
    $params[] = $location;
}

$query .= " ORDER BY r.id DESC";

$result = sqlsrv_query($conn, $query, $params);
if ($result === false) {
    die("Error fetching data: " . print_r(sqlsrv_errors(), true));
}

// Create PDF
$pdf = new FPDF('L', 'pt', 'Letter');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 6);
$pdf->SetFillColor(200, 200, 200);

// Table headers
$headers = ['ID', 'Program Name', 'Year', 'Nature of Training', 'Duration', 'Employee Name', 'Plant', 'Day From', 'Day To', 'Hostel Book', 'Status'];
$widths = [20, 150, 30, 80, 30, 80, 40, 60, 60, 40, 100];

// Output headers
foreach ($headers as $i => $header) {
    $pdf->Cell($widths[$i], 30, $header, 1, 0, 'C', true);
}
$pdf->Ln();

// Set font for data rows
$pdf->SetFont('Arial', '', 6);
$pdf->SetFillColor(255, 255, 255);

while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $hostelValue = ($row['hostel_book'] == 2) ? 'Yes' : 'No';
    $flag = ($row['flag'] == 7) ? 'Overall Approved' : 'Error';

    // Format dates
    $dayFrom = ($row['day_from'] !== null) ? $row['day_from']->format('Y-m-d') : 'N/A';
    $dayTo = ($row['day_to'] !== null) ? $row['day_to']->format('Y-m-d') : 'N/A';

    // Output row data
    $data = [
        $row['id'],
        $row['PROGRAM_NAME'],
        $row['year'],
        $row['nature_training'],
        $row['duration'],
        $row['name'],
        $row['loc_desc'],
        $dayFrom,
        $dayTo,
        $hostelValue,
        $flag
    ];

    foreach ($data as $i => $value) {
        $pdf->Cell($widths[$i], 50, $value, 1, 0, 'C');
    }
    $pdf->Ln();
}

// Output PDF
$filename = 'Overall_Approved_Training_Program_' . date('Y-m-d') . '.pdf';
$pdf->Output($filename, 'D');

// Clean up
sqlsrv_free_stmt($result);
sqlsrv_close($conn);

?>
