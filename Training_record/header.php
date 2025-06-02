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
            $current_page = basename($_SERVER['PHP_SELF']);
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
                //$access = $row['access'];
                $deptcode =$row['dept_code'];

                //echo $access;
            }            
    ?>

<?php
$emp_num = $_SESSION['emp_num'];  // assuming session holds emp_num
$sql = "SELECT [empno], [access]  
        FROM [Complaint].[dbo].[Training_HR_User]  
        WHERE empno = ?";
$params = array($emp_num);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (sqlsrv_has_rows($stmt)) {
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    $empno = $row['empno'];
    $access = $row['access'];

    // Example usage
    //echo "Emp No: " . $empno . "<br>";
    //echo "Access: " . $access;
} else {
    //echo "No record found.";
}
?>


  <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
        <head>
        <link rel="icon" type="image/png" sizes="32x32" href="../images/employee.ico">
            </head>
        <style>
    body {
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        margin: 0;
        padding: 0;
        
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
      
        <!-- <li class='nav-item'>
            <a class='nav-link' style='color:white' href='index.php'>Home&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li> -->

        <li class='nav-item'>
            <a class='nav-link' style="color: <?php echo ($current_page == 'training_dashboard.php') ? 'yellow' : 'white'; ?>;" 
             href='training_dashboard.php'>Training Status&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>

        <li class='nav-item'>
            <a class='nav-link' style="color: <?php echo ($current_page == 'TNI_PMS.php') ? 'yellow' : 'white'; ?>;" 
             href='TNI_PMS.php'>PMS TNI Status&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>

        <?php if (isset($access) && $access == 1): ?>
        <li class='nav-item'>
            <a class='nav-link' style="color: <?php echo ($current_page == 'mandays.php') ? 'yellow' : 'white'; ?>;" 
             href='mandays.php'>Check Mandays&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>
        <?php endif; ?>

        <li class='nav-item'>
            <a class='nav-link' style="color: <?php echo ($current_page == 'employeemandays.php') ? 'yellow' : 'white'; ?>;" 
             href='employeemandays.php'>Check Employee Mandays&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>
       
 
    </ul>