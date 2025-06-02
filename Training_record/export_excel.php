<?php
session_start();

// Database connection
$serverName = "192.168.100.240";
$connectionOptions = [
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch filtered data
$where = "ar.training_feedback_flag in ('7','8') and act_Nact_flag='1'";
$params = [];

if (!empty($_POST['empno'])) {
    $where .= " AND ar.empno LIKE ?";
    $params[] = '%' . $_POST['empno'] . '%';
}

$sql = "SELECT ar.*, ems.grade, ems.loc_desc AS Plant, ems.employee_grp AS EmployeeGroupCode 
        FROM [Complaint].[dbo].[attendance_records] ar 
        LEFT JOIN [Complaint].[dbo].[emp_mas_sap] ems ON ar.empno = ems.empno 
        WHERE act_Nact_flag='1' 
        ORDER BY ar.from_date";

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Get current date in YYYY-MM-DD format
$currentDate = date("Y-m-d");
$filename = "Employee_Training_Data_$currentDate.csv";

// Set headers to force download as CSV
header('Content-Type: text/csv; charset=utf-8');
header("Content-Disposition: attachment; filename=$filename");

$output = fopen('php://output', 'w');
fputcsv($output, ["SL No", "Employee Name", "Emp No", "Plant", "Dept", "Grade", "Employee Group", "Training Location",
                  "Program ID", "Program Name", "Nature of Training", "Training Subtype", "Training Mode",
                  "Faculty", "Duration", "Mandays", "Attendance", "From Date", "To Date", "Year"]);

// Data rows
$serial = 1;
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    fputcsv($output, [
        $serial,
        $row['name'],
        $row['empno'],
        $row['Plant'],
        $row['dept'],
        $row['grade'],
        ($row['EmployeeGroupCode'] === 'A' ? 'Executive' : 'Non-Executive'),
        $row['location'],
        $row['program_id'],
        $row['program_name'],
        $row['nature_of_training'],
        $row['training_subtype'],
        $row['training_mode'],
        $row['faculty'],
        $row['duration'],
        $row['mandays'],
        $row['attendance'],
        ($row['from_date'] ? $row['from_date']->format('Y-m-d') : ''),
        ($row['to_date'] ? $row['to_date']->format('Y-m-d') : ''),
        $row['year']
    ]);

    $serial++;
}

fclose($output);
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
exit;
?>
