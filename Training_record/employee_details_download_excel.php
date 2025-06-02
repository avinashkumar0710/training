<?php
// employee_details_download_excel.php

// Include database connection details (replace with your actual credentials)
$serverName = "192.168.100.240";
$database = "complaint";
$username = "sa";
$password = "Intranet@123";

// Get the flag value from the URL parameter
$flag = isset($_GET['flag']) ? intval($_GET['flag']) : null;

// Determine the flag condition for the SQL query
if ($flag !== 1 && $flag !== 2 && $flag !== 3 && $flag !== null) {
    $flagCondition = "(ar.flag = 1 OR ar.flag = 2 OR ar.flag = 3)"; // Query all if invalid flag
} elseif ($flag === null) {
    $flagCondition = "(ar.flag = 1 OR ar.flag = 2 OR ar.flag = 3)"; // Query all if no flag provided
} else {
    $flagCondition = "ar.flag = " . intval($flag); // Specific flag
}

try {
    $conn = sqlsrv_connect($serverName, array("Database" => $database, "UID" => $username, "PWD" => $password));
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Get the employee ID from the URL parameter (adjust as needed)
    $empId = isset($_GET['emp_id']) ? $_GET['emp_id'] : '';

    // SQL query to fetch attendance records for the specific employee ID
    $sql = "SELECT
    ROW_NUMBER() OVER (ORDER BY ar.name) AS SNo,
    ar.program_id as ProgramID,
    ar.name AS EmployeeName,
    ar.empno AS EmployeeNumber,
    ems.loc_desc As Plant,
    ar.dept AS Department,
    ems.grade AS Grade,
    ems.employee_grp AS EmployeeGroupCode,
    ar.program_name AS ProgramName,
    ar.nature_of_training AS NatureOfTraining,
    ar.training_subtype AS TrainingSubtype,
    ar.training_mode AS TrainingMode,
    ar.faculty AS Faculty,
    ar.attendance AS Attendance,
    ar.training_location AS TrainingLocation,
    ar.duration AS ProgramDays,
    FORMAT(ar.from_date, 'yyyy-MM-dd') AS FromDate,
    FORMAT(ar.to_date, 'yyyy-MM-dd') AS ToDate,
    ar.mandays AS ManDays
FROM [Complaint].[dbo].[attendance_records] ar
LEFT JOIN [Complaint].[dbo].[emp_mas_sap] ems ON ar.empno = ems.empno 
ORDER BY ar.name";

    $stmt = sqlsrv_prepare($conn, $sql, array(&$empId));
    if (!$stmt) {
        die(print_r(sqlsrv_errors(), true));
    }
    sqlsrv_execute($stmt);

    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . str_replace(' ', '_', $empId) . '_training_details.xls"');
    header('Cache-Control: max-age=0');
    $external='';

    if ($flag == 2) {
        $external = 'Internal';
    } elseif ($flag == 3) {
        $external = 'External';
    } else {
        $external = 'N/A'; // Or any other default value if flag is neither 2 nor 3
    }
    // Start the Excel table
    echo '<table border="1">';

    // Output table header
    echo '<tr>';
    echo '<th>S.No.</th>';
    echo '<th>Program ID</th>';
    echo '<th>Employee Name</th>';
    echo '<th>Employee No</th>';
    echo '<th>Plant</th>';
    echo '<th>Department</th>';
    echo '<th>Grade</th>';
    echo '  ';
    echo '<th>Program Name</th>';
    echo '<th>Nature of Training</th>';
    echo '<th>Training Subtype</th>';
    echo '<th>Training Mode</th>';
    echo '<th>Faculty</th>';
    echo '<th>Attendance</th>';
    echo '<th>Training Location</th>';
    echo '<th>Program Days</th>';
    echo '<th>From Date</th>';
    echo '<th>To Date</th>';
    echo '<th>ManDays</th>';
    echo '<th>Internal/External</th>';
    echo '</tr>';

    // Output table data and calculate total mandays
    $totalManDays = 0;
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $employeeGroup = '';
        if ($row['EmployeeGroupCode'] === 'A') {
            $employeeGroup = 'Executive';
        } elseif ($row['EmployeeGroupCode'] === 'B') {
            $employeeGroup = 'Non-Executive';
        } else {
            $employeeGroup = 'N/A';
        }

        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['SNo']) . '</td>';
        echo '<td>' . htmlspecialchars($row['ProgramID']) . '</td>';
        echo '<td>' . htmlspecialchars($row['EmployeeName']) . '</td>';
        echo '<td>' . htmlspecialchars($row['EmployeeNumber']) . '</td>';
        echo '<td>' . htmlspecialchars($row['Plant']) . '</td>';
        echo '<td>' . htmlspecialchars($row['Department']) . '</td>';
        echo '<td>' . htmlspecialchars($row['Grade']) . '</td>';
        echo '<td>' . htmlspecialchars($employeeGroup) . '</td>';
        echo '<td>' . htmlspecialchars($row['ProgramName']) . '</td>';
        echo '<td>' . htmlspecialchars($row['NatureOfTraining']) . '</td>';
        echo '<td>' . htmlspecialchars($row['TrainingSubtype']) . '</td>';
        echo '<td>' . htmlspecialchars($row['TrainingMode']) . '</td>';
        echo '<td>' . htmlspecialchars($row['Faculty']) . '</td>';
        echo '<td>' . htmlspecialchars($row['Attendance']) . '</td>';
        echo '<td>' . htmlspecialchars($row['TrainingLocation']) . '</td>';
        echo '<td>' . htmlspecialchars($row['ProgramDays']) . '</td>';
        echo '<td>' . htmlspecialchars($row['FromDate']) . '</td>';
        echo '<td>' . htmlspecialchars($row['ToDate']) . '</td>';
        echo '<td>' . htmlspecialchars($row['ManDays']) . '</td>';
        echo '<td>' . htmlspecialchars($external) . '</td>';
        echo '</tr>';
        $totalManDays += (int) $row['ManDays'];
    }

    // Output total mandays row at the end
    echo '<tr>';
    echo '<td colspan="20" style="text-align: right; font-weight: bold;">Total ManDays:</td>';
    echo '<td style="font-weight: bold;">' . htmlspecialchars($totalManDays) . '</td>';
    echo '</tr>';

    // End the Excel table
    echo '</table>';

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    exit();

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>