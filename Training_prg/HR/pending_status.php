<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Function</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"  rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!---scroll javascript---->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    body {
        font-weight: 600;
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        background-color: #e8eef3;
    }

    .scroll-button {
        bottom: 20px;
        right: 20px;
        width: 100px;
        height: 30px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 5%;
        cursor: pointer;
        text-align: center;
        z-index: 1000;
    }

    .scrollable-table {
        height: 300px;
        overflow-y: auto;
    }

    .bg-danger {
    background-color: red !important; /* Override Bootstrap if needed */
}
    
    </style>
</head>

<?php include '../header_HR.php';?>
<?php

session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }

    $sessionemp= $_SESSION["emp_num"];

    // Add '00' in front if session value has only 6 digits
    $sessionemp1 = str_pad($sessionemp, 8, "0", STR_PAD_LEFT);
//echo $sessionemp1;


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

$plantQuery = "SELECT Plant FROM [Complaint].[dbo].[EA_webuser_tstpp] WHERE emp_num LIKE ?";
$paramsPlant = ["$sessionemp%"];
$plantResult = sqlsrv_query($conn, $plantQuery, $paramsPlant);

if ($plantResult === false) {
    die(print_r(sqlsrv_errors(), true));
}

$plantRow = sqlsrv_fetch_array($plantResult, SQLSRV_FETCH_ASSOC);
$userPlant = $plantRow['Plant'] ?? null;
//echo 'we ' . $userPlant;

// Fetch distinct years for dropdown
$yearQuery = "SELECT DISTINCT year FROM [Complaint].[dbo].[request] ORDER BY year DESC";
$yearResult = sqlsrv_query($conn, $yearQuery);

if ($yearResult === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch distinct departments for dropdown
$deptQuery = "SELECT DISTINCT a.dept, r.plant
              FROM [Complaint].[dbo].[request] r
              JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
              WHERE COALESCE(r.flag, 0) IN ('0', '2') AND r.plant = ?";

$paramsDept = [$userPlant];
$deptResult = sqlsrv_query($conn, $deptQuery, $paramsDept);

if ($deptResult === false) {
    die(print_r(sqlsrv_errors(), true));
}
$serialNo = 1;
$selected_year = null;
$selected_dept = null;

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_year = $_POST['year'] ?? null;
    $selected_dept = $_POST['dept'] ?? null;
}

// Prepare the SQL query conditionally
$sql = "SELECT 
    r.id, 
    r.srl_no,
    r.empno, 
    r.Program_name, 
    r.nature_training, 
    r.year, 
    r.remarks, 
    r.duration, 
    r.tentative_date, 
    a.name AS emp_name, 
    a.dept, 
    r.uploaded_date, 
    r.aprroved_time,
    r.hostel_book, 
    a.location, 
    r.appr_empno,
    COALESCE(r.flag, 0) AS flag, 
    a.rep_ofcr, 
    rep.name AS rep_ofcr_name,  
    rep.email AS rep_ofcr_email,
    a.hod_ro,  
    hod.name AS hod_ro_name,
    hod.email AS hod_ro_email,
    t.day_from, 
    t.day_to
FROM 
    [Complaint].[dbo].[request] r
JOIN 
    [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
LEFT JOIN 
    [Complaint].[dbo].[emp_mas_sap] rep ON a.rep_ofcr = rep.empno  
LEFT JOIN 
    [Complaint].[dbo].[emp_mas_sap] hod ON a.hod_ro = hod.empno  
LEFT JOIN 
    [Complaint].[dbo].[training_mast] t ON r.srl_no = t.srl_no
WHERE 
    r.flag IN ('0', '2') AND a.location = ?";

$params = [$userPlant];

// Apply year filter if selected
if (!empty($selected_year) && $selected_year !== 'ALL') {
    $sql .= " AND r.year = ?";
    $params[] = $selected_year;
}

// Apply department filter if selected
if (!empty($selected_dept) && $selected_dept !== 'ALL') {
    $sql .= " AND a.dept = ?";
    $params[] = $selected_dept;
}

// Order results
$sql .= " ORDER BY r.Program_name";

// Execute query safely
$result = sqlsrv_query($conn, $sql, $params);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>
<body>
   
    <h6><i class='fa fa-home'></i>&nbsp;<i><u>HR->Pending Status</u></i></h6>

    <div class="container-fluid">
    <div class="container mt-3">
            <form method="post" action="">
                <label for="year">Select Year:</label>
                <select name="year">
                    <option value="ALL">All Years</option>
                    <?php while ($row = sqlsrv_fetch_array($yearResult, SQLSRV_FETCH_ASSOC)): ?>
                        <option value="<?= htmlspecialchars($row['year']); ?>" 
                            <?= ($selected_year == $row['year']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($row['year']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="dept">Select Department:</label>
                <select name="dept">
                    <option value="ALL">All Departments</option>
                    <?php while ($row = sqlsrv_fetch_array($deptResult, SQLSRV_FETCH_ASSOC)): ?>
                        <option value="<?= htmlspecialchars($row['dept']); ?>" 
                            <?= ($selected_dept == $row['dept']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($row['dept']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <input type="submit" class="btn btn-success" value="Filter">
            </form>
        </div>




<?php

$serialNo = 1;


if (sqlsrv_has_rows($result)) {
    // Output table
    echo "<h4>&nbsp;&nbsp;Pending Status (<i>*Note: Pink background indicates mail already sent</i>)</h4>";
    echo "<div class='container-fluid' style='height: 570px; overflow: auto;'>";
    
    // Start the form
    echo "<form method='post' action='send_email.php' id='emailForm'>";
    echo "<table class='table table-bordered border-success' border='3'>";
    echo "<thead style='position: sticky; top: 0; background-color: beige;'>
            <tr>           
                <th scope='col'>Sl.</th>
                <th scope='col' style='display:none;'>id</th>
                <th scope='col'>Emp Name</th>
                <th scope='col'>Program Name</th>
                <th scope='col'>Nature of Training</th>
                <th scope='col'>Year</th>
                <th scope='col'>Dept</th>
                <th scope='col'>Plant</th>
                <th scope='col'>Duration</th>
                <th scope='col'>Last Approved Date</th>
                <th scope='col'>Total Pending Days</th>
                
               
                <th scope='col'>Hostel Required</th>
                <th scope='col'>Remarks</th>
                <th scope='col'>status</th>
               
              
                <th scope='col'>Select Mail</th>
                <th scope='col'>Select All&nbsp;<input type='checkbox' id='select_all'></th>
            </tr>
        </thead>";
        $plantMapping = [
            'NS04' => 'Bhilai',
            'NS03' => 'Rourkela',
            'NS02' => 'Durgapur',
            'NS01' => 'Corporate Center'
        ];
    $serialNo = 1;
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {

        $apprEmpno = $row['appr_empno'] ?? 'N/A'; // Fetch appr_empno
    $flag = $row['flag']; // Fetch flag value

     // Determine which email & status to display
     if ($flag == '0') {
        $approverEmail = $row['rep_ofcr_email']; // Use Reporting Officer's email
        $status = "Pending at " . htmlspecialchars($row['rep_ofcr_name']);
    } elseif ($flag == '2') {
        $approverEmail = $row['hod_ro_email']; // Use HOD's email
        $status = "Pending at " . htmlspecialchars($row['hod_ro_name']);
    } else {
        $approverEmail = 'N/A'; // Fallback if no valid flag
        $status = "N/A";
    }
        // Check if email has been sent
        $checkQuery = "SELECT COUNT(*) as count FROM send_mail_list WHERE request_id = ?";
        $checkParams = array($row['id']);
        $checkStmt = sqlsrv_query($conn, $checkQuery, $checkParams);
        $isSent = false; 

        if ($checkStmt) {
            $checkRow = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
            if ($checkRow['count'] > 0) {
                $isSent = true;
            }
            sqlsrv_free_stmt($checkStmt);
        }

        $plantCode = isset($row['location']) ? trim($row['location']) : '';

        // Map plant code to plant name (fallback to original value if not in mapping)
        $plantName = isset($plantMapping[$plantCode]) ? $plantMapping[$plantCode] : $plantCode;
        // Determine row background color
        $rowClass = $isSent ? 'table-danger' : 'table-light';

        // Map plant code to plant name
      
        
        // Convert plant code to plant name
        //$plantNameDisplay = isset($plantMapping[$plantName]) ? $plantMapping[$plantName] : $plantName;
        // Get current date
$currentDateObj = new DateTime();
$currentDate = $currentDateObj->format('Y-m-d');

// Fetch and format the date (either approved_time or uploaded_date)
$referenceDate = isset($row['aprroved_time']) ? $row['aprroved_time'] : $row['uploaded_date'];
$referenceDateFormatted = isset($referenceDate) ? $referenceDate->format('Y-m-d') : null;

// Calculate Pending Days
$pendingDays = ($referenceDateFormatted !== null) ? $currentDateObj->diff(new DateTime($referenceDateFormatted))->days : 'N/A';

        

        // Render row
        echo "<tr class='$rowClass'>
        <td>" . $serialNo++ . "</td>
        <td style='display:none;'>" . htmlspecialchars($row['id']) . "</td>
        <td>" . htmlspecialchars($row['emp_name']) . "</td>
        <td>" . htmlspecialchars($row['Program_name']) . "</td>
        <td>" . htmlspecialchars($row['nature_training']) . "</td>
        <td>" . htmlspecialchars($row['year']) . "</td>
        <td>" . htmlspecialchars($row['dept']) . "</td>
        <td>" . htmlspecialchars($plantName) . "</td>
        <td>" . htmlspecialchars($row['duration']) . "</td>
        <td>" . (!empty($row['aprroved_time']) ? htmlspecialchars($row['aprroved_time']->format('Y-m-d H:i:s')) : htmlspecialchars($row['uploaded_date']->format('Y-m-d H:i:s'))) . "</td>
        <td style='color:blue;'>" . $pendingDays . "</td> 
        <td style='color: " . ($row['hostel_book'] == 1 ? 'green' : 'red') . "'>" . ($row['hostel_book'] == 1 ? 'Yes' : 'No') . "</td>
        <td>" . htmlspecialchars($row['remarks']) . "</td>

        <td style='color:orange;'>" . htmlspecialchars($status) . "</td>
        <td style='color:blue;'>" . htmlspecialchars($approverEmail) . "</td> <!-- Display Correct Email -->
        
        <td><input type='checkbox' class='select_checkbox' name='selected_ids[]' value='" . $row['id'] . "'></td>
    </tr>";
    }
    echo "</table>";
    echo "</div>";
    echo "<br>";
    // Add the Send Mail button
    echo "<button type='button' class='btn btn-primary' onclick='submitForm()'>Send Mail</button>";
    echo "</form>"; // Close the form
} else {
    //echo "No programs found for " . ($selected_year !== 'ALL' ? $selected_year : 'All years') . " and " . ($selected_plant !== 'ALL' ? $plantMapping[$selected_plant] ?? $selected_plant : 'All Plants');
}

// Free resources
sqlsrv_free_stmt($result);
sqlsrv_close($conn);
?>

    </div>
    </div>

    <script>
// Function to handle "Select All" checkbox
document.getElementById('select_all').addEventListener('click', function() {
    let checkboxes = document.querySelectorAll('.select_checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

// Function to submit the form with selected rows
function submitForm() {
    let form = document.getElementById('emailForm');
    let selectedCheckboxes = document.querySelectorAll('.select_checkbox:checked');

    if (selectedCheckboxes.length === 0) {
        alert('Please select at least one row to send an email.');
        return;
    }

    // Submit the form
    form.submit();
}
</script>
    <script>
    // Scroll up function
    $('#scrollUp').on('click', function() {
        $('div').animate({
            scrollTop: '-=1000'
        }, 'slow'); // Adjust scroll speed as needed
    });

    // Scroll down function
    $('#scrollDown').on('click', function() {
        $('div').animate({
            scrollTop: '+=1000'
        }, 'slow'); // Adjust scroll speed as needed
    });
    </script>
    <script>
    const errorMessage = document.getElementById('errorMessage');

    // Add event listener to the form for validation
    document.querySelector('form').addEventListener('submit', function(event) {
        // Check if a year is selected
        if (document.getElementById('year').value === '') {
            // Prevent form submission
            event.preventDefault();
            // Display error message
            errorMessage.textContent = 'Please select a year.';
        }
    });

    // Add event listener to the download button for validation
    document.getElementById('downloadForm').addEventListener('submit', function(event) {
        // Check if a year is selected
        if (document.getElementById('selectedYear').value === '') {
            // Prevent form submission
            event.preventDefault();
            // Display error message
            errorMessage.textContent = 'Please select a year for download.';
        }
    });
    </script>

</body>

</html>
<?php include '../footer.php';?>