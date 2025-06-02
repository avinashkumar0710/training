<?php
session_start();
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $srl_no = $_POST['srl_no'];
    $program_name = $_POST['Program_name'];
    $nature_training = $_POST['nature_training'];
    $duration = $_POST['duration'];
    $faculty = $_POST['faculty'];
    $training_mode = $_POST['training_mode'];
    //$tentative_date = $_POST['tentative_date'];
    $day_from = $_POST['day_from'];
    $day_to = $_POST['day_to'];
    $internal_external = $_POST['Internal_external'];
    $year = $_POST['year'];
    $target_group = $_POST['target_group'];
    $venue = $_POST['venue'];
    //$hostel_reqd = $_POST['hostel_reqd'];
    $coordinator = $_POST['coordinator'];
    $remarks = $_POST['admin_remarks'];
    $closed_date = $_POST['Closed_date'];
    $employee_grp = $_POST['Employee_grp'];
    //echo    $employee_grp;

    //extra added as per changes
    $open_for = $_POST['open_for'];
    $training_code = $_POST['training_code'];
    $faculty_Intrnl_extrnl = $_POST['faculty_Intrnl_extrnl'];
    $training_subtype = $_POST['training_subtype'];
    $available_seats = $_POST['available_seats'];
    
    // Plants Data
    $NS01 = $_POST['NS01'];
    $NS02 = $_POST['NS02'];
    $NS03 = $_POST['NS03'];
    $NS04 = $_POST['NS04'];

    // Grades Data
    $E0 = $_POST['E0'];
    $E1 = $_POST['E1'];
    $E2 = $_POST['E2'];
    $E3 = $_POST['E3'];
    $E4 = $_POST['E4'];
    $E5 = $_POST['E5'];
    $E6 = $_POST['E6'];
    $E7 = $_POST['E7'];
    $E8 = $_POST['E8'];
    $E9 = $_POST['E9'];

    // Prepare the SQL Update Query
    $query = "UPDATE [Complaint].[dbo].[training_mast] 
              SET srl_no=?, Program_name=?, nature_training=?, duration=?, faculty=?, 
                  training_mode=?, day_from=?, day_to=?, 
                  Internal_external=?, year=?, target_group=?, venue=?, 
                  coordinator=?, admin_remarks=?, Closed_date=? , open_for=?, faculty_Intrnl_extrnl=?, training_subtype=?, available_seats=?, training_code=?
              WHERE id=?";

    $params = array(
        $srl_no, $program_name, $nature_training, $duration, $faculty,
        $training_mode, $day_from, $day_to,
        $internal_external, $year, $target_group, $venue, 
        $coordinator, $remarks, $closed_date, $open_for, $faculty_Intrnl_extrnl, $training_subtype, $available_seats, $training_code,
        $id
    );

    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt) {
        // Now update plants and grades in the related table
        $query2 = "UPDATE [Complaint].[dbo].[training_mast_com] 
                   SET NS01=?, NS02=?, NS03=?, NS04=?, 
                       E0=?, E1=?, E2=?, E3=?, E4=?, E5=?, E6=?, E7=?, E8=?, E9=? , Employee_grp=?
                   WHERE srl_no=?";

        $params2 = array(
            $NS01, $NS02, $NS03, $NS04,
            $E0, $E1, $E2, $E3, $E4, $E5, $E6, $E7, $E8, $E9,$employee_grp,
            $srl_no
        );

        $stmt2 = sqlsrv_query($conn, $query2, $params2);

        if ($stmt2) {
            echo "<script>alert('Training Updated Successfully!'); 
            window.parent.closeModal();
            window.parent.location.reload();
            </script>";
        } else {
            echo "Error updating plants/grades: " . print_r(sqlsrv_errors(), true);
        }
    } else {
        echo "Error updating training: " . print_r(sqlsrv_errors(), true);
    }

    sqlsrv_close($conn);
}


?>
