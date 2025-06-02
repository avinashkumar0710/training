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
}

// Only fetch records for the logged-in user
$empNum = $_SESSION["emp_num"];

//echo $empNum;

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Mandays</title>
    <link rel="icon" href="../images/analysis.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add this in your <head> section -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<style>
    .row { 
    display: flex; 
    flex-wrap: nowrap; 
}

</style>
<?php include 'header.php';?>
<body>
<?php
// Fetch distinct filter options
function getDistinctValues($conn, $column) {
    $query = "SELECT DISTINCT $column FROM [Complaint].[dbo].[attendance_records] ";
    $stmt = sqlsrv_query($conn, $query, [$_SESSION["emp_num"]]);
    $values = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if (!empty($row[$column])) {
            $values[] = $row[$column];
        }
    }
    return $values;
}

$attendanceOptions = getDistinctValues($conn, 'attendance');
$programNameOptions = getDistinctValues($conn, 'program_name');
$facultyOptions = getDistinctValues($conn, 'faculty');
$trainingModeOptions = getDistinctValues($conn, 'training_mode');
$yearOptions = getDistinctValues($conn, 'year');

// Capture filter values
$attendanceFilter = $_GET['attendance'] ?? '';
$programNameFilter = $_GET['program_name'] ?? '';
$facultyFilter = $_GET['faculty'] ?? '';
$trainingModeFilter = $_GET['training_mode'] ?? '';
$yearFilter = $_GET['year'] ?? '';

// Build dynamic query
$sql = "SELECT * FROM [Complaint].[dbo].[attendance_records] where act_Nact_flag = '1' ";
$params = [$_SESSION["emp_num"]];

if (!empty($attendanceFilter)) {
    $sql .= " AND attendance = ?";
    $params[] = $attendanceFilter;
}
if (!empty($programNameFilter)) {
    $sql .= " AND program_name = ?";
    $params[] = $programNameFilter;
}
if (!empty($facultyFilter)) {
    $sql .= " AND faculty = ?";
    $params[] = $facultyFilter;
}
if (!empty($trainingModeFilter)) {
    $sql .= " AND training_mode = ?";
    $params[] = $trainingModeFilter;
}
if (!empty($yearFilter)) {
    $sql .= " AND year = ?";
    $params[] = $yearFilter;
}

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<div class="container-fluid">
    <h4 class="mb-3">Training Attendance Records</h4>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-2">
            <select name="attendance" class="form-select">
                <option value="">All Attendance</option>
                <?php foreach ($attendanceOptions as $option): ?>
                    <option value="<?= $option ?>" <?= ($attendanceFilter == $option) ? 'selected' : '' ?>><?= $option ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="program_name" class="form-select">
                <option value="">All Programs</option>
                <?php foreach ($programNameOptions as $option): ?>
                    <option value="<?= $option ?>" <?= ($programNameFilter == $option) ? 'selected' : '' ?>><?= $option ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="faculty" class="form-select">
                <option value="">All Faculty</option>
                <?php foreach ($facultyOptions as $option): ?>
                    <option value="<?= $option ?>" <?= ($facultyFilter == $option) ? 'selected' : '' ?>><?= $option ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="training_mode" class="form-select">
                <option value="">All Modes</option>
                <?php foreach ($trainingModeOptions as $option): ?>
                    <option value="<?= $option ?>" <?= ($trainingModeFilter == $option) ? 'selected' : '' ?>><?= $option ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <select name="year" class="form-select">
                <option value="">All Years</option>
                <?php foreach ($yearOptions as $option): ?>
                    <option value="<?= $option ?>" <?= ($yearFilter == $option) ? 'selected' : '' ?>><?= $option ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
        <div class="col-md-1">
            <a href="employeemandays.php" class="btn btn-secondary w-100">Clear</a>
        </div>
    </form>

    <div style="max-height: 650px; overflow-y: auto;">
    <div class="table-responsive">
        <table class="table table-bordered table-sm table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Record ID</th><th>Name</th><th>Location</th><th>Program ID</th><th>Program Name</th>
                    <th>Duration</th><th>Total Attendance</th><th>SRL No</th><th>Training Location</th>
                    <th>From</th><th>To</th><th>Mandays</th><th>Nature</th><th>Subtype</th><th>Mode</th>
                    <th>Attendance</th><th>Faculty</th><th>Year</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalMandays = 0;
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $totalMandays += (float) $row['mandays'];
                    echo "<tr>";
                    echo "<td>{$row['record_id']}</td>";
                    echo "<td>{$row['name']}</td>";
                    echo "<td>{$row['location']}</td>";
                    echo "<td>{$row['program_id']}</td>";
                    echo "<td>{$row['program_name']}</td>";
                    echo "<td>{$row['duration']}</td>";
                    echo "<td>{$row['total_attendance']}</td>";
                    echo "<td>{$row['srl_no']}</td>";
                    echo "<td>{$row['training_location']}</td>";
                    echo "<td>" . ($row['from_date'] ? $row['from_date']->format('Y-m-d') : '') . "</td>";
                    echo "<td>" . ($row['to_date'] ? $row['to_date']->format('Y-m-d') : '') . "</td>";
                    echo "<td>{$row['mandays']}</td>";
                    echo "<td>{$row['nature_of_training']}</td>";
                    echo "<td>{$row['training_subtype']}</td>";
                    echo "<td>{$row['training_mode']}</td>";
                    echo "<td>{$row['attendance']}</td>";
                    echo "<td>{$row['faculty']}</td>";
                    echo "<td>{$row['year']}</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <div class="alert alert-info">
            <strong>Total Mandays:</strong> <?= $totalMandays ?>
        </div>
    </div>
</div>


    </body>

<?php include 'footer.php';?>