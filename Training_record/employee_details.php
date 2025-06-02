<?php
// Start a session
session_start();
if (!isset($_SESSION["emp_num"])) {
    header("location:login.php");
    exit;
}

// Retrieve employee ID and flag from the URL
$emp_id = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : null;
$flag = isset($_GET['flag']) ? (int)$_GET['flag'] : null;
//echo 'flag' .$flag;

error_log("emp_id: " . $emp_id);
error_log("flag: " . $flag);

// Validate employee ID input
if ($emp_id === null) {
    echo "Invalid employee ID.";
    exit;
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

// Dynamic Query Based on Flag
if ($flag !== 0) {
    // If flag is provided, include it in the query
    $query = "SELECT *   
              FROM attendance_records 
              WHERE name = ? AND flag = ?";
    $params = array($emp_id, $flag);
} else {
    // If flag is not provided, exclude it from the query
    $query = "SELECT *   
              FROM attendance_records 
              WHERE name = ?";
    $params = array($emp_id);
}

// Execute the query
$stmt = sqlsrv_query($conn, $query, $params);

// Check if any records were found
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch all attendance records for the employee
$attendanceRecords = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $attendanceRecords[] = $row;
}

// Close the statement if necessary
sqlsrv_free_stmt($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Attendance Details</title>
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
<link href="https://fonts.googleapis.com/css2?family=Raleway:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <style>
         body{
            font-family: "Raleway", sans-serif;
            margin-top:20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: pink; /* Light pink background for headers */
        }
        .total-row {
            font-weight: bold;
            background-color: #e9a85a; /* Background color for total row */
        }
    </style>
</head>
<body>
<div class="container-fluid">
<h2>Attendance Details for <span style="background-color: yellow; padding: 2px;"><i><?php echo htmlspecialchars($emp_id); ?></i></span></h2>

    <?php if ($attendanceRecords): ?>
        <div style="overflow-y: auto; max-height: 800px;"> <!-- Wrapper div for scrollable table -->
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
            $serialNumber = 1;         // Initialize serial number
            $totalManDays = 0;         // Initialize total variable
            foreach ($attendanceRecords as $record):
                $totalManDays += $record['mandays']; // Increment totalManDays

                // Fetch grade based on empno
                $empno = htmlspecialchars($record['empno']); // Use $record here
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
                    <td><?php echo htmlspecialchars($record['program_id']); ?></td>
                    <td><?php echo htmlspecialchars($record['name']); ?></td>
                    <td><?php echo htmlspecialchars($record['empno']); ?></td>
                    <td> <?php echo $loc_desc; ?></td>
                    <td><?php echo htmlspecialchars($record['dept']); ?></td>
                    <td><?php echo htmlspecialchars($grade); ?></td>
                    <td><?php echo $employee_grp; ?></td>
                    <td><?php echo htmlspecialchars($record['program_name']); ?></td>
                    <td><?php echo htmlspecialchars($record['nature_of_training']); ?></td>
                        <td><?php echo htmlspecialchars($record['training_subtype']); ?></td>
                        <td><?php echo htmlspecialchars($record['training_mode']); ?></td>
                        <td><?php echo htmlspecialchars($record['faculty']); ?></td>
                        <!-- <td><?php echo htmlspecialchars($record['attendance']); ?></td> -->
                        <td>
                            <?php
                           $attendanceCode = htmlspecialchars($record['attendance']);
                           $displayText = ($attendanceCode === 'A') ? 'Attend' : 'Not Attend';
                           $color = ($attendanceCode === 'A') ? 'Blue' : 'red';
                           echo '<span style="color: ' . $color . ';"><em><b>' . $displayText . '</b></em></span>';
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($record['training_location']); ?></td>
                    <td><?php echo htmlspecialchars($record['duration']); ?></td>
                    <td><?php echo htmlspecialchars($record['from_date']->format('Y-m-d')); ?></td>
                    <td><?php echo htmlspecialchars($record['to_date']->format('Y-m-d')); ?></td>
                    <td><?php echo htmlspecialchars($record['mandays']); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="18" style="text-align: right;">Total ManDays:</td>
                <td><?php echo htmlspecialchars($totalManDays); ?></td>
            </tr>
        </tbody>
        </table>
        </div> <!-- End of wrapper div -->
    <?php else: ?>
        <p>No attendance records found for this employee.</p>
    <?php endif; ?>


    <!-- Go Back Button -->
    <div style="margin-top: 20px;">
        <a href="training_dashboard.php" class="btn btn-primary" style="text-decoration: none; padding: 10px 15px; background-color: #007bff; color: white; border-radius: 5px;">
        Go to Training Dashboard</a>
        <!-- <a href="employee_details_download_excel.php?emp_id=<?php echo urlencode($emp_id); ?>" class="btn btn-success">Download Excel</a> -->
       
    </div>
</div>
</body>
</html>

<?php
// Close the database connection
sqlsrv_close($conn);
?>
