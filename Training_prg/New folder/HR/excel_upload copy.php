<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp=$_SESSION["emp_num"];
   //echo $sessionemp;

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
<?php include '../header_HR.php';?>

<?php
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
    exit();
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
            $srl_no = trim($row[0]); // Assuming srl_no is the first column
            $Program_name = trim($row[1]);
            $program_pdf = trim($row[2]);
            $nature_training = trim($row[3]);
            $duration = trim($row[4]);
            $faculty = trim($row[5]);
            $training_mode = trim($row[6]);
            $tentative_date = trim($row[7]);
            $Internal_external = trim($row[8]);
            $year = (int)trim($row[9]); // Ensure year is an integer
            $target_group = trim($row[10]);
            $venue = trim($row[11]);
            $hostel_reqd = trim($row[12]);
            $coordinator = trim($row[13]);
            $remarks = trim($row[14]);
            $upload_date = date('Y-m-d H:i:s'); // Upload date
            $ip_address = $_SERVER['REMOTE_ADDR']; // IP address
            $Closed_date = null; // Assuming Closed_date is null for now

            // SQL query to insert data into TrainingPrograms table
            $query = "INSERT INTO TrainingPrograms (
                srl_no, Program_name, program_pdf, nature_training, duration, faculty, 
                training_mode, tentative_date, Internal_external, year, target_group, 
                venue, hostel_reqd, coordinator, remarks, upload_date, ip_address, Closed_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $params = array(
                $srl_no, $Program_name, $program_pdf, $nature_training, $duration, 
                $faculty, $training_mode, $tentative_date, $Internal_external, 
                $year, $target_group, $venue, $hostel_reqd, 
                $coordinator, $remarks, $upload_date, $ip_address, $Closed_date
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
            echo "<script>alert('Data Inserted: $newRowsCount rows');</script>";
        }

        // Redirect back to the same page
        echo "<script>window.location.href = 'excel_upload copy.php';</script>";
    } else {
        echo "<script>alert('Failed to upload file.');</script>";
    }
}

// SQL query to select data from TrainingPrograms table
$query = "SELECT * FROM TrainingPrograms";

// Execute the query
$stmt = sqlsrv_query($conn, $query);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true)); // Handle query error
}
?>



<body>
<h6><i class='fa fa-home'></i>&nbsp;<i><u>HR->HR Upload</u></i></h6>



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



<div class="container-fluid" style="height: 700px; overflow-y: auto;">
        <h2 class="mb-4">Training Programs</h2>
        
        <!-- Form to handle checkbox updates -->
        <form method="post" action="">
            <table class="table table-bordered" style="height: 100%; border-collapse: collapse">
                <thead style="position: sticky; top: 0; background-color: beige;">
                    <tr style="font-weight: bold;">
                        
                        <th>ID</th>
                        <th>Serial No</th>
                        <th>Program Name</th>
                        <th>Program PDF</th>
                        <th>Nature of Training</th>
                        <th>Duration</th>
                        <th>Faculty</th>
                        <th>Training Mode</th>
                        <th>Tentative Date</th>
                        <th>Internal/External</th>
                        <th>Year</th>
                        <th>Target Group</th>
                        <th>Venue</th>
                        <th>Hostel Required</th>
                        <th>Coordinator</th>
                        <th>Remarks</th>
                        <!-- <th>Upload Date</th>
                        <th>IP Address</th> -->
                        <th>Closed Date</th>
                        <th>Select Closed Date</th>
                        <td>Checkbox</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                        echo "<tr>";
                       
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['srl_no']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Program_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['program_pdf']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['nature_training']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['duration']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['faculty']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['training_mode']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['tentative_date']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Internal_external']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['year']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['target_group']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['venue']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['hostel_reqd']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['coordinator']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['remarks']) . "</td>";
                        // echo "<td>" . htmlspecialchars($row['upload_date']) . "</td>";
                        // echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
                        echo "<td>" . htmlspecialchars($row["Closed_date"] ? $row["Closed_date"]->format('Y-m-d') : 'null') . "</td>";
                        echo "<td><input type='text' name='closed_dates[]' class='datepicker form-control' placeholder='Select Date'></td>"; // Date picker input
                        echo "<td><input type='checkbox' name='ids[]' value='" . htmlspecialchars($row['id']) . "'></td>"; // Checkbox for selection
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
            <button type="submit" name="update_closed" class="btn btn-primary">Update Closed Dates</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

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
