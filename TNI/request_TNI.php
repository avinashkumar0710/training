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
        $exactHodEmpno = $_POST['exactHodEmpno'];
        

//echo '$exactHodEmpno' .$exactHodEmpno;

// Fetch the rep_ofcr and hod_ro values
if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $rep_ofcr = $row['rep_ofcr'];
    $hod_ro = $row['hod_ro'];
    $location = $row['location'];
    //echo '$hod_ro'.$hod_ro;
    
    // Check if hod_ro is available in rep_ofcr
    if ($exactHodEmpno ==  $employeeNumber) {
        $flag = '4'; // Approve TNI
        //echo 'true';
    } elseif (strpos($rep_ofcr, $hod_ro) !== false) {
        $flag = '2'; // Pending at HOD
        //echo '2222';
    } else {
        $flag = '0'; // Pending at R1
        //echo '1111';
    }
    
    // Use $rep_ofcr and $hod_ro as needed
    //echo '$rep_ofcr: ' . $rep_ofcr;
    // echo '$hod_ro: ' . $hod_ro;
    // echo '$flag: ' . $flag;
}
        $successFlag = true;
        $alreadyInsertedFlag = false;

       // Loop through selected data and fetch additional information based on ID
       foreach ($selectedData as $data) {
        $selectedId = $data['srl_no'];
        $remarksData = $data['remarks'];
        $hosteldata = $data['hostel_required'];

        // Fetch nature_training from TNI_mast table based on the selected ID
        $sqlFetchNatureTraining = "SELECT nature_training FROM [Complaint].[dbo].[TNI_mast] WHERE srl_no = ?";
        $paramsFetchNatureTraining = array($selectedId);
        $stmtFetchNatureTraining = sqlsrv_query($conn, $sqlFetchNatureTraining, $paramsFetchNatureTraining);

        if ($stmtFetchNatureTraining === false) {
            $successFlag = false;
            break; // Break the loop on error
        }

        $natureTrainingData = sqlsrv_fetch_array($stmtFetchNatureTraining, SQLSRV_FETCH_ASSOC);
        $natureTraining = $natureTrainingData['nature_training'];

        // Check if the combination of empno and nature_training exists and count them
        $sqlCheckCombination = "SELECT COUNT(*) AS count FROM [Complaint].[dbo].[request_TNI] WHERE empno = ? AND nature_training = ?";
        $paramsCheckCombination = array($employeeNumber, $natureTraining);
        $stmtCheckCombination = sqlsrv_query($conn, $sqlCheckCombination, $paramsCheckCombination);

        if ($stmtCheckCombination === false) {
            $successFlag = false;
            break; // Break the loop on error
        }

        $combinationData = sqlsrv_fetch_array($stmtCheckCombination, SQLSRV_FETCH_ASSOC);

        // If the count is less than 2, proceed with the insertion
        if ($combinationData['count'] < 2) {
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

                // Insert the record into the 'request' table
                $sqlInsert = "INSERT INTO [dbo].[request_TNI] (srl_no, empno, Program_name, faculty, nature_training, year, uploaded_date, flag, remarks, duration, tentative_date, target_group, rep_ofcr, hostel_book, plant) 
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
        } else {
            $alreadyInsertedFlag = true;
        }

        // Close the combination check statement
        sqlsrv_free_stmt($stmtCheckCombination);
    }

    // Close the connection outside the loop
    sqlsrv_close($conn);
            
        }

       
   
        
        if ($successFlag) {
        //     echo '<script>alert("Data successfully inserted!");</script>';
        //     // Optionally, redirect to another page
        //    //header("Location: all_users.php");
        //    echo '<script>window.location.href = "Training_TNI.php";</script>';

        //     exit();
        if ($alreadyInsertedFlag) {
           
            echo '<script>alert("Program_Name with Nature of Training has already been inserted and will not be added again.");</script>';
        } else {
            echo '<script>alert("Data successfully inserted!");</script>';
        }
        echo '<script>window.location.href = "Training_TNI.php";</script>';
        exit();
        } else {
            // echo '<script>alert("Error inserting data. Please try again.");</script>';
            // // Optionally, redirect to another page
            // header("Location: Training_TNI.php");
            // exit();
            echo '<script>alert("Error inserting data. Please try again.");</script>';
            echo '<script>window.location.href = "Training_TNI.php";</script>';
            exit();
        }
    }


    //echo '<script>window.location.href = "Training_TNI.php";</script>';
    // Close the connection outside the loop
    
    
?>



