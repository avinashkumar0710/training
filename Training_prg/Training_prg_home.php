<?php 
// start a new session
// Allow any origin to access this resource
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp= $_SESSION["emp_num"];

    // Add '00' in front if session value has only 6 digits
    if(strlen($sessionemp) == 6) {
        $sessionemp = '00' . $sessionemp;
    }
    //echo 'empno' .$sessionemp;

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


// Step 1: Fetch the rep_ofcr based on the session employee number
$sql1 = "SELECT rep_ofcr FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
$params1 = array($sessionemp); // Use the employee number from session variable
$stmt1 = sqlsrv_query($conn, $sql1, $params1);

// Check if the query executed successfully
if ($stmt1 === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch the rep_ofcr
if ($row1 = sqlsrv_fetch_array($stmt1, SQLSRV_FETCH_ASSOC)) {
    $rep_ofcr = $row1['rep_ofcr']; // Fetch the reporting officer
    //echo 'Reporting Officer: ' . $rep_ofcr . '<br>';

    // Step 2: Now, using the rep_ofcr, fetch the email
    $sql2 = "SELECT [email] FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
    $params2 = array($rep_ofcr); // Use the rep_ofcr to fetch email
    $stmt2 = sqlsrv_query($conn, $sql2, $params2);

    // Check if the second query executed successfully
    if ($stmt2 === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch the email for the reporting officer
    if ($row2 = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
        $email = $row2['email']; // Fetch the email
        //echo 'Email of Reporting Officer: ' . $email . '<br>';
    } else {
        //echo "No email found for reporting officer: $rep_ofcr";
    }

} else {
    echo "No reporting officer found for employee number: $sessionemp";
}


?>
<!---------------------------------Start Header Area------------------------------------>
<html>

<head>
    <title>Training | Home</title>
    <link rel="icon" type="image/png" sizes="32x32" href="employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>

    <style>
    body {
        font-weight: 600;
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        margin: 0;
        /* Remove default body margin */
        padding: 0;
        /* Remove default body padding */
        background-color: #e8eef3;
    }

    .scrollable1 {
        height: 760px;
        overflow-y: auto;
        border-color: black;
    }

    #dtBasicExample {
        border-radius: 25px;
        border: 2px solid yellowgreen;
    }

    .nav-link {
        color: #F8F9F9;
    }

    button[disabled] {
    background-color: red;
    cursor: not-allowed;
}

    </style>


</head>
<?php include 'header.php';?>



<div class="container">
    <center><br>
        <!-- <h4>Request by you</h4><p style="background-color:yellow;"><span >Note * : Send Mail button only be active just after 3 days of submitted request</p> -->
        <?php 
    // Fetch data from the 'request' table for a specific empno
    $employeeno = $_SESSION["emp_num"];

    // SQL query to get the requests for the specific employee
    // $sql = "SELECT [id],[srl_no], [empno], [Program_name], [Faculty], [nature_training], [year], [uploaded_date], [flag], [remarks], [duration], [tentative_date], [target_group], [hostel_book] 
    //         FROM [Complaint].[dbo].[request]  
    //         WHERE empno = ?";

    $sql = "SELECT [id],[srl_no], [request].[empno], [Program_name], [Faculty], [nature_training], [year], [uploaded_date], [flag], [remarks], [duration], [tentative_date], [target_group], [hostel_book] ,
	[request].[rep_ofcr], emp_mas_sap.name, request.aprroved_time
            FROM [Complaint].[dbo].[request]    join emp_mas_sap  on [Complaint].[dbo].[request].rep_ofcr = emp_mas_sap.empno  where request.empno = ?";

    $params = array($employeeno);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if ($user_role === "00") {
    // Display the fetched data in table format
    if (sqlsrv_has_rows($stmt)) {
        echo '<div class="scrollable1">
            <table class="table table-bordered border-success" border="3">
                <thead style="position: sticky; top: 0; background-color: beige; z-index: 1;">
                    <tr class="bg-primary" style="color:#ffffff">  
                        <th scope="col" style="display: none;">id</th>
                        <th scope="col">Sl</th>           
                        <th scope="col">Program_name</th>               
                        <th scope="col">Nature_of_training</th>
                        <th scope="col">Year</th>  
                        <th scope="col">Hostel Required</th>
                        <th scope="col">Status</th>                                            
                        <th scope="col">Remarks</th>
                        <!--<th scope="col">Submit Date</th>-->
                     <!--<th scope="col">New Date</th>-->  <!-- New Date column -->
                      <!--  <th scope="col">Action</th>  -->             
                    </tr>
                </thead>
                <tbody>';

        $serialNo = 1;
        $currentDate = new DateTime(); // Get the current date
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Calculate new date (3 days from uploaded_date)
            $newDate = clone $row['uploaded_date'];
            $newDate->modify('+3 days'); // Add 3 days to uploaded_date

            // Format the new date and uploaded date
            $formattedNewDate = $newDate->format('Y-m-d');
            $formattedUploadedDate = $row['uploaded_date']->format('Y-m-d');

            echo "<tr class='table-light'>";
            echo "<td style='display: none;'>{$row['id']}</td>";
            echo "<td>$serialNo</td>";
            echo "<td>{$row['Program_name']}</td>";
            echo "<td>{$row['nature_training']}</td>";
            echo "<td>{$row['year']}</td>";

            // Determine the status based on the flag value
            $status = '';
            switch ($row['flag']) {
                case 0:
                    $status = '<span style="color:blue">Pending at Reporting Officer</span>';
                    break;
                case 1:
                    $status = '<span style="color:red">Reject by Reporting Officer</span>';
                    break;
                case 2:
                    $status = '<span style="color:blue">Pending at HOD</span>';
                    break;
                case 3:
                    $status = '<span style="color:red">Reject by HOD</span>';
                    break;
                case 4:
                    $status = '<span style="color:green">Training Approved from HOD</span>';
                    break;
                case 5:
                    $status = '<span style="color:blue">Pending from BUH</span>';
                    break;
                case 6:
                    $status = '<span style="color:green">Approved from BUH</span>';
                    break;
                case 7:
                    $status = '<span style="color:green">Overall Approved</span>';
                    break;
                case 88:
                    $status = '<span style="color:red">Reject From HR</span>';
                    break;
                case 99:
                    $status = '<span style="color:green">Approved From HR</span>';
                    break;    
                default:
                    $status = 'Unknown';
            }

            // Determine hostel booking status
            $hostelStatus = ($row['hostel_book'] == 1) ? 'Yes' : 'No';
            echo "<td>{$hostelStatus}</td>";
            echo "<td>$status</td>";            
            echo "<td>{$row['remarks']}</td>"; 
           // echo "<td>{$formattedUploadedDate}</td>";  // Original submit date

            // Display new date (Submit Date + 3 days)
            // echo "<td>{$formattedNewDate}</td>";  // New Date column

            // Compare the new date with the current date (enabled if current date >= new date)
            $isButtonEnabled = ($currentDate >= $newDate) ? '' : 'disabled';

            // echo "<td>
            //         <form method='post' action='send_email.php'>
            //             <input type='hidden' name='request_id' value='{$row['id']}'>
            //             <button type='submit' id='sendMailButton_{$row['id']}' name='sendmail' class='btn btn-success' $isButtonEnabled>Send Mail</button>
            //         </form>
            //       </td>";

            echo "</tr>";
            $serialNo++;
        }
        echo '</tbody></table></div>';
    } else {
        echo '<p>No Pending Request by You.</p>';
    }
    }
    // Close the connection
    sqlsrv_close($conn);
?>




</div>


</body>


    
<!-- <div style="background-color: #3333331c; padding: 10px; text-align: center; margin-top: -45px; border-radius: 5px;">
    <p style="font-weight: bold;">
        <span style="color:green">Fill Nomination </span>-> <span style="color:green">Approve</span>&nbsp;/&nbsp;<span style="color:red">Reject</span> by Reporting Officer -> <span style="color:green">Approved</span>&nbsp;/&nbsp;<span style="color:red">Rejected</span> by HOD -> <span style="color:green">Approved</span>&nbsp;/&nbsp;<span style="color:red">Rejected</span> by HR -> 
        <span style="color:green">Overall Training Approved</span>&nbsp;/&nbsp;<span style="color:green">Rejected</span> by BUH
    </p>
</div> -->

</html>
<?php include '../footer.php';?>