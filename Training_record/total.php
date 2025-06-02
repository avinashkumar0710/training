<?php 
// start a new session
// Allow any origin to access this resource

session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }

    $sessionemp= $_SESSION["emp_num"];

    // Add '00' in front if session value has only 6 digits
    if(strlen($sessionemp) == 6) {
        $sessionemp = '00' . $sessionemp;
    }

    // Database Connection
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
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <style>
/* Style for the tab container */
/* Center the tabs */
.tab-container {
    display: flex;
    justify-content: center; /* Horizontally centers the tabs */
    margin-top: 20px;
}

/* Space between the tabs */
.tabs {
    display: flex;
    justify-content: center;
    gap: 15px; /* Adjust the gap between the tabs */
}

/* Custom styling for the active tab */
.btn.active {
    background-color: #007bff; /* Change to primary color when active */
    color: white;
    border-color: #007bff; /* Match the border color */
}

body{
    background-color: #9ad8db4d;
}

   </style>

</head>

<?php include 'header.php';?>
<h6><i class='fa fa-home'></i>&nbsp;<u><i>Home->Training Record & MIS->प्रशिक्षण</i></u></h6>
<div class="tab-container">
    <div class="tabs">
    <a href="training_dashboard.php"><div class="btn btn-outline-primary">Internal Online Training</div></a>
    <a href="external_training_dashboard.php"><div class="btn btn-outline-primary">External Online Training</div></a>
        <a href="training_validation.php"><div class="btn btn-outline-primary">Internal Classroom</div></a>
        <a href="external_classroom.php"><div class="btn btn-outline-primary">External Classroom</div></a>
        <a href="training_overall.php"><div class="btn btn-outline-primary ">Over All</div></a>
        <a href="index.html"><div class="btn btn-outline-primary">View Graph</div></a>
        <a href="total.php"><div class="btn btn-primary active">All Planned Program</div></a>
    </div>
</div>
<br>
<?php
// Database Connection
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die("Database connection failed: " . print_r(sqlsrv_errors(), true));
}

// Fetch Merged Data
$sql = "SELECT record_id, name, dept, location, program_id, program_name, duration, total_attendance,
               attend_date, empno, training_location, from_date, to_date, mandays, nature_of_training,
               training_subtype, training_mode, attendance, faculty
        FROM [Complaint].[dbo].[attendance_records]
        WHERE flag = '3' AND attendance = 'NA'";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die("Error fetching records: " . print_r(sqlsrv_errors(), true));
}

// Display Data in Table
echo '<div style="max-height: 700px; overflow-y: auto;">';
echo '<table class="table table-bordered border-dark" border="1">';
echo '<thead class="bg-success border-dark" style="position: sticky; top: 0; z-index: 1; background-color: #198754;">';
echo '<tr>
<th>Serial No</th>
       
        <th>Name</th>
        <th>Dept</th>
        <th>Location</th>
        <th>Program ID</th>
        <th>Program Name</th>
        <th>Duration</th>
      
       
        <th>Employee No</th>
        <th>Training Location</th>
        <th>From Date</th>
        <th>To Date</th>
        <th>Mandays</th>
        <th>Nature of Training</th>
        <th>Training Subtype</th>
        <th>Training Mode</th>
        <th>Attendance</th>
        <th>Faculty</th>
      </tr>
      </thead>';

$serialNo = 1; 
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo '<tr>
    <td>' . $serialNo++ . '</td>
           
            <td>' . htmlspecialchars($row['name']) . '</td>
            <td>' . htmlspecialchars($row['dept']) . '</td>
            <td>' . htmlspecialchars($row['location']) . '</td>
            <td>' . htmlspecialchars($row['program_id']) . '</td>
            <td>' . htmlspecialchars($row['program_name']) . '</td>
            <td>' . htmlspecialchars($row['duration']) . '</td>         
           
            <td>' . htmlspecialchars($row['empno']) . '</td>
            <td>' . htmlspecialchars($row['training_location']) . '</td>
            <td>' . htmlspecialchars($row['from_date']->format('Y-m-d')) . '</td>
            <td>' . htmlspecialchars($row['to_date']->format('Y-m-d')) . '</td>
            <td>' . htmlspecialchars($row['mandays']) . '</td>
            <td>' . htmlspecialchars($row['nature_of_training']) . '</td>
            <td>' . htmlspecialchars($row['training_subtype']) . '</td>
            <td>' . htmlspecialchars($row['training_mode']) . '</td>
            <td>' . htmlspecialchars($row['attendance']) . '</td>
            <td>' . htmlspecialchars($row['faculty']) . '</td>
          </tr>';
}

echo '</table>';

// Free resources
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>

<?php include '../footer.php';?>
