<?php
ob_start(); // Start buffering output
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:HODlogin.php");
}

$sessionemp = $_SESSION["emp_num"];

// Ensure $hodempno has 8 digits (prepend 00 if it starts with 0) or 7 digits
$sessionemp = str_pad($sessionemp, 8, "0", STR_PAD_LEFT);

echo 'session :' .$sessionemp;
// Include your database connection file or establish a connection here
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


$plantfetch = "SELECT location FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = '$sessionemp'";
$plantStmt = sqlsrv_query($conn, $plantfetch);

if ($plantStmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch the row
if ($row = sqlsrv_fetch_array($plantStmt, SQLSRV_FETCH_ASSOC)) {
    $location = $row['location'];

    // Now you can use $location as needed
    //echo 'Location: ' . $location;
} else {
    // Handle the case where no location is found for the session employee
    echo 'Location not found for employee: ' . $sessionemp;
}

// Check if the program name and selected employees are set
if (isset($_POST['programName']) && isset($_POST['selectedEmployees'])) {
    // Retrieve the selected program name
    $programId = $_POST['programName'];
    echo '$programId: ' . $programId;
    
    // Retrieve the list of selected employees
    $selectedEmployees = $_POST['selectedEmployees'];
    echo "Selected Employees array: <br>";
    print_r($selectedEmployees);
  
    // SQL query to fetch program details based on the selected program ID
    $programQuery = "SELECT srl_no, Program_name, nature_training, duration, faculty, tentative_date, year, target_group
                    FROM [Complaint].[dbo].[training_mast]
                    WHERE id = ?";

    // Prepare and execute the program query
    $programParams = array($programId);
    $programStmt = sqlsrv_query($conn, $programQuery, $programParams);

    // Check if the program query was executed successfully
    if ($programStmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }   

    // Fetch program details
    $programDetails = sqlsrv_fetch_array($programStmt, SQLSRV_FETCH_ASSOC);
    $srl_no = $programDetails['srl_no'];
    $Program_name = $programDetails['Program_name'];
    $year = $programDetails['year'];
    
    // Debugging output
    echo '$srl_no: ' . $srl_no;
    echo '$programDetails: ' . $Program_name;
    echo 'year: ' . $year;

    // Loop through the selected employees and insert data into the table for each employee
    $index = 0; 
    foreach ($selectedEmployees as $empno) {
        $status = '4';  // Set status based on your conditions
        $hostelRequirement = $_POST['hostelRequired'][$index];
        $index++;
        
        // Debugging output
        echo 'hostelRequirement: ' . $hostelRequirement;
        echo "Checking for Employee Number: $empno in Program: $Program_name, Year: $year, Serial No: $srl_no <br>";

        // Check if the row already exists
        $checkQuery = "SELECT COUNT(*) AS count, empno 
                        FROM [Complaint].[dbo].[request] 
                        WHERE srl_no = ? AND empno = ? AND Program_name = ? AND year = ? 
                        GROUP BY empno";
        $checkParams = array($srl_no, $empno, $Program_name, $year);
        $checkStmt = sqlsrv_query($conn, $checkQuery, $checkParams);

        if ($checkStmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        // Fetch the count and existing employee numbers
        $count = 0;
        $existingEmpnos = array();
        while ($row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
            $count = $row['count'];
            $existingEmpnos[] = $row['empno'];
        }

        // Debugging output
        echo "Number of records found for Employee Number $empno: $count<br>";

        // Check how many programs the employee is already registered for in the current year
                    $yearCountQuery = "SELECT COUNT(*) AS totalCount 
                    FROM [Complaint].[dbo].[request] 
                    WHERE empno = ? AND year = ?";
                $yearCountParams = array($empno, $year);
                $yearCountStmt = sqlsrv_query($conn, $yearCountQuery, $yearCountParams);

                if ($yearCountStmt === false) {
                die(print_r(sqlsrv_errors(), true));
                }

                $yearCountRow = sqlsrv_fetch_array($yearCountStmt, SQLSRV_FETCH_ASSOC);
                $totalCount = $yearCountRow['totalCount'];

                // Check if the total count is 8 or more
                if ($totalCount > 8) {
                echo "<script>
                alert('Only 8 programs can be registered in a year for Employee Number: $empno');
                </script>";
                continue; // Skip the current employee
                }
       
        // If count is greater than 0, it means the row already exists
        if ($count > 0) {
            $existingEmpnames = array();
            foreach ($existingEmpnos as $existingEmpno) {
                $nameQuery = "SELECT name FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
                $nameParams = array($existingEmpno);
                $nameStmt = sqlsrv_query($conn, $nameQuery, $nameParams);
                if ($nameStmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
                $nameRow = sqlsrv_fetch_array($nameStmt, SQLSRV_FETCH_ASSOC);
                $existingEmpnames[] = trim($nameRow['name']);
            }

            // Create a string of the existing employee names
            $existingEmpnamesString = implode(", ", $existingEmpnames);    
            echo '$existingEmpnamesString: ' . $existingEmpnamesString;

            // Show a popup message with employee names
            $existingEmpnamesStringSafe = json_encode($existingEmpnamesString);
$programNameSafe = json_encode($programDetails['Program_name']);

echo "<script>
    alert('The record already exists for employee number(s): ' + $existingEmpnamesStringSafe + ', Program Name: ' + $programNameSafe);
</script>";
            //echo "<script>alert('Test alert - does this work?');</script>";
            ob_end_flush();
        } else {
            // Insert data into the database
            $insertQuery = "INSERT INTO [dbo].[request] (srl_no, empno, Program_name, faculty, nature_training, year, uploaded_date, flag, remarks, duration, tentative_date, target_group, ordinate_req, ordinate_datetime, hostel_book, plant)
                            VALUES (?, ?, ?, ?, ?, ?, GETDATE(), ?, ?, ?, ?, ?, ?, GETDATE(), ?, ?)";
            $insertParams = array(
                $srl_no,
                $empno,
                $Program_name,
                $programDetails['faculty'],
                $programDetails['nature_training'],
                $year,
                $status,
                'Subordinate Request',
                $programDetails['duration'],
                $programDetails['tentative_date'],
                $programDetails['target_group'],
                $sessionemp,
                $hostelRequirement,
                $location
            );
            $insertStmt = sqlsrv_query($conn, $insertQuery, $insertParams);

            // Check if the insert query was executed successfully
            if ($insertStmt === false) {
                die(print_r(sqlsrv_errors(), true));
            } else {
                // Fetch the name of the employee
                $empnameQuery = "SELECT name FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
                $empnameParams = array($empno);
                $empnameStmt = sqlsrv_query($conn, $empnameQuery, $empnameParams);

                if ($empnameStmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }

                $empname = "";
                if ($row = sqlsrv_fetch_array($empnameStmt, SQLSRV_FETCH_ASSOC)) {
                    $empname = trim($row['name']);
                }

                // Safely encode the dynamic values to ensure no issues in JavaScript
$empnameSafe = json_encode($empname);
$programNameSafe = json_encode($programDetails['Program_name']);

// Show success popup
echo "<script>
    alert('Record inserted successfully for Employee: ' + $empnameSafe + ' & Program: ' + $programNameSafe);
</script>";
            }
        }
    }

    // Redirect to the approval page after processing
    echo "<script>window.location.href = 'TNI_approval.php';</script>";
    ob_end_flush();
    // Close the connection
    sqlsrv_close($conn);
    exit();
} else {
    // Handle the case where program name or selected employees are not set
    echo "<script>alert('Please select Employee Name');window.location.href = 'TNI_approval.php';</script>";
    exit();
}

?>
