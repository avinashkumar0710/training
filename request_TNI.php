<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $employeeNumber=$_SESSION["emp_num"];
    // Add '00' in front if session value has only 6 digits
    if(strlen($employeeNumber) == 6) {
        $employeeNumber = '00' . $employeeNumber;
    }

    //echo 'employeeNumber : ' .$employeeNumber;

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
    ?>
<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request'])) {
    // Ensure 'selectedData' is set in the post data
    if (isset($_POST['selectedData'])) {
        $selectedData = json_decode($_POST['selectedData'], true);

        // Establish the database connection
        $serverName = "192.168.100.240";
        $connectionOptions = array(
            "Database" => "complaint",
            "UID" => "sa",
            "PWD" => "Intranet@123"
        );
        $conn = sqlsrv_connect($serverName, $connectionOptions);

        if (!$conn) {
            die(print_r(sqlsrv_errors(), true));
        }

       // SQL query to fetch rep_ofcr and hod_ro
$sql = "SELECT rep_ofcr, hod_ro, location FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = '$employeeNumber'";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}


// Fetch the rep_ofcr and hod_ro values
if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $rep_ofcr = $row['rep_ofcr'];
    $hod_ro = $row['hod_ro'];
    $location = $row['location'];
    //echo 'plant'.$location;
    
    // Check if hod_ro is available in rep_ofcr
    if (strpos($rep_ofcr, $hod_ro) !== false) {
        // If hod_ro is found in rep_ofcr, set flag to 'Pending at HOD'
        //$flag = 'Pending at HOD';
        $flag = '4';
    } else {
        // Otherwise, set flag to 'Pending at R1'
        //$flag = 'Pending at R1';
        $flag = '0';
    }
    
    // Use $rep_ofcr and $hod_ro as needed
    //echo '$rep_ofcr: ' . $rep_ofcr;
    // echo '$hod_ro: ' . $hod_ro;
    // echo '$flag: ' . $flag;
}



        $successFlag = true;

        // Loop through selected data and fetch additional information based on ID
        foreach ($selectedData as $data) {
    $selectedId = $data['srl_no'];
    $remarksData = $data['remarks'];
    $hosteldata = $data['hostel_required'];

    // Check if the record already exists
    $sqlCheckExistence = "SELECT COUNT(*) AS count FROM [dbo].[request_TNI] WHERE srl_no = ? AND empno = ?";
    $paramsCheckExistence = array($selectedId, $employeeNumber);
    $stmtCheckExistence = sqlsrv_query($conn, $sqlCheckExistence, $paramsCheckExistence);

    if ($stmtCheckExistence === false) {
        $successFlag = false;
        break; // Break the loop on error
    }

    $existenceData = sqlsrv_fetch_array($stmtCheckExistence, SQLSRV_FETCH_ASSOC);

    // If the record doesn't exist, insert it
    if ($existenceData['count'] == 0) {
        // Fetch data from 'training_mast' table based on the selected ID
        $sqlFetch = "SELECT * FROM [Complaint].[dbo].[TNI_mast] WHERE srl_no = ?";
        $paramsFetch = array($selectedId);
        $stmtFetch = sqlsrv_query($conn, $sqlFetch, $paramsFetch);

        if ($stmtFetch === false) {
            $successFlag = false;
            break; // Break the loop on error
        }

        // Fetch the data
        $rowData = sqlsrv_fetch_array($stmtFetch, SQLSRV_FETCH_ASSOC);

        $currentDate = date("Y-m-d");
        //echo '$rep_ofcr A :' .$rep_ofcr;
        //echo '$hod_ro A:' .$hod_ro;
        // Insert the record into the 'request' table
        //$flag = 'Pending';
        //echo 'new ='.$employeeNumber;
        $sqlInsert = "INSERT INTO [dbo].[request_TNI] (srl_no, empno, Program_name, faculty, nature_training, year, uploaded_date, flag,  remarks, duration, tentative_date, target_group, rep_ofcr, hostel_book, plant) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $paramsInsert = array(
            $selectedId,
            $employeeNumber,
            $rowData['Program_name'],
            $rowData['faculty'],
            $rowData['nature_training'],
            $rowData['year'],
            $currentDate,
            $flag,
            $remarksData,
            $rowData['duration'],
            $rowData['tentative_date'],
            $rowData['target_group'],
            $rep_ofcr,
            $hosteldata,
            $location
        );

        $stmtInsert = sqlsrv_query($conn, $sqlInsert, $paramsInsert);

        if ($stmtInsert === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        
        // Close the fetch statement
        sqlsrv_free_stmt($stmtFetch);
    }

    // Close the existence check statement
    sqlsrv_free_stmt($stmtCheckExistence);
}

        
        // Close the connection outside the loop
        sqlsrv_close($conn);
        
        if ($successFlag) {
            echo '<script>alert("Data successfully inserted!");</script>';
            // Optionally, redirect to another page
           //header("Location: all_users.php");
           echo '<script>window.location.href = "Training_TNI.php";</script>';

            exit();
        } else {
            echo '<script>alert("Error inserting data. Please try again.");</script>';
            // Optionally, redirect to another page
            header("Location: Training_TNI.php");
            exit();
        }
    }
}
?>



