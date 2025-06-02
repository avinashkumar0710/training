<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Function</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
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

    .container {
        padding: 10px;
    }
    </style>
     <script>
        function validateForm() {
            var year = document.getElementById("year").value;
            if (year === "") {
                document.getElementById("errorMessage").innerText = "Please select a year.";
                return false;
            }
            return true;
        }

        function autoSubmit() {
            document.getElementById("showProgramForm").submit();
        }
    </script>
</head>

<?php include '../header_HR.php';?>

<body>
    <h6><i class='fa fa-home'></i>&nbsp;<i><u>HR->HR Functions</u></i></h6>
    <?php
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
    exit();
}

$sessionemp = $_SESSION["emp_num"] ?? '';

// Add '00' in front if session value has only 6 digits
// if (strlen($sessionemp) == 6) {
//     $sessionemp = '00' . $sessionemp;
// }

// Database Connection
$serverName = "192.168.100.240";
$connectionInfo = [
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch Plant from EA_webuser_tstpp
$plantQuery = "SELECT Plant FROM [Complaint].[dbo].[EA_webuser_tstpp] WHERE emp_num LIKE ?";
$plantParams = ["%$sessionemp%"];
$plantResult = sqlsrv_query($conn, $plantQuery, $plantParams);
$plantRow = sqlsrv_fetch_array($plantResult, SQLSRV_FETCH_ASSOC);
$userPlant = $plantRow['Plant'] ?? null;

//echo $userPlant;

// Fetch distinct locations for plant dropdown
$plantQuery = "SELECT DISTINCT location FROM [Complaint].[dbo].[emp_mas_sap] ORDER BY location";
$plantResult = sqlsrv_query($conn, $plantQuery);

// Fetch data based on selected plant
$selectedPlant = $_POST['plant'] ?? 'ALL';
$sql = "SELECT r.srl_no, r.id, 
            CASE WHEN LEN(r.empno) = 6 THEN '00' + r.empno ELSE r.empno END AS empno, 
            r.PROGRAM_NAME, r.year, r.duration, r.faculty, r.plant, r.hostel_book, r.flag, 
            t.day_from, t.day_to, r.uploaded_date, r.aprroved_time,
            e.email, e.name, e.dept, t.Closed_date 
        FROM [Complaint].[dbo].[request] r
        JOIN [Complaint].[dbo].[emp_mas_sap] e 
            ON CASE WHEN LEN(r.empno) = 6 THEN '00' + r.empno ELSE r.empno END = e.empno
        LEFT JOIN [Complaint].[dbo].[training_mast] t 
            ON r.srl_no = t.srl_no
        WHERE r.flag = '4'";

$queryParams = [];
if ($selectedPlant !== 'ALL') {
    $sql .= " AND r.plant = ?";
    $queryParams[] = $selectedPlant;
}

$sql .= " ORDER BY r.id DESC, r.year DESC";
$stmt = sqlsrv_query($conn, $sql, $queryParams);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Mapping plant codes to readable names
$plantNames = [
    'NS04' => 'Bhilai',
    'NS03' => 'Rourkela',
    'NS02' => 'Durgapur',
    'NS01' => 'Corporate Center'
];
?>

<div class="container-fluid">
    <div class="input-group mb-3">
        <form method="POST" id="showProgramForm">
            <label for="year">Select a Year:</label>
            <select name="year" id="year">
                <option value="ALL" <?= (!isset($_POST['year']) || $_POST['year'] == 'ALL') ? 'selected' : '' ?>>All</option>
                <?php
                // Fetch distinct years
                $distinctYearsQuery = "SELECT DISTINCT year FROM [Complaint].[dbo].[request] WHERE flag='4' ORDER BY year DESC";
                $yearsResult = sqlsrv_query($conn, $distinctYearsQuery);

                if ($yearsResult) {
                    while ($yearRow = sqlsrv_fetch_array($yearsResult, SQLSRV_FETCH_ASSOC)) {
                        $yearValue = $yearRow['year'];
                        $selectedAttr = (isset($_POST['year']) && $_POST['year'] == $yearValue) ? 'selected' : '';
                        echo "<option value=\"$yearValue\" $selectedAttr>$yearValue</option>";
                    }
                } else {
                    echo "<option value='' disabled>Error fetching years</option>";
                }
                ?>
            </select>

            <label for="plant">Select a Plant:</label>
            <select name="plant" id="plant" <?= $userPlant !== 'NS04' ? 'disabled' : ''; ?>>
                <option value="ALL" <?= (!isset($_POST['plant']) || $_POST['plant'] == 'ALL') ? 'selected' : '' ?>>All</option>
                <?php
                // Fetch distinct plant locations
                $distinctPlantsQuery = "SELECT DISTINCT location FROM [Complaint].[dbo].[emp_mas_sap] ORDER BY location";
                $plantsResult = sqlsrv_query($conn, $distinctPlantsQuery);

                // Mapping codes to readable names
                $plantNames = [
                    'NS04' => 'Bhilai',
                    'NS03' => 'Rourkela',
                    'NS02' => 'Durgapur',
                    'NS01' => 'Corporate Center'
                ];

                if ($plantsResult) {
                    while ($plantRow = sqlsrv_fetch_array($plantsResult, SQLSRV_FETCH_ASSOC)) {
                        $plantValue = $plantRow['location'];
                        $plantText = $plantNames[$plantValue] ?? $plantValue; // Use mapping, or default to original value
                        $selectedAttr = (isset($_POST['plant']) && $_POST['plant'] == $plantValue) ? 'selected' : '';
                        echo "<option value=\"$plantValue\" $selectedAttr>$plantText</option>";
                    }
                } else {
                    echo "<option value='' disabled>Error fetching plants</option>";
                }
                ?>
            </select>

            <button type="submit" class="btn btn-info">Show Programs</button>
        </form>
    </div>
</div>



            <form action="download_excel_HR_approve.php" method="post" id="downloadForm">
                <input type="hidden" name="year" id="selectedYear"
                    value="<?php echo htmlspecialchars($_POST['year'] ?? ''); ?>">
                <input type="hidden" name="location" id="selectedLocation"
                    value="<?php echo htmlspecialchars($location); ?>">
                <!-- <button type="submit" class="btn btn-success" id="downloadButton">Download Excel</button> -->
            </form>
           

           
        </div>
</div>

<?php
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
    exit();
}

$sessionemp = $_SESSION["emp_num"] ?? '';
if (empty($sessionemp)) {
    die("Error: Session variable 'emp_num' is empty");
}



//echo $sessionemp;
// Establish connection (if not already connected)
$serverName = "192.168.100.240";
$connectionOptions = [
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// Get User's Plant
$plantQuery = "SELECT Plant FROM [Complaint].[dbo].[EA_webuser_tstpp] WHERE emp_num LIKE ?";
$params = [$sessionemp . '%'];
$plantResult = sqlsrv_query($conn, $plantQuery, $params);
$plantRow = sqlsrv_fetch_array($plantResult, SQLSRV_FETCH_ASSOC);
$userPlant = $plantRow['Plant'] ?? null;

//echo '33'.$userPlant;

// Fetch Selected Filters
$serialNo = 1;
$selected_year = $_POST['year'] ?? 'ALL';
$selected_plant = $_POST['plant'] ?? 'ALL';

// SQL Query to Fetch Data
$sql = "SELECT 
            r.empno, r.Program_name, r.nature_training, r.year, r.srl_no, 
            t.day_from, t.day_to, r.remarks, r.duration, r.tentative_date, 
            r.uploaded_date, r.aprroved_time, a.name AS emp_name, a.dept, 
            r.hostel_book, a.location, r.appr_empno, 
            b.name AS approve_name
        FROM 
            [Complaint].[dbo].[request] r 
        JOIN 
            [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno  
        LEFT JOIN 
            [Complaint].[dbo].[emp_mas_sap] b 
            ON b.empno = CASE 
                          WHEN LEN(r.appr_empno) = 6 THEN '00' + r.appr_empno 
                          ELSE r.appr_empno 
                        END
        LEFT JOIN 
            [Complaint].[dbo].[training_mast] t ON r.srl_no = t.srl_no
        WHERE 
            r.flag = '4'";

// Add conditions for year and plant selection
$queryParams = [];
if ($selected_year !== 'ALL') {
    $sql .= " AND r.year = ?";
    $queryParams[] = $selected_year;
}

// Restrict data for non-NS04 users
if ($userPlant !== 'NS04') {
    $sql .= " AND a.location = ?";
    $queryParams[] = $userPlant;
} elseif ($selected_plant !== 'ALL') { // Allow plant selection for NS04
    $sql .= " AND a.location = ?";
    $queryParams[] = $selected_plant;
}

$sql .= " ORDER BY r.Program_name";

// Execute Query
$result = sqlsrv_query($conn, $sql, $queryParams);
if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Display Table
if (sqlsrv_has_rows($result)) {
    echo "<h4>HOD Approved Training List</h4>";
    echo "<div class='table' style='height: 600px; overflow: auto;'>";
    echo "<table class='table table-bordered border-success' border='3'>";

    echo "<thead style='position: sticky; top: 0; background-color: beige;'>
             <tr>           
                <th scope='col'>Serial No</th>
                <th scope='col'>Emp Name</th>
                <th scope='col'>Program Name</th>
                <th scope='col'>Nature of Training</th>
                <th scope='col'>Year</th>
                <th scope='col'>Duration</th>
                <th scope='col'>Day From</th>
                <th scope='col'>Day To</th>
                <th scope='col'>Dept</th>
                <th scope='col'>Hostel Required</th>
                <th scope='col'>Location</th>
                <th scope='col'>Approved By</th>
                <th scope='col'>Approved Date</th>
                <th scope='col'>Remarks</th>
             </tr>
          </thead>";

    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        // Convert location codes to readable names
        $locationNames = [
            'NS04' => 'Bhilai',
            'NS03' => 'Rourkela',
            'NS02' => 'Durgapur',
            'NS01' => 'Corporate Center'
        ];
        $locationName = $locationNames[$row['location']] ?? $row['location'];

        // Format Dates (Avoid NULL Errors)
        $day_from = $row['day_from'] ? $row['day_from']->format('Y-m-d') : 'N/A';
        $day_to = $row['day_to'] ? $row['day_to']->format('Y-m-d') : 'N/A';
        $approved_time = $row['aprroved_time'] ? $row['aprroved_time']->format('Y-m-d H:i:s') : 'N/A';

        echo "<tr class='table-light'>
                <td>". $serialNo++ ."</td>
                <td>". htmlspecialchars($row['emp_name']) ."</td>
                <td>". htmlspecialchars($row['Program_name']) ."</td>
                <td>". htmlspecialchars($row['nature_training']) ."</td>
                <td>". htmlspecialchars($row['year']) ."</td>
                <td>". htmlspecialchars($row['duration']) ."</td>
                <td>$day_from</td>
                <td>$day_to</td>
                <td>". htmlspecialchars($row['dept']) ."</td>
                <td style='color: ". ($row['hostel_book'] == 1 ? 'green' : 'red') ."'>". ($row['hostel_book'] == 1 ? 'Yes' : 'No') ."</td>
                <td>$locationName</td>
                <td>". htmlspecialchars($row['approve_name'] ?? 'N/A') ."</td>
                <td style='color:blue;'>$approved_time</td>
                <td>". htmlspecialchars($row['remarks']) ."</td>
              </tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "No programs found for selected filters.";
}

// Free Result Set & Close Connection
sqlsrv_free_stmt($result);
sqlsrv_close($conn);
?>

    </div>
    </div>
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