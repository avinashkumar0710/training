<?php 
// Start a session and check for authentication
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
    exit; // Make sure script stops after redirect
}

$sessionemp = $_SESSION["emp_num"];

// Add '00' in front if session value has only 6 digits
if(strlen($sessionemp) == 6) {
    $sessionemp = '00' . $sessionemp;
}

//echo $sessionemp;
// Database connection
$serverName = "192.168.100.240"; // Note double backslashes for escaping
$connectionInfo = array("Database" => "complaint", "UID" => "sa", "PWD" => "Intranet@123");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch the department and month from the query string (URL parameters)
$dept = isset($_GET['dept_code']) ? urldecode($_GET['dept_code']) : null;
$month = isset($_GET['month']) ? (int)$_GET['month'] : null;
$flag = isset($_GET['flag']) ? (int)$_GET['flag'] : null;
$dept_details = urldecode($_GET['dept_details']); // Decode the dept_details


// Validate department and month input (no need to validate flag here)
if ($dept === null || $month === null) {
    echo "Invalid department or month.";
    exit;
}

// If flag is neither 1 nor 2, set to query both 1 and 2
if ($flag !== 1 && $flag !== 2 && $flag !== 3) {
    $flagCondition = "(flag = 1 OR flag = 2 OR flag = 3)"; // Both flags
} else {
    $flagCondition = "flag = $flag"; // Specific flag
}

//echo $flag;
// Function to get month name
function getMonthName($monthNum) {
    $months = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
    return $months[$monthNum] ?? 'Unknown';
}

// Query to fetch attendance details for the given department and month
//$query = "SELECT *  FROM attendance_records WHERE dept = ? AND MONTH(attend_date) = ? AND flag = $flag";
$query = "SELECT * FROM attendance_records WHERE dept_code = ? AND MONTH(attend_date) = ? AND $flagCondition AND act_Nact_flag='1'";
$params = array($dept, $month);
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Check if there are any records
$has_records = false;
$rows = array();
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $rows[] = $row;
    $has_records = true;
}

// Free statement resource
sqlsrv_free_stmt($stmt);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Attendance Details for <?php echo htmlspecialchars($dept_details); ?></title>


    <title>Training | Home</title>
    <link rel="icon" href="../images/analysis.png">
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
    <link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap"
        rel="stylesheet">


    <style>
    body {
        font-family: "Raleway", sans-serif;
        margin-top: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid #ddd;
        padding: 8px;
    }

    tr,
    th {
        background-color: pink;
        text-align: left;

    }
    </style>
</head>

<body>
    <div class="container-fluid">
        <h2>Attendance Details for <span
                style="background-color: yellow; padding: 2px;"><i><?php echo htmlspecialchars($dept_details); ?></i></span>
            in
            <i><span style="background-color: yellow; padding: 2px;"><?php echo getMonthName($month); ?></span></i>
        </h2>

        <?php if ($has_records): ?>
        <div style="overflow-y: auto; max-height: 800px;">
            <!-- Wrapper div for scrollable table -->
            <table class="table table-success table-hover table-bordered border-success" border="2">
                <thead>
                    <tr>
                        <th style="background-color: pink;">S.No.</th>
                        <th style="background-color: pink;">Program ID</th>
                        <th style="background-color: pink;">Employee Name</th>
                        <th style="background-color: pink;">Employee No</th>
                        <th style="background-color: pink;">Plant</th>
                        <th style="background-color: pink;">Department</th>
                        <th style="background-color: pink;">Grade</th>
                        <th style="background-color: pink;">Employee Group</th>
                        <th style="background-color: pink;">Program Name</th>
                        <th style="background-color: pink;">Nature of Training</th>
                        <th style="background-color: pink;">Training Subtype</th>
                        <th style="background-color: pink;">Training Mode</th>
                        <th style="background-color: pink;">Faculty</th>
                        <th style="background-color: pink;">Attendance</th>
                        <th style="background-color: pink;">Training Location</th>
                        <th style="background-color: pink;">Program Days</th>
                        <th style="background-color: pink;">From Date</th>
                        <th style="background-color: pink;">To Date</th>
                        <th style="background-color: pink;">ManDays</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
        $serialNumber = 1;
        $totalManDays = 0; // Initialize total variable
        foreach ($rows as $row):
            $totalManDays += $row['mandays']; // Sum up the ManDays (total_attendance)

            // Fetch grade based on empno
            $empno = htmlspecialchars($row['empno']);
            $grade = '';
             // Format empno if it's 6 digits
             $formattedEmpno = $empno;
             if (strlen($empno) === 6) {
                 $formattedEmpno = '00' . $empno;
             }
            $sqlGrade = "SELECT grade, employee_grp, loc_desc FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = '$formattedEmpno'";
            $stmtGrade = sqlsrv_query($conn, $sqlGrade);
            if ($stmtGrade && $gradeResult = sqlsrv_fetch_array($stmtGrade, SQLSRV_FETCH_ASSOC)) {
                $grade = htmlspecialchars($gradeResult['grade']);
                $loc_desc = htmlspecialchars($gradeResult['loc_desc']);
                $employee_grp_code = htmlspecialchars($gradeResult['employee_grp']);
                $employee_grp = ''; // Initialize employee_grp
            
                if ($employee_grp_code === 'A') {
                    $employee_grp = 'Executive';
                } elseif ($employee_grp_code === 'B') {
                    $employee_grp = 'Non-Executive';
                } else {
                    $employee_grp = 'N/A'; // Or handle other cases as needed
                }
            
            } else {
                $grade = 'N/A';
                $employee_grp = 'N/A';
                $loc_desc = 'N/A';
                // Handle errors if needed: print_r(sqlsrv_errors(), true);
            }
            sqlsrv_free_stmt($stmtGrade);

           
        ?>
                    <tr>
                        <td><?php echo $serialNumber++; ?></td>
                        <td><?php echo htmlspecialchars($row['program_id']); ?></td>
                        <td>
                            <a href="employee_details.php?name=<?php echo urlencode($row['name']); ?>&flag=<?php echo $flag; ?>"
                                style="text-decoration: none; color: inherit;">
                                <u><?php echo htmlspecialchars($row['name']); ?></u>
                            </a>
                        </td>
                        <td> <?php echo $empno; ?></td>
                        <td> <?php echo $loc_desc; ?></td>
                        <td><?php echo htmlspecialchars($row['dept']); ?></td>
                        <td><?php echo $grade; ?></td><td><?php echo $employee_grp; ?></td>
                        <td><?php echo htmlspecialchars($row['program_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['nature_of_training']); ?></td>
                        <td><?php echo htmlspecialchars($row['training_subtype']); ?></td>
                        <td><?php echo htmlspecialchars($row['training_mode']); ?></td>
                        <td><?php echo htmlspecialchars($row['faculty']); ?></td>
                        <!-- <td><?php echo htmlspecialchars($row['attendance']); ?></td> -->
                        <td>
                            <?php
                           $attendanceCode = htmlspecialchars($row['attendance']);
                           $displayText = ($attendanceCode === 'A') ? 'Attend' : 'Not Attend';
                           $color = ($attendanceCode === 'A') ? 'Blue' : 'red';
                           echo '<span style="color: ' . $color . ';"><em><b>' . $displayText . '</b></em></span>';
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['training_location']); ?></td>
                        <td><?php echo htmlspecialchars($row['duration']); ?></td>
                        <td><?php echo htmlspecialchars($row['from_date']->format('Y-m-d')); ?></td>
                        <td><?php echo htmlspecialchars($row['to_date']->format('Y-m-d')); ?></td>
                        <td style="background-color: #e9a85a;">
                            <i><b><?php echo htmlspecialchars($row['mandays']); ?></b></i></td>
                    </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="18" style="text-align: right;"><b>Grand Total of ManDays</b></td>
                        <td style="background-color: #f2f2f2;"><i><b><?php echo $totalManDays; ?></b></i></td>
                    </tr>
                </tbody>
            </table>

            


        </div>

        <!-- Go Back Button -->
        <div style="margin-top: 20px;">
                <a href="training_dashboard.php" class="btn btn-primary"
                    style="text-decoration: none; padding: 10px 15px; background-color: #007bff; color: white; border-radius: 5px;">
                    Go to Training Dashboard</a>
                    <?php
                    $flag = isset($_GET['flag']) && in_array($_GET['flag'], ['1', '2', '3']) ? $_GET['flag'] : '';
                    ?>

                    <a href="details_download_excel.php<?php echo $flag !== '' ? '?flag=' . htmlspecialchars($flag) : ''; ?>"
                    class="btn btn-success"
                    style="text-decoration: none; padding: 10px 15px; background-color: #28a745; color: white; border-radius: 5px; margin-left: 10px;">
                        Download Excel
                    </a>

            </div>
    </div> <!-- End of wrapper div -->
    <?php else: ?>
    <p>No attendance records found for this department and month.</p>
    <?php endif; ?>

</body>

</html>

<?php
// Close the database connection
sqlsrv_close($conn);
?>


<?php include '../footer.php';?>