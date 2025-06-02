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

if (isset($_POST['status']) && is_array($_POST['status']) && isset($_POST['attendance']) && is_array($_POST['attendance']) && isset($_POST['attendance_date']) && is_array($_POST['attendance_date'])) {
    $statusData = $_POST['status'];
    $attendanceData = $_POST['attendance'];
    $attendanceDateData = $_POST['attendance_date'];

    // echo "<pre>";
    // print_r($attendanceData);
    // print_r($statusData);
    // print_r($attendanceDateData);
    // echo "</pre>";

    foreach ($statusData as $userId => $days) {
        // Fetch user and program details from the database
        $queryDetails = "
        SELECT 
            lt.[id], 
            lt.[Title], 
            lt.[Duration], 
            lt.[nature_of_training], 
            ls.[program_id], 
            ls.flag, 
            ls.[id] AS actual_id, 
            emp.[name] AS emp_name, 
            emp.[dept], 
            emp.loc_desc, 
            emp.location,
            CASE 
                WHEN LEFT(emp.empno, 2) = '00' THEN RIGHT(emp.empno, LEN(emp.empno) - 2) 
                ELSE emp.empno 
            END AS empno, -- Conditionally remove leading zeros
            EA_webuser_tstpp.dept_code
        FROM 
            [Complaint].[dbo].[link_tracking] lt 
        JOIN  
            [Complaint].[dbo].[link_show] ls ON lt.[id] = ls.[program_id] 
        JOIN 
            [Complaint].[dbo].[emp_mas_sap] emp ON ls.[empno] = emp.[empno] 
        JOIN 
            [Complaint].[dbo].[EA_webuser_tstpp] EA_webuser_tstpp ON 
            (CASE 
                WHEN LEFT(emp.empno, 2) = '00' THEN RIGHT(emp.empno, LEN(emp.empno) - 2) 
                ELSE emp.empno 
             END) = EA_webuser_tstpp.emp_num -- Join on modified empno
        WHERE 
            ls.[id] = ?;    
        ";
        $paramsDetails = array($userId);
        $stmtDetails = sqlsrv_query($conn, $queryDetails, $paramsDetails);

        if ($stmtDetails === false) {
            die("Error fetching details: " . print_r(sqlsrv_errors(), true));
        }

        $rowDetails = sqlsrv_fetch_array($stmtDetails, SQLSRV_FETCH_ASSOC);

        if ($rowDetails) {
            // Fetch necessary details
            $srl_no = $rowDetails['actual_id'];
            $name = trim($rowDetails['emp_name']);
            $dept = trim($rowDetails['dept']);
            $dept_code = $rowDetails['dept_code'];
            $loc_desc = trim($rowDetails['loc_desc']);
            $program_id = $rowDetails['program_id'];
            $program_name = trim($rowDetails['Title']);
            $location = trim($rowDetails['location']);
            $empno = $rowDetails['empno'];
            $duration_raw = $rowDetails['Duration'];
            preg_match('/\d+/', $duration_raw, $matches);
            $duration = isset($matches[0]) ? intval($matches[0]) : 0;

            // Calculate total attendance fraction
            $totalAttendanceFraction = 0;
            $flag = 2;

            foreach ($days as $day => $status) {
                $attendance = isset($attendanceData[$userId][$day]) ? $attendanceData[$userId][$day] : 0;

                // If status is 'Not Attend', set attendance to 0
                if ($status == 0 || $attendance === null || $attendance === '') {
                    $attendance = 0.0;
                }

                $totalAttendanceFraction += floatval($attendance);

                // Format attendance date
                $formattedDate = isset($attendanceDateData[$userId][$day]) ? date('Y-m-d', strtotime($attendanceDateData[$userId][$day])) : null;

                // Insert record
                $query = "INSERT INTO attendance_records (srl_no, name, dept, location, program_name, duration, day, attendance_status, attendance_fraction, total_attendance, attend_date, flag, dept_code, empno, loc_desc)
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = array($srl_no, $name, $dept, $loc_desc, $program_name, $duration, $day, $status, $attendance, $totalAttendanceFraction, $formattedDate, $flag, $dept_code, $empno, $location);

                // Print the values to be inserted
                // echo "<pre>";
                // echo "Length of name: " . strlen($name) . "\n";
                // echo "Length of dept: " . strlen($dept) . "\n";
                // echo "Length of location: " . strlen($location) . "\n";
                // echo "Length of program_name: " . strlen($program_name) . "\n";
                // echo "Length of duration: " . strlen($duration) . "\n";
                // echo "Length of day: " . strlen($day) . "\n";
                // echo "Length of attendance_status: " . strlen($status) . "\n";
                // echo "Length of attendance_fraction: " . strlen($attendance) . "\n";
                // echo "Length of total_attendance: " . strlen($totalAttendanceFraction) . "\n";
                // echo "Length of attend_date: " . strlen($formattedDate) . "\n";
                // echo "Length of flag: " . strlen($flag) . "\n";
                // echo "Length of dept_code: " . strlen($dept_code) . "\n";
                // echo "Length of empno: " . strlen($empno) . "\n";
                // echo "Length of loc_desc: " . strlen($loc_desc) . "\n";
                // echo "</pre>";
                

                $stmt = sqlsrv_query($conn, $query, $params);

                if ($stmt === false) {
                    die("Error inserting record: " . print_r(sqlsrv_errors(), true));
                }
            }

            // Update request table with flag 8
            $updateQuery = "UPDATE [Complaint].[dbo].[link_show] SET flag='8' WHERE id=?";
            $updateParams = array($userId);
            $updateStmt = sqlsrv_query($conn, $updateQuery, $updateParams);

            if ($updateStmt === false) {
                die("Error updating record: " . print_r(sqlsrv_errors(), true));
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
