<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp=$_SESSION["emp_num"];

    // Pad the emp_num with leading zeros if it's 6 digits
    if (strlen($sessionemp) == 6) {
        $sessionemp1 = "00" . $sessionemp;
    }
    //echo $_SESSION["emp_num"];
    //echo '   ' . $sessionemp1;

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script> -->
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>   
    <link rel='stylesheet' href='allusers.css'> 
         

    <title>Training Nomination</title>
    <style> 
    
   


</style>
</head>
<?php include 'header.php';
//echo "Exact HOD Empno: " . $exactHodEmpno;?>
    <h6><i class='fa fa-home'></i>&nbsp;<u><i>Home-> Training Nominations</i></u></h6>
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
    ?>
    
    <?php  
  
   //sqlsrv_close($conn); 
?>

 <!------------------------------------------------------------------------------------------------------------------------------------>   
<body>
<div class="container-fluid">
<div style="display: flex;align-content: space-around;flex-direction: row-reverse;justify-content: center;align-items: center; ">
<!-- <button id="openModal" class="btn btn-primary btn-sm" style="margin-left: 15px;"><i class="fa fa-file-pdf-o" style="color:red" aria-hidden="true"></i> View Program Details</button> -->
    <form method="POST">
        <label for="year">Select a year:</label>
        <select name="year" id="year">
            <option value="" disabled selected>Select year</option>
            <?php
            // Establishes the connection
            $serverName = "192.168.100.240";
            $connectionOptions = array(
                "Database" => "complaint",
                "UID" => "sa",
                "PWD" => "Intranet@123"
            );

            $conn = sqlsrv_connect($serverName, $connectionOptions);

            if (!$conn) {
                die(print_r(sqlsrv_errors(), true));
            }

            // SQL query to fetch distinct years
            $distinctYearsQuery = "SELECT DISTINCT year FROM [Complaint].[dbo].[training_mast]";
            $yearsResult = sqlsrv_query($conn, $distinctYearsQuery);

            if ($yearsResult) {
                // Loop through distinct years and generate options
                while ($yearRow = sqlsrv_fetch_array($yearsResult, SQLSRV_FETCH_ASSOC)) {
                    $yearValue = $yearRow['year'];
                    $selectedAttr = (isset($_POST['year']) && $_POST['year'] == $yearValue) ? 'selected' : '';
                    echo "<option value=\"$yearValue\" $selectedAttr>$yearValue</option>";
                }
            }

            $sqlCount = "SELECT COUNT(empno) AS total_count FROM [Complaint].[dbo].[request] WHERE empno = ? and flag not in ('1','3','88','000')";
            $paramsCount = array($employeeNumber);
            $stmtCount = sqlsrv_query($conn, $sqlCount, $paramsCount);

            if ($stmtCount === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            $totalCountRow = sqlsrv_fetch_array($stmtCount, SQLSRV_FETCH_ASSOC);
            $totalCount = $totalCountRow['total_count'];
            $remainingRequests = 8 - $totalCount; // Assuming the limit is 8
            echo  $remainingRequests;

            // Check if the count exceeds the limit
            // if ($remainingRequests >= 8) {
            //     echo '<script>alert("You have reached the maximum limit of training requests!");</script>';
            // }

            sqlsrv_free_stmt($stmtCount);

            // Close the SQL Server connection
            sqlsrv_close($conn);


            ?>
        </select>&nbsp;&nbsp;
        <button type="submit" class="btn btn-info">Show Programs</button>
    </form>&nbsp;&nbsp;&nbsp;

    <!-- <div class="search-container">
        <input type="text" id="search_param" class="form-control" name="search" placeholder="Search Program Name"> -->
        <!-- <div id="liveSearchResults"></div> -->
        
    <!-- </div> -->
        
    </div>


    <!-----------------------------------modalup program files-------------------------------------------------------->
    <!-- <div class="program-list">
    
</div> -->

<!-- Modal Structure -->
<div id="pdfModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Available PDF Files</h5>
                
            </div>
            <div class="modal-body custom-modal-body">
                <ul id="pdfList">
                    <!-- PDF files will be dynamically populated here -->
                </ul>
            </div>
           
        </div>
    </div>
</div>

<?php
// Define the directory path
$directoryPath = 'HR/uploads/';

// Fetch all PDF files from the directory
$pdfFiles = glob($directoryPath . '*.pdf');

// Create an associative array where keys are file names
$fileLinks = array();
foreach ($pdfFiles as $file) {
    $fileName = basename($file); // Get file name only
    $fileLinks[$fileName] = $directoryPath . $fileName; // Store full path
}
?>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>


<script>
// Wait for the document to be ready
document.addEventListener('DOMContentLoaded', function () {
    const pdfList = document.getElementById('pdfList');
    const pdfFiles = <?php echo json_encode($fileArray); ?>; // Pass PHP array to JavaScript

    // Populate the PDF list in the modal
    pdfFiles.forEach(function(file) {
    const li = document.createElement('li');
    li.style.lineHeight = '2.5'; // Set line height for each list item
    li.innerHTML = `<i class="fa fa-file-pdf-o" style="color:red" aria-hidden="true"></i> 
                    <a href="HR/uploads/${file}" target="_blank">${file}</a>`;
    pdfList.appendChild(li);
});


    // Show the modal on button click
    document.getElementById('openModal').addEventListener('click', function () {
        const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
        modal.show();
    });
});
</script>
    
<!-----------------------------------modalup program files ends-------------------------------------------------------->

<?php 
// Count the total training requests for the employee


?>


    <br>
<h3>Training Nomination List&nbsp;<i style="font-size:small; background-color:yellow;">
&nbsp;&nbsp;(Note: <span style ="color:green"> <b>Green</b> </span>indicates Request from reporting Officer and <span style ="color:Blue"> <b>Blue</b></span> indicated request byself.)</i>
Your total training requests: <strong><?php echo $totalCount; ?></strong> |
Remaining requests allowed: <strong><?php echo $remainingRequests; ?></strong></h3>

<div class="scrollable-table" style="font-size:12px;">  
    
<?php

$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

$sql = "SELECT grade, location, employee_grp FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
 $params = array($sessionemp); // Secure against SQL injection
 $stmt = sqlsrv_query($conn, $sql, $params);
 
 if ($stmt === false) {
     die(print_r(sqlsrv_errors(), true));
 }
 
 // Store fetched values
 $employeeData = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
 if (!$employeeData) {
     die("No matching employee found.");
 }
 
 $grade = $employeeData['grade'];
 $location = $employeeData['location'];
 $employee_grp = $employeeData['employee_grp'];

  echo "Grade: " . $grade ;
            echo "Location: " .  $location;
            echo "Employee Group: " . $employee_grp ;
 
 sqlsrv_free_stmt($stmt);
  
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['year'])) {


    // Establish the connection
    $conn = sqlsrv_connect($serverName, $connectionOptions);

    if (!$conn) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch data from the database based on the selected year
    $selectedYear = $_POST['year'];

        // echo $_SESSION["emp_num"];
         $emp=$_SESSION["emp_num"];

    // SQL query with a LEFT JOIN to the request table
    // $sql = "SELECT t.[srl_no], t.[Program_name], t.[nature_training], t.[duration], t.[faculty], t.[training_mode], t.[day_from], t.[day_to],
    //                t.[tentative_date], t.[Internal_external], t.[year], t.[target_group], t.[venue], t.[coordinator], 
    //                t.[remarks], t.[Closed_date], r.rep_ofcr, r.ordinate_req, r.[srl_no] AS request_srl_no
    //         FROM [Complaint].[dbo].[training_mast] t
    //         LEFT JOIN [Complaint].[dbo].[request] r ON t.srl_no = r.srl_no AND r.empno = ?
    //         WHERE t.[year] = ?";

    $sql = "SELECT 
    t.srl_no,     t.Program_name,     t.nature_training,     t.duration,     t.faculty,     t.training_mode,     t.Internal_external,     t.year,     t.target_group,
     t.admin_remarks, t.faculty_Intrnl_extrnl, t.training_subtype, t.available_seats, t.open_for,
    t.venue,     t.hostel_reqd,     t.coordinator,    t.upload_date,     t.ip_address,     t.Closed_date,     t.day_from,    t.day_to,     t.flag,     com.NS01,     com.NS02, 
    com.NS03,     com.NS04,     com.E0,     com.E1,     com.E2,     com.E3,     com.E4,     com.E5,    com.E6,     com.E7,     com.E8,     com.E9,     com.Employee_grp, r.empno,
    r.rep_ofcr,    r.ordinate_req,    r.srl_no AS request_srl_no -- 🔥 This ensures request_srl_no is fetched
FROM 
    [Complaint].[dbo].[training_mast] AS t
JOIN 
    [Complaint].[dbo].[training_mast_com] AS com 
    ON t.srl_no = com.srl_no
LEFT JOIN 
    [Complaint].[dbo].[request] AS r 
    ON t.srl_no = r.srl_no -- 🔥 Ensures request_srl_no exists
    AND r.empno = $emp
WHERE 
    t.flag = 1  
    AND t.year = ?";

    $params = array($selectedYear, $location, $location, $location, $location,
    $employee_grp, // Employee group check

    $employee_grp, $grade, $grade, $grade, $grade, $grade, $grade, $grade, $grade, $grade, $grade, $grade, // Grade dynamic check
    $grade, $employee_grp  // Final checks
);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Initialize a counter for serial number
    $serialNo = 1;
    $disabledRowCount = 0;

    // Fetch and display each row
    echo "<table class='table table-bordered border-success' border='3'>";
    echo "<thead style='position: sticky; top: 0; background-color: beige; z-index: 1;'>";
    echo "<tr class='bg-primary'>";
    echo "<th scope='col'>Srl. No</th>";
    echo "<th scope='col'>Program Name</th>";
    echo "<th scope='col'>Nature of Training</th>";
    echo "<th scope='col'>Training SubType</th>";
    echo "<th scope='col'>Duration</th>";
    echo "<th scope='col'>Faculty</th>";
    echo "<th scope='col'>Faculty Type- Internal/External</th>";
    echo "<th scope='col'>Training Mode</th>";
    echo "<th scope='col'>Day From</th>";
    echo "<th scope='col'>Day To</th>";
    echo "<th scope='col'>Programme Type- Internal/External</th>";
    echo "<th scope='col'>Year</th>";
    echo "<th scope='col'>Target Group</th>";
    echo "<th scope='col'>Venue</th>";
    echo "<th scope='col'>Coordinator</th>";
    echo "<th scope='col'>Open For</th>";
    echo "<th scope='col'>Remarks by Admin</th>";
    echo "<th scope='col'>Hostel Required</th>";
    echo "<th scope='col'>Remarks from Employee</th>";
    echo "<th scope='col' class='action-column'>Actions</th>";
    echo "<th scope='col'>PDF</th>";
    echo "</tr>";
    echo "</thead>";
    
    echo "<tbody id='tbl_body'>";
$serialNo = 1;
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    
    $requestSrlNo = $row['request_srl_no'] ?? '';
    $locationMatch = false;
    $gradeMatch = false;
    $disabled = ''; 
    $rowClass = '';

     // **If row is in request table, disable it and color it red**
     if (!empty($requestSrlNo)) {
        $rowClass = 'disabled-row-request'; // Apply red background
        $disabled = 'disabled'; // Disable inputs
    }

    // **If employee has exceeded limit, disable all rows**
    if ($totalCount >= 8) {
        $disabled = 'disabled';
        $rowClass = 'disabled-row-request';
    }

    // **1️⃣ Check if row exists in request table**
    //$requestExists = !empty($row['request_srl_no']);

    // **Check Location Match**
    if (($location == 'NS01' && $row['NS01'] == 1) ||
        ($location == 'NS02' && $row['NS02'] == 1) ||
        ($location == 'NS03' && $row['NS03'] == 1) ||
        ($location == 'NS04' && $row['NS04'] == 1)) {
        $locationMatch = true;
    }

    // **Check Grade Match (Only for Employee Group 'A')**
    if ($employee_grp == 'A') {
        if (($grade == 'E0' && $row['E0'] == 1) ||
            ($grade == 'E1' && $row['E1'] == 1) ||
            ($grade == 'E2' && $row['E2'] == 1) ||
            ($grade == 'E3' && $row['E3'] == 1) ||
            ($grade == 'E4' && $row['E4'] == 1) ||
            ($grade == 'E5' && $row['E5'] == 1) ||
            ($grade == 'E6' && $row['E6'] == 1) ||
            ($grade == 'E7' && $row['E7'] == 1) ||
            ($grade == 'E8' && $row['E8'] == 1) ||
            ($grade == 'E9' && $row['E9'] == 1)) {
            $gradeMatch = true;
        }
    } elseif ($employee_grp == 'B') {
        // ✅ Employee Group B → Ignore Grade, Just Match Employee Group
        if ($row['Employee_grp'] == 'B') {
            $gradeMatch = true;
        }
    }
    elseif ($employee_grp != 'A' && $employee_grp != 'B') {
        // ✅ Employee Group 'All Employees' → Ignore grade, check only location
        $gradeMatch = true;
    }

    // **If row does NOT match the filter, disable inputs**
    $disabled = (!$locationMatch || !$gradeMatch) ? 'disabled' : '';
    $rowClass = (!$locationMatch || !$gradeMatch) ? 'disabled-row' : '';

    if (!empty($row['request_srl_no'])) {
        $rowClass = 'disabled-row-request'; // Apply red background
    }

    

    echo "<tr class='table-light $rowClass'>";
    echo "<td>{$row['srl_no']}</td>";
    echo "<td>{$row['Program_name']}</td>";
    echo "<td>{$row['nature_training']}</td>"; 
    echo "<td>{$row['training_subtype']}</td>";
    echo "<td>{$row['duration']}</td>";
    echo "<td>{$row['faculty']}</td>";
    echo "<td>{$row['faculty_Intrnl_extrnl']}</td>";
    echo "<td>{$row['training_mode']}</td>";
    echo "<td>{$row['day_from']->format('Y-m-d')}</td>";
    echo "<td>{$row['day_to']->format('Y-m-d')}</td>";
    echo "<td>{$row['Internal_external']}</td>";
    echo "<td>{$row['year']}</td>";
    echo "<td>{$row['target_group']}</td>";
    echo "<td>{$row['venue']}</td>";
    echo "<td>{$row['coordinator']}</td>";
    echo "<td>{$row['open_for']}</td>";
    echo "<td>{$row['admin_remarks']}</td>";

    // **Hostel Required dropdown (Disabled if not matching)**
    echo "<td>
            <select name='hostel_required[]' data-id='{$row['srl_no']}' $disabled required>
                
                <option value='1'>Yes</option>
                <option value='0'>No</option>
            </select>
        </td>";

    // **Remarks input (Disabled if not matching)**
    echo "<td><input type='text' name='remarks[]' data-id='{$row['srl_no']}' placeholder='Enter remarks' $disabled></td>";

    // **Checkbox (Disabled if not matching)**
    echo "<td>
            <label class='checkbox-container'>
                <input type='checkbox' name='selectedIds[]' value='{$row['srl_no']}' onchange='updateSubmitButton()' $disabled>
                <span class='checkmark'></span>
            </label>
        </td>";
        echo "<td>";
        $srl_no = $row['srl_no']; // Assuming srl_no corresponds to the file name

        // Generate expected PDF filename (assuming the naming pattern is like "12345.pdf" for srl_no=12345)
        $expectedFileName = $srl_no . ".pdf"; 

        // Check if the expected file exists in the directory
        if (isset($fileLinks[$expectedFileName])) {
            echo "<a href='" . $fileLinks[$expectedFileName] . "' target='_blank' title='View PDF'>
                    <i class='fa fa-file-pdf-o' style='font-size:20px;color:red'></i>
                </a>";
        } else {
            echo "<i class='fa fa-file-pdf-o' style='font-size:20px;color:gray' title='No PDF available'></i>";
        }
        echo "</td>";


    echo "</tr>";

    $serialNo++;
    }
    echo "</tbody>";
    echo "</table>";
    
    // Button to toggle hidden rows
    // echo "<button id='toggleRowsBtn'>Show/Hide Filtered Data</button>";
    
    
    // Close the connection
    sqlsrv_close($conn);
    
}
?>


    </div>
    <script>
document.getElementById('toggleRowsBtn').addEventListener('click', function () {
    let rows = document.querySelectorAll('.hidden-row');
    rows.forEach(row => {
        if (row.style.display === 'none') {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
});
</script>
    <script>
   function updateData() {
    var year = document.getElementById('year').value;
    var searchParam = document.getElementById('search_param').value;

    // Use AJAX to fetch data dynamically
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            // Replace the content of liveSearchResults with the live search results
            document.getElementById('tbl_body').innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "live_search.php?year=" + year + "&search=" + searchParam, true);
    xhttp.send();
}

// Trigger the updateData function on input in the search input
document.getElementById('search_param').addEventListener('input', updateData);
</script>




    <br>
    <form action='request.php' method='post'  id='dataForm'>
    <input type='hidden' name='selectedData' id='selectedDataInput'>
        <!-- <button type='submit' name='request' class='btn btn-success'>Submit Request</button> -->
        <input type='hidden' name='exactHodEmpno' id='exactHodEmpnoInput' value='<?php echo $exactHodEmpno; ?>'>
        <button type='submit' name='request' onclick='logSelectedData()' id='submitButton' class='btn btn-success' disabled>Submit Request</button>

    </form>
</div>
<script>
 function updateSubmitButton() {
        var checkboxes = document.querySelectorAll('input[name="selectedIds[]"]:checked');
        var submitButton = document.getElementById('submitButton');

        // Enable the button if any checkbox is checked, otherwise disable it
        submitButton.disabled = checkboxes.length === 0;
    }

    // Attach the updateSubmitButton function to the change event of checkboxes
    var checkboxes = document.querySelectorAll('input[name="selectedIds[]"]');
    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', updateSubmitButton);
    });

    function logSelectedData() {
        // Array to store data of each selected checkbox
        var selectedData = [];

        // Get all checkboxes that are checked
        var checkboxes = document.querySelectorAll('input[name="selectedIds[]"]:checked');

        // Loop through each checkbox and extract data
        checkboxes.forEach(function (checkbox) {
            var srl_no = checkbox.value;
            var remarksInput = document.querySelector('input[name="remarks[]"][data-id="' + srl_no + '"]');
            var remarks = remarksInput ? remarksInput.value : '';  // Get remarks value or set it to an empty string if not found

            var hostelSelect = document.querySelector('select[name="hostel_required[]"][data-id="' + srl_no + '"]');
            var hostelRequired = hostelSelect ? hostelSelect.value : '';  // Get hostel_required value or set it to an empty string if not found

            var data = {
                srl_no: srl_no,
                remarks: remarks,
                hostel_required: hostelRequired
            };

            selectedData.push(data);
        });

        // Log the selected data to the console
        console.log('Selected Data:', selectedData);
        console.log('Debug - Selected Data (JS):', selectedData);

        // Set the selected data as a value of a hidden input field
    document.getElementById('selectedDataInput').value = JSON.stringify(selectedData);

// Submit the form
document.getElementById('dataForm').submit();
    }
</script>

<!-------------------------------------------Pending and approve and Reject request------------------------------------------------------------>

</div>
    


</body>
<?php include '../footer.php';?>
</html>
