<?php
// SQL Server connection
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Fetching input data
$editIds = $_POST['edit_ids'] ?? [];

$names = $_POST['name'] ?? [];
$depts = $_POST['dept'] ?? [];
$locations = $_POST['location'] ?? [];
$program_names = $_POST['program_name'] ?? [];
$durations = $_POST['duration'] ?? [];
$dept_codes = $_POST['dept_code'] ?? [];
$loc_descs = $_POST['loc_desc'] ?? [];
$training_locations = $_POST['training_location'] ?? [];
$from_dates = $_POST['from_date'] ?? [];
$to_dates = $_POST['to_date'] ?? [];
$mandays_list = $_POST['mandays'] ?? [];
$natures = $_POST['nature_of_training'] ?? [];
$subtypes = $_POST['training_subtype'] ?? [];
$modes = $_POST['training_mode'] ?? [];
$attendances = $_POST['attendance'] ?? [];
$faculties = $_POST['faculty'] ?? [];
$years = $_POST['year'] ?? [];

$updatedCount = 0;

foreach ($editIds as $recordId) {
    $params = [
        trim($names[$recordId] ?? ''),
        trim($depts[$recordId] ?? ''),
        trim($locations[$recordId] ?? ''),
        trim($program_names[$recordId] ?? ''),
        trim($durations[$recordId] ?? ''),
        trim($dept_codes[$recordId] ?? ''),
        trim($loc_descs[$recordId] ?? ''),
        trim($training_locations[$recordId] ?? ''),
        ($from_dates[$recordId] ?? '') !== '' ? date('Y-m-d', strtotime($from_dates[$recordId])) : null,
        ($to_dates[$recordId] ?? '') !== '' ? date('Y-m-d', strtotime($to_dates[$recordId])) : null,
        trim($mandays_list[$recordId] ?? ''),
        trim($natures[$recordId] ?? ''),
        trim($subtypes[$recordId] ?? ''),
        trim($modes[$recordId] ?? ''),
        trim($attendances[$recordId] ?? ''),
        trim($faculties[$recordId] ?? ''),
        trim($years[$recordId] ?? ''),
        $recordId
    ];

    $sql = "UPDATE [Complaint].[dbo].[attendance_records]
            SET name = ?, dept = ?, location = ?, program_name = ?, duration = ?,
                dept_code = ?, loc_desc = ?, training_location = ?, from_date = ?, to_date = ?,
                mandays = ?, nature_of_training = ?, training_subtype = ?, training_mode = ?,
                attendance = ?, faculty = ?, year = ?
            WHERE record_id = ?";

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        echo "Error updating record ID $recordId: " . print_r(sqlsrv_errors(), true);
    } else {
        $updatedCount++;
    }
}

if ($stmt === false) {
    echo "Error updating record ID $recordId: " . print_r(sqlsrv_errors(), true);
} else {
    $updatedCount++;
}

echo "<script>
    alert('Record(s) updated successfully.');
    window.location.href = 'edit.php';
</script>";

?>
