<?php
// Start Session
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
}

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
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training | Home</title>
    
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"  rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <style>
    body {
        font-weight: 600;
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        margin: 0;
        /* Remove default body margin */
        padding: 0;
        /* Remove default body padding */
        background-color: #e8eef3;
    }

    .scrollable1 {
        height: 760px;
        overflow-y: auto;
        border-color: black;
    }

    #dtBasicExample {
        border-radius: 25px;
        border: 2px solid yellowgreen;
    }

    .nav-link {
        color: #F8F9F9;
    }

    button[disabled] {
    background-color: red;
    cursor: not-allowed;
}

    </style>


</head>

<?php include '../header_HR.php';?>

<?php
// Initialize filter variables
$empNameFilter = $_GET['emp_name'] ?? '';
$programNameFilter = $_GET['program_name'] ?? '';
$plantFilter = $_GET['plant'] ?? '';

// Base SQL query
$sql = "SELECT 
r.srl_no, r.empno, r.Program_name, r.faculty, r.year, r.duration, r.target_group, r.hostel_book, r.id, r.aprroved_time, r.flag, r.appr_empno, r.plant,
e.emp_name 
FROM 
[Complaint].[dbo].[request] r
LEFT JOIN 
[Complaint].[dbo].[EA_webuser_tstpp] e
ON 
CAST(r.empno AS NVARCHAR) = e.emp_num
WHERE 
r.flag IN ('1', '3', '88', '000')";

// Add filters to the SQL query if provided
if (!empty($empNameFilter)) {
    $sql .= " AND e.emp_name LIKE ?";
}
if (!empty($programNameFilter)) {
    $sql .= " AND r.Program_name LIKE ?";
}
if (!empty($plantFilter)) {
    $sql .= " AND r.plant = ?";
}

// Prepare and execute the SQL query
$params = [];
if (!empty($empNameFilter)) {
    $params[] = "%$empNameFilter%";
}
if (!empty($programNameFilter)) {
    $params[] = "%$programNameFilter%";
}
if (!empty($plantFilter)) {
    $params[] = $plantFilter;
}

$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Mapping flag values to readable statuses
$flagStatus = [
    '1'   => 'Rejected by Reporting Officer',
    '3'   => 'Rejected by HOD',
    '88'  => 'Rejected by HR',
    '000' => 'Pending'
];
?>

<!-- Filter Form -->
<div class="container-fluid">
    <h2 class="text-center">Rejected & Pending Requests</h2>
    <form method="GET" action="">
        <div class="row mb-3">
            <!-- <div class="col-md-3">
                <input type="text" name="emp_name" class="form-control" placeholder="Filter by Employee Name" value="<?= htmlspecialchars($empNameFilter) ?>">
            </div>
            <div class="col-md-3">
                <input type="text" name="program_name" class="form-control" placeholder="Filter by Program Name" value="<?= htmlspecialchars($programNameFilter) ?>">
            </div> -->
            <div class="col-md-3">
                <select name="plant" class="form-control">
                    <option value="">Filter by Plant</option>
                    <option value="NS04" <?= ($plantFilter == 'NS04') ? 'selected' : '' ?>>Bhilai</option>
                    <option value="NS03" <?= ($plantFilter == 'NS03') ? 'selected' : '' ?>>Rourkela</option>
                    <option value="NS02" <?= ($plantFilter == 'NS02') ? 'selected' : '' ?>>Durgapur</option>
                    <option value="NS01" <?= ($plantFilter == 'NS01') ? 'selected' : '' ?>>Corporate Center</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <a href="?" class="btn btn-secondary">Clear Filters</a>
            </div>
        </div>
    </form>

    <!-- Table to display data -->
    <div class="table-responsive" style="height: 680px; overflow: auto;">
        <table class="table table-bordered table table-striped border-success" border="3">
            <thead style="position: sticky; top: 0; background-color: burlywood; z-index: 1;">
                <tr>
                     <th>Srl No</th> 
                    <th>ProgramID</th>                   
                    <th>Emp Name</th>
                    <th>Program Name</th>                    
                    <th>Year</th>                   
                    <th>Status</th>                   
                    <th>Duration</th>                   
                    <th>Target Group</th>                   
                    <th>Rejected Time</th>                
                    <th>Hostel Book</th>
                    <th>Plant</th>
                    <th style="display:none">ID</th>
                </tr>
            </thead>
            <tbody>
                <?php  
                $serialNo = 1;
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)): ?>
                    <tr>
                        <td><?= $serialNo ?></td> 
                        <td><?= $row['srl_no'] ?></td>                       
                        <td><?= $row['emp_name'] ?? 'N/A' ?></td>
                        <td><?= $row['Program_name'] ?></td>                        
                        <td><?= $row['year'] ?></td>                       
                        <td style="color:red;"><?= $flagStatus[$row['flag']] ?? 'Unknown' ?></td>                       
                        <td><?= $row['duration'] ?></td>                        
                        <td><?= $row['target_group'] ?></td>                      
                        <td><?= $row['aprroved_time'] ? $row['aprroved_time']->format('Y-m-d H:i:s') : '' ?></td>                      
                        <td><?= ($row['hostel_book'] == 1) ? 'Yes' : 'No' ?></td>
                        <?php
                        // Mapping plant codes to readable names
                        $plantNames = [
                            'NS04' => 'Bhilai',
                            'NS03' => 'Rourkela',
                            'NS02' => 'Durgapur',
                            'NS01' => 'Corporate Center'
                        ];
                        ?>
                        <td><?= $plantNames[$row['plant']] ?? $row['plant'] ?></td>
                        <td style="display:none"><?= $row['id'] ?></td>
                    </tr>
                    
                <?php  $serialNo++; endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
<?php include '../footer.php';?>