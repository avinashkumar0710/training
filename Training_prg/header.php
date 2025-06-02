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
     $current_page = basename($_SERVER['PHP_SELF']); 
// Condition to hide/show navigation
if ($user_role === "00") {
    // Show navigation items for roles other than GM & BUH and Chief Executive Officer
    ?>
    <li class="nav-item">
        <a class="nav-link" style="color:<?php echo ($current_page == 'all_users.php') ? 'yellow' : 'white'; ?>;" href="all_users.php">Available Training Programmes</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" style="color:<?php echo ($current_page == 'Training_prg_home.php') ? 'yellow' : 'white'; ?>;" href="Training_prg_home.php">Status</a>
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
                    <a class='nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>' style="color:white" href='HOD/index.php'>Training Approval</a>       
                </li>

            <li class='nav-item'>         
                <a class='nav-link <?php echo ($current_page == 'TNI_approval.php') ? 'active' : ''; ?>' style="color:white" href='HOD/TNI_approval.php'>Training Nomination for Subordinate</a> 
            </li>
   
        <?php endif; ?>

      
        <?php
         $current_page = basename($_SERVER['PHP_SELF']);
        
         if ( $user_role === "22"): ?>
          <li class='nav-item'>
        <a class='nav-link <?php echo ($current_page == 'Training_HOD.php') ? 'active' : ''; ?>' 
           style="color: <?php echo ($current_page == 'Training_HOD.php') ? 'yellow' : 'white'; ?>;" 
           href='Training_HOD.php'>HOD</a>
    </li>
           <li class='nav-item'>
        <a class='nav-link <?php echo ($current_page == 'TNI_approval.php') ? 'active' : ''; ?>' 
           style="color:white" 
           href='HOD/TNI_final.php'>Training Nomination for Subordinate</a> 
    </li>
    <!-- <li class='nav-item'>
        <a class='nav-link <?php echo ($current_page == 'External_training_calender.php') ? 'active' : ''; ?>' 
           style="color:white" 
           href='HOD/External_training_calender.php'>External Training Calender</a> 
    </li>

    <li class='nav-item'>
        <a class='nav-link <?php echo ($current_page == 'nominate_for_external_prg.php') ? 'active' : ''; ?>' 
           style="color:white" 
           href='HOD/nominate_for_external_prg.php'>Nominate for External Program</a> 
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
        <a class='nav-link <?php echo ($current_page == 'External_trg_calender_upload.php') ? 'active' : ''; ?>' 
           style="color:white" 
           href='HOD/upload_External_trg_calender.php'>Upload External Training Calender</a> 
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
                    <a class='dropdown-item' href='HR/excel_upload.php'>HR Upload</a>
                <a class='dropdown-item' href='HR/permission.php'>Access permission</a>
                <a class='dropdown-item' href='HR/rejected_request.php'>Rejected Status</a>
                <hr>
                <a class='dropdown-item' href='HR/edit_training_types.php'>Edit / Add Training Types</a>
                <hr>
                <?php endif; ?>
               
                
               
                <a class='dropdown-item' href='HR/pending_status.php'>Pending Status</a>  
            <a class='dropdown-item' href='HR/upload.php'>HR Functions(HOD Approved List)</a>
            <hr>    
            <?php endif; ?>             
               
            <a class='dropdown-item' href='HR/Administrator.php'>Administrator</a>          
            <a class='dropdown-item' href='HR/buh_nomin.php'>Send Nominations for BUH Approval</a>
            <a class='dropdown-item' href='HR/mail_training.php'>Mail Training Order</a>
            <a class='dropdown-item' href='HR/report.php'>Overall Report</a>
                
                <!-- <hr>
                <a class='dropdown-item' href='HR/TNI_excel_upload.php'>TNI Excel Upload</a>
                <a class='dropdown-item' href='HR/hr_functions_TNI.php'>HR Function TNI</a> -->
            </div>
        </li>

        <li class='nav-item'>
            <a class='nav-link' style="color:white" href='buh_approval.php'>BUH Approval&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>
        <?php endif; ?>


        <?php if ($user_role === "33"): ?>
        <li class='nav-item'>
            <a class='nav-link' href='buh_approval.php'>BUH Approval&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>
        <?php endif; ?>

      
    </ul>



   