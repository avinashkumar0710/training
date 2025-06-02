<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Function</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
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

    .bg-danger {
    background-color: red !important; /* Override Bootstrap if needed */
}
    
    </style>
</head>

<?php include '../header_HR.php';?>

<body>
   
    <h6><i class='fa fa-home'></i>&nbsp;<i><u>HR->Pending Status</u></i></h6>

    <div class="container-fluid">
        <div class="input-group mb-3">
            <form method="POST" id="showProgramForm" onsubmit="return validateForm()">
                <label for="year">Select a year:</label>
                <select name="year" id="year">
                    <option value="" disabled <?= !isset($_POST['year']) ? 'selected' : '' ?>>Select year</option>
                    <option value="ALL" <?= (isset($_POST['year']) && $_POST['year'] == 'ALL') ? 'selected' : '' ?>>ALL
                    </option> <!-- "ALL" option -->

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
        $distinctYearsQuery = "SELECT DISTINCT year FROM [Complaint].[dbo].[request] WHERE flag='4' AND plant='$location' order by year desc";
        $yearsResult = sqlsrv_query($conn, $distinctYearsQuery);

        if ($yearsResult) {
            // Loop through distinct years and generate options
            while ($yearRow = sqlsrv_fetch_array($yearsResult, SQLSRV_FETCH_ASSOC)) {
                $yearValue = $yearRow['year'];
                $selectedAttr = (isset($_POST['year']) && $_POST['year'] == $yearValue) ? 'selected' : '';
                echo "<option value=\"$yearValue\" $selectedAttr>$yearValue</option>";
            }
        } else {
            echo "<option value='' disabled>Error fetching years</option>";
        }

        // Close the SQL Server connection
        sqlsrv_close($conn);
        ?>
                </select>
                <button type="submit" class="btn btn-info">Show Programs</button>
                <p id="errorMessage" style="color: red;"></p> <!-- Error message -->
            </form>
            &nbsp;


            <!-- <form action="download_excel_HR_approve.php" method="post" id="downloadForm">
                <input type="hidden" name="year" id="selectedYear"
                    value="<?php echo htmlspecialchars($_POST['year'] ?? ''); ?>">
                <input type="hidden" name="location" id="selectedLocation"
                    value="<?php echo htmlspecialchars($location); ?>">
                <button type="submit" class="btn btn-success" id="downloadButton">Download Excel</button>
            </form> -->
            &nbsp;&nbsp;&nbsp;

            <div class='scroll'>
                <button class='btn btn-primary' id='scrollUp'>ScrollUp&#8593;</button>&nbsp;
                <button class='btn btn-primary' id='scrollDown'>ScrollDown&#8595;</button>
            </div>
        </div>


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
$selected_year = null;

// Check if form is submitted or show all data by default
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_year = $_POST['year'];
}

// Prepare the SQL query conditionally
if ($selected_year && $selected_year !== 'ALL') {
    $sql = "SELECT r.id, r.empno, r.Program_name, r.nature_training, r.year, r.remarks, r.duration, r.tentative_date, a.name, r.hostel_book, a.location, r.flag, a.rep_ofcr, a.hod_ro, rep.email AS rep_ofcr_email, hod.email AS hod_ro_email 
            FROM [Complaint].[dbo].[request] r
            JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
            LEFT JOIN [Complaint].[dbo].[emp_mas_sap] rep ON a.rep_ofcr = rep.empno
            LEFT JOIN [Complaint].[dbo].[emp_mas_sap] hod ON a.hod_ro = hod.empno
            WHERE r.flag IN ('0', '2') 
            AND r.year = ? 
            ORDER BY r.Program_name";
    $params = array($selected_year);
} else {
    // If "ALL" is selected or on page load, retrieve data for all years
    $sql = "SELECT r.id, r.empno, r.Program_name, r.nature_training, r.year, r.remarks, r.duration, r.tentative_date, a.name, r.hostel_book, a.location, r.flag, a.rep_ofcr, a.hod_ro, rep.email AS rep_ofcr_email, hod.email AS hod_ro_email 
            FROM [Complaint].[dbo].[request] r
            JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
            LEFT JOIN [Complaint].[dbo].[emp_mas_sap] rep ON a.rep_ofcr = rep.empno
            LEFT JOIN [Complaint].[dbo].[emp_mas_sap] hod ON a.hod_ro = hod.empno
            WHERE r.flag IN ('0', '2') 
            ORDER BY r.Program_name";
    $params = array();
}

$result = sqlsrv_query($conn, $sql, $params);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (sqlsrv_has_rows($result)) {
    // Output data in a table
    echo "<h4>&nbsp;&nbsp;Pending Status(<i>*Note : Pink background indicates that mail already send</i>)</h4>";
    echo "<div class='container-fluid' style='height: 600px; overflow: auto;'>";
    echo "<table class='table table-bordered border-success' border='3' border='1'>";
    echo "<thead style='position: sticky; top: 0; background-color: beige;'>
            <tr>           
                <th scope='col'>Serial No</th>
                <th scope='col'>Emp Name</th>
                <th scope='col'>Program_name</th>
                <th scope='col'>Nature of Training</th>
                <th scope='col'>Year</th>
                <th scope='col'>Duration</th>
                <th scope='col'>Tentative_Date</th>
                <th scope='col'>Hostel_Required</th>
                <th scope='col'>Remarks</th>
                <th scope='col'>Status</th>
                <th scope='col'>Select Mail</th>
                
            </tr>
        </thead>";

    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        // Determine the status based on the flag value
        $status = '';
        switch ($row["flag"]) {
            case 0:
                $status = '<span style="color:blue">Pending at Reporting Officer</span>';
                break;
            case 1:
                $status = '<span style="color:green">Approved by Reporting Officer</span>';
                break;
            case 2:
                $status = '<span style="color:blue">Pending at HOD</span>';
                break;
            case 3:
                $status = '<span style="color:green">Approved by HOD</span>';
                break;
            case 4:
                $status = '<span style="color:red">Rejected</span>';
                break;
            default:
                $status = '<span style="color:gray">Unknown</span>';
        }

          // Check if the request_id exists in send_mail_list
          $checkQuery = "SELECT COUNT(*) as count FROM send_mail_list WHERE request_id = ?";
          $checkParams = array($row['id']);
          $checkStmt = sqlsrv_query($conn, $checkQuery, $checkParams);
          $isSent = false; // Flag to indicate if email has been sent
  
          if ($checkStmt) {
              $checkRow = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
              if ($checkRow['count'] > 0) {
                  $isSent = true; // Email has been sent for this request_id
              }
              sqlsrv_free_stmt($checkStmt);
          }
  
          // Apply CSS class based on the flag
          $rowClass = $isSent ? 'table-danger' : 'table-light'; // Use 'table-danger' for red background
  

        // Render the row with the form
        echo "<tr class='$rowClass'>
                <td>" . $serialNo++ . "</td>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . htmlspecialchars($row['Program_name']) . "</td>
                <td>" . htmlspecialchars($row['nature_training']) . "</td>
                <td>" . htmlspecialchars($row['year']) . "</td>
                <td>" . htmlspecialchars($row['duration']) . "</td>
                <td>" . htmlspecialchars($row['tentative_date']) . "</td>
                <td style='color: " . ($row['hostel_book'] == 1 ? 'green' : 'red') . "'>" . ($row['hostel_book'] == 1 ? 'Yes' : 'No') . "</td>
                <td>" . htmlspecialchars($row['remarks']) . "</td>
                <td>" . $status . "</td>
                <td>
                    <form method='post' action='send_email.php'>
                        <input type='hidden' name='id' value='". htmlspecialchars($row['id']) ."'>
                        <input type='hidden' name='empno' value='". htmlspecialchars($row['empno']) ."'>
                        <input type='hidden' name='name' value='". htmlspecialchars($row['name']) ."'>
                        <input type='hidden' name='program_name' value='". htmlspecialchars($row['Program_name']) ."'>
                        <input type='hidden' name='flag' value='". htmlspecialchars($row['flag']) ."'> <!-- Flag/status value -->
                        
                        <!-- New dropdown to select which email to send to -->
                        <select name='selected_email' class='form-control'>
                            <option value='' disabled selected>Select Email</option>
                            <option value='". htmlspecialchars($row['rep_ofcr_email']) ."'>Rep Officer: ". htmlspecialchars($row['rep_ofcr_email']) ."</option>
                            <option value='". htmlspecialchars($row['hod_ro_email']) ."'>HOD RO: ". htmlspecialchars($row['hod_ro_email']) ."</option>
                        </select>

                        <input type='submit' value='Send Mail' class='btn btn-primary'>
                    </form>
                </td>
              </tr>";
    }
    echo "</table>";
    echo "</div>";
} else {
    echo "No programs found for " . ($selected_year !== 'ALL' ? $selected_year : 'All years');
}

// Free the result set
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