    <?php 
// start a new session
// Allow any origin to access this resource

session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:HODlogin.php");
}

$sessionemp = $_SESSION["emp_num"];

// Ensure $hodempno has 8 digits (prepend 00 if it starts with 0) or 7 digits
$sessionemp = str_pad($sessionemp, 8, "0", STR_PAD_LEFT);

//echo 'employeeno' . $sessionemp;

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
<html>

<head>
    <title>Training | HOD</title>
    <link rel="icon" type="image/png" sizes="32x32" href="employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"    rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 
    
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

    .row{
        margin: 0;
        /* Remove default body margin */
        padding: 0;
        /* Remove default body padding */
    }
    .checkbox-container {
    position: relative;
    display: inline-block;
    vertical-align: middle;
}

.checkbox-container input[type="checkbox"] {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    position: absolute;
    top: 0;
    left: 0;
    height: 20px;
    width: 20px;
    background-color: #eee;
    border: 1px solid #ccc;
}

.checkbox-container:hover .checkmark {
    background-color: #ddd;
}

.checkbox-container input[type="checkbox"]:checked + .checkmark {
    background-color: #2196F3;
}

.checkmark:after {
    content: "";
    position: absolute;
    display: none;
}

.checkbox-container input[type="checkbox"]:checked + .checkmark:after {
    display: block;
}

.checkbox-container .checkmark:after {
    left: 7px;
    top: 3px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

    </style>
</head>
<?php include 'header.php';?>


 <!---------------------------------------------------------------------------------------------------------------------------------------------------->
 <body style="background-color: #e8eef3">
    <div class="full-width">         
    <div class="row">
    <div class="col-md-6">
            <?php   

    $sql = "SELECT hod_ro, dept, location FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
    $params = array($sessionemp);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch the result
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    // Assuming that 'hod_ro' is the column name
    $hod_ro = $row['hod_ro'];
    //echo 'dept:' .$dept;
    //echo 'hod_ro:' .$hod_ro;
    $dept = $row['dept'];
    $location = $row['location'];

    // Fetch empno from emp_mas_sap table based on hod_ro
    $sql = "SELECT empno FROM [Complaint].[dbo].[emp_mas_sap] WHERE hod_ro = ?";
    $params = array($hod_ro);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch all empno values
    $empnos = array();  // Initialize $empnos as an empty array
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $empno = ltrim($row['empno'], '00'); // Remove leading '0'
        $empnos[] = $empno;
        //echo 'Empno: ' . $empno . '<br>';
    }

      // SQL query to fetch rep_ofcr
      $sql = "select rep_ofcr,hod_ro   FROM [Complaint].[dbo].[emp_mas_sap] WHERE rep_ofcr !='$hod_ro' and hod_ro='$hod_ro'";
      $stmt = sqlsrv_query($conn, $sql);

      if ($stmt === false) {
          die(print_r(sqlsrv_errors(), true));
      }

      // Fetch the rep_ofcr value
      if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
          $rep_ofcr = $row['rep_ofcr'];
          $hod_ro = $row['hod_ro'];
          // Use $rep_ofcr as needed
          //echo '$rep_ofcr :' .$rep_ofcr;
          //echo '$hod_ro :' .$hod_ro;
      }
        //echo 'access' .$accessValue;
    //echo 'session' .$sessionemp;
    //echo 'location' .$location;

    // $sql = "";
    // if ($accessValue == 1) {
    //     // Access value is 1, so execute the modified query
    //     $sql = "SELECT r.Id, r.srl_no, e.name, e.empno, r.Program_name, r.Faculty, r.nature_training, r.year,  r.remarks, r.duration, r.tentative_date, r.target_group, r.rep_ofcr, e.hod_ro
    //     FROM [Complaint].[dbo].[request_TNI] r
    //     JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
    //     WHERE flag = '0'";
    // } else {
        // Access value is not 1, so execute the original query
        // $sql = "SELECT e.empno, e.name, e.rep_ofcr, e.hod_ro, r.Id, r.srl_no, r.Program_name, r.Faculty, r.nature_training, r.year, r.remarks, r.duration, r.tentative_date, r.target_group, r.empno, r.flag
		// FROM [Complaint].[dbo].[emp_mas_sap] e join [Complaint].[dbo].[request] r ON 
        // e.empno = r.empno where e.hod_ro='$sessionemp'  and r.flag='2'";

        $sql = "SELECT e.empno, e.name, e.rep_ofcr, e.hod_ro, r.Id, r.srl_no, r.Program_name, r.Faculty, r.nature_training, r.year, r.remarks, r.duration, r.tentative_date, r.target_group, r.empno, r.flag
		FROM [Complaint].[dbo].[emp_mas_sap] e join [Complaint].[dbo].[request] r ON 
        e.empno = r.empno where e.hod_ro='$sessionemp'  and r.flag='2'";
    //}

    // Execute the SQL query
    $stmt = sqlsrv_query($conn, $sql);

    echo '<h6><i class="fa fa-home"></i>&nbsp;<i><u>HOD->TNI HOD Approval</u></i></h6>';
    echo '<center><h5>Pending HOD List</h5></center>';
    echo '<div class="table" style="height:630px; overflow-x: auto; font: size 10px;">';
    echo '<form action="approve_HOD.php" method="post" id="approveForm" onsubmit="return handleSubmit()">';
    

    // Start the table structure
    echo '<table class="table table-bordered border-success" border="3" border="1" >';
    echo '<thead style="position: sticky; top: 0; background-color: beige;z-index: 1;">';
    echo '<tr class="table-success" style="font-size:14px;">';
    echo '<th scope="col">Emp Name</th>';
    echo '<th scope="col">Program Name</th>';
    echo '<th scope="col">Faculty</th>';
    echo '<th scope="col">Nature of Training</th>';
    echo '<th scope="col">Year</th>';
    echo '<th scope="col">Duration</th>';
    echo '<th scope="col">Tentative Date</th>';
    echo '<th scope="col">Target Group</th>';
    echo '<th scope="col">Remarks</th>';
    echo '<th scope="col">Select</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    // Loop through the fetched data and create table rows
    if ($stmt === false) {
        // Handle error
    } elseif (sqlsrv_has_rows($stmt)) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            echo '<tr class="table-light" style="font-size:14px;">';
            echo '<td>' . $row['name'] . '</td>';
            echo '<td>' . $row['Program_name'] . '</td>';
            echo '<td>' . $row['Faculty'] . '</td>';
            echo '<td>' . $row['nature_training'] . '</td>';
            echo '<td>' . $row['year'] . '</td>';
            echo '<td>' . $row['duration'] . '</td>';
            echo '<td>' . $row['tentative_date'] . '</td>';
            echo '<td>' . $row['target_group'] . '</td>';
            echo '<td>' . $row['remarks'] . '</td>';
            echo '<td>';
            echo '<label class="checkbox-container">';
            echo '<input type="checkbox" name="selectedIds[]" value="' . $row['Id'] . '" data-group="' . $row['nature_training'] . '" onchange="handleCheckboxSelection(this)">';
            echo '<span class="checkmark"></span>';
            echo '</label>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        // No pending requests found
        echo '<tr><td colspan="10">No pending requests found</td></tr>';
    }

    // Close the table structure
    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    // Submit button initially disabled
    echo '<button type="submit" id="approveButton" name="approve" class="btn btn-success" >Approve Selected</button>';
    

    // Close the form
    echo '</form>';    

// Close the connection
sqlsrv_close($conn);
?>
     
   
     <script>
    // Function to update the submit button and handle checkbox selection
   
        

    // Function to handle checkbox selection
    function handleCheckboxSelection(checkbox) {
        console.log("Checkbox clicked:", checkbox.value);
        var checkboxes = document.querySelectorAll('input[type="checkbox"][data-group="' + checkbox.dataset.group + '"]');
        console.log("Checkboxes in group:", checkboxes);
        
    }

    // Function to handle form submission
    function handleSubmit() {
        var checkboxes = document.querySelectorAll('input[name="selectedIds[]"]');
        var selectedCount = 0;

        checkboxes.forEach(function(checkbox) {
            if (checkbox.checked) {
                selectedCount++;
            }
        });

        if (selectedCount === 0) {
            alert("Please select at least one checkbox.");
            return false; // Prevent form submission
        }

        return true; // Allow form submission
    }
</script>
</div>
 <!-------------------------------------Approved by You------------------------------------------------------>
 <div class="col-md-6" style="box-shadow: rgba(0, 0, 0, 0.25) 0px 54px 55px, rgba(0, 0, 0, 0.12) 0px -12px 30px, 
        rgba(0, 0, 0, 0.12) 0px 4px 6px, rgba(0, 0, 0, 0.17) 0px 12px 13px, rgba(0, 0, 0, 0.09) 0px -3px 5px; height: 850px;">
        <?php
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);

// Create connection
$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}
// Get the current year
$currentYear = date("Y");
// Check if a year is selected
if (isset($_POST['year'])) {
    $selectedYear = $_POST['year'];
} else {
    $selectedYear = $currentYear;
}

//echo 'session'. $sessionemp;
// Fetch distinct years from the database

    $sqlYear = "SELECT DISTINCT r.year
    FROM [Complaint].[dbo].[request] r 
    JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
    WHERE flag in ('4') and appr_empno ='$sessionemp'";


$stmtYear = sqlsrv_query($conn, $sqlYear);

if ($stmtYear === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Extract years from the result set
$years = array();
while ($rowYear = sqlsrv_fetch_array($stmtYear, SQLSRV_FETCH_ASSOC)) {
    $years[] = $rowYear['year'];
}
sqlsrv_free_stmt($stmtYear);

// $sqlRecords = "";
// if ($accessValue == 1) {
//     // Access value is 1, so execute the modified query
//     $sqlRecords = "SELECT r.srl_no, r.empno, r.Program_name, r.year, a.name, r.aprroved_time, r.flag, r.tentative_date, a.hod_ro 
//     FROM [Complaint].[dbo].[request_TNI] r
//     JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
//     WHERE flag in ('2')  AND r.year = '$selectedYear'";
// } else {
    // Access value is not 1, so execute the original query
    $sqlRecords = "SELECT r.srl_no, r.empno, r.Program_name, r.year, a.name, r.aprroved_time, r.flag, r.tentative_date, a.hod_ro ,a.rep_ofcr, r.nature_training
    FROM [Complaint].[dbo].[request] r
    JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
    WHERE flag in ('4') AND   r.appr_empno ='$sessionemp' AND r.year = '$selectedYear'";

//}

$stmtRecords = sqlsrv_query($conn, $sqlRecords);

if ($stmtRecords === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Display the form for selecting a year
echo '<form method="post">';
echo '<label for="year">Select Year:</label>&nbsp';
echo '<select name="year" id="year">';
foreach ($years as $year) {
    $selected = ($year == $selectedYear) ? 'selected' : '';
    echo "<option value=\"$year\" $selected>$year</option>";
}
echo '</select>';
echo '&nbsp<button type="submit" class="btn btn-primary">Show</button>&nbsp<i style="font-size:small; background-color:yellow;">&nbsp;&nbsp;(Note: Please select the year first to display the Program Name.)</i>';
echo '</form>';

echo '<center><h5>Approve / Reject By You</h5></center>';
echo '<div class="table" style="height:650px; overflow: auto;">';


// Display the records in a table format
echo '<table class="table table-bordered border-success" border="3">
<thead style="position: sticky; top: 0; z-index: 1;">        
    <tr class="table-success" style="font-size:14px;">                 
        <th scope="col">Emp Name</th>
        <th scope="col">Program_name</th>
        <th scope="col">Nature of Training</th>
        <th scope="col">Year</th>
        <th scope="col">Tentative Date</th>
        <th scope="col">Time</th>
        <th scope="col">Status</th>
    </tr>
</thead>
<tbody>';

while ($row = sqlsrv_fetch_array($stmtRecords, SQLSRV_FETCH_ASSOC)) {
    echo '<tr class="table-light" style="font-size:14px;">';
    
    echo '<td>' . $row['name'] . '</td>';
    echo '<td>' . $row['Program_name'] . '</td>';
    echo '<td>' . $row['nature_training'] . '</td>';
    echo '<td>' . $row['year'] . '</td>';
    echo '<td>' . $row['tentative_date'] . '</td>';
    echo '<td>';
    if ($row['aprroved_time'] !== null) {
        echo '<span style="color: blue;">' . $row['aprroved_time']->format('Y-m-d') . '</span> <span style="color: red;">' . $row['aprroved_time']->format('H:i:s') . '</span>';
    } else {
        echo 'NULL';
    }
    echo '</td>';

    $status = '';
    switch ($row['flag']) {
        
        case 1:
            $status = '<span style="color:red">Reject by Reporting Officer';
            break;

        case 2:
            $status = '<span style="color:red">Pending at HOD';
            break;

        case 3:
                $status = '<span style="color:red">Rejected by HOD';
                break;   
     
        case 4:
            $status = '<span style="color:Green">Training Approved from HOD';
            break;
      
        default:
            $status = 'Unknown';
    }
    
    echo "<td>$status</td>"; 
    echo '</tr>';
}

echo '</tbody></table>';
echo '</div>';

// Close the connection
sqlsrv_close($conn);
?>

</body>
<?php include '../footer.php';?>