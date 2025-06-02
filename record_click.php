<?php
$serverName = "192.168.100.240";
$connectionInfo = array("Database" => "complaint", "UID" => "sa", "PWD" => "Intranet@123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Get POST data
$id = $_POST['id'];
$user_id = $_POST['user_id'];
$title = $_POST['title'];  //program name
$link = urldecode($_POST['link']);
$click_time = date("Y-m-d H:i:s"); // Current date and time in 'YYYY-MM-DD HH:MM:SS' format

// Format empno to 8 digits if it's 6 digits
$empno_for_query = (strlen($user_id) == 6) ? '00' . $user_id : $user_id;

// 1. First fetch program details from link_tracking
$program_sql = "SELECT program_id, Duration, Training_Subtype, duration_select,
                nature_of_training, Internal_External, faculty,
                CONVERT(varchar, open_time, 120) AS open_time, 
                CONVERT(varchar, close_time, 120) AS close_time
                FROM [Complaint].[dbo].[link_tracking] 
                WHERE id = ?";
$program_params = array($id);
$program_stmt = sqlsrv_query($conn, $program_sql, $program_params);

if ($program_stmt === false || !sqlsrv_has_rows($program_stmt)) {
    die("Program not found in database");
}

$program_data = sqlsrv_fetch_array($program_stmt, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($program_stmt);

// 2. Fetch employee details
$emp_sql = "SELECT loc_desc, dept, name, location 
            FROM [Complaint].[dbo].[emp_mas_sap] 
            WHERE empno = ?";
$emp_params = array($empno_for_query);
$emp_stmt = sqlsrv_query($conn, $emp_sql, $emp_params);

if ($emp_stmt === false || !sqlsrv_has_rows($emp_stmt)) {
    die("Employee not found in database");
}

$emp_data = sqlsrv_fetch_array($emp_stmt, SQLSRV_FETCH_ASSOC);
sqlsrv_free_stmt($emp_stmt);

// Current date and other fixed values
$current_date = date("Y-m-d");
$year = "2025-26";

// $currentYear = date("Y"); // Get the current year (e.g., 2025)
// $nextYearLastTwoDigits = substr(date("Y", strtotime("+1 year")), -2); // Get next year's last two digits (e.g., 26)

// $year = "$currentYear-$nextYearLastTwoDigits";

// echo $year; // Output: 2025-26 (for example)


// 3. Insert into attendance_records
// 3. Check if attendance record already exists
// $check_attendance_sql = "SELECT * FROM [Complaint].[dbo].[attendance_records] 
//                         WHERE empno = ? AND program_id = ?";
// $check_attendance_params = array($user_id, $program_data['program_id']);
// $check_attendance_stmt = sqlsrv_query($conn, $check_attendance_sql, $check_attendance_params);

$check_attendance_sql = "SELECT * FROM [Complaint].[dbo].[attendance_records] 
                         WHERE empno = ? AND program_id = ? AND attend_date = ?";
$check_attendance_params = array($user_id, $program_data['program_id'], $current_date);
$check_attendance_stmt = sqlsrv_query($conn, $check_attendance_sql, $check_attendance_params);


if ($check_attendance_stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (sqlsrv_has_rows($check_attendance_stmt)) {
    // Update existing attendance record with your specific field structure
    $update_attendance_sql = "UPDATE [Complaint].[dbo].[attendance_records]
                            SET [user_id] = ?,
                                [name] = ?,
                                [dept] = ?,
                                [location] = ?,
                                [program_name] = ?,
                                [total_attendance] = ?,
                                [srl_no] = ?,
                                [attend_date] = ?,
                                [dept_code] = ?,
                                [loc_desc] = ?,
                                [training_location] = ?,
                                [from_date] = ?,
                                [to_date] = ?,
                                [mandays] = ?,
                                [nature_of_training] = ?,
                                [training_subtype] = ?,
                                [training_mode] = ?,
                                [faculty] = ?,
                                [year] = ?,
                                [attendance] = 'A',
                                [act_Nact_flag] = 1
                            WHERE empno = ? AND program_id = ?";
    
    $update_attendance_params = array(
        $program_data['program_id'],  // user_id
        $emp_data['name'],
        $emp_data['dept'],
        $emp_data['loc_desc'],       // location
        $title,                      // program_name
        $program_data['duration_select'], // total_attendance
        $program_data['program_id'], // srl_no
        $current_date,               // attend_date
        $emp_data['dept'],          // dept_code
        $emp_data['location'],      // loc_desc
        $emp_data['loc_desc'],      // training_location
        $program_data['open_time'], // from_date
        $program_data['close_time'],// to_date
        $program_data['duration_select'], // mandays
        $program_data['nature_of_training'],
        $program_data['Training_Subtype'],
        $program_data['Internal_External'],
        $program_data['faculty'],
        $year,
        $user_id,                   // empno (WHERE clause)
        $program_data['program_id'] // program_id (WHERE clause)
    );
    
    $update_attendance_stmt = sqlsrv_query($conn, $update_attendance_sql, $update_attendance_params);
    if ($update_attendance_stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    sqlsrv_free_stmt($update_attendance_stmt);
} else {
    // Insert new attendance record with your specific field structure
    $insert_attendance_sql = "INSERT INTO [Complaint].[dbo].[attendance_records] 
                        ([user_id], [name], [dept], [location], [program_id], [program_name],
                         [duration], [day], [attendance_status], [attendance_fraction],
                         [total_attendance], [srl_no], [attend_date], [flag], [dept_code],
                         [empno], [loc_desc], [training_location], [from_date], [to_date],
                         [mandays], [nature_of_training], [training_subtype], [training_mode],
                         [attendance], [faculty], [year], [act_Nact_flag])
                        VALUES (?, ?, ?, ?, ?, ?, 
                                ?, 1, 1, 1, 
                                ?, ?, ?, 1, ?,
                                ?, ?, ?, ?, ?,
                                ?, ?, ?, ?,
                                'A', ?, ?, 1)";

$insert_attendance_params = array(
    $program_data['program_id'],  // user_id
    $emp_data['name'],
    $emp_data['dept'],
    $emp_data['loc_desc'],       // location
    $program_data['program_id'], // program_id
    $title,                      // program_name
    $program_data['Duration'],   // duration (from Duration field in query)
    $program_data['duration_select'], // total_attendance
    $program_data['program_id'], // srl_no
    $current_date,               // attend_date
    $emp_data['dept'],          // dept_code
    $user_id,                   // empno
    $emp_data['location'],      // loc_desc
    $emp_data['loc_desc'],      // training_location
    $program_data['open_time'], // from_date
    $program_data['close_time'],// to_date
    $program_data['duration_select'], // mandays
    $program_data['nature_of_training'],
    $program_data['Training_Subtype'],
    $program_data['Internal_External'],
    $program_data['faculty'],
    $year
);
    
    $insert_attendance_stmt = sqlsrv_query($conn, $insert_attendance_sql, $insert_attendance_params);
    if ($insert_attendance_stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    sqlsrv_free_stmt($insert_attendance_stmt);
}

sqlsrv_free_stmt($check_attendance_stmt);

// 4. Your existing link_show tracking code
$check_sql = "SELECT * FROM [Complaint].[dbo].[link_show] WHERE program_id = ? AND empno = ?";
$check_params = array($id, $user_id);
$check_stmt = sqlsrv_query($conn, $check_sql, $check_params);

if ($check_stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (sqlsrv_has_rows($check_stmt)) {
    // Update existing record
    $update_sql = "UPDATE [Complaint].[dbo].[link_show] 
                  SET click_time = ?, 
                      loc_desc = ?,
                      dept = ?,
                      emp_name = ?,
                      location = ?
                  WHERE program_id = ? AND empno = ?";
    $update_params = array(
        $click_time,
        $emp_data['loc_desc'],
        $emp_data['dept'],
        $emp_data['name'],
        $emp_data['location'],
        $id,
        $user_id
    );
    $update_stmt = sqlsrv_query($conn, $update_sql, $update_params);
    if ($update_stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    sqlsrv_free_stmt($update_stmt);
} else {
    // Insert new record
    $insert_sql = "INSERT INTO [Complaint].[dbo].[link_show] 
                  (program_id, empno, title, link, click_time, flag
                   ) 
                  VALUES (?, ?, ?, ?, ?, '77')";
    $insert_params = array(
        $id,
        $user_id,
        $title,
        $link,
        $click_time
       
    );
    $insert_stmt = sqlsrv_query($conn, $insert_sql, $insert_params);
    if ($insert_stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    sqlsrv_free_stmt($insert_stmt);
}

sqlsrv_free_stmt($check_stmt);
sqlsrv_close($conn);

echo "Click and attendance recorded successfully";
?>