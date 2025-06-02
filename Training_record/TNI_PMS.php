<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp=$_SESSION["emp_num"];
   //echo $sessionemp;

$serverName = "NSPCL-AD\SQLEXPRESS";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "",
    "PWD" => ""
);           
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$sql = "SELECT [empno], [access] FROM [Complaint].[dbo].[Training_HR_User]";
$stmt = sqlsrv_query($conn, $sql);

$enableUpload = false;

if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if ($row['empno'] == $_SESSION["emp_num"]) {
            $enableUpload = true;
            break;
        }
    }
    sqlsrv_free_stmt($stmt);
}
sqlsrv_close($conn);

?> 


<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/analysis.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>   <!---scroll javascript---->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>TNI PMS</title>

    <style>
      
      table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
        body{
            font-weight: 600;
            font-style: normal;
            font-family: "Nunito Sans", sans-serif;
            background-color: #5d87192e;
            }

            .table-container {
            height: 690px; /* Set the fixed height */
            overflow-y: scroll; /* Make it scrollable */
            border: 1px solid #ddd; /* Optional: Add border */
            margin-bottom: 20px; /* Optional: Add space below the container */
        }
         /* Style the scroll buttons */
         .scroll-buttons {
            text-align: center;
        }

        .scroll-buttons button {
            padding: 10px;
            margin: 5px;
            cursor: pointer;
        }
        </style>
</head>
<?php include 'header.php'; ?>   

<?php
if (isset($_POST["import"])) {
    $fileName = $_FILES["excel"]["name"];
    $fileExtension = explode('.', $fileName);
    $fileExtension = strtolower(end($fileExtension));
    $newFileName = date("Y.m.d") . " - " . date("h.i.sa") . "." . $fileExtension;

    $targetDirectory = "uploads/" . $newFileName;
    if (move_uploaded_file($_FILES['excel']['tmp_name'], $targetDirectory)) {

        // Suppress errors for production (enable during development)
        error_reporting(0);
        ini_set('display_errors', 0);

        require 'excelReader/excel_reader2.php';
        require 'excelReader/SpreadsheetReader.php';

        $reader = new SpreadsheetReader($targetDirectory);
        $newRowsCount = 0;

        // Loop through each row in the spreadsheet
        foreach ($reader as $key => $row) {
            // Skip the header row if necessary
            if ($key === 0) continue; // Adjust if there's no header

            // Retrieve column data from the Excel row
            $employeeNo = trim($row[1]);
            $employeeName = trim($row[2]);
            $personnelArea = trim($row[3]);
            $PersonnelAreaDescription =trim($row[4]);
            $personalSubarea = trim($row[5]);
            $personnelSubareaDescription = trim($row[6]);
            $employeeSubGroup = trim($row[7]);
            $position = trim($row[8]);
            $department = trim($row[9]);
            $appraisalStartDate = trim($row[10]);
            $appraisalEndDate = trim($row[11]);
            $typeOfTrainingNeed = trim($row[12]);
            $trainingName = trim($row[13]);
            $srl_no = trim($row[14]);
          

            // SQL query to insert data into TNI_PMS table
            $query = "INSERT INTO TNI_PMS (
                EmployeeNo, EmployeeName, PersonnelArea, PersonnelAreaDescription, PersonalSubarea,
                PersonnelSubareaDescription, EmployeeSubGroup, Position, Department,
                AppraisalStartDate, AppraisalEndDate, TypeOfTrainingNeed, TrainingName, srl_no
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = array(
                $employeeNo, $employeeName, $personnelArea, $PersonnelAreaDescription, $personalSubarea,
                $personnelSubareaDescription, $employeeSubGroup, $position, $department,
                $appraisalStartDate, $appraisalEndDate, $typeOfTrainingNeed, $trainingName, $srl_no 
            );

            // Execute the query
            $stmt = sqlsrv_query($conn, $query, $params);

            // Check for errors in query execution
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true)); // Stop script execution on error
            }

            $newRowsCount++;
        }

        // Delete the uploaded file after processing
        unlink($targetDirectory);

        // Display an alert based on the number of new rows inserted
        if ($newRowsCount > 0) {
            echo "<script>alert('Data Inserted');</script>";
        }

        // Redirect back to the same page
        echo "<script>window.location.href = 'TNI_PMS.php';</script>";
    } else {
        echo "<script>alert('Failed to upload file.');</script>";
    }
}

$serverName = "NSPCL-AD\SQLEXPRESS";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "",
    "PWD" => ""
);           
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
// SQL query to select data from TNI_PMS table
$query = "SELECT ID, EmployeeNo, EmployeeName, PersonnelArea, PersonnelAreaDescription, PersonalSubarea, PersonnelSubareaDescription, 
                EmployeeSubGroup, Position, Department, AppraisalStartDate, AppraisalEndDate, 
                TypeOfTrainingNeed, TrainingName, srl_no
          FROM TNI_PMS where EmployeeNo='$sessionemp'";

// Execute the query
$stmt = sqlsrv_query($conn, $query);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true)); // Handle query error
}
?>


<body>
<h6><i class='fa fa-home'></i>&nbsp;<i><u>HR->HR Upload(TNI)</u></i></h6>

<div class="container">
    <form action="" method="post" enctype="multipart/form-data">
        <div class="input-group mb-3">
            <!-- <input type="file" name="excel" class="form-control" id="inputGroupFile02" required>
            <button type="submit" name="import" class="input-group-text" for="inputGroupFile02">Upload</button> -->
            <input type="file" name="excel" class="form-control" id="inputGroupFile02" <?php echo $enableUpload ? '' : 'disabled'; ?> required>
            <button type="submit" name="import" class="input-group-text" for="inputGroupFile02" <?php echo $enableUpload ? '' : 'disabled'; ?>>Upload</button>
        </div>
    </form>
</div>
<div class="controls-container">
    
    
    <div class="scroll-buttons" style="margin-top: 10px;">
    <input type="text" id="searchInput" onkeyup="searchTable()" placeholder="Search for any detail..." style="width:50%;margin-bottom: 10px; padding: 5px;">
        <button class="btn btn-primary" style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;" onclick="scrollTable('up')">Scroll Up</button>
        <button class="btn btn-primary" style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;" onclick="scrollTable('down')">Scroll Down</button>
    </div>
</div>

<div class="table-container">
    <table class="table table-bordered border-success" style="height: 100%; border-collapse: collapse;">
        <thead style="position: sticky; top: 0; z-index: 1;">
            <tr class='table-warning'>
                <th>ID</th>
                <th>Srl No</th>
                <th>Employee No</th>
                <th>Employee Name</th>
                <th>Personnel Area</th>
                <th>Personnel Area Description</th>
                <th>Personal Subarea</th>
                <th>Personnel Subarea Description</th>
                <th>Employee Sub Group</th>
                <th>Position</th>
                <th>Department</th>
                <th>Appraisal Start Date</th>
                <th>Appraisal End Date</th>
                <th>Type Of Training Need</th>
                <th>Training Name</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php
            // Loop through the result set and display each row in the table
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['ID']) . "</td>";
                echo "<td>" . (is_null($row['srl_no']) ? 'null' : htmlspecialchars($row['srl_no'])) . "</td>";
                echo "<td>" . htmlspecialchars($row['EmployeeNo']) . "</td>";
                echo "<td>" . htmlspecialchars($row['EmployeeName']) . "</td>";
                echo "<td>" . htmlspecialchars($row['PersonnelArea']) . "</td>";
                echo "<td>" . htmlspecialchars($row['PersonnelAreaDescription']) . "</td>";
                echo "<td>" . htmlspecialchars($row['PersonalSubarea']) . "</td>";
                echo "<td>" . htmlspecialchars($row['PersonnelSubareaDescription']) . "</td>";
                echo "<td>" . htmlspecialchars($row['EmployeeSubGroup']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Position']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Department']) . "</td>";
                echo "<td>" . htmlspecialchars($row['AppraisalStartDate']->format('Y-m-d')) . "</td>"; // Convert date
                echo "<td>" . htmlspecialchars($row['AppraisalEndDate']->format('Y-m-d')) . "</td>"; // Convert date
                echo "<td>" . htmlspecialchars($row['TypeOfTrainingNeed']) . "</td>";
                echo "<td>" . htmlspecialchars($row['TrainingName']) . "</td>";
                echo "</tr>";
            }

            // Free statement and connection resources
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
            ?>
        </tbody>
    </table>
</div>

<script>
    function scrollTable(direction) {
        var container = document.querySelector('.table-container');
        var scrollAmount = 500; // Amount to scroll in pixels

        if (direction === 'up') {
            container.scrollBy({ top: -scrollAmount, behavior: 'smooth' }); // Scroll up
        } else if (direction === 'down') {
            container.scrollBy({ top: scrollAmount, behavior: 'smooth' }); // Scroll down
        }
    }

    function searchTable() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toLowerCase();
        table = document.getElementById("tableBody");
        tr = table.getElementsByTagName("tr");

        for (i = 0; i < tr.length; i++) {
            tr[i].style.display = "none"; // Hide all rows initially

            td = tr[i].getElementsByTagName("td");
            for (j = 0; j < td.length; j++) {
                if (td[j]) {
                    txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toLowerCase().indexOf(filter) > -1) {
                        tr[i].style.display = ""; // Show the row if a match is found
                        break; // Stop checking other columns in this row if a match is found
                    }
                }
            }
        }
    }
</script>

</div>


</body>
<?php include '../footer.php';?>
</html>
