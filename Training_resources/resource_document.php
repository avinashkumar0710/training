<!DOCTYPE html>
<html>

<head>
    <title>Training Resources document Uploaded by Employee</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .table-container {
            margin-top: 30px;
        }
    </style>
</head>
<?php include 'header.php';?>
<?php
// Database connection
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "Complaint",
    "Uid" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch data
$sql = "SELECT pf.[feedback_id], pf.[program_title], pf.[file_path], pf.[emp_num], pf.[created_at], 
em.[name], em.[grade],   ar.[from_date],
    ar.[to_date], ar.[training_location]
FROM [Complaint].[dbo].[program_feedback] pf
LEFT JOIN [Complaint].[dbo].[emp_mas_sap] em ON pf.emp_num = em.empno
LEFT JOIN [Complaint].[dbo].[attendance_records] ar ON pf.emp_num = ar.empno";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<h6><i class='fa fa-home'></i>&nbsp;<u><i>Training Resources->Training Resources document Uploaded by Employee</i></u></h6>
<body>
<div class="container">

<table  class="table table-bordered" border="1" cellpadding="8" cellspacing="0">
    <thead style="position: sticky; top: 0; background-color: beige;">
        <tr style="font-weight: bold;">
        <th>Sl. No</th>
              
                <th>Program Name</th>
                <th>Uploaded By(Emp No)</th>
                <th>Uploaded By(Emp Name)</th>
                <th>Grade</th>
                <th>Programm Attend at</th>
                <th>Program From</th>
                <th>Program To</th>
                <th>Date of Upload</th>
                <th>File</th>
        </tr>
    </thead>
    <tbody>
    <?php
            $serialNo = 1; // Initialize serial number
            while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                <tr>
                    <td><?php echo $serialNo++; ?></td>
                    <td><?php echo htmlspecialchars($row['program_title']); ?></td>
                   
                    <td><?php echo htmlspecialchars($row['emp_num']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['grade']); ?></td>
                    <td><?php echo htmlspecialchars($row['training_location']); ?></td>
                    <td><?php echo htmlspecialchars($row['from_date']->format('Y-m-d')); ?></td>
                    <td><?php echo htmlspecialchars($row['to_date']->format('Y-m-d')); ?></td>
                     <td><?php echo htmlspecialchars($row['created_at']->format('Y-m-d H:i:s')); ?></td>
                    <td>
                        <?php if (!empty($row['file_path'])): ?>
                            <a href="http://192.168.100.9:8080/training/Training_feedback/<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank"><i class="fa fa-file-pdf-o" style="font-size:28px;color:red"></i></a>
                        <?php else: ?>
                            No File
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
    </tbody>
</table>
</div>
</body>

<?php
sqlsrv_close($conn);
?>
<?php include 'footer.php';?>