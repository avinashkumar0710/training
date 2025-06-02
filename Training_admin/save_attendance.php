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

// Ensure that both 'status' and 'attendance' are set in the POST data
if (isset($_POST['status']) && is_array($_POST['status']) && isset($_POST['attendance']) && is_array($_POST['attendance']) && isset($_POST['attendance_date']) && is_array($_POST['attendance_date'])) {
    $statusData = $_POST['status'];
    $attendanceData = $_POST['attendance'];
    $attendanceDateData = $_POST['attendance_date'];

    echo "<pre>";
    print_r($_POST['attendance']);
    print_r($_POST['status']);
    print_r($_POST['attendance_date']);
    echo "</pre>";

    foreach ($statusData as $userId => $days) {
        // Fetch user and program details from the database
        $queryDetails = "
        SELECT e.name, e.dept, e.loc_desc, e.location, e.empno ,r.id AS program_id, r.Program_name, r.duration, r.srl_no,  w.dept_code
        FROM [Complaint].[dbo].[request] r 
        JOIN [Complaint].[dbo].[emp_mas_sap] e ON e.empno = r.empno
        JOIN [Complaint].[dbo].[EA_webuser_tstpp] w ON 
        RIGHT(REPLICATE('0', 8) + CAST(w.emp_num AS VARCHAR(8)), 8) = e.empno
            WHERE r.id = ?
        ";
        $paramsDetails = array($userId);
        $stmtDetails = sqlsrv_query($conn, $queryDetails, $paramsDetails);

        if ($stmtDetails === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $rowDetails = sqlsrv_fetch_array($stmtDetails, SQLSRV_FETCH_ASSOC);

        if ($rowDetails) {
            $srl_no = $rowDetails['srl_no'];
            $name = $rowDetails['name'];
            $empno = $rowDetails['empno'];
            $dept_code =$rowDetails['dept_code'];
            $location = $rowDetails['location'];
            $dept = $rowDetails['dept'];
            $location = $rowDetails['loc_desc'];
            $program_id = $rowDetails['program_id'];
            $program_name = $rowDetails['Program_name'];
            $duration = intval(str_replace(' Day', '', $rowDetails['duration']));

            // Calculate the total attendance fraction
            $totalAttendanceFraction = 0;
            //$flag = 1;
            $flag = ($status == 0) ? 0 : 1; 
            
            // Process each day’s attendance
            foreach ($days as $day => $status) {
                $attendance = isset($attendanceData[$userId][$day]) ? $attendanceData[$userId][$day] : 0;

                // If status is 'Not Attend', set attendance to 0
                if ($status == 0 || $attendance === null || $attendance === '') {
                    $attendance = 0.0;
                }

                $totalAttendanceFraction += floatval($attendance);

                // Set flag based on attendance status
                $flag = ($status == 0) ? 0 : 1;

                // Format the attendance date for the current day
                $formattedDate = isset($attendanceDateData[$userId][$day]) ? date('Y-m-d', strtotime($attendanceDateData[$userId][$day])) : null;

                // Insert or update the record in the database
                $query = "INSERT INTO attendance_records (user_id, srl_no, name, dept, location, program_id, program_name, duration, day, attendance_status, attendance_fraction, total_attendance, attend_date, flag, dept_code, empno)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = array($userId, $srl_no, $name, $dept, $location, $program_id, $program_name, $duration, $day, $status, $attendance, $totalAttendanceFraction, $formattedDate, $flag, $dept_code, $empno);
                $stmt = sqlsrv_query($conn, $query, $params);

                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
            }

            // Update the request table with flag 8
            $updateQuery = "UPDATE [Complaint].[dbo].[request] SET flag='8' WHERE id=?";
            $updateParams = array($userId);
            $updateStmt = sqlsrv_query($conn, $updateQuery, $updateParams);

            if ($updateStmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
        }
    }

    echo "<script>
        alert('Attendance records saved successfully!');
        window.location.href = 'index.php';
    </script>";
} else {
    echo "<script>
        alert('Invalid data received.');
        window.location.href = 'index.php';
    </script>";
}


// Close the connection
sqlsrv_close($conn);
?>
