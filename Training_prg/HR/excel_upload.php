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
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>   <!---scroll javascript---->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Excel Upload </title>

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
            background-color: #e8eef3;
            }

            input[type="checkbox"]:disabled {
    filter: hue-rotate(180deg) brightness(0.8);
}
        </style>
</head>
<?php include '../header_HR.php';?>
    
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

        require '../excelReader/excel_reader2.php';
        require '../excelReader/SpreadsheetReader.php';

        $reader = new SpreadsheetReader($targetDirectory);
        $newRowsCount = 0;
        $updatedRowsCount = 0;
        
        foreach ($reader as $key => $row) {
            if ($key === 0) continue; // Adjust if there's no header

            // Retrieve column data from the Excel row
            $srl_no = trim($row[0]); // Assuming srl_no is the first column
            $Program_name = trim($row[1]);            
            $nature_training = trim($row[2]);
            $duration = trim($row[3]);
            $day_from = trim($row[4]);
            $day_to = trim($row[5]);
            $faculty = trim($row[6]);
            $training_mode = trim($row[7]);
            $tentative_date = trim($row[8]);
            $Internal_external = trim($row[9]);
            $year = (int)trim($row[10]); // Ensure year is an integer
            $target_group = trim($row[11]);
            $venue = trim($row[12]);
            $hostel_reqd = trim($row[13]);
            $coordinator = trim($row[14]);
            $remarks = trim($row[15]);
            $upload_date = date('Y-m-d H:i:s'); // Upload date
            $ip_address = $_SERVER['REMOTE_ADDR']; // IP address
            $Closed_date = null; // Assuming Closed_date is null for now
           
          
            
          
           
            // Check if srl_no already exists in the database
            $query = "SELECT COUNT(*) AS count FROM [training_mast] WHERE srl_no = ?";
            //echo 'llo7';
            $params = array($srl_no);
            $stmt = sqlsrv_query($conn, $query, $params);
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $count = $row['count'];
            
            if ($count == 0) { // Insert new row if srl_no doesn't exist
                $query = "INSERT INTO [Complaint].[dbo].[training_mast] (
    [srl_no], [Program_name], [nature_training], [duration], [day_from], [day_to],
    [faculty], [training_mode], [tentative_date], [Internal_external], [year], 
    [target_group], [venue], [hostel_reqd], [coordinator], [remarks], 
    [upload_date], [ip_address], [Closed_date]
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE(), '$ip_address', NULL);";
    
    $params = array(
        $srl_no, $Program_name, $nature_training, $duration, $day_from, $day_to,
        $faculty, $training_mode, $tentative_date, $Internal_external, 
        $year, $target_group, $venue, $hostel_reqd, 
        $coordinator, $remarks, $upload_date, $ip_address, $Closed_date
    );
        
                $stmt = sqlsrv_query($conn, $query, $params);
                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
        
                $newRowsCount++;
            } else {
                // Update existing row if srl_no exists
                $query = "UPDATE [training_mast] SET  
                    Program_name = ?,
                    nature_training = ?,
                    duration = ?,
                    day_from = ?,
                    day_to = ?,
                    faculty = ?,
                    tentative_date = ?,
                    year = ?,
                    target_group = ?,
                    upload_date = ?,
                    ip_address = ?,
                    Closed_date = ?,                    
                    training_mode = ?,
                    Internal_external = ?,
                    venue = ?,
                    hostel_reqd = ?,
                    coordinator = ?,
                    remarks = ?
                WHERE srl_no = ?";
                $params = array(
                    $Program_name, $nature_training, $duration, $day_from, $day_to, $faculty, $tentative_date, $year, $target_group, date('Y-m-d H:i:s'), $_SERVER['REMOTE_ADDR'], $Closed_date, 
                    $training_mode ,$Internal_external ,$venue, $hostel_reqd, $coordinator, $remarks, $srl_no
                );
        
                $stmt = sqlsrv_query($conn, $query, $params);
                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
        
                $updatedRowsCount++;
            }
        }
        

        // Delete the uploaded file after processing
        //unlink($targetDirectory);

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
<h6><i class='fa fa-home'></i>&nbsp;<i><u>HR->HR Upload</u></i></h6>

<div class="d-flex align-items-stretch bg-white rounded shadow-sm">
    <!-- Section 1: File Upload (Light Gray Background) -->
    <div class="p-3 flex-grow-1" style="background-color: #f8f9fa; border-right: 1px solid #dee2e6;">
        <form action="" method="post" enctype="multipart/form-data" class="h-100 d-flex align-items-center">
            <div class="input-group">
                <input type="file" name="excel" class="form-control" id="inputGroupFile02" required accept=".xlsx,.xls">
                <button type="submit" name="import" class="btn btn-primary">
                    <i class="fas fa-upload me-1"></i> Upload
                </button>
            </div>
        </form>
    </div>
    

    <!-- Section 2: Action Buttons (Lighter Blue Background) -->
    <div class="p-3 d-flex align-items-center flex-wrap gap-2" style="background-color: #e9f5ff;">
        <!-- Scroll Buttons -->
        <div class="btn-group">
            <button class="btn btn-outline-secondary" id="scrollUp">
            Scroll Up&nbsp;<i class="fas fa-arrow-up"></i>
            </button>
            <button class="btn btn-outline-secondary" id="scrollDown">
            Scroll Down&nbsp;<i class="fas fa-arrow-down"></i>
            </button>
        </div>

        <!-- Action Buttons -->
        <button class="btn btn-warning" id="addButton" onclick="openAddModal1()">
            ADD<i class="fas fa-plus"></i>
        </button>
        <button class="btn btn-danger" onclick="deactivateSelected()">
            Deactivate Selected<i class="fas fa-ban"></i>
        </button>
        <button class="btn btn-success" onclick="reactivateSelected()">
        Reactivate Selected<i class="fas fa-redo"></i>
        </button>
        <button class="btn btn-dark" onclick="clearSelection()">
            Clear Selected&nbsp;<i class="fas fa-times"></i>
        </button>
    </div>

     <!-- Section 3: Set Per employee Available Seats -->
     <div class="p-3 d-flex align-items-center flex-wrap gap-2" style="background-color: white;">
    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#seatsModal" onclick="loadCurrentSeats()">
        <i class="fas fa-chair me-1"></i> Set Per Employee Available Seats
    </button>
    <span id="currentSeatsDisplay" class="ms-2 badge bg-primary"></span>
</div>

<!-- Modal -->
<div class="modal fade" id="seatsModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Available Seats Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="seatsForm">
                    <div class="mb-3">
                        <label class="form-label">Set Available Seats</label>
                        <input type="number" class="form-control" id="availableSeats" min="1" required>
                    </div>
                    <div class="current-seats alert alert-info py-2" style="display: none;">
                        <small>Current setting: <span id="currentSeatsInModal">0</span> seats</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveSeats()">Save Configuration</button>
            </div>
        </div>
    </div>
</div>
</div>

<script>
   // Load current seats when page loads and when modal opens
function loadCurrentSeats() {
    fetch('get_seats.php')
        .then(response => response.json())
        .then(data => {
            if (data.seats) {
                // Update display badge
                document.getElementById('currentSeatsDisplay').textContent = 
                    `Current: ${data.seats} seat${data.seats !== 1 ? 's' : ''}`;
                
                // Update modal display
                document.getElementById('currentSeatsInModal').textContent = data.seats;
                document.querySelector('.current-seats').style.display = 'block';
                
                // Set input value
                document.getElementById('availableSeats').value = data.seats;
            } else {
                document.querySelector('.current-seats').style.display = 'none';
            }
        });
}

// Save seats configuration
function saveSeats() {
    const seats = document.getElementById('availableSeats').value;
    
    if (!seats || seats < 1) {
        alert('Please enter a valid number of seats (minimum 1)');
        return;
    }

    fetch('save_seats.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ seats: seats })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Configuration saved successfully');
            loadCurrentSeats(); // Refresh the display
            $('#seatsModal').modal('hide');
        } else {
            alert('Error: ' + data.message);
        }
    });
}

// Load current seats when page loads
document.addEventListener('DOMContentLoaded', loadCurrentSeats);
    </script>


<script>
    function openAddModal1() {
        var modal = new bootstrap.Modal(document.getElementById('editModal1'));
        document.getElementById('editFrame1').src = "add_training.php"; // Change to your add page
        modal.show();
    }
</script>


<script>
// Select/Deselect all checkboxes
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.rowCheckbox:not(:disabled)');
    const redoCheckboxes = document.querySelectorAll('.redoCheckbox:not(:disabled)');

    // Select All for active records (main checkbox column)
    selectAll.addEventListener('change', function() {
        rowCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Uncheck Select All if any active checkbox is unchecked
    rowCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (!this.checked) {
                selectAll.checked = false;
            } else {
                const allChecked = [...rowCheckboxes].every(cb => cb.checked);
                selectAll.checked = allChecked;
            }
        });
    });
});

// Deactivate selected active records
function deactivateSelected() {
    const selectedIds = [];
    document.querySelectorAll('.rowCheckbox:checked').forEach(checkbox => {
        selectedIds.push(checkbox.value);
    });

    if (selectedIds.length === 0) {
        alert('Please select at least one active record to deactivate');
        return;
    }

    if (confirm(`Deactivate ${selectedIds.length} selected record(s)?`)) {
        // AJAX call to update flag to 0
        fetch('update_records.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'deactivate', ids: selectedIds })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        });
    }
}

// Reactivate selected inactive records
function reactivateSelected() {
    const selectedIds = [];
    document.querySelectorAll('.redoCheckbox:checked').forEach(checkbox => {
        selectedIds.push(checkbox.value);
    });

    if (selectedIds.length === 0) {
        alert('Please select at least one inactive record to reactivate');
        return;
    }

    if (confirm(`Reactivate ${selectedIds.length} selected record(s)?`)) {
        // AJAX call to update flag to 1
        fetch('update_records.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'reactivate', ids: selectedIds })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            location.reload();
        });
    }
}

// Clear all selections
function clearSelection() {
    document.querySelectorAll('.rowCheckbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.getElementById('selectAll').checked = false;
}
</script>


<div class="modal fade" id="editModal1" tabindex="-1" aria-labelledby="editModalLabel1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel1"><b>Add Training</b></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="background-color:#d6e9dd;">
                <iframe id="editFrame1" style="width: 100%; height: 750px; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Scroll Functionality -->
<script>
    document.getElementById("scrollUp").addEventListener("click", function() {
        window.scrollBy({ top: -100, behavior: 'smooth' });
    });

    document.getElementById("scrollDown").addEventListener("click", function() {
        window.scrollBy({ top: 100, behavior: 'smooth' });
    });
</script>


    <!-- Displaying data in a table -->
    <div style="height: 680px; overflow-y: auto; margin:10px 10px;">
    <form id="updateForm" action="update_training.php" method="post">
        <table class="table table-bordered" style="height: 100%; border-collapse: collapse; font-size:16px;">
            <thead style="position: sticky; top: 0; background-color: beige;">
                <tr style="font-weight: bold;">     
                         
                    <td>Srl No</td>
                    <td>Training code</td>  
                    <td>Programme Name</td>                   
                    <td>Nature of Training</td>
                    <td>Training Subtype</td> 
                    <td>Duration</td>
                    <td>Day From</td>
                    <td>Day To</td>
                    <td>Faculty</td>
                    <td>Training Mode</td>
                    <td>Faculty Type _Internal/External</td>
                    <!-- <td>Tentative Date</td> -->
                    <!-- <td>Internal / External</td> -->
                    <td>Year</td>
                    <td>Target Group</td>
                    <td>Venue</td>
                    <!-- <td>Hostel Reqd</td> -->
                    <td>Coordinator</td>
                    <td>Programme Type _Internal/External</td>
                    <td>Open For</td> 
                    <td>Admin Remarks</td>
                    <td>Available Seats</td>                   
                    <td>NS01 </td>
                    <td>NS02 </td>
                    <td>NS03 </td>
                    <td>NS04 </td>
                    <td>Grade E0</td>
                    <td>Grade E1</td>
                    <td>Grade E2</td>
                    <td>Grade E3</td>
                    <td>Grade E4</td>
                    <td>Grade E5</td>
                    <td>Grade E6</td>
                    <td>Grade E7</td>
                    <td>Grade E8</td>
                    <td>Grade E9</td>
                   
                    <td>Employee Group</td>
                    <td>Closed Date</td>
                    <!-- <td>Date Picker</td> -->
                    <td>Action</td> <!-- New columns -->
                    <th> Select All<br><div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                </div></th> <!-- Checkbox column header -->
                <th>Redo</th> <!-- New Redo column -->
                </tr>
            </thead>
            <?php
            //$query = "SELECT * FROM [training_mast]";
            $query = "SELECT 
            mast.*,
            com.NS01, com.NS02, com.NS03, com.NS04,
            com.E0, com.E1, com.E2, com.E3, com.E4, com.E5, com.E6, com.E7, com.E8, com.E9,
            ISNULL(com.Employee_grp, 'Not Select') AS Employee_grp
             -- Selecting all columns from training_mast, adjust if needed
        FROM 
            [Complaint].[dbo].[training_mast_com] AS com
        JOIN 
            [Complaint].[dbo].[training_mast] AS mast
        ON 
            com.srl_no = mast.srl_no;
            ";
            $stmt = sqlsrv_query($conn, $query);

            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) :
                $isActive = $row['flag'] == 1;
            ?>
            <tr class="<?php echo $isActive ? '' : 'table-secondary'; ?>">
            <td style="display: none;"><?php echo $row["id"]; ?></td>
                <td><?php echo $row["srl_no"]; ?></td>
                <td><?php echo $row["training_code"]; ?></td>
                <td><?php echo $row["Program_name"]; ?></td>                
                <td><?php echo $row["nature_training"]; ?></td>
                <td><?php echo $row["training_subtype"]; ?></td>
                <td><?php echo $row["duration"]; ?></td>
                <td><?php echo $row["day_from"]->format('Y-m-d'); ?></td>
                <td><?php echo $row["day_to"]->format('Y-m-d'); ?></td>
                <td><?php echo $row["faculty"]; ?></td>
                <td><?php echo $row["training_mode"]; ?></td>
                <td><?php echo $row["faculty_Intrnl_extrnl"]; ?></td>
                <!-- <td><?php echo $row["tentative_date"]; ?></td> -->
               
                <td><?php echo $row["year"]; ?></td>
                <td><?php echo $row["target_group"]; ?></td>
                <td><?php echo $row["venue"]; ?></td>
                <!-- <td><?php echo $row["hostel_reqd"]; ?></td> -->
                <td><?php echo $row["coordinator"]; ?></td>
                <td><?php echo $row["Internal_external"]; ?></td>
                <td><?php echo $row["open_for"]; ?></td>
                
                 <td><?php echo $row["admin_remarks"]; ?></td>
                 <td><?php echo $row["available_seats"]; ?></td>
                
                

            
            
        
                <td>
    <input type="checkbox" name="plant_auth[]" value="NS01" <?php echo ($row["NS01"] == 1) ? "checked" : ""; ?> onclick="return false;"> NS01
</td>
<td>
    <input type="checkbox" name="plant_auth[]" value="NS02" <?php echo ($row["NS02"] == 1) ? "checked" : ""; ?> onclick="return false;"> NS02
</td>
<td>
    <input type="checkbox" name="plant_auth[]" value="NS03" <?php echo ($row["NS03"] == 1) ? "checked" : ""; ?> onclick="return false;"> NS03
</td>
<td>
    <input type="checkbox" name="plant_auth[]" value="NS04" <?php echo ($row["NS04"] == 1) ? "checked" : ""; ?> onclick="return false;"> NS04
</td>
        

        
<td><input type="checkbox" name="grade_auth[]" value="E0" <?php echo ($row["E0"] == 1) ? "checked" : ""; ?> onclick="return false;"> E0</td>
<td><input type="checkbox" name="grade_auth[]" value="E1" <?php echo ($row["E1"] == 1) ? "checked" : ""; ?> onclick="return false;"> E1</td>
<td><input type="checkbox" name="grade_auth[]" value="E2" <?php echo ($row["E2"] == 1) ? "checked" : ""; ?> onclick="return false;"> E2</td>
<td><input type="checkbox" name="grade_auth[]" value="E3" <?php echo ($row["E3"] == 1) ? "checked" : ""; ?> onclick="return false;"> E3</td>
<td><input type="checkbox" name="grade_auth[]" value="E4" <?php echo ($row["E4"] == 1) ? "checked" : ""; ?> onclick="return false;"> E4</td>
<td><input type="checkbox" name="grade_auth[]" value="E5" <?php echo ($row["E5"] == 1) ? "checked" : ""; ?> onclick="return false;"> E5</td>
<td><input type="checkbox" name="grade_auth[]" value="E6" <?php echo ($row["E6"] == 1) ? "checked" : ""; ?> onclick="return false;"> E6</td>
<td><input type="checkbox" name="grade_auth[]" value="E7" <?php echo ($row["E7"] == 1) ? "checked" : ""; ?> onclick="return false;"> E7</td>
<td><input type="checkbox" name="grade_auth[]" value="E8" <?php echo ($row["E8"] == 1) ? "checked" : ""; ?> onclick="return false;"> E8</td>
<td><input type="checkbox" name="grade_auth[]" value="E9" <?php echo ($row["E9"] == 1) ? "checked" : ""; ?> onclick="return false;"> E9</td>

        

<!-- <td>
    <label><input type="radio" name="employee_group" value="All Executives" <?php echo ($row["Employee_grp"] == "All Executives") ? "checked" : ""; ?>> All Executives</label><br>
    <label><input type="radio" name="employee_group" value="All Employees" <?php echo ($row["Employee_grp"] == "All Employees") ? "checked" : ""; ?>> All Employees</label><br>
    <label><input type="radio" name="employee_group" value="All Non_Executives" <?php echo ($row["Employee_grp"] == "All Non_Executives") ? "checked" : ""; ?>> All Non Executives</label><br>
    <label><input type="radio" name="employee_group" value="All Females" <?php echo isset($row["Employee_grp"]) ? $row["Employee_grp"] : "All Females"; ?>> All Females</label><br>
     

</td> -->
<td><?php echo isset($row["Employee_grp"]) ? $row["Employee_grp"] : "Not Select"; ?></td> 

        <td style="color: <?php echo ($row["Closed_date"] && $row["Closed_date"] < new DateTime()) ? 'red' : 'green'; ?>">
                <?php echo $row["Closed_date"] ? $row["Closed_date"]->format('Y-m-d') : 'null'; ?>
            </td>

        <!-- <td>
                <input type="date" name="datepicker[]" class="datepicker" data-id="<?php echo $row['id']; ?>" min="<?php echo date('Y-m-d'); ?>" onchange="toggleCheckbox(this)">
                </td>  -->
                <!-- <td style="text-align: center;">
                <input type="checkbox" name="checkbox[]" class="checkbox" value="<?php echo $row['id']; ?>"  style="width: 20px; height: 20px; display: block; margin: auto;" onchange="logCheckboxValue(this)">
            </td> -->
            <td style="text-align: center;">    
            <i class="fa fa-pencil-alt pencil-icon"  data-id="<?php echo $row['id']; ?>" style="font-size: 20px; cursor: pointer;" onclick="openEditModal(this)"></i>
            <!-- <i class="fa fa-trash" data-id="<?php echo $row['id']; ?>" 
                   style="font-size: 20px; cursor: pointer; margin-left: 10px;" 
                   onclick="<?php echo $isActive ? 'deleteentry(this)' : ''; ?>"
                   <?php echo $isActive ? '' : 'style="opacity: 0.5; cursor: not-allowed;"'; ?>></i> -->
            </td>
            <td><input type="checkbox" class="rowCheckbox" value="<?php echo $row['id']; ?>"  <?php echo $isActive ? '' : 'disabled'; ?> style="transform: scale(1.5); margin: 0;">
            ID : <?php echo $row["srl_no"]; ?>
        </td>
        <td style="text-align: center;">
                <input type="checkbox" class="redoCheckbox" value="<?php echo $row['id']; ?>" 
                       <?php echo $isActive ? 'disabled' : ''; ?>
                       style="transform: scale(1.5); margin: 0;">
            </td>
            
<script>
    function deleteentry(element) {
    var id = element.getAttribute("data-id");

    if (confirm("Are you sure you want to delete this entry?")) {
        fetch('delete_entry.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'id=' + id
        })
        .then(response => response.json()) // ✅ Parse JSON response
        .then(data => {
            if (data.status === "success") {
                alert(data.message);  // ✅ Correct success message
                location.reload();  // ✅ Refresh page after successful deletion
            } else {
                alert("Error deleting entry: " + data.message);  // ✅ Show error only if deletion fails
            }
        })
        .catch(error => console.error('Error:', error));
    }
}


    </script>
<!-- 
            <script>
                function logId(icon) {
                    let id = icon.getAttribute('data-id');
                    console.log("Selected ID:", id);
                }
            </script> -->

            </tr>
            <?php endwhile; ?>
        </table>

        <!-- Hidden input fields to send the selected rows and date -->
        <!-- <input  name="ids" id="selectedIdsInput">
        <input name="date" id="selectedDateInput"> -->
        </div><br>
        <!-- <button type="submit" id="updateButton" class="btn btn-primary">Update Selected</button> -->
    </form>

<!-- Bootstrap Modal -->
<!-- Bootstrap Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <!-- Change modal-lg to modal-xl -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Training</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="editFrame" style="width: 100%; height: 750px; border: none;"></iframe> <!-- 100% width -->
            </div>
        </div>
    </div>
</div>



<!-- JavaScript to Open Modal -->
<script>
function openEditModal(icon) {
    let id = icon.getAttribute('data-id');
    let modalFrame = document.getElementById("editFrame");

    // Load `edit_training.php` in iframe
    modalFrame.src = "edit_training.php?id=" + id;

    // Open Bootstrap Modal
    var modal = new bootstrap.Modal(document.getElementById("editModal"));
    modal.show();
}
</script>

<script>
    function closeModal() {
        var modal = new bootstrap.Modal(document.getElementById('editModal'));
        modal.hide();
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


    function logCheckboxValue(checkbox) {
    console.log("Checkbox ID:", checkbox.value);
}

</script>
<div class="container-fluid mt-3 mb-4">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="alert alert-info py-2" style="font-size: 1rem;">
                <strong>Note :</strong> 
                <span style="color: #000000;">
                    All Executives: <span class="fw-bold">A</span>, 
                    All Non-Executives: <span class="fw-bold">B</span>, 
                    All Employees: <span class="fw-bold">C</span>
                </span>
            </div>
        </div>
    </div>
</div>
</body>
<?php include '../footer.php';?>
</html>


<script>
    // function toggleCheckbox(element) {
    //     var checkbox = element.parentNode.nextElementSibling.querySelector('.checkbox');
    //     checkbox.disabled = !checkbox.disabled;
    //     updateHiddenInputs();
    // }

    // function updateHiddenInputs() {
    //     var selectedIds = [];
    //     var selectedDates = [];
    //     var datepickers = document.querySelectorAll('.datepicker');
    //     datepickers.forEach(function(datepicker) {
    //         if (!datepicker.disabled) {
    //             selectedIds.push(datepicker.dataset.id);
    //             selectedDates.push(datepicker.value);
    //         }
    //     });
    //     document.getElementById('selectedIdsInput').value = selectedIds.join(',');
    //     document.getElementById('selectedDateInput').value = selectedDates.join(',');
    // }
</script>