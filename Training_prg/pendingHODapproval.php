<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
    // Redirect to login.php if 'emp_num' is not set
    header("location:login.php");
    exit; // Ensure no further code is executed after the redirect
}

// Get the session value
$sessionemp = $_SESSION["emp_num"];
//echo "Session Employee Number: " . $sessionemp . "<br>";

// Add '00' in front if the session value has exactly 6 digits
if (strlen($sessionemp) === 6) {
    $sessionemp1 = '00' . $sessionemp;
} else {
    $sessionemp1 = $sessionemp; // If not 6 digits, keep the original value
}

//echo "Modified Employee Number: " . $sessionemp1 . "<br>";

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

// Handle form submission to update the flag
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_ids'])) {
    $selectedIds = $_POST['selected_ids'];
    foreach ($selectedIds as $id) {
        $updateQuery = "UPDATE [Complaint].[dbo].[request] SET flag = '66' WHERE id = ?";
        $params = [$id];
        $stmt = sqlsrv_query($conn, $updateQuery, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    }
    $_SESSION['success_message'] = "Successfully Approved!";
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
    ?> 
    <!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" sizes="32x32" href="employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
   
       
    <title>Pending List from HOD for going approval of BUH</title>
    <style> 
    
        body
        {
             background-color: #e8eef3;
            font-weight: 600;
            font-style: normal;
            font-family: "Nunito Sans", sans-serif;
        }
        .scrollable-table {
            height: 580px;
            overflow-y: auto;
            border-color: black;            
        }

        .scrollable1 {
            height: 760px;
            overflow-y: auto;
            border-color: black;
        }

        .row{
            padding:0px;
            display: flex;
            justify-content: space-between;
            max-width:100%;
        }

        .checkbox-container {
            display: inline-block;
            position: relative;
            padding-left: 15px;
            margin-bottom: 15px;
            cursor: pointer;
        }

        .checkbox-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .checkmark {
            position: absolute;
            top: 5;
            left: 20;
            height: 25px;
            width: 25px;
            background-color: #fff;
            border: 1px solid #ccc;
        }

        .checkbox-container input:checked + .checkmark {
            background-color: #28a745; /* Set the color you want when the checkbox is checked */
            border-color: #28a745; /* Set the border color you want when the checkbox is checked */
        }

      
        th {color: white;}

        table table-bordered border-success tbody thead{
            width:40%;
        } 

</style>
</head>
<?php include 'header.php';?>
<h6><i class='fa fa-home'></i>&nbsp;<u><i>Home->HOD Confirmation approval for going approval of BUH</i></u></h6>

<?php 

// SQL Query
//echo $sessionemp;
$sql = "
WITH EmployeeHierarchy AS (
    SELECT 
        emp.empno AS EmployeeID,
        emp.name AS EmployeeName,
        emp.design AS Designation,
        emp.rep_ofcr AS ReportsTo,
        1 AS HierarchyLevel
    FROM [Complaint].[dbo].[emp_mas_sap] emp
    WHERE emp.empno = '$sessionemp'

    UNION ALL

    SELECT 
        e.empno AS EmployeeID,
        e.name AS EmployeeName,
        e.design AS Designation,
        e.rep_ofcr AS ReportsTo,
        eh.HierarchyLevel + 1 AS HierarchyLevel
    FROM [Complaint].[dbo].[emp_mas_sap] e
    INNER JOIN EmployeeHierarchy eh ON e.rep_ofcr = eh.EmployeeID
)
SELECT 
    eh.EmployeeID,
    eh.EmployeeName,
    eh.Designation,
    eh.ReportsTo,
    eh.HierarchyLevel,
    req.empno,
    req.flag,
    req.srl_no,
    req.id, req.[Program_name],req.[Faculty],req.[nature_training],req.[year],req.duration,req.tentative_date,req.target_group,req.remarks,req.hostel_book
FROM EmployeeHierarchy eh
INNER JOIN [Complaint].[dbo].[request] req ON eh.EmployeeID = req.empno
WHERE req.flag = '4'
ORDER BY eh.HierarchyLevel, eh.ReportsTo, eh.EmployeeName;
";

// Execute the query
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true)); // Handle query errors
}

// Generate the HTML table
echo "<p>Note :  This is Final Approval from Head HOD Department. After Aproval it goes to HR For Approval.</p>";
echo "<form method='POST' action=''>";
echo "<br><div class='container'>";
echo "<div class='scrollable-table'>";
echo "<table class='table table-bordered border-success' border='3' style='width:100%; border-collapse: collapse;'>";
echo "<thead style='position: sticky; top: 0; background-color: green;z-index: 1;'> 
        <tr>
           
            <th>Employee Name</th>
            <th>Program Name</th>
            <th>Faculty</th>
            <th>Nature of Training</th>
            <th>Year</th>
            <th>Duration</th>
            <th>Tentative Date</th>            
            <th>Target Group</th>
           
            <th>Remarks</th>
            <th>Select</th>
        </tr>
      </thead>";
echo "<tbody>";

// Fetch and display data
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo "<tr>
            
            <td>{$row['EmployeeName']}</td>
            <td>{$row['Program_name']}</td>
            <td>{$row['Faculty']}</td>
            <td>{$row['nature_training']}</td>
            <td>{$row['year']}</td>
            <td>{$row['duration']}</td>
            <td>{$row['tentative_date']}</td>
            <td>{$row['target_group']}</td>        
            <td>{$row['remarks']}</td>
            <td><input type='checkbox' name='selected_ids[]' value='{$row['id']}'></td>
          </tr>";
}

echo "</tbody>";
echo "</table>";

echo "</form>";
echo "<button type='submit' class='btn btn-success' style='margin-top: 10px; padding: 5px 10px;'>Submit</button>";

// Free resources and close connection
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>

<?php include '../footer.php';?>
<?php if (isset($_SESSION['success_message'])): ?>
        <script>
            alert("<?php echo $_SESSION['success_message']; ?>");
        </script>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
</html>