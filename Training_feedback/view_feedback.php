<?php
session_start();
if (!isset($_SESSION["emp_num"])) {
    header("location:login.php");
}
$sessionemp = $_SESSION["emp_num"];

$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$query = "
    SELECT 
        pf.[feedback_id],
        pf.[program_title],
        pf.[program_duration],
        pf.[faculty],
        pf.[overall_objectives],
        pf.[content_depth],
        pf.[program_duration_feedback],
        pf.[relevance],
        pf.[program_coordinated],
        pf.[faculty_feedback],
        pf.[hospitality_arrangements],
        pf.[administrative_arrangements],
        pf.[stay_arrangements],
        pf.[created_at],
        pf.[file_path]
    FROM 
        [Complaint].[dbo].[program_feedback] pf
    WHERE 
        pf.[emp_num] = ?
";
$params = array($sessionemp);
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Feedback</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>   <!---scroll javascript---->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<script>
    function goBack() {
        window.location.href = "http://192.168.100.9:8080/training/Training_feedback/index.php";
    }
    </script>
<body>
<div class="container">
    <br>
<div style="display: flex; align-items: center;">
    <i class="fa fa-arrow-circle-left" style="font-size: 48px; color: red; cursor: pointer; margin-right: 10px;" onclick="goBack()"></i>
    <h2 style="margin: 0;">Your Feedback</h2> 
</div><br>
    <table class="table table-bordered">
        <thead style="position: sticky; top: 0; background-color: beige;">
            <tr style="font-weight: bold;">
                <th>Program Title</th>
                <th>Program Duration</th>
                <th>Faculty</th>
                <th>Overall Objectives</th>
                <th>Content & Depth</th>
                <th>Program Duration Feedback</th>
                <th>Relevance</th>
                <th>Program Coordinated</th>
                <th>Faculty Feedback</th>
                <th>Hospitality Arrangements</th>
                <th>Administrative Arrangements</th>
                <th>Stay Arrangements</th>
                <th>Created At</th>
                <th>PDF</th>
            </tr>
        </thead>
        <tbody>
        <?php
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['program_title']) . "</td>";
            echo "<td>" . htmlspecialchars($row['program_duration']) . "</td>";
            echo "<td>" . htmlspecialchars($row['faculty']) . "</td>";
            echo "<td>" . htmlspecialchars($row['overall_objectives']) . "</td>";
            echo "<td>" . htmlspecialchars($row['content_depth']) . "</td>";
            echo "<td>" . htmlspecialchars($row['program_duration_feedback']) . "</td>";
            echo "<td>" . htmlspecialchars($row['relevance']) . "</td>";
            echo "<td>" . htmlspecialchars($row['program_coordinated']) . "</td>";
            echo "<td>" . htmlspecialchars($row['faculty_feedback']) . "</td>";
            echo "<td>" . htmlspecialchars($row['hospitality_arrangements']) . "</td>";
            echo "<td>" . htmlspecialchars($row['administrative_arrangements']) . "</td>";
            echo "<td>" . htmlspecialchars($row['stay_arrangements']) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']->format('Y-m-d H:i:s')) . "</td>";
            echo "<td>" . htmlspecialchars($row['file_path']) . "</td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
<?php include '../footer.php';?>