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
    $serverName = "NSPCL-AD\SQLEXPRESS";
    $connectionInfo = array(
        "Database" => "complaint",
        "UID" => "",
        "PWD" => ""
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

    <style>
    body {
        font-weight: 600;
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        margin: 0; /* Remove default body margin */
        padding: 0; /* Remove default body padding */
        background-color: #e8eef3;
    }

    /* Ensure the video fills the width */
    video {
        width: 100%;
        height: auto; /* Maintain aspect ratio */
        display: block; /* Ensure the video is treated as a block element */
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

    img {
        width: 100%;
        /* Set width to 100% to make the image responsive */
        height: 860px;
        /* Maintain aspect ratio */
        display: block;
        margin: 0 auto;
    }

    video {
      width: 100%;
      height: 90%;
      object-fit: contain; /* Adjusts the video to fit without distorting */
    }
    </style>


</head>
<?php           
            // Check if the user is authenticated
           
           
            $name = "SELECT emp_name, access, dept_code FROM EA_webuser_tstpp WHERE emp_num = ?";    //for user name show in header
            $params = array($_SESSION['emp_num']);
            $stmt = sqlsrv_query($conn, $name, $params);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            if (sqlsrv_has_rows($stmt)) {
                // Get the user name from the result set
                $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                $username = $row['emp_name'];
                $access = $row['access'];
                $deptcode =$row['dept_code'];
            } 

            //echo 'empl' .$sessionemp;
            //echo 'dept' .$deptcode;

            $hod = 0;
            $sqlhod = "SELECT * FROM [Complaint].[dbo].[emp_mas_sap] WHERE hod_ro = $sessionemp";
            $stmt = sqlsrv_query($conn, $sqlhod);
            
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            // else {
            //     $hod = 1;
            //  }
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {                
                // $hod_ro = $row['hod_ro'];
                // echo 'hod:' .$hod_ro;
                // Now you can use $hod_ro as needed

                $hod = 1;
                //echo 'hod: ' .$hod;
            }
            $hremp = 0;
            $accessValue = null;
            $sql = "SELECT empno, access FROM [Complaint].[dbo].[Training_HR_User] where empno= $sessionemp";
            $stmt = sqlsrv_query($conn, $sql);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                // Store the access value in a variable
                 $accessValue = $row['access'];            
                 //echo 'Empno: ' . $row['empno'] . ', Access: ' . $accessValue . '<br>';
                $hremp = 1;
                //echo ' hremp'.$hremp;
            }     
            
            
            $compare = "SELECT [empno],[name],[rep_ofcr],[hod_ro], [design], [grade] , [dept] ,[location] FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = $sessionemp";    //for user name show in header
            $params = array($_SESSION['emp_num']);
            $stmt = sqlsrv_query($conn, $compare, $params);

            $buh = 'GM & BUH';
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            if (sqlsrv_has_rows($stmt)) {
                // Get the user name from the result set
                $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                $rep_ofcr = $row['rep_ofcr'];
                $hod_ro = $row['hod_ro'];
                $design =$row['design'];
                $grade =$row['grade'];                  

                $dept =$row['dept'];
                $location = $row['location'];
                $rep_ofcr = $row['rep_ofcr'];
                //$deptcode =$row['dept_code'];
                // echo 'rep_ofcr' .$rep_ofcr;
                //echo 'grade' .$grade;
                //echo 'design' .$design;
                
            }     
    ?>
    <?php 
$sql = "
WITH HODs AS (
    SELECT hod_ro AS empno, COUNT(*) AS hod_count
    FROM [Complaint].[dbo].[emp_mas_sap]
    WHERE hod_ro IS NOT NULL AND location = ? AND dept LIKE ?
    GROUP BY hod_ro
),
Reps AS (
    SELECT rep_ofcr AS empno, COUNT(*) AS rep_count
    FROM [Complaint].[dbo].[emp_mas_sap]
    WHERE rep_ofcr IS NOT NULL AND location = ? AND dept LIKE ?
    GROUP BY rep_ofcr
),
RepEmployeeCount AS (
    SELECT rep_ofcr AS empno, COUNT(*) AS num_of_employees
    FROM [Complaint].[dbo].[emp_mas_sap]
    WHERE rep_ofcr IS NOT NULL AND location = ? AND dept LIKE ?
    GROUP BY rep_ofcr
),
AllRoles AS (
    SELECT 
        e.empno,
        e.name,
        COALESCE(h.hod_count, 0) AS hod_count,
        COALESCE(r.rep_count, 0) AS rep_count,
        COALESCE(rec.num_of_employees, 0) AS num_of_employees
    FROM [Complaint].[dbo].[emp_mas_sap] e
    LEFT JOIN HODs h ON e.empno = h.empno
    LEFT JOIN Reps r ON e.empno = r.empno
    LEFT JOIN RepEmployeeCount rec ON e.empno = rec.empno
    WHERE e.location = ?
    AND e.dept LIKE ?
)
SELECT 
    ar.empno,
    ar.name,
    ar.hod_count,
    ar.rep_count,
    ar.num_of_employees,
    CASE
        WHEN ar.hod_count = (SELECT MAX(hod_count) FROM HODs) AND ar.rep_count = (SELECT MAX(rep_count) FROM Reps) THEN 'Exact HOD and Exact Reporting Officer'
        WHEN ar.hod_count = (SELECT MAX(hod_count) FROM HODs) THEN 'Exact HOD'
        WHEN ar.rep_count = (SELECT MAX(rep_count) FROM Reps) THEN 'Exact Reporting Officer'
        WHEN ar.hod_count > 0 AND ar.rep_count > 0 THEN 'HOD and Reporting Officer'
        WHEN ar.hod_count > 0 THEN 'HOD'
        WHEN ar.rep_count > 0 THEN 'Reporting Officer'
        ELSE 'Employee'
    END AS role
FROM AllRoles ar
WHERE 
    CASE
        WHEN ar.hod_count = (SELECT MAX(hod_count) FROM HODs) THEN 'Exact HOD'
        ELSE 'Employee'
    END = 'Exact HOD';
";

// Preparing and executing the statement
$params = array($location, '%' . $dept . '%', $location, '%' . $dept . '%', $location, '%' . $dept . '%', $location, '%' . $dept . '%');
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetching the result
$exactHodEmpno = null;
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $exactHodEmpno = $row['empno'];
}

// Close the statement and connection
sqlsrv_free_stmt($stmt);

?>

<body>
    <div class='card text-center'>
        <div class='card-header'>
            <b><i><SPAN style='background-color:yellow'> <?php echo $username; ?>
                    </SPAN></i></b>&nbsp;&nbsp;
            <a href='../signout.php'><input type='submit' class='btn btn-success btn-sm' value='LOGOUT'></a>&nbsp;
        </div>
    </div>

    <ul class='nav justify-content-center' style='background-color: #34495E;'>
    <?php if ($design !== 'GM & BUH'): ?>
    <li class='nav-item'>
        <a class='nav-link' href='home.php'>Home&nbsp;&nbsp;&nbsp;&nbsp;</a>
    </li>
    <?php endif; ?>
    
    <?php if ($design != 'GM & BUH                      '): ?>
            <li class='nav-item dropdown'>
            <a class='nav-link' style='color: white;' href='../all_users.php'>Training Nominations</a>
        </li>
        <?php endif; ?>
   
    <?php // Construct the query
             $query = "
                 SELECT empno, name, rep_ofcr, hod_ro
                 FROM [Complaint].[dbo].[emp_mas_sap]
                 WHERE rep_ofcr = '" . $sessionemp . "'
             ";
             
             // Execute the query
             $result = sqlsrv_query($conn, $query);
             
             if ($result === false) {
                 die(print_r(sqlsrv_errors(), true));
             }
             
             // Check if the design is not 'GM & BUH' and there are results from the query
             $hasReportingOfficer = false;
             while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                 $hasReportingOfficer = true;
                 //echo '$hasReportingOfficer' .$hasReportingOfficer;
                 break; // We only need to know if there's at least one row, so we can break here
             }
             sqlsrv_free_stmt($result); // Free the result set
             if ($hasReportingOfficer && $design !== 'GM & BUH' && $exactHodEmpno !== $sessionemp):  ?>      
    <li class='nav-item dropdown'>
        <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown'
            aria-haspopup='true' aria-expanded='false' style='color: white;'>
            Reporting Officer
        </a>
        <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
            <a class='dropdown-item'   href='../HOD/TNI_approval.php'>Training Nomination for Subordinate</a>               
            <a class='dropdown-item' href='../HOD/index.php'>Training Approval</a>       
        </div>
    </li>
    <?php endif; ?>

    <?php if ($exactHodEmpno == $sessionemp): ?>
        <li class='nav-item'>
            <a class='nav-link' style="color:white" href='Training_HOD.php'>HOD</a>
        </li>
        <?php endif; ?>

    <?php if ($hremp == 1): ?>
    <li class='nav-item dropdown'>
        <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown'
            aria-haspopup='true' aria-expanded='false' style='color: white;'>
            HR
        </a>
        <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
            <a class='dropdown-item' href='upload.php'>HR Functions</a>
            <a class='dropdown-item' href='excel_upload.php'>HR Upload</a>
            <a class='dropdown-item' href='buh_nomin.php'>Send Nominations for BUH Approval</a>
            <a class='dropdown-item' href='mail_training.php'>Mail Training Order</a>
            <a class='dropdown-item' href='report.php'>Report</a>  
            <a class='dropdown-item' href='Administrator.php'>Administrator</a><hr>
            <a class='dropdown-item' href='TNI_excel_upload.php'>TNI Excel Upload</a>
            <a class='dropdown-item' href='hr_functions_TNI.php'>HR Function TNI</a>
        </div>
    </li>           
    <?php endif; ?>   

    
    <?php if ($design === 'GM & BUH'): ?>
    <li class='nav-item'>
        <a class='nav-link' href='buh_approval.php'>BUH Approval&nbsp;&nbsp;&nbsp;&nbsp;</a>
    </li>
    <?php endif; ?> 
</ul>

      <br>
    

     <!-- <source src='singlepage.mp4' alt='Homepage Image'>  -->

    <!-- <video autoplay muted loop>
        <source src="Presentation1.mp4" type="video/mp4">
            Your browser does not support the video tag.
    </video> -->
    <div class="container">
    <center><h4>Request by you</h4>

 <?php
$serverName = "NSPCL-AD\SQLEXPRESS";
$connectionOptions = array(
    "Database" => "complaint",
    "UID" => "",
    "PWD" => ""
);

// Establish the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch data from the 'request' table for a specific empno
$employeeno = $_SESSION["emp_num"];
$sql = "SELECT  [srl_no]
        ,[empno]
        ,[Program_name]
        ,[Faculty]
        ,[nature_training]
        ,[year]
        ,[uploaded_date]
        ,[flag]
        ,[remarks]
        ,[duration]
        ,[tentative_date]
        ,[target_group]
        ,[hostel_book]
    FROM [Complaint].[dbo].[request]
    WHERE empno = ?";

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
                    <th scope="col">Program_name</th>               
                    <th scope="col">Nature_of_training</th>
                    <th scope="col">Year</th>  
                    <th scope="col">Hostel Required</th>
                    <th scope="col">Status</th>                                            
                    <th scope="col">Remarks</th>                
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
                $status = '<span style="color:blue">Pending at R1</span>';
                break;
            case 1:
                $status = '<span style="color:red">Reject by R1';
                break;
            case 2:
                $status = '<span style="color:blue">Pending at HOD';
                break;
            case 3:
                $status = '<span style="color:red">Reject by HOD';
                break;
            case 4:
                $status = '<span style="color:green">Approve';
                break;
            case 5:
                $status = '<span style="color:blue">Pending from BUH';
                break;
            case 6:
                $status = '<span style="color:green">Approved from BUH';
                break;
            case 7:
                $status = '<span style="color:green">Overall Approved';
                break;
            default:
                $status = 'Unknown';
        }
        
        // Determine hostel booking status
    $hostelStatus = ($row['hostel_book'] == 1) ? 'Yes' : 'No';

    echo "<td>{$hostelStatus}</td>";
        echo "<td>$status</td>";  
          
        echo "<td>{$row['remarks']}</td>"; 
    // echo "<td>
    //     <form action='deleteuser.php' method='post' style='display:inline;'>
    //         <input type='hidden' name='idToDeleteUser' value='{$row['srl_no']}'>
    //         <button type='submit' name='delete' class='btn btn-danger'><i class='fa fa-trash' aria-hidden='true'></i></button>
    //     </form>
    // </td>";
    echo "</tr>";
    $serialNo++;
}

echo '</tbody></table></div>';

}
else {
    echo '<p>No Pending Request by You.</p>';
}


// Close the connection
sqlsrv_close($conn);
?> 
</div>


</body>

</html>

<?php include '../footer.php';?>