<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp=$_SESSION["emp_num"];
//echo $_SESSION["emp_num"];

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
    <title>Import Excel To SQL Server</title>

    <style>
      
        .scroll-button {            
            bottom: 20px;
            right: 20px;
            width: 100px;
            height: 30px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5%;
            cursor: pointer;
            text-align: center;         
            z-index: 1000;
        }
        body{
            font-weight: 600;
            font-style: normal;
            font-family: "Nunito Sans", sans-serif;
            background-color: #5d87192e;
            }
        </style>
</head>
<?php include 'header.php';?>   
    
<?php
// $serverName = "192.168.100.240";
// $connectionInfo = array(
//     "Database" => "complaint",
//     "UID" => "sa",
//     "PWD" => "Intranet@123"
// );           
// $conn = sqlsrv_connect($serverName, $connectionInfo);

// if ($conn === false) {
//     die(print_r(sqlsrv_errors(), true));
// }

if(isset($_POST["import"])){
    $fileName = $_FILES["excel"]["name"];
    $fileExtension = explode('.', $fileName);
    $fileExtension = strtolower(end($fileExtension));
    $newFileName = date("Y.m.d") . " - " . date("h.i.sa") . "." . $fileExtension;

    $targetDirectory = "uploads/" . $newFileName;
    move_uploaded_file($_FILES['excel']['tmp_name'], $targetDirectory);

    error_reporting(0);
    ini_set('display_errors', 0);

    require 'excelReader/excel_reader2.php';
    require 'excelReader/SpreadsheetReader.php';

    $reader = new SpreadsheetReader($targetDirectory);
    $newRowsCount = 0;
    $updatedRowsCount = 0;

    foreach ($reader as $key => $row) {
        $srl_no = $row[0];
        $Program_name = $row[1];
        $nature_training = $row[2];
        $duration = $row[3];
        $faculty = $row[4];
        $tentative_date = $row[5];
        $year = $row[6];
        $target_group = $row[7];
        $id = $row[8];
        $Closed_date = $row[9]; // Assuming the Closed_date is fetched from the CSV
    
        // Check if srl_no already exists in the database
        $query = "SELECT COUNT(*) AS count FROM TNI_mast WHERE srl_no = ?";
        $params = array($srl_no);
        $stmt = sqlsrv_query($conn, $query, $params);
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $count = $row['count'];
    
        if ($count == 0) { // Insert new row if srl_no doesn't exist
            $query = "INSERT INTO TNI_mast (
                id, srl_no, Program_name, nature_training, duration, faculty, tentative_date, year, target_group, upload_date, ip_address, Closed_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = array(
                $id, $srl_no, $Program_name, $nature_training, $duration, $faculty, $tentative_date, $year, $target_group, date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR'], $Closed_date
            );
    
            $stmt = sqlsrv_query($conn, $query, $params);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
    
            $newRowsCount++;
        } else {
            // Update existing row if srl_no exists
            $query = "UPDATE TNI_mast SET
                Program_name = ?,
                nature_training = ?,
                duration = ?,
                faculty = ?,
                tentative_date = ?,
                year = ?,
                target_group = ?,
                upload_date = ?,
                ip_address = ?,
                Closed_date = ?
            WHERE srl_no = ?";
            $params = array(
                $Program_name, $nature_training, $duration, $faculty, $tentative_date, $year, $target_group, date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR'], $Closed_date, $srl_no
            );
    
            $stmt = sqlsrv_query($conn, $query, $params);
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
    
            $updatedRowsCount++;
        }
    }
    

    // Delete the uploaded file after processing
    unlink($targetDirectory);

    // Display appropriate message based on new and updated rows count
    if ($newRowsCount > 0 && $updatedRowsCount > 0) {
        echo "<script>alert('Data imported and updated');</script>";
    } elseif ($newRowsCount > 0) {
        echo "<script>alert('Data Inserted');</script>";
    } elseif ($updatedRowsCount > 0) {
        echo "<script>alert('Data updated');</script>";
    }

    // Redirect back to the same page
    echo "<script>window.location.href = 'excel_upload.php';</script>";
}

?>
<body>
<h6><i class='fa fa-home'></i>&nbsp;<i><u>HR->HR Upload(TNI)</u></i></h6>

    <div class="container">
    <form class="" action="" method="post" enctype="multipart/form-data">
    <div class="input-group mb-3">
        <input type="file" name="excel" class="form-control" id="inputGroupFile02" required value="">
        <button type="submit" name="import" class="input-group-text" for="inputGroupFile02">Upload</button>
    </form>
    </div>
    
    <!-- Scroll buttons -->
    <div class='scroll'>
    <!-- Scroll-up button -->
    <button class="scroll-button" id="scrollUp">ScrollUp&#8593;</button>
    <!-- Scroll-down button -->
    <button class="scroll-button" id="scrollDown">ScrollDown&#8595;</button>&nbsp;<i style="font-size:small; background-color:yellow;">&nbsp;(Note: if user doesnt want to select date after by mistaken select then 
    first uncheck the checkbox, goto "Datepicker" and click "Clear")</i>
</div><br>

    <!-- Displaying data in a table -->
    <div style="height: 550px; overflow-y: auto;">
    <form id="updateForm" action="update_training.php" method="post">
        <table class="table table-bordered border-success" style="height: 100%; border-collapse: collapse;">
            <thead style="position: sticky; top: 0; z-index: 1;">
                <tr class='table-success'>     
                         
                    <td >Srl No</td>
                    <td >Program Name</td>
                    <td >Nature Training</td>
                    <td >Duration</td>
                    <td >Faculty</td>
                    <td >Tentative Date</td>
                    <td >Year</td>
                    <td >Target Group</td>
                    <td >Closed Date</td>
                    <td >Date Picker</td>
                    <td >Checkbox</td> <!-- New columns -->
                </tr>
            </thead>
            <?php
            $query = "SELECT * FROM TNI_mast";
            $stmt = sqlsrv_query($conn, $query);

            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) :
            ?>
            <tr class='table-light'>
            <td style="display: none;"><?php echo $row["id"]; ?></td>
                <td><?php echo $row["srl_no"]; ?></td>
                <td><?php echo $row["Program_name"]; ?></td>
                <td><?php echo $row["nature_training"]; ?></td>
                <td><?php echo $row["duration"]; ?></td>
                <td><?php echo $row["faculty"]; ?></td>
                <td><?php echo $row["tentative_date"]; ?></td>
                <td><?php echo $row["year"]; ?></td>
                <td><?php echo $row["target_group"]; ?></td>
                <td style="color: <?php echo ($row["Closed_date"] && $row["Closed_date"] < new DateTime()) ? 'red' : 'green'; ?>">
    <?php echo $row["Closed_date"] ? $row["Closed_date"]->format('Y-m-d') : 'null'; ?>
</td>



                <td>
                    <input type="date" name="datepicker[]" class="datepicker" data-id="<?php echo $row['id']; ?>" min="<?php echo date('Y-m-d'); ?>" onchange="toggleCheckbox(this)">
                </td> <!-- Date picker -->
                <td style="text-align: center;">
    <input type="checkbox" name="checkbox[]" class="checkbox" value="<?php echo $row['id']; ?>" disabled style="width: 20px; height: 20px; display: block; margin: auto;">
</td>

 <!-- Checkbox initially disabled -->
            </tr>
            <?php endwhile; ?>
        </table>

        <!-- Hidden input fields to send the selected rows and date -->
        <input type="hidden" name="ids" id="selectedIdsInput">
        <input type="hidden" name="date" id="selectedDateInput">
        </div><br>
        <button type="submit" id="updateButton" class="btn btn-primary">Update Selected</button>
    </form>


<script>
    function toggleCheckbox(element) {
        var checkbox = element.parentNode.nextElementSibling.querySelector('.checkbox');
        checkbox.disabled = !checkbox.disabled;
        updateHiddenInputs();
    }

    function updateHiddenInputs() {
        var selectedIds = [];
        var selectedDates = [];
        var datepickers = document.querySelectorAll('.datepicker');
        datepickers.forEach(function(datepicker) {
            if (!datepicker.disabled) {
                selectedIds.push(datepicker.dataset.id);
                selectedDates.push(datepicker.value);
            }
        });
        document.getElementById('selectedIdsInput').value = selectedIds.join(',');
        document.getElementById('selectedDateInput').value = selectedDates.join(',');
    }
</script>



<script>
    // Scroll up function
    $('#scrollUp').on('click', function() {
        $('div').animate({ scrollTop: '-=1000' }, 'slow'); // Adjust scroll speed as needed
    });

    // Scroll down function
    $('#scrollDown').on('click', function() {
        $('div').animate({ scrollTop: '+=1000' }, 'slow'); // Adjust scroll speed as needed
    });
</script>

</body>
<?php include '../footer.php';?>
</html>
