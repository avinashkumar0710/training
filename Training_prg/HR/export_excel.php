<?php
// Database Connection
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

// Get filters from POST
// Fetch filters from POST properly
$selectedPlant = $_POST['selected_plant'] ?? 'ALL';
$selectedProgram = $_POST['selected_program'] ?? 'ALL';


// SQL Query to Fetch Data
$sql = "SELECT r.srl_no, r.id, r.empno, 
            CASE WHEN LEN(r.empno) = 6 THEN '00' + r.empno ELSE r.empno END AS update_empno, 
            r.PROGRAM_NAME, r.year, r.duration, r.faculty, r.plant, r.hostel_book, r.flag, 
            e.email, e.name, e.dept, t.Closed_date 
        FROM [Complaint].[dbo].[request] r
        JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
        LEFT JOIN [Complaint].[dbo].[training_mast] t ON r.srl_no = t.srl_no
        WHERE r.flag = '4'";

if ($selectedPlant !== 'ALL') {
    $sql .= " AND r.plant = '$selectedPlant'";
}

if ($selectedProgram !== 'ALL') {
    $sql .= " AND r.PROGRAM_NAME = '$selectedProgram'";
}

$sql .= " ORDER BY r.id DESC, r.year DESC";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Set Headers for Excel File
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=filtered_data.xls");

// Open output buffer
$output = fopen("php://output", "w");

// Column Headers
$columns = ['Sl. No', 'Name', 'Program Name', 'Year', 'Duration', 'Faculty', 'Hostel Book', 'Department', 'Plant', 'Email', 'Status'];
fputcsv($output, $columns, "\t");

// Mapping plant codes to names
$plantNames = [
    'NS04' => 'Bhilai',
    'NS03' => 'Rourkela',
    'NS02' => 'Durgapur',
    'NS01' => 'Corporate Center'
];

// Fetch and write rows
$serialNo = 1;
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $data = [
        $serialNo,
        $row['name'],
        $row['PROGRAM_NAME'],
        $row['year'],
        $row['duration'],
        $row['faculty'],
        $row['hostel_book'] == 1 ? 'Yes' : 'No',
        $row['dept'],
        $plantNames[$row['plant']] ?? "Unknown",
        $row['email'],
        $row['flag'] == 4 ? 'Approve From Plant HOD' : 'Reject From HOD'
    ];
    fputcsv($output, $data, "\t");
    $serialNo++;
}

// Close output
fclose($output);
exit;
?>
