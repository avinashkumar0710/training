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

// First check auth before delete
$allowedEmpnos = ["99999999", "100031"];
if (!isset($_GET['auth']) || !in_array($_GET['auth'], $allowedEmpnos)) {
    echo "<script>alert('Unauthorized access. Redirecting...'); window.location.href = 'training_issue.php';</script>";
    exit;
}

// Now handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];

    $updateSQL = "UPDATE training_issue SET flag = 0 WHERE id = ?";
    $stmt = sqlsrv_prepare($conn, $updateSQL, [$deleteId]);

    if ($stmt) { // Check if the statement was prepared successfully
        if (sqlsrv_execute($stmt)) {
            echo "<script>alert('Issue Resolved successfully.'); window.location.href='view_issues.php?auth=" . $_GET['auth'] . "';</script>";
            exit;
        } else {
            echo "<script>alert('Failed to delete issue.');</script>";
            echo "<pre>";
            print_r(sqlsrv_errors());
            echo "</pre>";
        }
        sqlsrv_free_stmt($stmt); // Free the statement resource
    } else {
        echo "<script>alert('Failed to prepare the SQL statement.');</script>";
        echo "<pre>";
        print_r(sqlsrv_errors());
        echo "</pre>";
    }
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>All Training Issues</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h4 class="mb-0">Submitted Training Issues</h4>
        </div>
        <div class="card-body">
            <a href="training_issue.php" class="btn btn-outline-secondary mb-3">← Submit New Issue</a>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-secondary">
                        <tr>
                            <th>ID</th>
                            <th>Employee No</th>
                            <th>Issue</th>
                            <th>Plant</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $fetchSQL = "SELECT * FROM training_issue WHERE flag = 1 ORDER BY id DESC";
                        $result = sqlsrv_query($conn, $fetchSQL);

                        if ($result !== false && sqlsrv_has_rows($result)) {
                            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . $row['empno'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['issue']) . "</td>";
                                echo "<td>" . $row['plant'] . "</td>";
                                echo "<td>
                                <form method='post' action='view_issues.php?auth=" . urlencode($_GET['auth']) . "' 
                                      onsubmit='return confirm(\"Are you sure you want to delete this issue?\")'>
                                    <input type='hidden' name='delete_id' value='" . $row['id'] . "'>
                                    <button type='submit' class='btn btn-sm btn-danger'>Resolved</button>
                                </form>
                              </td>";
                        

                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='text-center'>No issues found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
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
