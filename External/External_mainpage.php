<?php 
// start a new session
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }

    $sessionemp= $_SESSION["emp_num"];
    $user_role = $_SESSION['user_role']; 
    //echo "<p>Your role: $user_role</p>";   
    // Add '00' in front if session value has only 6 digits
    if(strlen($sessionemp) == 6) {
        $sessionemp = '00' . $sessionemp;
    }
    //echo "<p>Your role: $sessionemp</p>";  
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

    $name = "SELECT emp_name, access, dept_code, Plant FROM EA_webuser_tstpp WHERE emp_num = ?";    //for user name show in header
            $params = array($_SESSION['emp_num']);
            $stmt = sqlsrv_query($conn, $name, $params);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            if (sqlsrv_has_rows($stmt)) {
                // Get the user name from the result set
                $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                $username = $row['emp_name'];
                $dept_code =$row['dept_code'];
                $Plant =$row['Plant'];
                // echo '$username' .$username;
                // echo '$dept_code' .$dept_code;
                // echo 'empno' .$sessionemp;
                // echo 'Plant' .$Plant;
            } 

            $compare = "SELECT [empno],[name], [design], [location] FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = $sessionemp";    //for user name show in header
            $params = array($_SESSION['emp_num']);
            $stmt = sqlsrv_query($conn, $compare, $params);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            if (sqlsrv_has_rows($stmt)) {
                // Get the user name from the result set
                $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
              
                $design =$row['design'];
               
                //echo 'design' .$design;
                
            }     
?>
<!DOCTYPE html>


<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="employee.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="Externalmainpage.css">
    <title>Training | HomePage</title>
</head>
<style>
#upload-form {
    height: 600px;
    /* Set a fixed height */
    overflow-y: auto;
    /* Enable vertical scrolling */
    border: 1px solid #ccc;
    /* Optional: add a border for better visibility */
    padding: 15px;
    /* Optional: add padding inside the form */
    background-color: antiquewhite;
}


.rectangle {
    font-size: 20px;
    width: 200px;
    height: 350px;
    background-color: #607d8b;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    box-sizing: border-box;
    transition: transform 0.3s ease, background-color 0.3s ease;
    border-radius: 10px;
    margin: 0 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2),
        0 0 20px rgba(0, 0, 0, 0.1),
        0 0 30px rgba(0, 0, 0, 0.1);
}
</style>

<header>
    <h1 class="title">Welcome <span style="color: yellow"><i><?php echo $username ?></i></span> to Training Portal</h1>
</header>
<?php 
        // Fetch the employee number and access level before outputting the links
        $employeeNumber = $_SESSION['emp_num'];
        $upload_time = date("Y-m-d H:i:s");
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
            //echo 'access' .$accessLevel;
        }

        // Free statement and connection resources for the second query
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        ?>
<div class="container-fluid" style="display: flex; height: 90vh;">
       <div class="container">        
        <?php
        $design = trim($design);

        if ($user_role === "33"): ?>
            <a href="Training_prg/buh_approval.php" class="no-underline">
                <div class="rectangle">
                    <span>BUH / CEO Approval</span>
                </div>
            </a>
            <?php endif; ?>


            <?php if (in_array($user_role, ["00", "11", "22", "44"])): ?>
            <a href="Training_prg/Training_prg_home.php" class="no-underline">
                <div class="rectangle">
                    <span>Training Program</span>
                </div>
            </a>
            <?php endif; ?>

            <?php if ($user_role === "44"): ?>

            <a href="Training_admin/attendancebyHR.php" class="no-underline">
                <div class="rectangle">
                    <span>Training Administration</span>
                </div>
            </a>


<?php endif; ?>

    <?php if (in_array($user_role, ["00", "11", "22", "44"])): ?>
    <a href="Training_feedback/index.php" class="no-underline">
        <div class="rectangle">
            <span>Training Feedback & Evaluation System</span>
        </div>
    </a>
<?php endif; ?>





         

            <?php 
$design = trim($design);
if (!in_array($design, ['GM & BUH',  'Chief Executive Officer']) && $accessLevel == 1): ?>
   
<?php endif; ?>



            <?php 
            //echo 'Debug: Design received - [' . $design . ']';

// Trim and handle comparison
$design = trim($design);
            if (!in_array($design, ['GM & BUH',  'Chief Executive Officer'])): ?>
    
<?php endif; ?>



<?php if (in_array($user_role, ["00", "11", "22", "33", "44"])): ?>
            <a href="Training_record/training_dashboard.php" class="no-underline">
                <div class="rectangle">
                    <span>Training Record & MIS</span>
                </div>
            </a>

           
            <a href="Training_resources/index.php" class="no-underline">
                <div class="rectangle">
                    <span>Training Resources</span>
                </div>
            </a>
            <?php endif; ?>       


        </div>
   
  
    <footer>
        <?php include 'footer.php';?>
    </footer>
    </body>

    </html>