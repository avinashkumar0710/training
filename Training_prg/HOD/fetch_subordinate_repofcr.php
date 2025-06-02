<?php
// Database connection
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die("Database connection failed.");
}

session_start();
$sessionemp = $_SESSION["emp_num"];
$sessionemp1 = str_pad($sessionemp, 8, "0", STR_PAD_LEFT);

// Get program name from AJAX request
$selectedProgram = $_POST['programName'] ?? '';

if (!empty($selectedProgram)) {
    // Fetch data from database
    $sqlRecords = "SELECT r.srl_no, r.empno, r.Program_name, r.year, a.name, 
                      COALESCE(r.flag, 0) AS flag, r.aprroved_time
               FROM [Complaint].[dbo].[request] r
               JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
               WHERE appr_empno = ? AND r.program_name = ?";


    $params = array($sessionemp, $selectedProgram);
    $stmtRecords = sqlsrv_query($conn, $sqlRecords, $params);

    if ($stmtRecords === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    echo '<h3>Subordinate Requested By You</h3>';
    echo '<div class="table" style="height:700px; overflow: auto;">';
    echo '<table class="table table-bordered border-success">';
    echo '<thead style="position: sticky; top: 0; background-color: beige;">
          <tr>
            <th>Srl. No</th>
            <th>Emp Name</th>
            <th>Program Name</th>
            <th>Year</th>
            <th>Status</th>
            <th>Date Time</th>
          </tr>
          </thead>';
    echo '<tbody>';

    $serialNo = 1;
    while ($row = sqlsrv_fetch_array($stmtRecords, SQLSRV_FETCH_ASSOC)) {
        $status = match ($row['flag']) {
            0 => '<span style="color:blue">Pending at Reporting Officer</span>',
            1 => '<span style="color:red">Rejected by Reporting Officer</span>',
            2 => '<span style="color:blue">Pending at HOD</span>',
            3 => '<span style="color:red">Rejected by HOD</span>',
            4 => '<span style="color:green">Training Approved by HOD</span>',
            5 => '<span style="color:blue">Pending from BUH</span>',
            6 => '<span style="color:green">Approved by BUH</span>',
            7 => '<span style="color:green">Overall Approved</span>',
            88 => '<span style="color:red">Rejected by HR</span>',
            99 => '<span style="color:green">Approved by HR</span>',
            default => 'Unknown',
        };

        echo '<tr>';
        echo '<td>' . $serialNo . '</td>';
        echo '<td>' . htmlspecialchars($row['name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['Program_name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['year']) . '</td>';
        echo "<td>$status</td>";
        echo '<td>' . ($row['aprroved_time'] ? $row['aprroved_time']->format('Y-m-d H:i:s') : 'NULL') . '</td>';
        echo '</tr>';
        $serialNo++;
    }

    echo '</tbody></table></div>';

    sqlsrv_free_stmt($stmtRecords);
}

sqlsrv_close($conn);
?>
