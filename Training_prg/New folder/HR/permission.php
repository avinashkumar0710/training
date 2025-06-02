<?php 
// start a new session
// Allow any origin to access this resource
ob_start(); // Start output buffering
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp= $_SESSION["emp_num"];
    //echo 'empno' .$sessionemp;

    // Add '00' in front if session value has only 6 digits
    if(strlen($sessionemp) == 6) {
        $sessionemp = '00' . $sessionemp;
    }
    //echo 'empno' .$sessionemp;

  // Database connection
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

// Check if the connection failed
if ($conn === false) {
    die("Connection Error: " . print_r(sqlsrv_errors(), true));
}

// Step 1: Fetch the location and dept based on the session employee number
$sqlLocation = "SELECT location, dept FROM emp_mas_sap WHERE empno = ?";
$paramsLocation = array($sessionemp);
$Location = sqlsrv_query($conn, $sqlLocation, $paramsLocation);

// Check if the query failed
if ($Location === false) {
    die("Location Query Error: " . print_r(sqlsrv_errors(), true));
}

// Fetch location and dept
$rowLocation = sqlsrv_fetch_array($Location, SQLSRV_FETCH_ASSOC);
if ($rowLocation === null) {
    die("No location or dept found for empno: $sessionemp");
}
$location = $rowLocation['location'];
$dept = $rowLocation['dept'];

// Step 2: Fetch empno and name for the location and dept
$sqlEmp = "SELECT empno, name FROM emp_mas_sap WHERE location = ? AND dept = ? AND status='A'";
$paramsEmp = array($location, $dept);
$Emp = sqlsrv_query($conn, $sqlEmp, $paramsEmp);

// Check if the query failed
if ($Emp === false) {
    die("Employee Query Error: " . print_r(sqlsrv_errors(), true));
}

// Fetch results into an array
$employees = [];
while ($rowEmp = sqlsrv_fetch_array($Emp, SQLSRV_FETCH_ASSOC)) {
    if ($rowEmp) {
        $employees[] = $rowEmp; // Store each row in the array
    }
}
?>
<!---------------------------------Start Header Area------------------------------------>
<html>

<head>
    <title>Training | Home</title>
    <link rel="icon" type="image/png" sizes="32x32" href="employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
    body {
        font-weight: 600;
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        margin: 0;
        /* Remove default body margin */
        padding: 0;
        /* Remove default body padding */
        background-color: #e8eef3;
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
    </style>


</head>

<body>
    <?php include '../header_HR.php';?>

    <h6><i class="fa fa-home" aria-hidden="true"></i>&nbsp;<i><u>HR->Access Permission</u></i></h6>


    <div class="container">
        <h2>Employee Access Management</h2>
        <form method="POST" action="">
            <table class="table table-bordered border-success">
                <thead style="position: sticky; top: 0; background-color: beige; z-index: 1;">
                    <tr class="bg-primary" style="color:#ffffff">
                        <th>Employee Name</th>
                        <th>Employee No</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): 
                        // Remove leading zeros for empno
                        $empno = ltrim($emp['empno'], '0');
                        
                        // Check the status of the employee in Training_HR_User
                        $sqlStatus = "SELECT access FROM [Complaint].[dbo].[Training_HR_User] WHERE empno = ?";
                        $paramsStatus = array($empno);
                        $stmtStatus = sqlsrv_query($conn, $sqlStatus, $paramsStatus);

                        // Initialize access as not found
                        $access = null; 
                        
                        if ($stmtStatus !== false) {
                            if ($rowStatus = sqlsrv_fetch_array($stmtStatus, SQLSRV_FETCH_ASSOC)) {
                                $access = $rowStatus['access']; // Get the access value
                            }
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($emp['name']); ?></td>
                        <td><?php echo htmlspecialchars($empno); ?></td>
                        <td
                            style="color: <?php echo htmlspecialchars($access !== null ? ($access ? 'green' : 'red') : 'blue'); ?>;">
                            <?php 
                            echo htmlspecialchars($access !== null ? ($access ? 'Active (1)' : 'Inactive (0)') : 'Not Found'); 
                            ?>
                        </td>
                        <td>
                            <select name="access[<?php echo htmlspecialchars($empno); ?>]" class="form-select">
                                <option value="" disabled selected>Please select</option>
                                <option value="1">Activate</option>
                                <option value="0">Deactivate</option>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
            <button type="submit" class="btn btn-primary">Submit Changes</button>
        </form>

        <?php
   // Step 3: Handle form submission for activate or deactivate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['access'])) {
    foreach ($_POST['access'] as $empno => $access) {
        // Remove leading zeros for empno
        $empno = ltrim($empno, '0');

        // Check if the employee already exists in the Training_HR_User table
        $sqlCheck = "SELECT empno FROM [Complaint].[dbo].[Training_HR_User] WHERE empno = ?";
        $paramsCheck = array($empno);
        $stmtCheck = sqlsrv_query($conn, $sqlCheck, $paramsCheck);

        if ($stmtCheck === false) {
            die("Check Query Error: " . print_r(sqlsrv_errors(), true));
        }

        if (sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC)) {
            // Update existing record
            $sqlUpdate = "UPDATE [Complaint].[dbo].[Training_HR_User] SET access = ? WHERE empno = ?";
            $paramsUpdate = array($access, $empno);
            $stmtUpdate = sqlsrv_query($conn, $sqlUpdate, $paramsUpdate);

            if ($stmtUpdate === false) {
                die("Update Query Error: " . print_r(sqlsrv_errors(), true));
            }
        } else {
            // Insert new record if the employee doesn't exist
            $sqlInsert = "INSERT INTO [Complaint].[dbo].[Training_HR_User] (empno, access) VALUES (?, ?)";
            $paramsInsert = array($empno, $access);
            $stmtInsert = sqlsrv_query($conn, $sqlInsert, $paramsInsert);

            if ($stmtInsert === false) {
                die("Insert Query Error: " . print_r(sqlsrv_errors(), true));
            }
        }
    }

    // Redirect to the same page to refresh the data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit(); // Ensure no further code is executed after the redirect
}

    // Close the connection
    sqlsrv_close($conn);
    ob_end_flush(); // End output buffering and send output
    ?>
    </div>


</body>

</html>
<?php include '../footer.php';?>