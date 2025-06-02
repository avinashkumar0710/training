<?php 
// start a new session
// Allow any origin to access this resource

session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:HODlogin.php");
}

$sessionemp = $_SESSION["emp_num"];

// Ensure $hodempno has 8 digits (prepend 00 if it starts with 0) or 7 digits
$sessionemp = str_pad($sessionemp, 8, "0", STR_PAD_LEFT);

//echo 'employeeno' . $hodempno;

// Database Connection
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

<!---------------------------------Start Header Area------------------------------------>
<html>

<head>
    <title>TNI Subordinate</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 
    <style>
    body {
        font-weight: 600;
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        margin: 0px;
        background-color: #e8eef3;
    }

    .row{margin : 2px;height: 900px;}

    .full-width{}
   
    </style>
</head>

<?php include '../header_HR.php';?>
<body>
    <!---------------------------------------------------------------------------------------------------------------------------------------------------->
    <div class="full-width">         
    <div class="row">
        <div class="col-md-7">
            <?php
            // Database Connection
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
            
            // Initialize $selected_year variable
            $selected_year = null;

            // Fetch hod_ro based on sessionemp
            $sql = "SELECT hod_ro FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
            $params = array($sessionemp);
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            // Fetch the result
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

            // Assuming that 'hod_ro' is the column name
            $hod_ro = $row['hod_ro'];
            echo  '$hod_ro' .$hod_ro;

            // Fetch empno from emp_mas_sap table based on hod_ro
            $sql = "SELECT empno FROM [Complaint].[dbo].[emp_mas_sap] WHERE hod_ro = ?";
            $params = array($hod_ro);
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            // Fetch all empno values
            $empnos = array();  // Initialize $empnos as an empty array
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $empno = ltrim($row['empno'], '00'); // Remove leading '0'
                $empnos[] = $empno;
            }

            // Fetch rep_ofcr based on hod_ro
            $sql = "SELECT rep_ofcr FROM [Complaint].[dbo].[emp_mas_sap] WHERE rep_ofcr != ? AND hod_ro = ?";
            $params = array($hod_ro, $hod_ro);
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            // Fetch the rep_ofcr value
            if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $rep_ofcr = $row['rep_ofcr'];
                echo '$rep_ofcr' .$rep_ofcr;
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['year'])) {
                $selected_year = $_POST['year'];
                // Your code to fetch program names based on the selected year
            }
            ?>
            <h6><i class='fa fa-home'></i>&nbsp;<i><u>HOD->TNI Nomination for Subordinate</u></i></h6>
            <div class="container">    
          
                    <form method="POST" id="showProgramForm">
                        <label for="year">Select a year:</label>
                        <select name="year" id="year">
                            <option value="" disabled selected>Select year</option>
                            <?php
                            // Fetch distinct years
                            $distinctYearsQuery = "SELECT DISTINCT year FROM [Complaint].[dbo].[training_mast]";
                            $yearsResult = sqlsrv_query($conn, $distinctYearsQuery);
                            if ($yearsResult) {
                                while ($yearRow = sqlsrv_fetch_array($yearsResult, SQLSRV_FETCH_ASSOC)) {
                                    $yearValue = $yearRow['year'];
                                    $selectedAttr = ($selected_year == $yearValue) ? 'selected' : '';
                                    echo "<option value=\"$yearValue\" $selectedAttr>$yearValue</option>";
                                }
                            }
                            ?>
                        </select>
                        <button type="submit" class="btn btn-info">Show Programs</button>&nbsp;
                        <i style="font-size:small; background-color:yellow;">&nbsp;&nbsp;(Note: Choose the year first to display the Program Name.)</i>
                        <p id="errorMessage" style="color: red;"></p> <!-- Error message -->
                    </form>
            
            </div>

            <div class="container">
                <form action="fetch_details.php" method="post" id="approveForm">
                    
                    <h3>Program Name<i style="font-size:small; background-color:yellow;">&nbsp;&nbsp;(Note: Highlighted Program Name in red color indicates the deadline date of nominations.)</i></h3>
                    
                    <?php
                   $currentDate = date('Y-m-d');
                   //echo "Current Date: " . $currentDate . "<br>";
                   $sql = "SELECT [id], [Program_name], [Closed_date] FROM [Complaint].[dbo].[training_mast] WHERE year = ?";
                   $params = array($selected_year);
                   $result = sqlsrv_query($conn, $sql, $params);
                   
                   if ($result === false) {
                       die(print_r(sqlsrv_errors(), true));
                   }
                   
                   echo '<select name="programName" id="programList" onchange="fetchDetails()" class="form-control" size="5">';
                   while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                    //echo "Closed Date: " . $row['Closed_date']->format('Y-m-d') . "<br>";
                       // Check if Closed_date is less than the current date
                       $disabled = ($row['Closed_date']->format('Y-m-d') < $currentDate) ? 'disabled' : '';
                       $color = ($row['Closed_date']->format('Y-m-d') < $currentDate) ? 'color: red;' : '';
                   
                       // Apply custom styling if option is disabled
                       $style = $disabled ? 'style="' . $color . '"' : '';
                   
                       // Output the option with disabled attribute and custom styling if necessary
                       echo '<option value="' . $row['id'] . '" ' . $disabled . ' ' . $style . '>' . $row['Program_name'] . '</option>';
                   }
                   echo '</select>';
                     echo'<br>';
                     //echo ' Access: ' . $accessValue . '<br>';

                     // Initialize SQL query variable
                     $sql = "";
                     
                     // Check the value of $accessValue
                    //  if ($accessValue == 1) {
                    //      // Query when accessValue is 1
                    //      $sql = "SELECT [empno], [name], [rep_ofcr], [hod_ro], [location]  FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = '$sessionemp' UNION ALL 
                    //      SELECT [empno], [name], [rep_ofcr], [hod_ro] , [location] FROM [Complaint].[dbo].[emp_mas_sap] WHERE hod_ro != rep_ofcr AND hod_ro = '$sessionemp'";
                    //  } else {
                         // Query when accessValue is not 1
                         $sql = "SELECT [empno], [name], [rep_ofcr], [hod_ro] , [location] FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = '$sessionemp' UNION ALL 
                         SELECT [empno], [name], [rep_ofcr], [hod_ro] , [location] FROM [Complaint].[dbo].[emp_mas_sap] WHERE  rep_ofcr = '$sessionemp'";
                     //}
                     
                     // Execute the query
                     $stmt = sqlsrv_query($conn, $sql);
                     
                     if ($stmt === false) {
                         die(print_r(sqlsrv_errors(), true));
                     }
                     
                     // Output the employee list
                     echo '<h3>Employee List</h3>';
                     echo '<div style="height: 350px; overflow: auto;">';
                     echo '<table class="table table-bordered border-success">';
                     echo '<thead style="position: sticky; top: 0; background-color: beige; z-index:1;">';
                     echo '<tr class="bg-primary" style="color:white;text-align: center;">';
                     echo '<th>Empno</th>';
                     echo '<th>Name</th>';
                     echo '<th>Hostel Required</th>'; // New column for hostel requirement
                     echo '<th>Select</th>';
                     echo '</tr>';
                     echo '</thead>';
                     echo '<tbody>';

            sqlsrv_fetch($stmt, 0);

            // Add a checkbox to select all employees
            echo '<tr>';
            echo '<td><strong>Select All</strong></td>';
            echo '<td></td>'; // Empty column for alignment
            echo '<td></td>'; // Empty column for alignment
            echo '<td style="text-align: center;">'; // Center the checkbox
            // Add a bigger checkbox for select all
            echo '<input type="checkbox" id="selectAllEmployees" style="transform: scale(1.5);">'; // Increase size using transform
            echo '</td>';
            echo '</tr>';

            // Loop through the results and generate table rows
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                echo '<tr class="table-light">';
                echo '<td>' . $row['empno'] . '</td>';
                echo '<td>' . $row['name'] . '</td>';
                echo '<td style="text-align: center;">'; // Center the select box
                // Add a dropdown for hostel requirement with inline styles
                echo '<select name="hostelRequired[]" class="form-control" style="border-color: blue; width: 150px;">'; // Increased width to 150px
                echo '<option value="0">No</option>';
                echo '<option value="1">Yes</option>';
                
                echo '</select>';
                echo '</td>';
                echo '<td style="text-align: center;">'; // Center the checkbox
                // Add a bigger checkbox for individual selection
                echo '<input type="checkbox" name="selectedEmployees[]" value="' . $row['empno'] . '" style="transform: scale(1.5);">'; // Increase size using transform
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>'; // Closing div for scrollable container

            echo '<br>';
            echo '<button type="submit" id="approveButton" name="approve" class="btn btn-success">Subordinate Submit</button>';
            //echo '</form>';
            

            // Close the connection
            sqlsrv_close($conn);
            ?>
                </form>
        </div>
    </div>


<script>
    // Get the "Select All" checkbox element
    var selectAllCheckbox = document.getElementById("selectAllEmployees");

    // Get all checkboxes for individual employees
    var employeeCheckboxes = document.querySelectorAll('input[name="selectedEmployees[]"]');

    // Add event listener to the "Select All" checkbox
    selectAllCheckbox.addEventListener('change', function() {
        // Loop through all employee checkboxes and set their checked property
        // to be the same as the "Select All" checkbox
        employeeCheckboxes.forEach(function(checkbox) {
            checkbox.checked = selectAllCheckbox.checked;
        });
    });
</script>
 


        <!-------------------------------------Approved by You------------------------------------------------------>
        <div class="col-md-5" style="box-shadow: rgba(0, 0, 0, 0.25) 0px 54px 55px, rgba(0, 0, 0, 0.12) 0px -12px 30px, rgba(0, 0, 0, 0.12) 0px 4px 6px, rgba(0, 0, 0, 0.17) 0px 12px 13px, rgba(0, 0, 0, 0.09) 0px -3px 5px;">
        <?php
$serverName = "NSPCL-AD\\SQLEXPRESS";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "",
    "PWD" => ""
);

// Create connection
$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// Get the current year
$currentYear = date("Y");

// Fetch the list of programs based on `ordinate_req`
$fetch_program_name = "SELECT DISTINCT program_name FROM [Complaint].[dbo].[request] WHERE ordinate_req = ?";

$params = array($sessionemp);
$stmtprogram_name = sqlsrv_query($conn, $fetch_program_name, $params);

if ($stmtprogram_name === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Extract program names from the result set
$programs = array();
while ($rowProgram = sqlsrv_fetch_array($stmtprogram_name, SQLSRV_FETCH_ASSOC)) {
    $programs[] = $rowProgram['program_name'];
}

// Free the statement resources
sqlsrv_free_stmt($stmtprogram_name);

// Display the form for selecting a program
$selectedProgram = isset($_POST['programName']) ? $_POST['programName'] : '';

echo '<form method="post">';
echo '<label for="programName">Select Program:</label>&nbsp';
echo '<select name="programName" id="programName">';
echo '<option value="" disabled selected>Select Program</option>'; // Default option

foreach ($programs as $program) {
    $selected = ($program == $selectedProgram) ? 'selected' : '';
    echo "<option value=\"$program\" $selected>$program</option>";
}
echo '</select>';
echo '&nbsp<button type="submit" class="btn btn-primary">Show</button><br>';
echo '<i style="font-size:small; background-color:yellow;">&nbsp;&nbsp;(Note: Choose Program Name first to display the Requested Employee Name.)</i>';
echo '</form>';

// Check if a program is selected
if (!empty($selectedProgram)) {
    echo '<h3>Subordinate Requested By You</h3>';
    echo '<div class="table" style="height:700px; overflow: auto;">';

    // SQL query to fetch records based on the selected program name
    $sqlRecords = "SELECT r.srl_no, r.empno, r.Program_name, r.year, a.name, r.flag, r.ordinate_datetime
                   FROM [Complaint].[dbo].[request] r
                   JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
                   WHERE ordinate_req = ? AND r.program_name = ?";

    // Add both `sessionemp` and selected program as parameters
    $params = array($sessionemp, $selectedProgram);
    $stmtRecords = sqlsrv_query($conn, $sqlRecords, $params);

    if ($stmtRecords === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Display the fetched records
    echo '<table class="table table-bordered border-success">';
    echo '<thead style="position: sticky; top: 0; background-color: beige;">';
    echo '<tr>';
    echo '<th>Srl. No</th>';
    echo '<th>Emp Name</th>';
    echo '<th>Program Name</th>';
    echo '<th>Year</th>';
    echo '<th>Status</th>';
    echo '<th>Date Time</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $serialNo = 1;
    while ($row = sqlsrv_fetch_array($stmtRecords, SQLSRV_FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td>' . $serialNo . '</td>';
        echo '<td>' . $row['name'] . '</td>';
        echo '<td>' . $row['Program_name'] . '</td>';
        $status = '';
        switch ($row['flag']) {
            case 0:
                $status = '<span style="color:blue">Pending at Reporting Officer</span>';
                break;
            case 1:
                $status = '<span style="color:red">Rejected by Reporting Officer</span>';
                break;
            case 2:
                $status = '<span style="color:blue">Pending at HOD</span>';
                break;
            case 3:
                $status = '<span style="color:red">Rejected by HOD</span>';
                break;
            case 4:
                $status = '<span style="color:green">Training Approved by HOD</span>';
                break;
            case 5:
                $status = '<span style="color:blue">Pending from BUH</span>';
                break;
            case 6:
                $status = '<span style="color:green">Approved by BUH</span>';
                break;
            case 7:
                $status = '<span style="color:green">Overall Approved</span>';
                break;
            case 88:
                $status = '<span style="color:red">Rejected by HR</span>';
                break;
            case 99:
                $status = '<span style="color:green">Approved by HR</span>';
                break;
            default:
                $status = 'Unknown';
        }
        echo '<td>' . $row['year'] . '</td>';
        echo "<td>$status</td>";
        echo '<td>' . ($row['ordinate_datetime'] ? $row['ordinate_datetime']->format('Y-m-d H:i:s') : 'NULL') . '</td>';
        echo '</tr>';
        $serialNo++;
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    sqlsrv_free_stmt($stmtRecords);
}

// Close the connection
sqlsrv_close($conn);
?>


        </div>
    </div>
    

</body>
</html>

<?php include '../footer.php';?>