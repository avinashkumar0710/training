<?php 
// start a new session
// Allow any origin to access this resource
ob_start(); // Start output buffering
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp= $_SESSION["emp_num"];
   

    // Add '00' in front if session value has only 6 digits
   
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
echo 'empno1' .$sessionemp;
// Step 1: Fetch the location and dept based on the session employee number
$sqlLocation = "SELECT location, dept FROM emp_mas_sap WHERE empno = $sessionemp";
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
?>
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


<?php
// SQL query to fetch employee details along with their current status
$sql = "SELECT 
            emp.empno, 
            emp.name, 
            emp.location, 
            emp.loc_desc, 
            emp.plant,
            CASE 
                WHEN u.access = 1 THEN 'Active' 
                ELSE 'Inactive' 
            END AS status
        FROM [Complaint].[dbo].[emp_mas_sap] emp
        LEFT JOIN [Complaint].[dbo].[Training_HR_User] u ON emp.empno = u.empno
        WHERE emp.location = ? AND emp.dept = 'Human Resource';";

// Prepare and execute the SQL statement
$params = array($location);
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Start the HTML table
echo '
<div class="container">
<table class="table table-bordered border-success" border="1">
<thead style="position: sticky; top: 0; background-color: beige; z-index: 1;">
        <tr class="bg-primary" style="color:#ffffff">
            <th>Employee Number</th>
            <th>Name</th>
            <th>Location</th>
            <th>Location Description</th>
            <th>Plant</th>
            <th>Status</th>
            <th>Action</th>
        </tr>';

// Fetch and display each employee's details
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($row['empno']) . '</td>';
    echo '<td>' . htmlspecialchars($row['name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['location']) . '</td>';
    echo '<td>' . htmlspecialchars($row['loc_desc']) . '</td>';
    echo '<td>' . htmlspecialchars($row['plant']) . '</td>';
    echo '<td>' . htmlspecialchars($row['status']) . '</td>'; // Display current status

    // Action buttons
    echo '<td>';
    if ($row['status'] === 'Active') {
        echo '<form method="post" action="update_status.php">
                <input type="hidden" name="empno" value="' . htmlspecialchars($row['empno']) . '">
                <input type="hidden" name="action" value="deactivate">
                <input type="submit" value="Deactivate">
              </form>';
    } else {
        echo '<form method="post" action="update_status.php">
                <input type="hidden" name="empno" value="' . htmlspecialchars($row['empno']) . '">
                <input type="hidden" name="action" value="activate">
                <input type="submit" value="Activate">
              </form>';
    }
    echo '</td>';
    echo '</tr>';
}

// End the HTML table
echo '</table>';
echo '</div>';

// Free statement and close connection
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>

</body>

</html>
<?php include '../footer.php';?>