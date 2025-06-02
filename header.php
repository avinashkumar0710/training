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
            
            
            $compare = "SELECT [empno],[name],[rep_ofcr],[hod_ro], [design], [grade], [dept] ,[location], [rep_ofcr] FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = $sessionemp";    //for user name show in header
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
                 //echo 'rep_ofcr' .$rep_ofcr;
                //echo 'location' .$location;
                //echo 'rep_ofcr' .$rep_ofcr;
                
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
            <a class='nav-link' style='color:white' href='TNI_home.php'>Home&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>
        <?php endif; ?>

        <?php if ($design != 'GM & BUH                      '): ?>
       
        <a class='nav-link' style='color:white' href='Training_TNI.php'>Training Identification Need</a>
            
        
        <?php endif; ?>

             <?php 
             // Construct the query
             $query = "
                 SELECT empno, name, rep_ofcr, hod_ro
                 FROM [Complaint].[dbo].[emp_mas_sap]
                 WHERE location = '" . $location . "' 
                 AND rep_ofcr = '" . $sessionemp . "'
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
             if ($hasReportingOfficer && $design !== 'GM & BUH' && $exactHodEmpno !== $sessionemp): ?>
    <li class='nav-item dropdown'>
        <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown'
           aria-haspopup='true' aria-expanded='false' style='color: white;'>
            Reporting Officer
        </a>
        <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
            <a class='dropdown-item' href='TNI_approval.php'>TNI Approval</a>
        </div>
    </li>
<?php endif; ?>

<?php 


// Print the exact HOD empno for verification
//echo "Exact HOD Empno: " . $exactHodEmpno;
if ($exactHodEmpno == $sessionemp): ?>
        <li class='nav-item'>
            <a class='nav-link' style="color:white" href='TNI_HOD.php'>HOD</a>
        </li>
        <?php endif; ?>

        <?php if ($hremp == 1): ?>
        <li class='nav-item dropdown'>
            <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown'
                aria-haspopup='true' aria-expanded='false' style='color: white;'>
                HR
            </a>
            <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
                <!-- <a class='dropdown-item' href='upload.php'>HR Functions</a> -->
                <a class='dropdown-item' href='excel_upload.php'>HR Upload</a>
                <!-- <a class='dropdown-item' href='buh_nomin.php'>Send Nominations for BUH Approval</a>
                <a class='dropdown-item' href='mail_training.php'>Mail Training Order</a> -->
                <a class='dropdown-item' href='report.php'>Report</a>
                <!-- <a class='dropdown-item' href='Administrator.php'>Administrator</a>
                <hr>
                <a class='dropdown-item' href='TNI_excel_upload.php'>TNI Excel Upload</a>
                <a class='dropdown-item' href='hr_functions_TNI.php'>HR Function TNI</a> -->
            </div>
        </li>
        <?php endif; ?>


        <?php if ($design === 'GM & BUH'): ?>
        <li class='nav-item'>
            <a class='nav-link' href='buh_approval.php'>BUH Approval&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>
        <?php endif; ?>
    </ul>