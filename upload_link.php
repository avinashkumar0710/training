<?php
session_start();

// Database Connection
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get all form values
    $program_id = $_POST['program_id'];
    $title = $_POST['title'];
    $date = date('Y-m-d');
    $timing_to_date = $_POST['timing_to_date']; // Date part
    $timing_from_date = $_POST['timing_from_date']; // Date part

    // Combine hour, minute, and ampm for timing FROM and TO
    $timing_from = $_POST['hour_from'] . ':' . $_POST['minute_from'] . ' ' . $_POST['ampm_from'];
    $timing_to = $_POST['hour_to'] . ':' . $_POST['minute_to'] . ' ' . $_POST['ampm_to'];

    // Convert the 12-hour format to 24-hour format for SQL storage
    $timing_from_24h = date("H:i:s", strtotime($timing_from));
    $timing_to_24h = date("H:i:s", strtotime($timing_to));

    // Combine the date with the 24-hour formatted time
    $timing_from_datetime = $timing_from_date . ' ' . $timing_from_24h . '.000';
    $timing_to_datetime = $timing_to_date . ' ' . $timing_to_24h . '.000';

    $timing = date('h:i A'); // Current time in 12-hour format with AM/PM

    // Fetch employee details
    $empno = $_SESSION['emp_num'];
if (strlen($empno) == 6) {
    $empno = '00' . $empno;  // Makes it 8 digits
} elseif (strlen($empno) != 8) {
    die("Invalid employee number format");
}
    $emp_query = "SELECT loc_desc, dept, name, location 
                 FROM [Complaint].[dbo].[emp_mas_sap] 
                 WHERE empno = ?";
    
    $emp_params = array($empno);
    $emp_stmt = sqlsrv_query($conn, $emp_query, $emp_params);
    
    if ($emp_stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    
    $emp_data = sqlsrv_fetch_array($emp_stmt, SQLSRV_FETCH_ASSOC);
    
    if (!$emp_data) {
        die("Employee not found in emp_mas_sap table");
    }
    
    $target_group = $_POST['target_group'];
    $faculty = $_POST['faculty'];
    $nature_of_training = $_POST['nature_of_training'];
    $training_subtype = $_POST['training_subtype']; // New field
    $training_mode = $_POST['training_mode'];
    $duration = $_POST['duration'];
    $duration_select = $_POST['duration_select']; 
    $internal_external = $_POST['internal_external'];
    
    $link = $_POST['link'];
    $user_id = $_SESSION['emp_num'];
    $upload_time = date('Y-m-d H:i:s');
    $flag = 'U';
    $remarks = $_POST['remarks'];

    // Insert query with new fields
    $query = "INSERT INTO [Complaint].[dbo].[link_tracking] 
              (program_id, user_id, title, link, upload_time, flag, date, timing, 
               open_time, close_time, target_group, faculty, nature_of_training, 
               training_subtype, training_mode, duration, duration_select, plant, name, loc_desc, dept, internal_external, remarks) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $params = array(
        $program_id,
        $user_id, 
        $title, 
        $link, 
        $upload_time, 
        $flag, 
        $date,  
        $timing, 
        $timing_to_datetime, 
        $timing_from_datetime, 
        $target_group, 
        $faculty, 
        $nature_of_training,
        $training_subtype, // New parameter
        $training_mode, 
        $duration, 
        $duration_select,
        $emp_data['location'],
        $emp_data['name'],
        $emp_data['loc_desc'],
        $emp_data['dept'],      
        
        $internal_external, 
        $remarks
    );

    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

    echo "<script>alert('Link successfully uploaded!'); window.location.href = 'mainpage.php';</script>";
} else {
    echo "Invalid request.";
}
?>
