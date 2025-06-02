<?php 
// start a new session
// Allow any origin to access this resource

session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }

    $sessionemp= $_SESSION["emp_num"];
    //echo $sessionemp;

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

           
    ?>

  <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
        <style>
    body {
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        margin: 0;
        padding: 0;
        background-color: #1971872e;
    }

    header {
        background-color: #34495E;
        color: #fff;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 80px;
    }
    
    
    </style>
<body>
    <div class='card text-center'>
        <div class='card-header'>
            <b><i><SPAN style='background-color:yellow'> <?php echo $username; ?>
                    </SPAN></i></b>&nbsp;&nbsp;
            <a href='../signout.php'><input type='submit' class='btn btn-success btn-sm' value='LOGOUT'></a>&nbsp;
        </div>
    </div>

    <ul class='nav justify-content-center' style='background-color: #34495E;'>
      
        <li class='nav-item'>
            <a class='nav-link' style='color:white' href='index.php'>Program Feedback&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li> 

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
        <li class='nav-item'>
            <a class='nav-link' style='color:white' href='feedbackview.php'>Program Feedback View&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li> 

        <li class='nav-item'>
            <a class='nav-link' style='color:white' href='overallfeedback.php'>Pending Status Feedback&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>

        <!-- <li class='nav-item'>
            <a class='nav-link' style='color:white' href='feedbackdetails.php'>Overall Feedback&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li> -->
        <?php endif; ?>   
    </ul>
    </body>
    