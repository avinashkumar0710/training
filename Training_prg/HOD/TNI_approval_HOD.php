<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:HODlogin.php");
}

$sessionemp = $_SESSION["emp_num"];
// Ensure $hodempno has 8 digits (prepend 00 if it starts with 0) or 7 digits
$sessionemp1 = str_pad($sessionemp, 8, "0", STR_PAD_LEFT);
echo 'employeeno' . $sessionemp1;

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
    <title>TNI Subordinate</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
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
        background-color: #e8eef3;
    }

    .row {
        margin: 2px;
        height: 900px;
    }

 
    </style>
     <script>
function showEmployeeDetails() {
    var employeeSelect = document.getElementById("employeeSelect");
    var selectedOption = employeeSelect.options[employeeSelect.selectedIndex];

    if (selectedOption.value) {
        document.getElementById("selectedEmployeeInput").value = selectedOption.value; // ✅ Store empno
        document.getElementById("selectedEmployeeName").innerText = selectedOption.getAttribute("data-name") || 'None';
        document.getElementById("hiddenGrade").value = selectedOption.getAttribute("data-grade") || '';
        document.getElementById("hiddenEmployeeGrp").value = selectedOption.getAttribute("data-employeegrp") || '';
        document.getElementById("hiddenLocation").value = selectedOption.getAttribute("data-location") || '';
    } else {
        resetFields();
    }
}

function resetFields() {
    document.getElementById("hiddenGrade").value = "";
    document.getElementById("hiddenEmployeeGrp").value = "";
    document.getElementById("hiddenLocation").value = "";
}

// Run this on page load to keep selected employee details after submit
window.onload = function() {
    showEmployeeDetails();
};
</script>



</head>

<?php include '../header_HR.php';?>

<body>
    <!---------------------------------------------------------------------------------------------------------------------------------------------------->
    <div class="full-width">
        <div class="row">
            <div class="col-md-7">
            <form method="POST" id="employeeForm">
            <label for="employeeSelect">Select Employee:</label>

         <select name="employeeSelect" id="employeeSelect" onchange="showEmployeeDetails()">
            <option value="" disabled selected>-- Select Employee --</option>

            <?php
            // Fetch Employees Under HOD
            $query = "SELECT empno, name, grade, employee_grp, location FROM [Complaint].[dbo].[emp_mas_sap] WHERE hod_ro = ? AND status = 'A' UNION
            SELECT empno, name, grade, employee_grp, location FROM [Complaint].[dbo].[emp_mas_sap] WHERE rep_ofcr = ? AND status = 'A'";
            $params = array($sessionemp1, $sessionemp1);

            echo '22' .$sessionemp1;
            $stmt = sqlsrv_query($conn, $query, $params);
    
            // Check if query executed successfully
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
    
            // Loop through employees
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                echo '<option value="' . htmlspecialchars($row['empno']) . '" 
                data-name="' . htmlspecialchars($row['name']) . '" 
                            data-grade="' . htmlspecialchars($row['grade']) . '" 
                            data-employeegrp="' . htmlspecialchars($row['employee_grp']) . '" 
                            data-location="' . htmlspecialchars($row['location']) . '">
                            ' . htmlspecialchars($row['name']) . '
                      </option>';
            }  

            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
            ?>
        </select>

        <input type="hidden" name="name" id="hiddenName">
        <input type="hidden" name="grade" id="hiddenGrade">
        <input type="hidden" name="employee_grp" id="hiddenEmployeeGrp">
        <input type="hidden" name="location" id="hiddenLocation">

         <!-- <div id="employeeDetails" style="margin-top: 10px; font-weight: bold;"></div> -->
         <button type="submit" class="btn btn-info">Submit</button>
    </form>

    <p id="selectedEmployeeContainer" style="display:none;">
    <span id="selectedEmployeeName">
        <?php echo isset($_POST['employee']) ? htmlspecialchars($_POST['employee']) : 'None'; ?>
    </span>
    </p>

            <?php
        $serverName = "192.168.100.240";
        $connectionOptions = array(
            "Database" => "complaint",
            "UID" => "sa",
            "PWD" => "Intranet@123"
        );

        $conn = sqlsrv_connect($serverName, $connectionOptions);

 
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Establish the connection
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if (!$conn) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch data from the database based on the selected year
    $selectedEmpNo = $_POST['employeeSelect'];
    $grade = $_POST['grade'];
    $employee_grp = $_POST['employee_grp'];
    $location = $_POST['location'];
    
    echo "Selected Employee: $selectedEmpNo, Grade: $grade, Employee Group: $employee_grp, Location: $location";

        // echo $_SESSION["emp_num"];
         $emp=$_SESSION["emp_num"];

         $sqlCount = "SELECT COUNT(empno) AS total_count FROM [Complaint].[dbo].[request] WHERE empno = '$selectedEmpNo' and flag not in ('1','3','88','000')";
    $paramsCount = array($selectedEmpNo);
    $stmtCount = sqlsrv_query($conn, $sqlCount, $paramsCount);

    if ($stmtCount === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $totalCountRow = sqlsrv_fetch_array($stmtCount, SQLSRV_FETCH_ASSOC);
    $totalCount = $totalCountRow['total_count'];
    $remainingRequests = 8 - $totalCount; // Assuming the limit is 8

    // Check if the count exceeds the limit
    if ($totalCount >= 8) {
        echo '<script>alert("You have reached the maximum limit of training requests!");</script>';
    }

    //echo '1'.$totalCount;
    echo 'remaining allowed: '.$remainingRequests;

   

    $sql = "SELECT 
    t.srl_no,     t.Program_name,     t.nature_training,     t.duration,     t.faculty,     t.training_mode,     t.tentative_date,     t.Internal_external,     t.year,     t.target_group, 
    t.venue,     t.hostel_reqd,     t.coordinator,     t.remarks,     t.upload_date,     t.ip_address,     t.Closed_date,     t.day_from,    t.day_to,     t.flag,     com.NS01,     com.NS02, 
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
   ";

    $params = array($location, $location, $location, $location,
     // Employee group check
     $employee_grp,
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
    echo '<div style="height: 600px; overflow: auto;">';
    echo "<table class='table table-bordered border-success' border='3'>";
    echo "<thead style='position: sticky; top: 0; background-color: beige; z-index: 1; '>";
    echo "<tr class='bg-primary'>";
    echo "<th scope='col'>Srl. No</th>";
    echo "<th scope='col'>Program Name</th>";
    echo "<th scope='col'>Nature of Training</th>";
   
    echo "<th scope='col'>Remarks by Admin</th>";
    echo "<th scope='col'>Hostel Required</th>";
    echo "<th scope='col'>Remarks from Employee</th>";
    echo "<th scope='col' class='action-column'>Actions</th>";
  
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
    //  if (!empty($requestSrlNo)) {
    //     $rowClass = 'disabled-row-request'; // Apply red background
    //     $disabled = 'disabled'; // Disable inputs
    // }

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
   
    echo "<td>{$row['remarks']}</td>";

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

    echo "</tr>";
    }
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
     
    // Close the connection
    sqlsrv_close($conn);
    
}
?>
            <form action='request.php' method='post'  id='dataForm'>
            <input type='hidden' name='selectedData' id='selectedDataInput'>
            <input type="hidden" name="selectedEmployee" id="selectedEmployeeInput">
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

            


            <!-------------------------------------Approved by You------------------------------------------------------>
            <div class="col-md-5"
                style="box-shadow: rgba(0, 0, 0, 0.25) 0px 54px 55px, rgba(0, 0, 0, 0.12) 0px -12px 30px, rgba(0, 0, 0, 0.12) 0px 4px 6px, rgba(0, 0, 0, 0.17) 0px 12px 13px, rgba(0, 0, 0, 0.09) 0px -3px 5px;">
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

// Fetch the list of programs based on `ordinate_req`
$fetch_program_name = "SELECT DISTINCT program_name FROM [Complaint].[dbo].[request] WHERE ordinate_req = ?";

$params = array($sessionemp);
$stmtprogram_name = sqlsrv_query($conn, $fetch_program_name, $params);

if ($stmtprogram_name === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Extract program names from the result set
$programs = array();
while ($rowProgram = sqlsrv_fetch_array($stmtprogram_name, SQLSRV_FETCH_ASSOC)) {
    $programs[] = $rowProgram['program_name'];
}

// Free the statement resources
sqlsrv_free_stmt($stmtprogram_name);

// Display the form for selecting a program
$selectedProgram = isset($_POST['programName']) ? $_POST['programName'] : '';

echo '<form method="post">';
echo '<label for="programName">Select Program:</label>&nbsp';
echo '<select name="programName" id="programName">';
echo '<option value="" disabled selected>Select Program</option>'; // Default option

foreach ($programs as $program) {
    $selected = ($program == $selectedProgram) ? 'selected' : '';
    echo "<option value=\"$program\" $selected>$program</option>";
}
echo '</select>';
echo '&nbsp<button type="submit" class="btn btn-primary">Show</button><br>';
echo '<i style="font-size:small; background-color:yellow;">&nbsp;&nbsp;(Note: Choose Program Name first to display the Requested Employee Name.)</i>';
echo '</form>';

// Check if a program is selected
if (!empty($selectedProgram)) {
    echo '<h3>Subordinate Requested By You</h3>';
    echo '<div class="table" style="height:700px; overflow: auto;">';

    // SQL query to fetch records based on the selected program name
    $sqlRecords = "SELECT r.srl_no, r.empno, r.Program_name, r.year, a.name, r.flag, r.ordinate_datetime
                   FROM [Complaint].[dbo].[request] r
                   JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
                   WHERE ordinate_req = ? AND r.program_name = ?";

    // Add both `sessionemp` and selected program as parameters
    $params = array($sessionemp, $selectedProgram);
    $stmtRecords = sqlsrv_query($conn, $sqlRecords, $params);

    if ($stmtRecords === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Display the fetched records
    echo '<table class="table table-bordered border-success">';
    echo '<thead style="position: sticky; top: 0; background-color: beige;">';
    echo '<tr>';
    echo '<th>Srl. No</th>';
    echo '<th>Emp Name</th>';
    echo '<th>Program Name</th>';
    echo '<th>Year</th>';
    echo '<th>Status</th>';
    echo '<th>Date Time</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $serialNo = 1;
    while ($row = sqlsrv_fetch_array($stmtRecords, SQLSRV_FETCH_ASSOC)) {
        echo '<tr>';
        echo '<td>' . $serialNo . '</td>';
        echo '<td>' . $row['name'] . '</td>';
        echo '<td>' . $row['Program_name'] . '</td>';
        $status = '';
        switch ($row['flag']) {
            case 0:
                $status = '<span style="color:blue">Pending at Reporting Officer</span>';
                break;
            case 1:
                $status = '<span style="color:red">Rejected by Reporting Officer</span>';
                break;
            case 2:
                $status = '<span style="color:blue">Pending at HOD</span>';
                break;
            case 3:
                $status = '<span style="color:red">Rejected by HOD</span>';
                break;
            case 4:
                $status = '<span style="color:green">Training Approved by HOD</span>';
                break;
            case 5:
                $status = '<span style="color:blue">Pending from BUH</span>';
                break;
            case 6:
                $status = '<span style="color:green">Approved by BUH</span>';
                break;
            case 7:
                $status = '<span style="color:green">Overall Approved</span>';
                break;
            case 88:
                $status = '<span style="color:red">Rejected by HR</span>';
                break;
            case 99:
                $status = '<span style="color:green">Approved by HR</span>';
                break;
            default:
                $status = 'Unknown';
        }
        echo '<td>' . $row['year'] . '</td>';
        echo "<td>$status</td>";
        echo '<td>' . ($row['ordinate_datetime'] ? $row['ordinate_datetime']->format('Y-m-d H:i:s') : 'NULL') . '</td>';
        echo '</tr>';
        $serialNo++;
    }

    echo '</tbody>';
    echo '</table>';
    echo '</div>';

    sqlsrv_free_stmt($stmtRecords);
}

// Close the connection
sqlsrv_close($conn);
?>


            </div>
        </div>


</body>

</html>

<?php include '../footer.php';?>