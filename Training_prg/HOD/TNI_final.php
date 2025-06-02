<!-- Section 1: PHP Session and Database Connection -->

<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION["emp_num"])) {
    header("location: HODlogin.php"); // Redirect to login page if not logged in
    exit();
}

// Pad employee number to 8 digits
$sessionemp = $_SESSION["emp_num"];
$sessionemp1 = str_pad($sessionemp, 8, "0", STR_PAD_LEFT);

//echo $sessionemp;

// Database connection
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);

$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true)); // Handle connection errors
}
$selectedEmpNo = '';
?>

<!-- Section 2: HTML Head and CSS -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TNI Subordinate HOD</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>
    <style>
        body {
            font-weight: 600;
            font-family: "Nunito Sans", sans-serif;
            margin: 0;
            background-color: #e8eef3;
        }
        .row {
            margin: 2px;
            height: 900px;
        }
        .disabled-row {
            background-color: #f0f0f0;
            color: #a0a0a0;
        }
       .disabled-row-request1 {
            background-color: red;
            color: red;
        }
        #selectedDataDisplay {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }      

        .disabled-row-request td {
            color: #000; /* Ensure text is readable */
        }

        /* Optional: Add a hover effect to highlight the row */
        .disabled-row-request:hover {
            background-color: #ff9999; /* Slightly darker red on hover */
        }

        .col-md-5{
            box-shadow: rgba(0, 0, 0, 0.25) 0px 54px 55px, rgba(0, 0, 0, 0.12) 0px -12px 30px;
            width:50%;
               }
        
        </style>
</head>

<!-- Section 3: JavaScript Functions -->

<script>
// Function to show employee details when selected
function showEmployeeDetails() {
    var employeeSelect = document.getElementById("employeeSelect");
    var selectedOption = employeeSelect.options[employeeSelect.selectedIndex];

    if (selectedOption.value) {
        // Update hidden input fields with selected employee's details
        document.getElementById("hiddenGrade").value = selectedOption.getAttribute("data-grade") || '';
        document.getElementById("hiddenEmployeeGrp").value = selectedOption.getAttribute("data-employeegrp") || '';
        document.getElementById("hiddenLocation").value = selectedOption.getAttribute("data-location") || '';

        // Display selected employee's name
        document.getElementById("selectedEmployeeName").innerText = selectedOption.getAttribute("data-name") || 'None';
    } else {
        resetFields();
    }
}

// Function to reset fields
function resetFields() {
    document.getElementById("hiddenGrade").value = "";
    document.getElementById("hiddenEmployeeGrp").value = "";
    document.getElementById("hiddenLocation").value = "";
}

// Function to update submit button state
function updateSubmitButton() {
    var checkboxes = document.querySelectorAll('input[name="selectedIds[]"]:checked');
    var submitButton = document.getElementById('submitButton');

    if (submitButton) {
        submitButton.disabled = checkboxes.length === 0; // Enable button if any checkbox is checked
    } else {
        console.error("Submit button not found!"); // Debugging: Log an error if the button is missing
    }
}

// Function to display selected checkbox data
function displaySelectedData() {
    var selectedData = []; // Array to store selected data
    var checkboxes = document.querySelectorAll('input[name="selectedIds[]"]:checked'); // Get all checked checkboxes

    // Loop through checked checkboxes
    checkboxes.forEach(function (checkbox) {
        var srl_no = checkbox.value; // Get the serial number of the selected program
        var row = checkbox.closest('tr'); // Get the closest table row

        // Fetch data from the row based on the updated table structure
        var programName = row.querySelector('td:nth-child(2)').innerText; // Program Name
        var natureTraining = row.querySelector('td:nth-child(3)').innerText; // Nature of Training
        var duration = row.querySelector('td:nth-child(4)').innerText; // Duration
        var faculty = row.querySelector('td:nth-child(5)').innerText; // Faculty
        var year = row.querySelector('td:nth-child(6)').innerText; // Year
        var tentativeDate = row.querySelector('td:nth-child(7)').innerText; // Tentative Date
        var targetGroup = row.querySelector('td:nth-child(8)').innerText; // Target Group
        var remarks = row.querySelector('td:nth-child(9)').innerText; // Remarks by Admin
        var hostelRequired = row.querySelector('select[name="hostel_required[]"]').value; // Hostel Required
        var employeeRemarks = row.querySelector('input[name="remarks[]"]').value; // Remarks from Employee

        // Add data to the selectedData array
        selectedData.push({
            srl_no: srl_no,
            programName: programName,
            natureTraining: natureTraining,
            duration: duration,
            faculty: faculty,
            year: year,
            tentativeDate: tentativeDate,
            targetGroup: targetGroup,
            remarks: remarks,
            hostelRequired: hostelRequired,
            employeeRemarks: employeeRemarks
        });
    });

    // Display selected data in the display area
    var displayDiv = document.getElementById('selectedProgramsList');
    if (displayDiv) {
        displayDiv.innerHTML = selectedData.map(item => `
            <li>
                <strong>Program ID:</strong> ${item.srl_no}, 
                <strong>Program Name:</strong> ${item.programName}, 
                <strong>Nature of Training:</strong> ${item.natureTraining}, 
                <strong>Duration:</strong> ${item.duration}, 
                <strong>Faculty:</strong> ${item.faculty}, 
                <strong>Year:</strong> ${item.year}, 
                <strong>Tentative Date:</strong> ${item.tentativeDate}, 
                <strong>Target Group:</strong> ${item.targetGroup}, 
                <strong>Remarks by Admin:</strong> ${item.remarks}, 
                <strong>Hostel Required:</strong> ${item.hostelRequired}, 
                <strong>Remarks from Employee:</strong> ${item.employeeRemarks}
            </li>
        `).join('');
    } else {
        console.error("Display div not found!"); // Debugging: Log an error if the display area is missing
    }
}

// Function to fetch and display row data
function fetchAndDisplayRowData(row) {
    var programName = row.querySelector('td:nth-child(2)').innerText; // Program Name
    var natureTraining = row.querySelector('td:nth-child(3)').innerText; // Nature of Training
    var duration = row.querySelector('td:nth-child(4)').innerText; // Duration
    var faculty = row.querySelector('td:nth-child(5)').innerText; // Faculty
    var year = row.querySelector('td:nth-child(6)').innerText; // Year
    var tentativeDate = row.querySelector('td:nth-child(7)').innerText; // Tentative Date
    var targetGroup = row.querySelector('td:nth-child(8)').innerText; // Target Group
    var remarks = row.querySelector('td:nth-child(9)').innerText; // Remarks by Admin
    var hostelRequired = row.querySelector('select[name="hostel_required[]"]').value; // Hostel Required
    var employeeRemarks = row.querySelector('input[name="remarks[]"]').value; // Remarks from Employee

    var rowData = {
        programName: programName,
        natureTraining: natureTraining,
        duration: duration,
        faculty: faculty,
        year: year,
        tentativeDate: tentativeDate,
        targetGroup: targetGroup,
        remarks: remarks,
        hostelRequired: hostelRequired,
        employeeRemarks: employeeRemarks
    };

    displayRowData(rowData);
}

// Function to display row data
// function displayRowData(data) {
//     var displayDiv = document.getElementById('selectedProgramsList');

//     if (displayDiv) {
//         var listItem = document.createElement('li');
//         listItem.innerHTML = `
//             <strong>Program Name:</strong> ${data.programName}, 
//             <strong>Nature of Training:</strong> ${data.natureTraining}, 
//             <strong>Duration:</strong> ${data.duration}, 
//             <strong>Faculty:</strong> ${data.faculty}, 
//             <strong>Year:</strong> ${data.year}, 
//             <strong>Tentative Date:</strong> ${data.tentativeDate}, 
//             <strong>Target Group:</strong> ${data.targetGroup}, 
//             <strong>Remarks by Admin:</strong> ${data.remarks}, 
//             <strong>Hostel Required:</strong> ${data.hostelRequired}, 
//             <strong>Remarks from Employee:</strong> ${data.employeeRemarks}
//         `;
//         displayDiv.appendChild(listItem);
//     } else {
//         console.error("Display div not found!");
//     }
// }

// Attach event listeners on page load
window.onload = function () {
    showEmployeeDetails();
    document.querySelectorAll('input[name="selectedIds[]"]').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            updateSubmitButton(); // Update the submit button state
            displaySelectedData(); // Display selected data

            if (this.checked) {
                var row = this.closest('tr');
                fetchAndDisplayRowData(row); // Fetch and display row data
            }
        });
    });
};

function logSelectedData() {
    var selectedData = []; // Array to store selected data
    var checkboxes = document.querySelectorAll('input[name="selectedIds[]"]:checked'); // Get all checked checkboxes

    // Loop through checked checkboxes
    checkboxes.forEach(function (checkbox) {
        var srl_no = checkbox.value; // Get the serial number of the selected program
        var row = checkbox.closest('tr'); // Get the closest table row

        // Fetch data from the row
        var programName = row.querySelector('td:nth-child(2)').innerText; // Program Name
        var natureTraining = row.querySelector('td:nth-child(3)').innerText; // Nature of Training
        var duration = row.querySelector('td:nth-child(4)').innerText; // Duration
        var faculty = row.querySelector('td:nth-child(5)').innerText; // Faculty
        var year = row.querySelector('td:nth-child(6)').innerText; // Year
        var tentativeDate = row.querySelector('td:nth-child(7)').innerText; // Tentative Date
        var targetGroup = row.querySelector('td:nth-child(8)').innerText; // Target Group
        var remarks = row.querySelector('td:nth-child(9)').innerText; // Remarks by Admin
        var hostelRequired = row.querySelector('select[name="hostel_required[]"]').value; // Hostel Required
        var employeeRemarks = row.querySelector('input[name="remarks[]"]').value; // Remarks from Employee

        // Add data to the selectedData array
        selectedData.push({
            srl_no: srl_no,
            programName: programName,
            natureTraining: natureTraining,
            duration: duration,
            faculty: faculty,
            year: year,
            tentativeDate: tentativeDate,
            targetGroup: targetGroup,
            remarks: remarks,
            hostelRequired: hostelRequired,
            employeeRemarks: employeeRemarks
        });
    });

    // Set the selected data as a value of the hidden input field
    document.getElementById('selectedDataInput').value = JSON.stringify(selectedData);

    // Submit the form
    document.getElementById('dataForm').submit();
}
</script>

<!-- Section 4: HTML Body and Form -->

<body>
    <?php include '../header_HR.php'; ?>
    
    <div class="full-width">
        <div class="row">
            
            <div class="col-md-6">
            <h6><i class="fa fa-home"></i>&nbsp;<i><u>HOD->TNI Nomination for Subordinate</u></i></h6>
                <!-- Employee Selection Form -->
                <form method="POST" id="employeeForm">
                <label for="employeeSelect">Select Employee:</label>
                <select name="employeeSelect" id="employeeSelect" onchange="showEmployeeDetails()">
                    <option value="" disabled selected>-- Select Employee --</option>
                    
                    <?php
                    
                    // Fetch employees under HOD
                    $query = "SELECT empno, name, grade, employee_grp, location FROM [Complaint].[dbo].[emp_mas_sap] WHERE hod_ro = ? AND status = 'A' UNION
                            SELECT empno, name, grade, employee_grp, location FROM [Complaint].[dbo].[emp_mas_sap] WHERE rep_ofcr = ? AND status = 'A'";
                    $params = array($sessionemp1, $sessionemp1);
                    $stmt = sqlsrv_query($conn, $query, $params);

                    if ($stmt === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }

                    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        $selected = ($row['empno'] == $selectedEmpNo) ? 'selected' : ''; // Preselect the option
                        echo '<option value="' . htmlspecialchars($row['empno']) . '" 
                                data-name="' . htmlspecialchars($row['name']) . '" 
                                data-grade="' . htmlspecialchars($row['grade']) . '" 
                                data-employeegrp="' . htmlspecialchars($row['employee_grp']) . '" 
                                data-location="' . htmlspecialchars($row['location']) . '">
                                ' . htmlspecialchars($row['name']) . '
                            </option>';
                    }
                    sqlsrv_free_stmt($stmt);
                    ?>
                </select>
                <input type="hidden" name="grade" id="hiddenGrade">
                <input type="hidden" name="employee_grp" id="hiddenEmployeeGrp">
                <input type="hidden" name="location" id="hiddenLocation">
                <button type="submit" class="btn btn-info">Submit</button>
            </form>

            <div id="selectedDataDisplay" style="display:none;">
                <p><strong>Selected Programs:</strong></p>
                <ul id="selectedProgramsList"></ul>
            </div>

                <!-- Selected Employee Details -->
                <!-- <p id="selectedEmployeeContainer">
                    <strong>Selected Employee:</strong> <span id="selectedEmployeeName">None</span>
                </p> -->

                <!-- Training Programs Table -->
                
                <?php
                $selectedEmpNo = '';
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    // Establish the connection
                    $conn = sqlsrv_connect($serverName, $connectionInfo);
                    if (!$conn) {
                        die(print_r(sqlsrv_errors(), true));
                    }

                    // echo "<pre>";
                    // print_r($_POST); // Debugging: Print all POST data
                    // echo "</pre>";
                
                    // Fetch data from the database based on the selected year
                    
                    $selectedEmpNo = $_POST['employeeSelect'];
                    $grade = $_POST['grade'];
                    $employee_grp = $_POST['employee_grp'];
                    $location = $_POST['location'];

                    
                    //echo 'selctedempno' .$selectedEmpNo;
                    
                    $query = "SELECT name, rep_ofcr FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
                    $params = array($selectedEmpNo);
                    $stmt = sqlsrv_query($conn, $query, $params);
                
                    if ($stmt === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }
                
                    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                    if ($row) {
                        $selectedEmpName = $row['name']; // Get the employee's name
                        $selectedrep_ofcr = $row['rep_ofcr'];
                    }
                    
                    echo "<i><b><span style='background-color:yellow;'>Selected Employee: $selectedEmpName</span> &nbsp;|&nbsp;</b></i>"; 
                    //echo "Grade: $grade, Employee Group: $employee_grp, Location: $location, rep_ofcr: $selectedrep_ofcr";
                
                        // echo $_SESSION["emp_num"];
                         $emp=$_SESSION["emp_num"];
                
                         $sqlCount = "SELECT COUNT(empno) AS total_count FROM [Complaint].[dbo].[request] WHERE empno = '$selectedEmpNo' and flag not in ('1','3','88','000')";
                    $paramsCount = array($selectedEmpNo);
                    $stmtCount = sqlsrv_query($conn, $sqlCount, $paramsCount);
                
                    if ($stmtCount === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }

                    // Fetch the available seats from the database
                    $seatsQuery = "SELECT TOP 1 available_seats 
                    FROM [Complaint].[dbo].[employee_seats] 
                    ORDER BY created_at DESC";
                    $seatsStmt = sqlsrv_query($conn, $seatsQuery);

                    if ($seatsStmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                    }

                    $seatsRow = sqlsrv_fetch_array($seatsStmt, SQLSRV_FETCH_ASSOC);
                    if (!$seatsRow) {
                    die("No available seats information found in the database");
                    }
                    $availableSeats = $seatsRow['available_seats'];
                
                    $totalCountRow = sqlsrv_fetch_array($stmtCount, SQLSRV_FETCH_ASSOC);
                    $totalCount = $totalCountRow['total_count'];
                    $remainingRequests = $availableSeats - $totalCount; // Assuming the limit is 8
                
                    // Check if the count exceeds the limit
                    if ($totalCount >= $availableSeats) {
                        echo '<script>alert("This Employee has reached the maximum limit of '.$availableSeats.' training requests!");</script>';
                    }
                
                    //echo '1'.$totalCount;
                    echo "<i><b><span style='background-color:yellow;'>Program remaining allowed: $remainingRequests</span></b></i>";
                
                   
                
                    $sql = "SELECT 
                    t.srl_no,     t.Program_name,     t.nature_training,     t.duration,     t.faculty,     t.training_mode,         t.Internal_external,     t.year,     t.target_group, t.available_seats,
                    t.venue,     t.hostel_reqd,     t.coordinator,     t.admin_remarks,     t.upload_date,     t.ip_address,     t.Closed_date,     t.day_from,    t.day_to,     t.flag,     com.NS01,     com.NS02, 
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
                    echo '<div style="height: 620px; overflow: auto;">';
                    echo "<table class='table table-bordered border-success' border='3'>";
                    echo "<thead style='position: sticky; top: 0; background-color: beige; z-index: 1; '>";
                    echo "<tr class='bg-primary'>";
                    echo "<th scope='col'>Srl. No</th>";
                    echo "<th scope='col'>Program Name</th>";
                    echo "<th scope='col' style='display:none;'>Nature of Training</th>";
                    echo "<th scope='col' style='display:none;'>Duration</th>";
                    echo "<th scope='col' style='display:none;'>Faculty</th>";
                    echo "<th scope='col'>Year</th>";
                    echo "<th scope='col' style='display:none;'>Tentative Date</th>";
                    echo "<th scope='col'>Target Grp</th>";
                    echo "<th scope='col'>Hostel Required</th>";
                    echo "<th scope='col'>Remarks</th>";
                    echo "<th scope='col' class='action-column'>Actions</th>";
                    echo "</tr>";
                    echo "</thead>";

                    echo "<tbody id='tbl_body'>";
                    $serialNo = 1;
                    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        $srl_no = $row['srl_no'];
                        $available_seats = (int) $row['available_seats'];
                        $requestSrlNo = $row['request_srl_no'] ?? '';
                        $locationMatch = false;
                        $gradeMatch = false;
                        $disabled = '';
                        $rowClass = '';

                        // Fetch count of requests for this srl_no
                        $countQuery = "SELECT COUNT(srl_no) AS srl_count FROM [Complaint].[dbo].[request] WHERE srl_no = ?";
                        $countStmt = sqlsrv_query($conn, $countQuery, array($srl_no));
                        $srlno_count = 0;
                        if ($countStmt && $countRow = sqlsrv_fetch_array($countStmt, SQLSRV_FETCH_ASSOC)) {
                            $srlno_count = (int) $countRow['srl_count'];
                        }
                        sqlsrv_free_stmt($countStmt);

                        // Check if seats are full
                        $seatsFull = $srlno_count >= $available_seats;
                        if ($seatsFull) {
                            $disabled = 'disabled';
                            $rowClass = 'disabled-row-full';
                        }


                        // Check if the row already exists in the request table
                        $checkQuery = "SELECT COUNT(*) AS record_count 
                                    FROM [Complaint].[dbo].[request] 
                                    WHERE srl_no = ? AND empno = ?";
                        $checkParams = array($srl_no, $selectedEmpNo);
                        $checkStmt = sqlsrv_query($conn, $checkQuery, $checkParams);

                        if ($checkStmt === false) {
                            die(print_r(sqlsrv_errors(), true));
                        }

                        $checkRow = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
                        $recordExists = ($checkRow['record_count'] > 0);

                        // If the record exists, change the row color to red and disable the checkbox
                        if ($recordExists) {
                            $rowClass = 'disabled-row-request1'; // Apply red background
                            $disabled = 'disabled'; // Disable inputs
                        }

                        // If employee has exceeded the limit, disable all rows
                        if ($totalCount >= $available_seats) {
                            $disabled = 'disabled';
                            $rowClass = 'disabled-row-request';
                        }

                        // Check Location Match
                        if (($location == 'NS01' && $row['NS01'] == 1) ||
                            ($location == 'NS02' && $row['NS02'] == 1) ||
                            ($location == 'NS03' && $row['NS03'] == 1) ||
                            ($location == 'NS04' && $row['NS04'] == 1)) {
                            $locationMatch = true;
                        }

                        // Check Grade Match (Only for Employee Group 'A')
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
                            // Employee Group B → Ignore Grade, Just Match Employee Group
                            if ($row['Employee_grp'] == 'B') {
                                $gradeMatch = true;
                            }
                        } elseif ($employee_grp != 'A' && $employee_grp != 'B') {
                            // Employee Group 'All Employees' → Ignore grade, check only location
                            $gradeMatch = true;
                        }

                        // If row does NOT match the filter, disable inputs
                        if (!$locationMatch || !$gradeMatch) {
                            $disabled = 'disabled';
                            $rowClass = 'disabled-row';
                        }

                        // Output the table row
                        echo "<tr class='table-light $rowClass'>";
                        echo "<td>{$row['srl_no']}</td>";
                        echo "<td>{$row['Program_name']}</td>";
                        echo "<td style='display:none;'>{$row['nature_training']}</td>";
                        echo "<td style='display:none;'>{$row['duration']}</td>";
                        echo "<td style='display:none;'>{$row['faculty']}</td>";
                        echo "<td>{$row['year']}</td>";
                        //echo "<td style='display:none;'>{$row['tentative_date']}</td>";
                        echo "<td>{$row['target_group']}</td>";
                        echo "<td>
                                <select name='hostel_required[]' data-id='{$row['srl_no']}' $disabled required>
                                    <option value='1'>Yes</option>
                                    <option value='0'>No</option>
                                </select>
                            </td>";
                            echo "<td>
                            <input type='text' name='admin_remarks[]' data-id='{$row['srl_no']}' placeholder='Enter remarks' $disabled>";
                    
                    if ($seatsFull) {
                        echo "<span class='text-danger font-weight-bold'>Seats are full</span>";
                    }
                    
                    echo "</td>";
                        echo "<td>
                                <label class='checkbox-container' style='transform: scale(1.5);'>
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
                <br>
                <form method="POST" action="target_page.php" id="dataForm">
                <!-- Hidden inputs for sessionemp, location, selectedrep_ofcr, and selectedEmpNo -->
                <input type="hidden" name="sessionemp" value="<?php echo htmlspecialchars($sessionemp); ?>">
                <input type="hidden" name="location" value="<?php echo htmlspecialchars($location); ?>">
                <input type="hidden" name="selectedrep_ofcr" value="<?php echo htmlspecialchars($selectedrep_ofcr); ?>">
                <input type="hidden" name="selectedEmpNo" value="<?php echo htmlspecialchars($selectedEmpNo); ?>">

                <!-- Hidden input for selected checkbox data -->
                <input type="hidden" name="selectedData" id="selectedDataInput">

                <!-- Submit button -->
                <button type="submit" name="request" onclick="logSelectedData()" id="submitButton" class="btn btn-success" disabled>Submit Request</button>
            </form>
            </div>           
              
        
        
         <!-------------------------------------Approved by You------------------------------------------------------>
         <div class="col-md-5"
                >
                <?php
        $serverName = "192.168.100.240";
        $connectionInfo = array(
            "Database" => "complaint",
            "UID" => "sa",
            "PWD" => "Intranet@123"
        );
        $sessionemp = $_SESSION["emp_num"];
        $sessionemp1 = str_pad($sessionemp, 8, "0", STR_PAD_LEFT);

        //echo $sessionemp;
        // Create connection
        $conn = sqlsrv_connect($serverName, $connectionInfo);

        if (!$conn) {
            die(print_r(sqlsrv_errors(), true));
        }

       

        // Fetch the list of programs based on `ordinate_req`
        $fetch_program_name = "SELECT DISTINCT program_name, srl_no FROM [Complaint].[dbo].[request] WHERE appr_empno = '$sessionemp' and flag='4'";

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

        echo '<form id="programForm1" method="post">';
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
        
        echo '<div id="result"></div>'; // Placeholder for the result

// Check if a program is selected
           

    // Close the connection
    sqlsrv_close($conn);
    ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $("#programForm1").submit(function(e) {
        e.preventDefault(); // Prevent form from reloading the page

        var programName = $("#programName").val(); // Get selected program

        if (!programName) {
            alert("Please select a program!");
            return;
        }

        $.ajax({
            type: "POST",
            url: "fetch_subordinate_request.php", // Ensure this file processes the request correctly
            data: { programName: programName },
            success: function(response) {
                $("#result").html(response); // Display fetched data in result div
            },
            error: function() {
                alert("Error fetching data.");
            }
        });
    });
});
</script>


            </div>
    </div>
    
</body>
</html>

<?php include '../footer.php';?>