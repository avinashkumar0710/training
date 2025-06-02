<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
}
$employeeNumber = $_SESSION["emp_num"];
// Add '00' in front if session value has only 6 digits
if(strlen($employeeNumber) == 6) {
    $employeeNumber = '00' . $employeeNumber;
    //echo '$employeeNumber1:  ' .$employeeNumber;
}

//  echo '$employeeNumber1' .$employeeNumber;

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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request']) && isset($_POST['selectedEmployee'])) {
    // Connect to the database
   

    // Ensure 'selectedData' is set in the post data
    $selectedEmployee = $_POST['selectedEmployee'];

    // Ensure 'selectedData' is set and not empty
    if (!empty($_POST['selectedData'])) {
        $selectedData = json_decode($_POST['selectedData'], true);

        if ($selectedData === null) {  // Check for JSON errors
            die('<script>alert("Error decoding selected data!");</script>');
        }

        

        echo "Received Selected Employee: " . htmlspecialchars($selectedEmployee);

        // Continue with the rest of your logic...
    $sql = "SELECT rep_ofcr, hod_ro, location FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = '$selectedEmployee'";
    $params = array($selectedEmployee);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $exactHodEmpno = $_POST['exactHodEmpno'];
    //echo '$exactHodEmpno;' . $exactHodEmpno;

    // Fetch the rep_ofcr, hod_ro, and location values
    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $rep_ofcr = $row['rep_ofcr'];
        $hod_ro = $row['hod_ro'];
        $location = $row['location'];
   
   
}

$flag = '4';
        $successFlag = true;

        // Loop through selected data and fetch additional information based on ID
        foreach ($selectedData as $data) {
            $selectedId = $data['srl_no'];
            $remarksData = $data['remarks'];
            $hosteldata = $data['hostel_required'];

            // Check if the record already exists
            $sqlCheckExistence = "SELECT COUNT(*) AS count FROM [dbo].[request] WHERE srl_no = ? AND empno = ?";
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
                $sqlFetch = "SELECT * FROM [Complaint].[dbo].[training_mast] WHERE srl_no = ?";
                $paramsFetch = array($selectedId);
                $stmtFetch = sqlsrv_query($conn, $sqlFetch, $paramsFetch);

                if ($stmtFetch === false) {
                    $successFlag = false;
                    break; // Break the loop on error
                }

                // Fetch the data
                $rowData = sqlsrv_fetch_array($stmtFetch, SQLSRV_FETCH_ASSOC);

                $currentDate = date("Y-m-d");
                // Insert the record into the 'request' table
                $sqlInsert = "INSERT INTO [dbo].[request] (srl_no, empno, Program_name, faculty, nature_training, year, uploaded_date, flag,  remarks, duration, tentative_date, target_group, appr_empno, approved_time,rep_ofcr, hostel_book, plant) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?)";
                $paramsInsert = array(
                    $selectedId,
                    $selectedEmployee,
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
                    $employeeNumber,
                    $currentDate,
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
            //echo '<script>window.location.href = "TNI_approval_HOD.php";</script>';
            exit();
        } else {
            echo '<script>alert("Error inserting data. Please try again.");</script>';
            //header("Location: TNI_approval_HOD.php");
            exit();
        }
    }
}
?>
