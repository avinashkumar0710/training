<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
}
$sessionemp = $_SESSION["emp_num"];

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
    <title>Training | Home</title>
    <link rel="icon" href="../images/analysis.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>
<body>
    <?php include 'header.php';?>
<div class="container-fluid">
  

    <br>
<?php 
// Dropdown query
$sql_dropdown = "SELECT DISTINCT srl_no, program_title FROM [Complaint].[dbo].[program_feedback]";
$stmt_dropdown = sqlsrv_query($conn, $sql_dropdown);

// Check for errors
if ($stmt_dropdown === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Display the dropdown
echo '<form method="POST" action="">';
echo '<label for="srl_no">Select Program:</label>';
echo '<select name="srl_no" id="srl_no">';
while ($row = sqlsrv_fetch_array($stmt_dropdown, SQLSRV_FETCH_ASSOC)) {
    echo '<option value="' . htmlspecialchars($row['srl_no']) . '">' . htmlspecialchars($row['program_title']) . '</option>';
}

echo '</select>';
echo '&nbsp;&nbsp;<button type="submit" class="btn btn-primary">Submit</button>';
echo '</form>';

// Show Download Excel button only if a program is selected
if (isset($_POST['srl_no'])) {
    echo '<form action="export_feedback.php" method="post" style="margin-top: 15px;">';
    echo '<input type="hidden" name="srl_no" value="' . htmlspecialchars($_POST['srl_no']) . '">';
    echo '<button type="submit" class="btn btn-success"><i class="fa fa-download"></i> Download Excel</button>';
    echo '</form>';
}
echo '<br>';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['srl_no'])) {
    $selected_srl_no = $_POST['srl_no'];

    // SQL query to fetch program feedback data with emp_name
    $sql_feedback = "
    SELECT 
        pf.feedback_id,
        pf.emp_num,
        ew.emp_name,
        pf.srl_no,
        
        pf.program_title,
        pf.program_duration,
        pf.faculty,
        pf.overall_objectives,
        pf.content_depth,
        pf.program_duration_feedback,
        pf.relevance,
        pf.program_coordinated,
        pf.faculty_feedback,
        pf.hospitality_arrangements,
        pf.administrative_arrangements,
        pf.stay_arrangements,
        pf.suggestion,
        pf.created_at,
        pf.file_path
    FROM 
        [Complaint].[dbo].[program_feedback] pf
    JOIN 
        [Complaint].[dbo].[EA_webuser_tstpp] ew ON pf.emp_num = ew.emp_num
    WHERE 
        pf.srl_no = ? and ew.status !='S' and ew.status !='O'";
    
    // Prepare and execute the feedback query
    $params = array($selected_srl_no);
    $stmt_feedback = sqlsrv_query($conn, $sql_feedback, $params);

    // Check for errors
    if ($stmt_feedback === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Display results
    echo '<table class="table table-bordered border-success">';
    echo '<thead style="position: sticky; top: 0; background-color: beige; z-index: 1;">';
    echo '<tr class="bg-primary" style="color:#ffffff">';
    // echo '<th>Feedback ID</th>';
    
    echo '<th>SL No</th>';
    echo '<th>Employee Name</th>';
    echo '<th>Employee Number</th>';
    // echo '<th>Request ID</th>';
    echo '<th>Program Title</th>';
    echo '<th>Program Duration</th>';
    echo '<th>Faculty Name</th>';
    echo '<th>Overall Objectives</th>';
    echo '<th>Content Depth</th>';
    echo '<th>Program Duration Feedback</th>';
    echo '<th>Relevance</th>';
    echo '<th>Program Coordinated</th>';
    echo '<th>Faculty Feedback</th>';
    echo '<th>Hospitality Arrangements</th>';
    echo '<th>Administrative Arrangements</th>';
    echo '<th>Stay Arrangements</th>';
    echo '<th>Suggestion</th>';
    echo '<th>Submit Date</th>';
    echo '<th>PDF</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    $serial = 1; // Initialize serial number
    // Fetch and display the feedback data
    while ($row = sqlsrv_fetch_array($stmt_feedback, SQLSRV_FETCH_ASSOC)) {
        echo '<tr>';
        // echo '<td>' . htmlspecialchars($row['feedback_id']) . '</td>';
        echo '<td>' . $serial++ . '</td>'; 
        echo '<td>' . htmlspecialchars($row['emp_name']) . '</td>';
        echo '<td>' . htmlspecialchars($row['emp_num']) . '</td>';
        // echo '<td>' . htmlspecialchars($row['srl_no']) . '</td>';
        // echo '<td>' . htmlspecialchars($row['request_id']) . '</td>';
        echo '<td>' . htmlspecialchars($row['program_title']) . '</td>';
        echo '<td>' . htmlspecialchars($row['program_duration']) . '</td>';
        echo '<td>' . htmlspecialchars($row['faculty']) . '</td>';
        echo '<td>' . htmlspecialchars($row['overall_objectives']) . '</td>';
        echo '<td>' . htmlspecialchars($row['content_depth']) . '</td>';
        echo '<td>' . htmlspecialchars($row['program_duration_feedback']) . '</td>';
        echo '<td>' . htmlspecialchars($row['relevance']) . '</td>';
        echo '<td>' . htmlspecialchars($row['program_coordinated']) . '</td>';
        echo '<td>' . htmlspecialchars($row['faculty_feedback']) . '</td>';
        echo '<td>' . htmlspecialchars($row['hospitality_arrangements']) . '</td>';
        echo '<td>' . htmlspecialchars($row['administrative_arrangements']) . '</td>';
        echo '<td>' . htmlspecialchars($row['stay_arrangements']) . '</td>';
        echo '<td>' . htmlspecialchars($row['suggestion']) . '</td>';
        $createdAt = $row['created_at'] instanceof DateTime ? $row['created_at'] : new DateTime($row['created_at']);
        echo '<td>' . htmlspecialchars($createdAt->format('d-m-Y')) . '</td>'; // Change 'H:i' to your desired format
        echo "<td><a href='{$row['file_path']}' target='_blank'><i class='fa fa-file-pdf-o text-danger' style='font-size: 20px;'></i></a></td>";

        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    // Free resources
    sqlsrv_free_stmt($stmt_feedback);
}

// Free resources
sqlsrv_free_stmt($stmt_dropdown);
sqlsrv_close($conn);
?>

</div>
    <?php include 'footer.php';?>
</body>
</html>
