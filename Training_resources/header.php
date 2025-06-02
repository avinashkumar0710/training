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
            <a class='nav-link' style='color: white;' href='index.php'>Available Training Resources</a>
        </li>

        <li class='nav-item'>
            <a class='nav-link' style='color: white;' href='resource_document.php'>Training Resources document Uploaded by Employee</a>
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
        <li class='nav-item'>
            <a class='nav-link' style='color: white;' href='Upload_Employee_Review.php'>
                Upload By HR Admin
            </a>
           
        </li>
        <?php endif; ?>


    </ul>



   