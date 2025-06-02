<?php
// Database connection
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Query
$sql = "SELECT feedback_id, emp_num, srl_no, program_title, program_duration, faculty,
               overall_objectives, content_depth, program_duration_feedback, relevance, program_coordinated,
               faculty_feedback, hospitality_arrangements, administrative_arrangements, stay_arrangements,
               created_at, suggestion, file_path
        FROM [Complaint].[dbo].[program_feedback]";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Set headers to download as Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=program_feedback_export.xls");

// Column headers
echo "Feedback ID\tEmp Num\tSRL No\tProgram Title\tProgram Duration\tFaculty\tOverall Objectives\tContent Depth\tProgram Duration Feedback\tRelevance\tProgram Coordinated\tFaculty Feedback\tHospitality Arrangements\tAdministrative Arrangements\tStay Arrangements\tCreated At\tSuggestion\tFile Path\n";

$baseURL = "http://192.168.100.9:8080/training/Training_feedback/";

// Rows
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $createdAt = $row['created_at'] instanceof DateTime ? $row['created_at']->format('Y-m-d') : '';
    $fileLink = $row['file_path'] ? $baseURL . $row['file_path'] : '';

    echo "{$row['feedback_id']}\t{$row['emp_num']}\t{$row['srl_no']}\t{$row['program_title']}\t{$row['program_duration']}\t{$row['faculty']}\t{$row['overall_objectives']}\t{$row['content_depth']}\t{$row['program_duration_feedback']}\t{$row['relevance']}\t{$row['program_coordinated']}\t{$row['faculty_feedback']}\t{$row['hospitality_arrangements']}\t{$row['administrative_arrangements']}\t{$row['stay_arrangements']}\t$createdAt\t{$row['suggestion']}\t{$fileLink}\n";
}


sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
exit;
?>
