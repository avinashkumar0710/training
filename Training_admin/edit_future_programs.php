<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById("selectAll").addEventListener("click", function () {
    var checkboxes = document.querySelectorAll(".select-item");
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});
</script>

<style>
    body{
        font-family: "Nunito", sans-serif;
        font-optical-sizing: auto; 
        font-style: normal;
        background-color: #f4f6f9;
        margin: 0;
        padding: 20px;    
    }
    .hide-column {
        display: none;
    }
</style>


<?php
// Database Connection
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Fetch Records
$sql = "SELECT record_id, name, dept, location, program_id, program_name, duration, total_attendance, 
        attend_date, empno, training_location, from_date, to_date, mandays, nature_of_training, 
        training_subtype, training_mode, attendance, faculty
        FROM [Complaint].[dbo].[attendance_records]
        WHERE flag='3' AND mandays='0' AND attendance='NA' AND faculty like ('%None%')";

$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}



// Display Data in Table with Update Form
echo '<form method="post" action="update_records.php">';
echo '<div style="max-height: 700px; overflow-y: auto;">';
echo '<table class="table table-bordered border-dark" border="1">';
echo '<thead class="bg-success border-dark" style="position: sticky; top: 0; z-index: 1; background-color: #198754;">';
echo '<tr>
        
        <th>Record ID</th>
        <th>Name</th>
        <th>Dept</th>
        <th>Location</th>
        <th>Program ID</th>
        <th>Program Name</th>
        <th>Duration</th>
        <th class="hide-column">Total Attendance</th> 
        <th class="hide-column">Attend Date</th>
        <th>Training Location</th>
        <th>From Date</th>
        <th>To Date</th>
        <th>Mandays</th>
        <th>Nature of Training</th>
        <th>Training Subtype</th>
        <th>Training Mode</th>
        <th>Attendance</th>
        <th>Faculty</th>
        <th><input type="checkbox" id="selectAll">All Select</th> <!-- Master Select-All Checkbox -->
      </tr>
      </thead>';

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo '<tr>
            
            <td>' . htmlspecialchars($row['record_id']) . '</td>
            <td>' . htmlspecialchars($row['name']) . '</td>
            <td>' . htmlspecialchars($row['dept']) . '</td>
            <td>' . htmlspecialchars($row['location']) . '</td>
            <td>' . htmlspecialchars($row['program_id']) . '</td>
            <td>' . htmlspecialchars($row['program_name']) . '</td>
            <td>' . htmlspecialchars($row['duration']) . '</td>
            <td class="hide-column">' . htmlspecialchars($row['total_attendance']) . '</td>
            <td class="hide-column">' . htmlspecialchars($row['attend_date']->format('Y-m-d')) . '</td>
            <td>' . htmlspecialchars($row['training_location']) . '</td>
            <td>' . htmlspecialchars($row['from_date']->format('Y-m-d')) . '</td>
            <td>' . htmlspecialchars($row['to_date']->format('Y-m-d')) . '</td>
            <td><input type="text" name="mandays[' . $row['record_id'] . ']" value="' . htmlspecialchars($row['mandays']) . '" min="0"></td>

            <td>' . htmlspecialchars($row['nature_of_training']) . '</td>
            <td>' . htmlspecialchars($row['training_subtype']) . '</td>
            <td>' . htmlspecialchars($row['training_mode']) . '</td>
            <td>
                <select name="attendance[' . $row['record_id'] . ']">
                    <option value="NA" ' . ($row['attendance'] == "NA" ? "selected" : "") . '>NA</option>
                    <option value="A" ' . ($row['attendance'] == "A" ? "selected" : "") . '>A</option>
                </select>
            </td>
            <td><input type="text" name="faculty[' . $row['record_id'] . ']" value="' . htmlspecialchars($row['faculty']) . '"></td>
            <td><input type="checkbox" class="select-item" name="selected_records[]" value="' . $row['record_id'] . '"></td>
          </tr>';
}

echo '</table>';
echo '</div>';
echo '<br>';
echo '<input type="submit" class="btn btn-success" name="update_selected" value="Update Selected">';
echo '</form>';

// Free resources
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>




