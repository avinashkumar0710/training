<?php
session_start();

// If no GET parameters and cached options exist, return them.
if (empty($_GET) && isset($_SESSION['filterOptions'])) {
    $response = [
        'data' => [],
        'filterOptions' => $_SESSION['filterOptions'],
        'totalRecords' => 0
    ];
    echo json_encode($response);
    exit;
}

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// Database configuration
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123",
    "CharacterSet" => "UTF-8"
);

// Establish connection
$conn = sqlsrv_connect($serverName, $connectionInfo);
if (!$conn) {
    http_response_code(500);
    die(json_encode(["error" => "Connection failed: " . print_r(sqlsrv_errors(), true)]));
}

// Add this near the top of your api.php, after other filters
    


// Rest of your existing API code...

// Get filter parameters from request and date parameters
$filters = [
    'year' => $_GET['year'] ?? null,
    'location' => $_GET['location'] ?? null,
    'dept' => $_GET['dept'] ?? null,
    'program' => $_GET['program'] ?? null
];

$startDate = $_GET['startDate'] ?? null;
$endDate   = $_GET['endDate'] ?? null;

// Initialize condition and parameter arrays once
$whereConditions = [];
$params = [];

// Add date condition if date filters are provided (choose one method)
// Using CONVERT to compare only date parts:
if ($startDate && $endDate) {
    $whereConditions[] = "CONVERT(date, attend_date) BETWEEN CONVERT(date, ?) AND CONVERT(date, ?)";
    $params[] = $startDate;
    $params[] = $endDate;
}

// Add WHERE conditions based on other filters
if ($filters['year'] && $filters['year'] !== 'all') {
    $whereConditions[] = "year = ?";
    $params[] = $filters['year'];
}
if ($filters['location'] && $filters['location'] !== 'all') {
    $whereConditions[] = "location = ?";
    $params[] = $filters['location'];
}
if ($filters['dept'] && $filters['dept'] !== 'all') {
    $whereConditions[] = "dept = ?";
    $params[] = $filters['dept'];
}
// if ($filters['program'] && $filters['program'] !== 'all') {
//     $whereConditions[] = "program_name = ?";
//     $params[] = $filters['program'];
// }

// In your api.php, add this to your filters:
if ($filters['program'] && $filters['program'] !== 'all') {
    $whereConditions[] = "program_id = ?";
    $params[] = $filters['program'];
}

// Build base query
$query = "SELECT 
    record_id, name, dept, location, program_id, program_name, 
    duration, total_attendance, attend_date, flag, dept_code, empno,
    training_location, from_date, to_date, mandays, nature_of_training,
    training_subtype, training_mode, attendance, faculty, year
FROM [Complaint].[dbo].[attendance_records]";

// Always include act_Nact_flag = '1'
$whereConditions[] = "act_Nact_flag = '1'";

// Append additional conditions if any
if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}


// Execute main query
$stmt = sqlsrv_query($conn, $query, $params);
if ($stmt === false) {
    http_response_code(500);
    die(json_encode(["error" => "Query failed: " . print_r(sqlsrv_errors(), true)]));
}

// Fetch and format data
$data = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Convert dates to strings consistently.
    foreach (['attend_date', 'from_date', 'to_date'] as $dateField) {
        if ($row[$dateField] instanceof DateTime) {
            $row[$dateField] = $row[$dateField]->format('Y-m-d');
        } elseif (is_string($row[$dateField])) {
            $row[$dateField] = date('Y-m-d', strtotime($row[$dateField]));
        }
    }
    $data[] = $row;
}

// Get distinct filter options
$filterOptions = [
    'years' => getDistinctValues($conn, 'year', '[Complaint].[dbo].[attendance_records]'),
    'locations' => getDistinctValues($conn, 'location', '[Complaint].[dbo].[attendance_records]'),
    'depts' => getDistinctValues($conn, 'dept', '[Complaint].[dbo].[attendance_records]'),
    //'programs' => getDistinctValues($conn, 'program_name', '[Complaint].[dbo].[attendance_records]')
    'programs' => getDistinctProgramOptions($conn) // Use this instead
];

$_SESSION['filterOptions'] = $filterOptions;

// Prepare response
$response = [
    'data' => $data,
    'filterOptions' => $filterOptions,
    'totalRecords' => count($data)
];

echo json_encode($response);

// Function to get distinct values (SQL Server 2012 compatible)
function getDistinctValues($conn, $column, $table, $where = '') {
    // Build query once.
    $query = "SELECT DISTINCT $column FROM $table 
              WHERE $column IS NOT NULL";
    if ($where) {
        $query .= " AND $where";
    }
    $query .= " ORDER BY $column";
    $stmt = sqlsrv_query($conn, $query);
    
    $values = [];
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if ($row[$column] !== null) {
                $values[] = $row[$column];
            }
        }
    }
    return $values;
}

function getDistinctProgramOptions($conn) {
    $query = "SELECT DISTINCT program_id, program_name 
              FROM [Complaint].[dbo].[attendance_records]
              WHERE program_id IS NOT NULL AND program_name IS NOT NULL
              ORDER BY program_name";

    $stmt = sqlsrv_query($conn, $query);
    $options = [];

    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $options[] = [
                'id' => $row['program_id'],
                'name' => $row['program_name']
            ];
        }
    }

    return $options;
}

// Clean up
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
