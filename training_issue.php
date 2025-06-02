<?php
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);

$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $empno = $_POST["empno"];
    $issue = $_POST["issue"];
    $plant = $_POST["plant"];

    $sql = "INSERT INTO training_issue (empno, issue, plant) VALUES (?, ?, ?)";
    $params = array($empno, $issue, $plant);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        $message = "<div class='alert alert-danger'>Error inserting data: " . print_r(sqlsrv_errors(), true) . "</div>";
    } else {
        $message = "<div class='alert alert-success'>Issue submitted successfully. Soon it will be resolved.</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<link rel="icon" type="image/png" sizes="32x32" href="employee.ico">
    <title>Training Issue Form</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
    function checkEmpNumberBeforeView() {
        var empno = prompt("Enter your Employee Number to view issues:");

        if (empno === "99999999" || empno === "100031") {
            window.location.href = "view_issues.php?auth=" + empno;
        } else if (empno === null || empno === "") {
            alert("Employee number is required.");
        } else {
            alert("Access denied. You are not authorized to view issues.");
        }
    }
</script>

</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Submit Training Issue</h4>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            <form method="post" action="">
                <div class="mb-3">
                    <label for="empno" class="form-label">Employee No:</label>
                    <input type="text" name="empno" id="empno" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="issue" class="form-label">Issue:</label>
                    <textarea name="issue" id="issue" rows="4" class="form-control" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="plant" class="form-label">Plant:</label>
                    <select name="plant" id="plant" class="form-select" required>
                        <option value="">-- Select Plant --</option>
                        <option value="NS04">Bhilai</option>
                        <option value="NS03">Rourkela</option>
                        <option value="NS02">Durgapur</option>
                        <option value="NS01">CC</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Submit Issue</button>
                <button class="btn btn-outline-primary ms-2" onclick="checkEmpNumberBeforeView()">View Submitted Issues</button>
            </form>
        </div>
    </div>
</div>

</body>
<style>
  #small a{ color: white;}
</style>

<footer class="fixed-bottom" style='background-color: #34495E;'>
<div class="card-footer bg-transparent border-success">
<center>
  <div id="small">
<!-- <small class="footer" style="color:white;"><a href="">OCMS Help</a> | <a href="feedback.php"> OCMS Feedback</a>  </small><br> -->
<!-- <small class="footer" style="color:white;"><a href="ocms_user_guide.pdf" target="_blank">OCMS Help</a> | <a href="feedback.php"> OCMS Feedback</a></small><br> -->

<small class="" style="color:white;">Developed by IT department © <script type="text/javascript">document.write( new Date().getFullYear() );</script> all rights reserved by NSPCL |
 <a class='nav-link' href='login.php'>Go to Loginpage</a>
 
</small></div>
</center> 
  </div>
  </footer>
</html>
