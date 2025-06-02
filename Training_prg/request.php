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



//start find rep_ofcr empty
$query1="select rep_ofcr FROM [Complaint].[dbo].[emp_mas_sap] where empno='$employeeNumber'";
$params1 = array($employeeNumber);
$stmt1 = sqlsrv_query($conn, $query1, $params1);    
if ($stmt1 === false) {
    die(print_r(sqlsrv_errors(), true));
}

$rep_ofcr = null;
if ($row = sqlsrv_fetch_array($stmt1, SQLSRV_FETCH_ASSOC)) {
    $rep_ofcr = $row['rep_ofcr'];
}

sqlsrv_free_stmt($stmt1);

if ($rep_ofcr !== null) {
    // Step 2: Use the fetched rep_ofcr to find the next level rep_ofcr
    $query2 = "SELECT rep_ofcr FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
    $params2 = array($rep_ofcr);
    $stmt2 = sqlsrv_query($conn, $query2, $params2);

    if ($stmt2 === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $next_rep_ofcr = null;
    if ($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
        $next_rep_ofcr = $row['rep_ofcr'];
    }

    sqlsrv_free_stmt($stmt2);

    //echo "First Level Rep Officer: " . $rep_ofcr . "<br>";
    //echo "Second Level Rep Officer: " . $next_rep_ofcr . "<br>";
} else {
    echo "No rep_ofcr found for empno: " . $employeeNumber;
}
//stop



if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['request'])) {
    // Ensure 'selectedData' is set in the post data
    if (isset($_POST['selectedData'])) {
        $selectedData = json_decode($_POST['selectedData'], true);

        // First get the current available seats limit
        $seatsQuery = "SELECT TOP 1 available_seats 
                      FROM [Complaint].[dbo].[employee_seats] 
                      ORDER BY created_at DESC";
        $seatsResult = sqlsrv_query($conn, $seatsQuery);
        
        if ($seatsResult === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $seatsRow = sqlsrv_fetch_array($seatsResult, SQLSRV_FETCH_ASSOC);
        $availableSeats = $seatsRow ? (int)$seatsRow['available_seats'] : 0;

        // Get the total count of requests for this employee
        $sqlCount = "SELECT COUNT(empno) AS totalCount 
                    FROM [dbo].[request] 
                    WHERE empno = ? AND flag NOT IN ('1','3','88','000')";
        $paramsCount = array($employeeNumber);
        $stmtCount = sqlsrv_query($conn, $sqlCount, $paramsCount);

        if ($stmtCount === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $countData = sqlsrv_fetch_array($stmtCount, SQLSRV_FETCH_ASSOC);
        $existingCount = $countData['totalCount'];
        
        // Count the number of selected checkboxes
        $selectedCount = count($selectedData);

        // Check if the total exceeds available seats
        if ($availableSeats > 0 && ($existingCount + $selectedCount) > $availableSeats) {
            echo '<script>alert("You cannot have more than '.$availableSeats.' requests.");</script>';
            echo '<script>window.location.href = "all_users.php";</script>';
            exit();
        }

        // Continue with the rest of your logic...
    $sql = "SELECT rep_ofcr, hod_ro, location FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
    $params = array($employeeNumber);
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

        // echo ' $rep_ofcr;' . $rep_ofcr;
        // echo '$hod_ro;' . $hod_ro;
        // echo '$location;' . $location;


     if ($rep_ofcr == 'NULL') {
         $flag = '4'; // Pending at HOD
     }
     elseif (strpos($rep_ofcr, $hod_ro) !== false) {
        $flag = '2'; // Pending at HOD
    } 
    else {
        $flag = '0'; // Pending at R1
    }

    //echo ' $flag;' . $flag;
}


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
                $sqlInsert = "INSERT INTO [dbo].[request] (srl_no, empno, Program_name, faculty, nature_training, year, uploaded_date, flag,  remarks, duration, target_group, rep_ofcr, hostel_book, plant) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
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
                    //$rowData['tentative_date'],
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
           echo '<script>window.location.href = "all_users.php";</script>';
            exit();
        } else {
            echo '<script>alert("Error inserting data. Please try again.");</script>';
            header("Location: all_users.php");
            exit();
        }
    }
}
?>
