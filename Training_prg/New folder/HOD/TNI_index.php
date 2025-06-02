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
    <link rel="icon" href="images/analysis.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 

    <style>
    body {
        font-weight: 600;
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        margin: 0px;
    }

    .row{margin : 2px;}
   
    </style>
</head>


<?php           
            // Check if the user is authenticated
            if (!isset($_SESSION["emp_num"])) {
                header("location: login.php");
                exit;
            }
           
            $name = "SELECT emp_name, access, dept_code FROM EA_webuser_tstpp WHERE emp_num = ?";
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

            //echo 'empl ' .$sessionemp;
            //echo ' dept ' .$deptcode;

            $hod = 0;
            $sqlhod = "SELECT * FROM [Complaint].[dbo].[emp_mas_sap] WHERE hod_ro = $sessionemp";
            $stmt = sqlsrv_query($conn, $sqlhod);
            
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            // else {
            //     $hod = 1;
            //  }
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

                
                // $hod_ro = $row['hod_ro'];
                // echo 'hod:' .$hod_ro;
                // Now you can use $hod_ro as needed

                $hod = 1;
            }
            $hremp = 0;
            $accessValue = null;
            $sql = "SELECT empno, access FROM [Complaint].[dbo].[Training_HR_User] where empno= $sessionemp";
            $stmt = sqlsrv_query($conn, $sql);

            
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                // Store the access value in a variable
                 $accessValue = $row['access'];            
               //echo 'Empno: ' . $row['empno'] . ', Access: ' . $accessValue . '<br>';

                $hremp = 1;
            }     
            
            $compare = "SELECT [empno],[name],[rep_ofcr],[hod_ro], [design], [grade] FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = $sessionemp";    //for user name show in header
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
                //$deptcode =$row['dept_code'];
                // echo 'rep_ofcr' .$rep_ofcr;
                //echo 'grade' .$grade;
                //echo 'design' .$design;
                
            }    
    ?>

<body style="background-color: #5d87192e">
    <div class='card text-center'>
        <div class='card-header'>
            <b><i><SPAN style='background-color:yellow'> <?php echo $username; ?>
                    </SPAN></i></b>&nbsp;&nbsp;
            <a href='../signout.php'><input type='submit' class='btn btn-success btn-sm' value='LOGOUT'></a>&nbsp;
        </div>
    </div>
    <ul class='nav justify-content-center' style='background-color: #34495E;'>
    <?php if ($design != 'GM & BUH                      '): ?>
        <li class='nav-item'>
            <a class='nav-link' style='color: white;' href='../Admin/home.php'>Home&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>
        <?php endif; ?>


        <?php if ($design != 'GM & BUH                      '): ?>
            <li class='nav-item dropdown'>
            <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown'
                aria-haspopup='true' aria-expanded='false' style='color: white;'>
                Training
        </a>
        <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
            <a class='nav-link' style='color: black;' href='../all_users.php'>Training Nominations</a><hr>
            <a class='nav-link' style='color: black;' href='../Training_TNI.php'>Training Identification</a>
        </div>
        </li>
        <?php endif; ?>

        <!-- <?php if ($hod == 1): ?>
        <li class='nav-item'>
            <a class='nav-link' style='color: white;' href='../HOD/index.php'>HOD&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>
        <?php endif; ?> -->

        <?php if ($rep_ofcr == $hod_ro && $design != 'GM & BUH                      '): ?>
            <li class='nav-item dropdown'>
            <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown'
                aria-haspopup='true' aria-expanded='false' style='color: white;'>
                HOD
            </a>
            <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
                <a class='dropdown-item'   href='TNI_approval.php'>Training Nomination for Subordinate</a>
               
                <a class='dropdown-item' href='index.php'>Training Approval</a><hr>
                <a class='dropdown-item' href='TNI_index.php'>TNI Approval</a> 
                
            </div>
        </li>
        <?php endif; ?>

        <?php if ($hremp == 1): ?>
        <!-- <li class='nav-item'>
        <a class='nav-link' style='color: white;' href='../Admin/upload.php'>HR Functions&nbsp;&nbsp;&nbsp;&nbsp;</a>
    </li> -->
        <li class='nav-item dropdown'>
            <a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-bs-toggle='dropdown'
                aria-haspopup='true' aria-expanded='false' style='color: white;'>
                HR
            </a>
            <div class='dropdown-menu' aria-labelledby='navbarDropdown'>
                <a class='dropdown-item' href='../admin/upload.php'>HR Functions</a>
                <a class='dropdown-item' href='../admin/excel_upload.php'>HR Upload</a>
                <a class='dropdown-item' href='../admin/buh_nomin.php'>Send Nominations for BUH Approval</a>
                <a class='dropdown-item' href='../admin/mail_training.php'>Mail Training Order</a>
                <a class='dropdown-item' href='../admin/report.php'>Report</a>  
               
                <a class='dropdown-item' href='../admin/Administrator.php'>Administrator</a><hr>
        <a class='dropdown-item' href='../admin/TNI_excel_upload.php'>TNI Excel Upload</a>
        <a class='dropdown-item' href='../admin/hr_functions_TNI.php'>HR Function TNI</a> 
            </div>
        </li>
       
        <?php endif; ?>

        <?php if ($design == 'GM & BUH                      '): ?>
        <li class='nav-item'>
            <a class='nav-link' style='color: white;' href='../admin/buh_approval.php'>BUH Approval&nbsp;&nbsp;&nbsp;&nbsp;</a>
        </li>
        <?php endif; ?>
    </ul>

    <!---------------------------------------------------------------------------------------------------------------------------------------------------->
    <body style="background-color: lightblue">
    <div class="full-width">         
    <div class="row">
    <div class="col-md-6">
            <?php
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
    

    $sql = "SELECT hod_ro FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
    $params = array($sessionemp);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch the result
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    // Assuming that 'hod_ro' is the column name
    $hod_ro = $row['hod_ro'];
    //echo 'hod_ro:' .$hod_ro;

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
          // Use $rep_ofcr as needed
          echo '$rep_ofcr :' .$rep_ofcr;
      }
        //echo 'access' .$accessValue;
    //echo 'session' .$sessionemp;
    echo '<h6><i class="fa fa-home"></i>&nbsp;<i><u>HOD->TNI Approval</u></i></h6>';
    echo '<center><h5>Pending TNI List</h5></center>';
    echo '<div class="table" style="height:630px; overflow-x: auto; font: size 10px;">';
    echo '<form action="approve_TNI.php" method="post" id="approveForm">

    <table class="table table-bordered" border="3" border="1" >
    <thead style="position: sticky; top: 0; background-color: beige;">
            <tr style="font-size:14px;">           
                    
                <th scope="col">Emp Name</th>
                <th scope="col">Program_name</th>
                <th scope="col">Faculty</th>
                <th scope="col">Nature of Training</th>
                <th scope="col">Year</th>
                <th scope="col">Duration</th>            
                <th scope="col">Tentative_Date</th>     
                <th scope="col">Target_group</th>
                <th scope="col">Remarks</th>
                <th scope="col">Approve / Reject</th>              
            </tr>
        </thead>
        <tbody>';


        // $sql = "SELECT r.Id, r.srl_no, e.name, e.empno, r.Program_name, r.Faculty, r.nature_training, r.year,  r.remarks, r.duration, r.tentative_date, r.target_group, r.rep_ofcr, e.hod_ro
        // FROM [Complaint].[dbo].[request] r
        // JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
        // WHERE  flag='0' and e.empno !=$sessionemp and   e.hod_ro = $hod_ro";   //changes on 2024-04-02

        $sql = "";
if ($accessValue == 1) {
    // Access value is 1, so execute the modified query
    $sql = "SELECT r.Id, r.srl_no, e.name, e.empno, r.Program_name, r.Faculty, r.nature_training, r.year,  r.remarks, r.duration, r.tentative_date, r.target_group, r.rep_ofcr, e.hod_ro
    FROM [Complaint].[dbo].[request_TNI] r
    JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
    WHERE flag = '0'";
} else {
    // Access value is not 1, so execute the original query
    $sql = "SELECT r.Id, r.srl_no, e.name, e.empno, r.Program_name, r.Faculty, r.nature_training, r.year,  r.remarks, r.duration, r.tentative_date, r.target_group, r.rep_ofcr, e.hod_ro
            FROM [Complaint].[dbo].[request_TNI] r
            JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
            WHERE flag = '0' AND e.empno != $sessionemp ";

}

        $stmt = sqlsrv_query($conn, $sql);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        if (sqlsrv_has_rows($stmt)) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                // Output each row as a table row
                echo '<tr class="table-light" style="font-size:14px;">';
                echo '<td>' . $row['name'] . '</td>';
                echo '<td>' . $row['Program_name'] . '</td>';
                echo "<td>{$row['Faculty']}</td>";
                echo "<td>{$row['nature_training']}</td>";
                echo "<td>{$row['year']}</td>";
                echo "<td>{$row['duration']}</td>";
                echo "<td>{$row['tentative_date']}</td>";
                echo "<td>{$row['target_group']}</td>";
                echo "<td>{$row['remarks']}</td>";

                echo '<td>
                <select class="approval-dropdown" name="approvalStatus[' . $row['Id'] . ']">
                    <option></option>
                    <option value="4">Approve</option>
                    <option value="1">Reject</option>
                </select>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
            // No pending requests found
            echo '<tr><td colspan="10">No pending requests found</td></tr>';
        }
        

// Close the table structure
echo '</tbody></table>';
echo '</div>';
echo '<button type="submit" id="approveButton" name="approve"  class="btn btn-success">Approve Selected</button>';
echo '</form>';

// Close the connection
sqlsrv_close($conn);
?>
        </div>
        <script>
        // Function to handle dropdown change event
        function handleDropdownChange(event) {
            // Retrieve the selected value from the dropdown
            const selectedValue = event.target.value;

            // Retrieve the row ID from the data attribute
            const rowId = event.target.dataset.rowId;

            // Retrieve the empno from the data attribute
            const empno = event.target.dataset.empno;

            // Log the selected value, row ID, and empno to the console
            console.log("Selected Value:", selectedValue);
            //console.log("Row ID:", rowId);
            //console.log("Empno:", empno);
        }

        // Get all dropdown elements with the class 'approval-dropdown'
        const dropdowns = document.querySelectorAll('.approval-dropdown');

        // Add event listener to each dropdown
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('change', handleDropdownChange);
        });
        </script>
        <script>
    // Function to handle form submission
    function validateForm(event) {
        // Get all dropdown elements
        const dropdowns = document.querySelectorAll('.approval-dropdown');
        
        // Variable to track if at least one dropdown is selected
        let isSelectionMade = false;
        
        // Check each dropdown
        dropdowns.forEach(dropdown => {
            // If a dropdown has a value selected
            if (dropdown.value !== '') {
                isSelectionMade = true;
            }
        });

        // If no dropdown has a value selected
        if (!isSelectionMade) {
            // Prevent form submission
            event.preventDefault();
            
            // Show error message or alert
            alert("Please select at least one 'Approve' or 'Reject' status.");
        }
    }

    // Get the approve form
    const approveForm = document.getElementById('approveForm');

    // Add event listener for form submission
    approveForm.addEventListener('submit', validateForm);
</script>


        <!-------------------------------------Approved by You------------------------------------------------------>
        <div class="col-md-6" style="box-shadow: rgba(0, 0, 0, 0.25) 0px 54px 55px, rgba(0, 0, 0, 0.12) 0px -12px 30px, 
        rgba(0, 0, 0, 0.12) 0px 4px 6px, rgba(0, 0, 0, 0.17) 0px 12px 13px, rgba(0, 0, 0, 0.09) 0px -3px 5px; height: 810px;">
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
if ($accessValue == 1) {
$sqlYear = "SELECT DISTINCT r.year
        FROM [Complaint].[dbo].[request_TNI] r 
        JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
        WHERE flag in ('4')";
}
else{
    $sqlYear = "SELECT DISTINCT r.year
    FROM [Complaint].[dbo].[request_TNI] r 
    JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
    WHERE flag in ('4')";
}

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

$sqlRecords = "";
if ($accessValue == 1) {
    // Access value is 1, so execute the modified query
    $sqlRecords = "SELECT r.srl_no, r.empno, r.Program_name, r.year, a.name, r.aprroved_time, r.flag, r.tentative_date, a.hod_ro 
    FROM [Complaint].[dbo].[request_TNI] r
    JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
    WHERE flag in ('4')  AND r.year = '$selectedYear'";
} else {
    // Access value is not 1, so execute the original query
    $sqlRecords = "SELECT r.srl_no, r.empno, r.Program_name, r.year, a.name, r.aprroved_time, r.flag, r.tentative_date, a.hod_ro 
    FROM [Complaint].[dbo].[request_TNI] r
    JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
    WHERE flag in ('4') AND r.year = '$selectedYear'";

}

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
echo '<div class="table" style="height:300px; overflow: auto;">';


// Display the records in a table format
echo '<table class="table table-bordered" border="3" border="1">
<thead style="position: sticky; top: 0; background-color: beige;">        
    <tr style="font-size:14px;">                 
        <th scope="col">Emp Name</th>
        <th scope="col">Program_name</th>
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
    echo '<td>' . $row['year'] . '</td>';
    echo '<td>' . $row['tentative_date'] . '</td>';
    echo '<td><span style="color: blue;">' . $row['aprroved_time']->format('Y-m-d') . '</span> <span style="color: red;">' . $row['aprroved_time']->format('H:i:s') . '</span></td>';
    $status = '';
    switch ($row['flag']) {
        
        case 1:
            $status = '<span style="color:red">Reject';
            break;

        case 2:
            $status = '<span style="color:red">Pending at HOD';
            break;

        case 3:
                $status = '<span style="color:red">Rejected by HOD';
                break;   
     
        case 4:
            $status = '<span style="color:Green">Approve';
            break;

        case 5:
            $status = '<span style="color:Green">Pending from BUH';
            break;
        
       case 6:
            $status = '<span style="color:red">Approved from BUH';
            break;
         
        case 7:
            $status = '<span style="color:Green">Overall Approved';
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



        <hr>
        <center><h5>Approved TNI List</h5></center>
        <div class="col-md-12" style="box-shadow: rgba(0, 0, 0, 0.25) 0px 54px 55px, rgba(0, 0, 0, 0.12) 0px -12px 30px, 
        rgba(0, 0, 0, 0.12) 0px 4px 6px, rgba(0, 0, 0, 0.17) 0px 12px 13px, rgba(0, 0, 0, 0.09) 0px -3px 5px; ">
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
    $serialNo = 1;
    $sqlYear = "SELECT r.empno, r.Program_name, r.nature_training, r.year, r.remarks, r.duration, r.tentative_date, a.name, r.hostel_book
                FROM [Complaint].[dbo].[request_TNI] r 
                JOIN [Complaint].[dbo].[emp_mas_sap] a on r.empno = a.empno  
                WHERE flag = '4'";
    $result = sqlsrv_query($conn, $sqlYear);

    if ($result === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    if (sqlsrv_has_rows($result)) {
        echo "<div class='table' style='overflow: auto;height:340px'>";
        echo "<table class='table table-bordered border-success' border='3' border='1'>";
        echo "<thead style='position: sticky; top: 0; background-color: beige;'>
                    <tr>           
                        <th scope='col'>Serial No</th>
                        <th scope='col'>Empno</th>
                        <th scope='col'>Program_name</th>
                        <th scope='col'>Year</th>
                        <th scope='col'>Duration</th>
                        <th scope='col'>Tentative_Date</th>
                        <th scope='col'>Remarks</th>
                    </tr>
                </thead>";

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            echo "<tr>
                    <td>". $serialNo++ ."</td>
                    <td>". $row['name'] ."</td>
                    <td>". $row['Program_name'] ."</td>
                    <td>". $row['year'] ."</td>
                    <td>". $row['duration'] ."</td>
                    <td>". $row['tentative_date'] ."</td>                           
                    <td>". $row['remarks'] ."</td>
                  </tr>";
        }
        echo "</table>"; // Add the closing table tag here
        echo "</div>"; // Close the table div
        sqlsrv_free_stmt($result);
    }
    sqlsrv_close($conn);
?>  


        </div>
    </div>
    </div>
    </div>

</body>
</html>

<?php include '../footer.php';?>