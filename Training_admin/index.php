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
    .height-adjust{
    height: 600px;
    overflow-y: auto;
    margin: 0px 50px;
    
    }
   </style>

</head>
<?php include 'header.php';?>
<h6><i class='fa fa-home'></i>&nbsp;<u><i>Home->Training Admin Data</i></u></h6>
<br>
<?php
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

// Fetch data from the database
$query = "
SELECT 
r.srl_no,
r.Program_name,
r.flag,
r.remarks,
r.duration,
r.id,
r.tentative_date,		
e.name,
e.loc_desc,
e.dept,
e.empno,
e.location,
w.dept_code
FROM 
[Complaint].[dbo].[request] r 
JOIN 
[Complaint].[dbo].[emp_mas_sap] e ON e.empno = r.empno
JOIN  
[Complaint].[dbo].[EA_webuser_tstpp] w ON 
    RIGHT(REPLICATE('0', 8) + CAST(w.emp_num AS VARCHAR(8)), 8) = e.empno
WHERE 
r.flag = '77'
";

$stmt = sqlsrv_query($conn, $query);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>


<body>

<div>  
      
    <?php
    // Reset statement and re-execute to fetch data
    sqlsrv_free_stmt($stmt);
    $stmt = sqlsrv_query($conn, $query);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Check if there is any data
    $hasData = false;
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $hasData = true;
        break;
    }
    ?>
    
    <form action="save_attendance.php" method="post">
        
        <div class="height-adjust">
            <?php
            if ($hasData) {
                // Reset statement and re-execute to fetch data again for display
                sqlsrv_free_stmt($stmt);
                $stmt = sqlsrv_query($conn, $query);
                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }

                echo '<table class="table table-bordered border-dark">';
                echo '<thead style="position: sticky; top: 0; background-color: beige; z-index: 1;">';
                echo '<tr class="bg-success border-dark">';
                echo '<th>Sl.</th>';
                echo '<th style=" display: none;">Empno</th>';
                echo '<th>Name</th>';
                echo '<th>Program Name</th>';
                echo '<th>Department</th>';
                echo '<th style=" display: none;">Depart Code</th>';
                echo '<th>Plant</th>';
                echo '<th>Tentative_Date</th>';
                echo '<th>Duration</th>';
                echo "<th>Total Attend</th>";
                

                // Calculate maximum duration
                $maxDuration = 0;
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $duration = intval(str_replace(' Day', '', $row['duration']));
                    if ($duration > $maxDuration) {
                        $maxDuration = $duration;
                    }
                }

                // Output headers up to maximum duration
                for ($i = 1; $i <= $maxDuration; $i++) {
                    echo "<th>Day $i</th>";
                }
                echo "<th>Total</th>"; // Add Total column
                echo '</tr>';
                echo '</thead>';
                echo '<tbody>';

                // Reset statement and re-execute to fetch data again for display
                sqlsrv_free_stmt($stmt);
                $stmt = sqlsrv_query($conn, $query);
                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }

                // Fetch and display rows
                $serialNo = 1;  
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    echo '<tr>';
                    echo "<td>{$serialNo}</td>";
                    echo '<td style=" display: none;">' . htmlspecialchars($row['empno']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['name']) . '</td>';                 
                    echo '<td>' . htmlspecialchars($row['Program_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['dept']) . '</td>';
                    echo '<td style=" display: none;">' . htmlspecialchars($row['dept_code']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['loc_desc']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['tentative_date']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['duration']) . '</td>';
                    echo "<td id='attend_count_{$row['id']}'>0</td>"; // Initially set to 0


                    // Output cells for each day up to the duration of this specific row
                    $duration = intval(str_replace(' Day', '', $row['duration']));
                    for ($i = 1; $i <= $maxDuration; $i++) {
                        echo '<td>';
                        if ($i <= $duration) {
                            echo "<select name='status[{$row['id']}][$i]' onchange='toggleAttendance(this, {$row['id']}, $i); updateAttendCount({$row['id']})'>";
                            echo '<option value="">Select</option>';
echo '<option value="1">Attend</option>';
echo '<option value="0">Not Attend</option>';
echo '</select>';

                            echo "<div id='attendance_{$row['id']}_$i'>";
                            echo "<select name='attendance[{$row['id']}][$i]' onchange='updateTotal({$row['id']})'>";
                            echo '<option value="">Select</option>';
                            echo '<option value="0.25">0.25</option>';
                            echo '<option value="0.50">0.50</option>';
                            echo '<option value="0.75">0.75</option>';
                            echo '<option value="1.00">1.00</option>';
                            echo '</select>';
                            echo "<input type='date' name='attendance_date[{$row['id']}][$i]' class='date-picker' />";
                            echo '</div>';
                        }
                        echo '</td>';
                    }
                    echo "<td id='total_{$row['id']}'>0.00</td>"; // Add total column with initial value
                    echo '</tr>';
                    $serialNo++;
                }
                echo '</tbody>';
                echo '</table>';
            } else {
                echo '<p>No data available.</p>';
            }
            ?>
        </div><br>
        <?php if ($hasData): ?>
           <center> <button class="btn btn-primary btn-lg" type="submit">Submit</button> </center>
        <?php endif; ?>
    </form>
</div>
<script>
function updateAttendCount(userId) {
    let attendCount = 0;

    // Select all "status" dropdowns for this user
    document.querySelectorAll(`select[name^='status[${userId}]']`).forEach(select => {
        if (select.value === "1") { // Count only "Attend"
            attendCount++;
        }
    });

    // Update the total attend count in the table
    document.getElementById(`attend_count_${userId}`).innerText = attendCount;
}
</script>


<script>
function toggleAttendance(selectElement, userId, day) {
    var attendanceDiv = document.getElementById('attendance_' + userId + '_' + day);
    if (selectElement.value == '0') {
        attendanceDiv.style.display = 'none';
        attendanceDiv.querySelector('select').value = '';
        updateTotal(userId);
    } else {
        attendanceDiv.style.display = 'block';
    }
}


function updateTotal(userId) {
    var total = 0;
    var attendCount = parseInt(document.getElementById('attend_count_' + userId).innerText) || 1; // Avoid division by 0

    // Sum up attendance fractions
    document.querySelectorAll(`select[name^='attendance[${userId}]']`).forEach(select => {
        if (select.value) {
            total += parseFloat(select.value);
        }
    });

    // Calculate the final value (total divided by total attend days)
    var result = total / attendCount;

    // Update the total attendance in the table
    document.getElementById('total_' + userId).innerText = result.toFixed(2);
}



document.addEventListener('DOMContentLoaded', function() {
    // Initialize attendance fields and totals
    var statusSelects = document.querySelectorAll('select[name^="status"]');
    statusSelects.forEach(function(select) {
        var userId = select.name.match(/status\[(\d+)\]/)[1];
        var day = select.name.match(/\[(\d+)\]$/)[1];
        toggleAttendance(select, userId, day);
    });

    var attendanceSelects = document.querySelectorAll('select[name^="attendance"]');
    attendanceSelects.forEach(function(select) {
        var userId = select.name.match(/attendance\[(\d+)\]/)[1];
        updateTotal(userId);
    });
});
</script>
</body>
</html>

<?php
// Close the connection
sqlsrv_close($conn);
?>


<?php include '../footer.php';?>