<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["emp_num"])) {
    header("Location: login.php");
    exit;
}

$serverName = "192.168.100.240";
$connectionOptions = [
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}// or your db connection

$where = "ar.training_feedback_flag in ('7','8')";
$params = [];

// Filters
if (!empty($_GET['empno'])) {
    $where .= " AND ar.empno LIKE ?";
    $params[] = '%' . $_GET['empno'] . '%';
}

if (!empty($_GET['employee_grp'])) {
    $input = strtolower($_GET['employee_grp']);
    
    // Check for anything that sounds like Executive
    if (stripos($input, 'non') !== false) {
        $where .= " AND ems.employee_grp = ?";
        $params[] = 'B'; // Non-Executive
    } elseif (stripos($input, 'exe') !== false) {
        $where .= " AND ems.employee_grp = ?";
        $params[] = 'A'; // Executive
    }
}

if (!empty($_GET['grade'])) {
    $where .= " AND ems.grade = ?";
    $params[] = $_GET['grade'];
}
if (!empty($_GET['dept'])) {
    $where .= " AND ar.dept LIKE ?";
    $params[] = '%' . $_GET['dept'] . '%';
}
if (!empty($_GET['plant'])) {
    $where .= " AND ems.loc_desc LIKE ?";
    $params[] = '%' . $_GET['plant'] . '%';
}
if (isset($_GET['duration']) && $_GET['duration'] !== '') {
    $where .= " AND ar.duration = ?";
    $params[] = $_GET['duration'];
}
if (!empty($_GET['from_date'])) {
    $where .= " AND ar.from_date >= ?";
    $params[] = $_GET['from_date'];
}
if (!empty($_GET['to_date'])) {
    $where .= " AND ar.to_date <= ?";
    $params[] = $_GET['to_date'];
}

$sql = "SELECT ar.*, ems.grade, ems.loc_desc AS Plant, ems.employee_grp AS EmployeeGroupCode 
        FROM [Complaint].[dbo].[attendance_records] ar 
        LEFT JOIN [Complaint].[dbo].[emp_mas_sap] ems ON ar.empno = ems.empno 
        WHERE $where 
        ORDER BY ar.from_date";

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$serial = 1;
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $employeeGroup = ($row['EmployeeGroupCode'] === 'A') ? 'Executive' :
                     (($row['EmployeeGroupCode'] === 'B') ? 'Non-Executive' : 'N/A');
    echo "<tr>";
    echo "<td>{$serial}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['empno']}</td>";
    echo "<td>{$row['Plant']}</td>";
    echo "<td>{$row['dept']}</td>";
    echo "<td>{$row['grade']}</td>";
    echo "<td>{$employeeGroup}</td>";
    echo "<td>{$row['location']}</td>";
    echo "<td>{$row['program_id']}</td>";
    echo "<td>{$row['program_name']}</td>";
    echo "<td>{$row['nature_of_training']}</td>";
    echo "<td>{$row['training_subtype']}</td>";
    echo "<td>{$row['training_mode']}</td>";
    echo "<td>{$row['faculty']}</td>";
    echo "<td>{$row['duration']}</td>";
    echo "<td>{$row['mandays']}</td>";
    echo "<td>{$row['attendance']}</td>";
    echo "<td>" . ($row['from_date'] ? $row['from_date']->format('Y-m-d') : '') . "</td>";
    echo "<td>" . ($row['to_date'] ? $row['to_date']->format('Y-m-d') : '') . "</td>";
    echo "<td>{$row['year']}</td>";
    echo "</tr>";
    $serial++;
}
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
