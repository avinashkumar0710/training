<?php 
// start a new session
// Allow any origin to access this resource
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp= $_SESSION["emp_num"];
    $user_role = $_SESSION['user_role']; 
    echo "<p>Your role: $user_role</p>";
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
?>
<!---------------------------------Start Header Area------------------------------------>

<?php           
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
                //echo '$design ' .$design;
                $grade =$row['grade'];                  

                $dept =$row['dept'];
                $location = $row['location'];
                $rep_ofcr = $row['rep_ofcr'];
                //$deptcode =$row['dept_code'];
                 //echo 'rep_ofcr' .$rep_ofcr;
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
    //echo '1212' .$exactHodEmpno;
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

        <?php
        // SQL query to fetch design
        $compare = "SELECT [design] FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
        $params = array($sessionemp); // Use session variable
        $stmt = sqlsrv_query($conn, $compare, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $design = ''; // Initialize variable to avoid undefined errors
        if (sqlsrv_has_rows($stmt)) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $design = $row['design'];
            //echo 'Design: ' . $design; // Debugging output
        }

        // Condition to hide/show navigation
        if ($design !== 'GM & BUH                      ' && $design !== 'Chief Executive Officer       ') {
            // Show navigation items for roles other than GM & BUH and Chief Executive Officer
            ?>
        <li class="nav-item dropdown">
            <a class="nav-link" style="color: white;" href="all_users.php">Available Training Programmes</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" style="color: white;" href="Training_prg_home.php">Status</a>
        </li>
        <?php
        }
    ?>

        <?php 
        //echo '$sessionemp' .$sessionemp;// Construct the query
             $query = "SELECT empno, name, rep_ofcr, hod_ro FROM [Complaint].[dbo].[emp_mas_sap] WHERE rep_ofcr = '" . $sessionemp . "'";
                          
             // Execute the query
             $result = sqlsrv_query($conn, $query);             
             if ($result === false) {
                 die(print_r(sqlsrv_errors(), true));
             }
             
             // Check if the design is not 'GM & BUH' and there are results from the query
             $hasReportingOfficer = false;
             $hod_ro = false;
             while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                 $hasReportingOfficer = true;
                 $hod_ro =  true;
                 //echo '$hasReportingOfficer' .$hasReportingOfficer;
                 break; // We only need to know if there's at least one row, so we can break here
             }


             $query = " SELECT  CASE  WHEN EXISTS ( SELECT 1 FROM [Complaint].[dbo].[emp_mas_sap] WHERE rep_ofcr = '$sessionemp' ) THEN 1
                            ELSE 0 END AS reportingoffc ";

            // Prepare and execute the query
            $params = array($sessionemp);
            $result = sqlsrv_query($conn, $query, $params);

            if ($result === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            // Fetch the result
            $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
            $reportingoffc = $row['reportingoffc']; // Store the result in the variable
            //echo '$reportingoffc' .$reportingoffc;

            $query2 = " SELECT CASE  WHEN EXISTS ( SELECT 1 FROM [Complaint].[dbo].[emp_mas_sap] WHERE hod_ro = '$sessionemp' ) THEN 1
                        ELSE 0 END AS hod_ro_exists ";

            // Prepare and execute the query for hod_ro
            $params2 = array($sessionemp);
            $result2 = sqlsrv_query($conn, $query2, $params2);

            if ($result2 === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            // Fetch the result for hod_ro
            $row2 = sqlsrv_fetch_array($result2, SQLSRV_FETCH_ASSOC);
            $hod_ro_exists = $row2['hod_ro_exists']; // Store the result in the variable
            //echo ' hod_ro: ' . $hod_ro_exists;
             
             sqlsrv_free_stmt($result); // Free the result set
             sqlsrv_free_stmt($result2);

             $sqlDesign = "SELECT empno, rep_ofcr, design FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = '$sessionemp'";
             $paramsDesign = array($rep_ofcr); // Using $rep_ofcr to find the designation
             $stmtDesign = sqlsrv_query($conn, $sqlDesign, $paramsDesign);
         
             if ($stmtDesign === false) {
                 die(print_r(sqlsrv_errors(), true));
             }
         
             $design = ''; // Initialize the variable for design
         
             // Fetch the design (designation) of the reporting officer
             if ($rowDesign = sqlsrv_fetch_array($stmtDesign, SQLSRV_FETCH_ASSOC)) {
                 $design = $rowDesign['design'];
                 //echo '$design;' . $design;
             }

             //echo '$sessionemp' .$sessionemp;
             if ($hasReportingOfficer  && $exactHodEmpno !== $sessionemp && $design !== 'GM & BUH                      ' && $design !== 'Chief Executive Officer       '):  ?>

        <li class='nav-item dropdown'>
            <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown'
                aria-haspopup='true' aria-expanded='false' style='color: white;'>
                Reporting Officer
            </a>
            <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
                <a class='dropdown-item' href='HOD/TNI_approval.php'>Training Nomination for Subordinate</a>
                <a class='dropdown-item' href='HOD/index.php'>Training Approval</a>
            </div>
        </li>
        <?php endif; ?>

        <!-- <?php if ($reportingoffc == 1 && $exactHodEmpno == $sessionemp): ?>
            <li class='nav-item dropdown'>
            <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown'
                aria-haspopup='true' aria-expanded='false' style='color: white;'>
                Reporting Officer1
            </a>
            <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
                <a class='dropdown-item' href='HOD/TNI_approval.php'>Training Nomination for Subordinate</a>
                 <a class='dropdown-item' href='HOD/index.php'>Training Approval</a>
            </div>
        </li>
        <?php endif; ?> -->

        <?php if ( $hod_ro_exists == 1): ?>
        <li class='nav-item'>
            <a class='nav-link' style="color:white" href='Training_HOD.php'>HOD</a>
        </li>
        <?php endif; ?>



        <?php 
         $employeeNumber = $_SESSION['emp_num'];
         $sql = "SELECT empno, access FROM [Complaint].[dbo].[Training_HR_User] WHERE empno = ?";
         $params = array($employeeNumber);
         $stmt = sqlsrv_query($conn, $sql, $params);
         
         if ($stmt === false) {
             die(print_r(sqlsrv_errors(), true));
         }
         
         $accessLevel = 0; // Default access level
         
         if (sqlsrv_has_rows($stmt)) {
             $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
             $accessLevel = $row['access'];
             //echo '$accessLevel:' .$accessLevel;
         }

        if ($accessLevel == 1): ?>
        <li class='nav-item dropdown'>
            <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown'
                aria-haspopup='true' aria-expanded='false' style='color: white;'>
                HR
            </a>
            <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
                <?php if ($employeeNumber == $sessionemp): ?>
                <a class='dropdown-item' href='HR/excel_upload.php'>HR Upload</a>
                <a class='dropdown-item' href='HR/Administrator.php'>Administrator<i>(Approve / Reject)</i></a>
                <a class='dropdown-item' href='HR/permission.php'>Access Permission<i>(whom you Authorized)</i></a>
                <hr>
                <?php endif; ?>
                <a class='dropdown-item' href='HR/pending_status.php'>Pending Status</a>
                <a class='dropdown-item' href='HR/upload.php'>HR Functions(HOD Approved List)</a>
                <a class='dropdown-item' href='HR/buh_nomin.php'>Send Nominations for BUH Approval</a>
                <a class='dropdown-item' href='HR/mail_training.php'>Mail Training Order</a>
                <a class='dropdown-item' href='HR/report.php'>Overall Report</a>

                <!-- <hr>
                <a class='dropdown-item' href='HR/TNI_excel_upload.php'>TNI Excel Upload</a>
                <a class='dropdown-item' href='HR/hr_functions_TNI.php'>HR Function TNI</a> -->
            </div>
        </li>
        <?php endif; ?>


        <?php if ($design === 'GM & BUH' && $hasReportingOfficer == '' ): ?>
        <li class='nav-item'>
            <a class='nav-link' href='buh_approval.php'>BUH Approval&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>
        <?php endif; ?>

        <?php if ($accessLevel == 1): ?>
        <li class='nav-item'>
            <a class='nav-link' style="color:white" href='buh_approval.php'>BUH Approval&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>
        <?php endif; ?>
        
        <?php
// Display session employee number
//echo 'findsession: ' . $sessionemp;

// Step 1: Query to fetch `rep_ofcr` for the current employee
$findrep_ofcr_query = "SELECT rep_ofcr FROM emp_mas_sap WHERE empno = ?";
$params1 = [$sessionemp];
$stmt1 = sqlsrv_query($conn, $findrep_ofcr_query, $params1);

if ($stmt1 === false) {
    die(print_r(sqlsrv_errors(), true)); // Handle query errors
}

$rep_ofcr = null;
if ($row = sqlsrv_fetch_array($stmt1, SQLSRV_FETCH_ASSOC)) {
    $rep_ofcr = $row['rep_ofcr'];
    //echo 'Reporting Officer: ' . $rep_ofcr . '<br>';
}
sqlsrv_free_stmt($stmt1);

// Step 2: Query to fetch `design` for the reporting officer
if ($rep_ofcr) {
    $fetch_design_query = "SELECT design FROM emp_mas_sap WHERE empno = ?";
    $params2 = [$rep_ofcr];
    $stmt2 = sqlsrv_query($conn, $fetch_design_query, $params2);

    if ($stmt2 === false) {
        die(print_r(sqlsrv_errors(), true)); // Handle query errors
    }

    $design = null;
    if ($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
        $design = $row['design'];
        //echo 'Designation: ' . $design . '<br>';
    }
    sqlsrv_free_stmt($stmt2);
} else {
    //echo 'No reporting officer found for the given employee.<br>';
}

// Step 3: Display the tab if the designation matches the roles
if ($design && in_array($design, ['GM & BUH                      ', 'Chief Executive Officer       '])): ?>
    <li class='nav-item'>
        <a class='nav-link' style="color:white" href='pendingHODapproval.php'>Pending HOD Approval</a>
    </li>
<?php endif; ?>

    </ul>