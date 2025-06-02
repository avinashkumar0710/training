<?php
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
// Start a session
session_start();
if (!isset($_SESSION["emp_num"])) {
    header("location:login.php");
    exit;
}

$sessionemp = $_SESSION["emp_num"];

// Add '00' in front if session value has only 6 digits
if(strlen($sessionemp) == 6) {
    $sessionemp = '00' . $sessionemp;
}

// Retrieve department from the URL
$dept = isset($_GET['dept_code']) ? urldecode($_GET['dept_code']) : null;
$flag = isset($_GET['flag']) ? (int)$_GET['flag'] : null;
//echo ''.$dept;
//$dept_details = isset($_GET['dept']) ? urldecode($_GET['dept']) : null;
//$dept_details = urldecode($_GET['dept_details']);
$query = "SELECT DeptName 
          FROM [Complaint].[dbo].[EA_DeptCode_Mas] 
          WHERE dept_id = ?";
$params = array($dept); // Pass dept_id as a parameter
$stmt = sqlsrv_query($conn, $query, $params);

// Check if the query executed successfully
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch the result
$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

$deptname = $row['DeptName'];
//echo 'deptname'. $deptname;

// Check if a record was found
// if ($row) {
//     echo "Department Name: " . htmlspecialchars($row['DeptName']);
// } else {
//     echo "No department found for the given code.";
// }

// Validate department input
if ($dept === null ) {
    echo "Invalid department or flag.";
    exit;
}

// If flag is neither 1 nor 2, set to query both 1 and 2
if ($flag !== 1 && $flag !== 2  && $flag !== 3) {
    $flagCondition = "(flag = 1 OR flag = 2 OR flag = 3)"; // Both flags
} else {
    $flagCondition = "flag = $flag"; // Specific flag
}

//var_dump($flag);

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

// Query to fetch department-specific data (modify this as per your table structure)
$query = "SELECT name, program_name, duration, attend_date, attendance_fraction, total_attendance FROM attendance_records WHERE dept_code = ? and $flagCondition ";
$params = array($dept);
$stmt = sqlsrv_query($conn, $query, $params);

// Check if any records were found
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch all rows
$rows = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $rows[] = $row;
}

// Close the statement
sqlsrv_free_stmt($stmt);

// If no rows found, set $has_records to false
$has_records = count($rows) > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">



    <style>
          body{
            font-family: "Raleway", sans-serif;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }
        td {
            text-align: left;   
        }
        .back-button {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
<h2><u>Details for Department:</u>  <span style="background-color: yellow; padding: 2px;"><?php echo htmlspecialchars($deptname); ?></span></h2><br>

<?php if ($has_records): ?>
    <div style="overflow-y: auto; max-height: 750px;"> <!-- Wrapper div for scrollable table -->
    <table class="table table-success table-hover table-bordered border-success" border="2">
        <thead>
            <tr>
                <th style="background-color: pink;">S. No.</th>
                <th style="background-color: pink;">Employee Name</th>
                <th style="background-color: pink;">Program Name</th>
                <th style="background-color: pink;">Program days</th>
                <th style="background-color: pink;">Attend Date</th>
                <th style="background-color: pink;">ManDays</th>
            </tr>
        </thead>
        <tbody>
        <?php 
          $serialNumber = 1; // Initialize serial number
        $totalManDays = 0; // Initialize total variable
        foreach ($rows as $row): 
            $totalManDays += $row['attendance_fraction']; // Sum up the ManDays (total_attendance)
        ?>
                <tr>
                <td><?php echo $serialNumber++; ?></td> <!-- Serial number column -->
                <td>
                    <a href="employee_details.php?name=<?php echo urlencode($row['name']); ?>&flag=<?php echo $flag; ?>" style="text-decoration: none; color: inherit;">
                        <u><?php echo htmlspecialchars($row['name']); ?></u>
                    </a>
                </td>
                    <td><?php echo htmlspecialchars($row['program_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['duration']); ?></td>
                    <td><?php echo htmlspecialchars($row['attend_date']->format('Y-m-d')); ?></td>
                    <td style="background-color: #e9a85a;"><i><b><?php echo htmlspecialchars($row['attendance_fraction']); ?></b></i></td>
                </tr>
            <?php endforeach; ?>
            <tr>
            <td colspan="5" style="text-align: right;"><b>Grand Total of ManDays</b></td>
            <td style="background-color: #f2f2f2;"><i><b><?php echo $totalManDays; ?></b></i></td>
        </tr>
        </tbody>
    </table>
    </div> <!-- End of wrapper div -->
<?php else: ?>
    <p>No records found for this department.</p>
<?php endif; ?>

<!-- Go Back Button -->
<div style="margin-top: 20px;">
    <a href="training_dashboard.php" class="btn btn-primary" style="text-decoration: none; padding: 10px 15px; background-color: #007bff; color: white; border-radius: 5px;">Go to Training Dashboard</a>
</div>
</div>
</body>
</html>

<?php
// Close the database connection
sqlsrv_close($conn);
?>


<?php include '../footer.php';?>