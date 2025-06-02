<?php 
// start a new session
// Allow any origin to access this resource

session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }

    $sessionemp= $_SESSION["emp_num"];

    // Add '00' in front if session value has only 6 digits
    if(strlen($sessionemp) == 6) {
        $sessionemp = '00' . $sessionemp;
    }

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
    <title>Training | Home</title>
    <link rel="icon" href="../images/analysis.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
 <style>
/* Style for the tab container */
/* Center the tabs */
.tab-container {
    display: flex;
    justify-content: center; /* Horizontally centers the tabs */
    margin-top: 20px;
}

/* Space between the tabs */
.tabs {
    display: flex;
    justify-content: center;
    gap: 15px; /* Adjust the gap between the tabs */
}

/* Custom styling for the active tab */
.btn.active {
    background-color: #007bff; /* Change to primary color when active */
    color: white;
    border-color: #007bff; /* Match the border color */
}

body{
    background-color: #e5ea96;
}

   </style>

</head>
<?php include 'header.php';?>
<h6><i class='fa fa-home'></i>&nbsp;<u><i>Home->Training Record & MIS->प्रशिक्षण</i></u></h6>
<div class="tab-container">
    <div class="tabs">
    <a href="training_dashboard.php"><div class="btn btn-outline-primary">Internal Online Training</div></a>
    <a href="external_training_dashboard.php"><div class="btn btn-outline-primary">External Online Training</div></a>
        <a href="training_validation.php"><div class="btn btn-outline-primary">Internal Classroom</div></a>
        <a href="external_classroom.php"><div class="btn btn-outline-primary">External Classroom</div></a>
        <a href="training_overall.php"><div class="btn btn-outline-primary active">Over All</div></a>
        <a href="index.html"><div class="btn btn-outline-primary">View Graph</div></a>
        <a href="total.php"><div class="btn btn-outline-primary">All Planned Program</div></a>
    </div>
</div>





<?php
// Step 1: Fetch distinct locations for the dropdown
$location_query = "SELECT DISTINCT loc_desc FROM [Complaint].[dbo].[emp_mas_sap]";
$location_result = sqlsrv_query($conn, $location_query);
$locations = [];

// Populate locations array
while ($location_row = sqlsrv_fetch_array($location_result, SQLSRV_FETCH_ASSOC)) {
    $locations[] = $location_row['loc_desc'];
}

// Step 2: Fetch attendance data based on selected location if any
$selected_location = isset($_POST['location']) ? $_POST['location'] : '';

// Build the attendance query based on selected location
$attendance_query = "
SELECT 
dept, 
dept_code,
MONTH(attend_date) AS month, 
SUM(total_attendance) AS total_attendance_sum, 
COUNT(total_attendance) AS total_attendance_count
FROM attendance_records 
WHERE flag in ('1','2','3') and act_Nact_flag = '1'
";

// Add location condition if a location is selected
if ($selected_location) {
    $attendance_query .= " AND location = ?";
}

// Group by dept and month
$attendance_query .= " GROUP BY dept_code, dept, MONTH(attend_date) ORDER BY dept_code, MONTH(attend_date);";

$params = $selected_location ? [$selected_location] : null;

$attendance_result = sqlsrv_query($conn, $attendance_query, $params);

if ($attendance_result === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Months array for y-axis - MOVED THIS UP BEFORE IT'S USED
$months = [
    4 => 'April', 
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    1 => 'January', 2 => 'February', 3 => 'March'
];

// Initialize arrays
$data = [];
$departments = [];
$attendance_totals = [];
$monthly_totals = array_fill_keys(array_keys($months), 0); // Now $months is defined
$grand_total = 0;

// Process attendance data
while ($row = sqlsrv_fetch_array($attendance_result, SQLSRV_FETCH_ASSOC)) {
    $dept_details = trim($row['dept']);
    $dept = trim($row['dept_code']);
    $month = $row['month'];
    $average_attendance = $row['total_attendance_sum'];
    
    // Store data
    $data[$dept][$month] = $average_attendance;
    $data[$dept]['dept_details'] = $dept_details;
    
    // Calculate monthly totals
    $monthly_totals[$month] += $average_attendance;
    $grand_total += $average_attendance;
    
    // Store total attendance for pie chart
    if (!isset($attendance_totals[$dept_details])) {
        $attendance_totals[$dept_details] = 0;
    }
    $attendance_totals[$dept_details] += $row['total_attendance_sum'];

    // Store unique departments for x-axis
    if (!in_array($dept, $departments)) {
        $departments[] = $dept;
    }
}

// Check if there are records to display
$has_records = !empty($data);
?>

<!-- HTML for Dropdown and Table -->
<form method="POST" action="">
    <h5><label for="location">&nbsp;<b>Select Location</b>&nbsp;:&nbsp;</label></h5>
    <select name="location" id="location" class="form-select form-select-sm w-auto mb-3" onchange="this.form.submit()">
        <option value="">All Locations</option>
        <?php foreach ($locations as $location): ?>
            <option value="<?php echo htmlspecialchars($location); ?>" <?php echo ($selected_location === $location) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($location); ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<h2>Training Dashboard</h2>

<!-- Table Section (100% width) -->
<div style="width: 100%; padding: 5px;">
    <?php if ($has_records): ?>
        <div style="overflow-y: auto; max-height: 550px;">
            <table class="table table-striped table-hover table-bordered border-success" border="2" cellpadding="10" cellspacing="0" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="background-color: pink;">Department</th>
                        <?php foreach ($months as $monthNum => $monthName): ?>
                            <th style="background-color: pink;"><?php echo $monthName; ?></th>
                        <?php endforeach; ?>
                        <th style="background-color: pink;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($departments as $dept): 
                        $dept_details = $data[$dept]['dept_details'];
                        $dept_total = 0;
                    ?>
                        <tr>
                            <td style="background-color: lightgray;">
                                <u><?php echo htmlspecialchars($dept) . ' (' . htmlspecialchars($dept_details) . ')'; ?></u>
                            </td>
                            <?php foreach ($months as $monthNum => $monthName): 
                                $attendance = isset($data[$dept][$monthNum]) ? $data[$dept][$monthNum] : 0;
                                $dept_total += $attendance;
                            ?>
                                <td>
                                    <?php if ($attendance > 0): ?>
                                        <a href="details.php?dept_code=<?php echo urlencode($dept); ?>&dept_details=<?php echo urlencode($dept_details); ?>&month=<?php echo $monthNum; ?>">
                                            <?php echo number_format($attendance, 2); ?>
                                        </a>
                                    <?php else: ?>
                                        0.00
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <td style="font-weight: bold;"><?php echo number_format($dept_total, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    
                    <!-- Total Row -->
                    <tr style="font-weight: bold; background-color: #d4edda;">
                        <td>Total (<?php echo number_format($grand_total, 2); ?>)</td>
                        <?php foreach ($months as $monthNum => $monthName): ?>
                            <td><?php echo number_format($monthly_totals[$monthNum], 2); ?></td>
                        <?php endforeach; ?>
                        <td><?php echo number_format($grand_total, 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No records found for the selected location.</p>
    <?php endif; ?>
</div>



<?php include '../footer.php';?>
