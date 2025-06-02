<?php 
// start a new session
// Allow any origin to access this resource
session_start();
if (!isset($_SESSION["emp_num"]) || !isset($_SESSION['user_role'])) {   
    header("Location: login.php");
    exit();
}

$sessionemp = $_SESSION["emp_num"];
$user_role = $_SESSION["user_role"]; // Store user role
//echo ('dfs'. $user_role);
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

            //echo 'empl' .$sessionemp;
            //echo 'dept' .$deptcode;

            // $hod = 0;
            // $sqlhod = "SELECT * FROM [Complaint].[dbo].[emp_mas_sap] WHERE hod_ro = $sessionemp";
            // $stmt = sqlsrv_query($conn, $sqlhod);
            
            // if ($stmt === false) {
            //     die(print_r(sqlsrv_errors(), true));
            // }
            // // else {
            // //     $hod = 1;
            // //  }
            // while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {                
            //     // $hod_ro = $row['hod_ro'];
            //     // echo 'hod:' .$hod_ro;
            //     // Now you can use $hod_ro as needed

            //     $hod = 1;
            //     //echo 'hod: ' .$hod;
            // }
            // $hremp = 0;
            // $accessValue = null;
            // $sql = "SELECT empno, access FROM [Complaint].[dbo].[Training_HR_User] where empno= $sessionemp";
            // $stmt = sqlsrv_query($conn, $sql);

            // if ($stmt === false) {
            //     die(print_r(sqlsrv_errors(), true));
            // }
            
            // while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            //     // Store the access value in a variable
            //      $accessValue = $row['access'];            
            //      //echo 'Empno: ' . $row['empno'] . ', Access: ' . $accessValue . '<br>';
            //     $hremp = 1;
            //     //echo ' hremp'.$hremp;
            // }     
            
            
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

<script>
        // When a user clicks a tab, change its color to yellow
        document.querySelectorAll('.nav-link').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.nav-link').forEach(t => t.style.color = 'white'); // Reset all to white
                this.style.color = 'yellow'; // Set clicked tab to yellow
            });
        });
    </script>

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
        // Condition to hide/show navigation
        $current_page = basename($_SERVER['PHP_SELF']); 

        if ($user_role === "00") {
            // Show navigation items for roles other than GM & BUH and Chief Executive Officer
            ?>
            <li class='nav-item'>
                <a class='nav-link' style="color:<?php echo ($current_page == 'all_users.php') ? 'yellow' : 'white'; ?>;" href="all_users.php">Available Training Programmes</a>
            </li>
            <li class='nav-item'>
                <a class='nav-link' style="color:<?php echo ($current_page == 'Training_prg_home.php') ? 'yellow' : 'white'; ?>;" href='Training_prg_home.php'>Status</a>
            </li>
            <?php
        }
        ?>





       

        <?php 
        //echo '$sessionemp' .$sessionemp;// Construct the query
          //echo '$sessionemp' .$sessionemp;
          $current_page = basename($_SERVER['PHP_SELF']); // Get current page filename

             if ($user_role === "11"):  ?>

        <li class='nav-item'>   
            <a class='nav-link' style="color:<?php echo ($current_page == 'index.php') ? 'yellow' : 'white'; ?>;" href='index.php'>Training Approval</a>       
        </li>

        <li class='nav-item'>         
        <a class='nav-link' style="color:<?php echo ($current_page == 'TNI_approval.php') ? 'yellow' : 'white'; ?>;" href='TNI_approval.php'>Training Nomination for Subordinate</a> 
        </li>
       
        <?php endif; ?>

      
        <?php if ( $user_role === "22"): ?>
            <li class='nav-item'>
            <a class='nav-link' style="color: <?php echo ($current_page == 'Training_HOD.php') ? 'yellow' : 'white'; ?>;" 
                href='../Training_HOD.php'>HOD</a>
            </li>

           <li class='nav-item'>
                <a class='nav-link'   style="color:<?php echo ($current_page == 'TNI_final.php') ? 'yellow' : 'white'; ?>;"  
                href='TNI_final.php'>Training Nomination for Subordinate</a> 
            </li>

            <!-- <li class='nav-item'>
                <a class='nav-link'  style="color:<?php echo ($current_page == 'External_training_calender.php') ? 'yellow' : 'white'; ?>;"             
           href='External_training_calender.php'>External Training Calender</a> 
    </li>

    <li class='nav-item'>
        <a class='nav-link' style="color:<?php echo ($current_page == 'nominate_for_external_prg.php') ? 'yellow' : 'white'; ?>;" 
           
           href='nominate_for_external_prg.php'>Nominate for External Program</a> 
    </li>

    <?php 
    
    $employeeNumber = $_SESSION['emp_num'];
    $sql = "SELECT empno, access , super_access FROM [Complaint].[dbo].[Training_HR_User] WHERE empno = ?";
    $params = array($employeeNumber);
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    
    $accessLevel = 0; // Default access level
    
    if (sqlsrv_has_rows($stmt)) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $accessLevel = $row['access'];
        $superaccessLevel = $row['super_access'];
        //echo '$superaccessLevel:' .$superaccessLevel;
    }

    if ($accessLevel == 1): ?>
        <a class='nav-link' style="color:<?php echo ($current_page == 'upload_External_trg_calender.php') ? 'yellow' : 'white'; ?>;" 
           
           href='upload_External_trg_calender.php'>Upload External Training Calender</a> 
                <?php endif; ?>
        <?php endif; ?> -->

        <?php 
         $employeeNumber = $_SESSION['emp_num'];
         $sql = "SELECT empno, access , super_access FROM [Complaint].[dbo].[Training_HR_User] WHERE empno = ?";
         $params = array($employeeNumber);
         $stmt = sqlsrv_query($conn, $sql, $params);
         
         if ($stmt === false) {
             die(print_r(sqlsrv_errors(), true));
         }
         
         $accessLevel = 0; // Default access level
         
         if (sqlsrv_has_rows($stmt)) {
             $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
             $accessLevel = $row['access'];
             $superaccessLevel = $row['super_access'];
             //echo '$superaccessLevel:' .$superaccessLevel;
         }

        if ($user_role === "44"): ?>
        <li class='nav-item dropdown'>
            <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown'
                aria-haspopup='true' aria-expanded='false' style='color: white;'>
                HR
            </a>
            <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
            <?php if ($employeeNumber == $sessionemp): ?>
                
                <?php if ($superaccessLevel == 1): ?>
                    <a class='dropdown-item' href='excel_upload.php'>HR Upload</a>
                <a class='dropdown-item' href='permission.php'>Access permission</a>
                <a class='dropdown-item' href='rejected_request.php'>Rejected Status</a>
                <hr>
                <a class='dropdown-item' href='edit_training_types.php'>Edit / Add Training Types</a>
                <hr>
                <?php endif; ?>
                
               
                <a class='dropdown-item' href='pending_status.php'>Pending Status</a>  
                <a class='dropdown-item' href='upload.php'>HR Functions(HOD Approved List)</a>                                  
                <hr>
            <?php endif; ?>               
            <a class='dropdown-item' href='Administrator.php'>Administrator</a>      
            <a class='dropdown-item' href='buh_nomin.php'>Send Nominations for BUH Approval</a>
            <a class='dropdown-item' href='mail_training.php'>Mail Training Order</a>
            <a class='dropdown-item' href='report.php'>Overall Report</a>
                
                <!-- <hr>
                <a class='dropdown-item' href='HR/TNI_excel_upload.php'>TNI Excel Upload</a>
                <a class='dropdown-item' href='HR/hr_functions_TNI.php'>HR Function TNI</a> -->
            </div>
        </li>

        <li class='nav-item'>
            <a class='nav-link' style="color:white" href='../buh_approval.php'>BUH Approval&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>
        <?php endif; ?>


        <?php if ($user_role === "33"): ?>
        <li class='nav-item'>
            <a class='nav-link' href='buh_approval.php'>BUH Approval&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>
        <?php endif; ?>

      
    </ul>



   