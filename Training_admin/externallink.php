<?php 
// Start a new session
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
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <style>
        .height-adjust {
            height: 600px;
            overflow-y: auto;
            margin: 0px 50px;
        }
    </style>
</head>

<body>
<?php include 'header.php'; ?>
<h6><i class='fa fa-home'></i>&nbsp;<u><i>Home->Online Training Attendance Validation</i></u></h6>
<br>

<?php
// Fetch data from the database
$query = "
SELECT DISTINCT
    lt.[id],
    lt.[user_id],
    lt.[Title], 
    lt.[target_group],
    lt.[faculty],
    lt.[Duration],
    lt.[Internal_External],
    lt.[open_time],
    lt.[close_time],
    lt.[nature_of_training],
    lt.[remarks],
    ls.[click_time],
    ls.[program_id],
    ls.flag,
    ls.[link],
    ls.[id] as actual_id,
    emp.[name] AS emp_name,
    emp.[dept]
FROM 
    [Complaint].[dbo].[link_tracking] lt
JOIN 
    [Complaint].[dbo].[link_show] ls ON lt.[id] = ls.[program_id]
JOIN 
    [Complaint].[dbo].[emp_mas_sap] emp ON ls.[empno] = emp.[empno] -- Joining with emp_mas_sap on empno
where ls.flag = '77'
ORDER BY 
    lt.[id] DESC
";

$stmt = sqlsrv_query($conn, $query);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$hasData = sqlsrv_has_rows($stmt);
?>

<div>
    <form action="external_save_attendance.php" method="post">
        <div class="height-adjust">
            <?php
            if ($hasData) {
                echo '<table class="table table-bordered border-dark">';
                echo '<thead style="position: sticky; top: 0; background-color: beige; z-index: 1;">';
                echo '<tr class="bg-success border-dark">';
                echo '<th>Sl.</th>';
                echo '<th>Name</th>';
                echo '<th>Department</th>';
                echo '<th>Program Name</th>';
                echo '<th>Target Group</th>';
                echo '<th>Faculty</th>';
                echo '<th>Open Time</th>';
                echo '<th>Close Time</th>';
                echo '<th>Duration</th>';

                // Calculate maximum duration
                $maxDuration = 0;
                $durations = [];

                // Reset statement and re-execute to calculate maximum duration
                sqlsrv_free_stmt($stmt);
                $stmt = sqlsrv_query($conn, $query);
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $duration = intval(str_replace('Days', '', $row['Duration']));
                    $durations[] = $duration;
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
                //echo 'maxduration :' .$maxDuration;
                // Reset statement and re-execute to fetch data again for display
                sqlsrv_free_stmt($stmt);
                $stmt = sqlsrv_query($conn, $query);

                // Fetch and display rows
                $serialNo = 1;
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    echo '<tr>';
                    echo "<td>{$serialNo}</td>";
                    echo '<td>' . htmlspecialchars($row['emp_name']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['dept']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['Title']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['target_group']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['faculty']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['open_time']->format('Y-m-d H:i:s')) . '</td>';
                    echo '<td>' . htmlspecialchars($row['close_time']->format('Y-m-d H:i:s')) . '</td>';
                    echo '<td>' . htmlspecialchars($row['Duration']) . '</td>';
                
                    // Output cells for each day up to the duration of this specific row
                    $duration = intval(str_replace(' days', '', $row['Duration']));
                    for ($i = 1; $i <= $maxDuration; $i++) {
                        echo '<td>';
                        if ($i <= $duration) {
                            // Ensure the actual_id is used correctly
                            $actualId = $row['actual_id'];
                            echo "<select name='status[{$actualId}][$i]' onchange='toggleAttendance(this, {$actualId}, $i)'>";
                            echo '<option value="1">Attend</option>';
                            echo '<option value="0">Not Attend</option>';
                            echo '</select>';
                            
                            // Hidden by default unless "Attend" is selected
                            echo "<div id='attendance_{$actualId}_$i' style='display:none;'>"; 
                            echo "<select name='attendance[{$actualId}][$i]' onchange='updateTotal({$actualId})'>";
                            echo '<option value="">Select</option>';
                            echo '<option value="0.25">0.25</option>';
                            echo '<option value="0.50">0.50</option>';
                            echo '<option value="0.75">0.75</option>';
                            echo '<option value="1.00">1.00</option>';
                            echo '</select>';
                            echo "<input type='date' name='attendance_date[{$actualId}][$i]' class='date-picker' />";
                            echo '</div>';
                        }
                        echo '</td>';
                    }
                
                    echo "<td id='total_{$actualId}'>0.00</td>"; // Add total column
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
            <center><button class="btn btn-primary btn-lg" type="submit">Submit</button></center>
        <?php endif; ?>
    </form>
</div>

<script>
function toggleAttendance(selectElement, actualId, day) {
    console.log('ActualId:', actualId, 'Day:', day); // Debugging log
    var attendanceDiv = document.getElementById('attendance_' + actualId + '_' + day);
    if (selectElement.value == '0') {
        attendanceDiv.style.display = 'none';
        attendanceDiv.querySelector('select').value = ''; // Clear the value
        updateTotal(actualId); // Recalculate total after hiding
    } else {
        attendanceDiv.style.display = 'block';
    }
}

function updateTotal(actualId) {
    var total = 0;

    // Select all attendance selects related to this actualId
    var attendanceSelects = document.querySelectorAll('select[name^="attendance[' + actualId + ']"]');
    
    // Sum up the values of selected attendance options
    attendanceSelects.forEach(function(select) {
        if (select.value) {
            total += parseFloat(select.value);
        }
    });

    // Update the total display for this actualId
    document.getElementById('total_' + actualId).innerText = total.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    // Initialize attendance fields and totals
    var statusSelects = document.querySelectorAll('select[name^="status"]');
    
    statusSelects.forEach(function(select) {
        // Extract the actualId and day from the select name (e.g., status[actualId][day])
        var actualId = select.name.match(/status\[(\d+)\]/)[1];
        var day = select.name.match(/\[(\d+)\]$/)[1];

        // Set the initial visibility based on the selected value
        toggleAttendance(select, actualId, day);
    });

    // Add event listeners to attendance selects to update total on change
    var attendanceSelects = document.querySelectorAll('select[name^="attendance"]');
    attendanceSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            var actualId = select.name.match(/attendance\[(\d+)\]/)[1];
            updateTotal(actualId);
        });
    });
});

</script>
</body>
</html>
<?php include '../footer.php';?>
