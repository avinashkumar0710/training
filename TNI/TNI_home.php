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
    <title>Training | Home</title>
    <link rel="icon" href="../images/analysis.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel="stylesheet" href="css/TNI_header.css">
   

</head>
<?php include 'header.php';?>

<br>



<div class="container">
    <center>
        <h4>Status of Training Need Identification</h4>

        <?php
// Database connection details
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);

// Establish the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch data from the 'request_TNI' table and match 'srl_no' in 'TNI_PMS'
$employeeno = $_SESSION["emp_num"];
$sql = "
    SELECT DISTINCT
        T1.[srl_no],
        T1.[empno],
        T1.[Program_name],
        T1.[Faculty],
        T1.[nature_training],
        T1.[year],
        T1.[uploaded_date],
        T1.[flag],
        T1.[remarks],
        T1.[duration],
        T1.[tentative_date],
        T1.[target_group],
        T1.[hostel_book],
        CASE 
            WHEN T2.[srl_no] IS NOT NULL THEN 'Available in PMS'
            ELSE 'Not Available in PMS'
        END AS PMS_Status
    FROM 
        [Complaint].[dbo].[request_TNI] T1
    LEFT JOIN 
        [Complaint].[dbo].[TNI_PMS] T2 
    ON 
        T1.[srl_no] = T2.[srl_no]
    WHERE 
        T1.[empno] = ?";

$params = array($employeeno);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Display the fetched data in table format
if (sqlsrv_has_rows($stmt)) {
    echo '<div class="scrollable1">
        <table class="table table-bordered border-success" border="3">
            <thead style="position: sticky; top: 0; background-color: beige; z-index: 1;">
                <tr class="bg-primary" style="color:#ffffff">  
                    <th scope="col">Sl</th>           
                    <th scope="col">Program Name</th>               
                    <th scope="col">Nature of Training</th>
                    <th scope="col">Year</th>  
                    <th scope="col">Hostel Required</th>
                    <th scope="col">Status</th>                                            
                    <th scope="col">Remarks</th>      
                    <th scope="col">PMS Details</th>            
                </tr>
            </thead>
            <tbody>';

    $serialNo = 1;
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo "<tr class='table-light'>";
        
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
                $status = '<span style="color:green">TNI Approved</span>';
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
            default:
                $status = 'Unknown';
        }

        // Determine hostel booking status
        $hostelStatus = ($row['hostel_book'] == 1) ? 'Yes' : 'No';

        echo "<td>{$hostelStatus}</td>";
        echo "<td>$status</td>";  
        echo "<td>{$row['remarks']}</td>"; 
        echo "<td>{$row['PMS_Status']}</td>"; // Display the PMS status (Available or Not)
        
        echo "</tr>";
        $serialNo++;
    }

    echo '</tbody></table></div>';
} else {
    echo '<p>No Pending Status by You.</p>';
}

// Close the connection
sqlsrv_close($conn);
?>

</div>


</body>

</html>

<?php include '../footer.php';?>