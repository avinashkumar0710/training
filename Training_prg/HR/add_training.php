<?php

session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
}
$sessionemp = $_SESSION["emp_num"];

// Database Connection
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

   // Fetch the last Srl No
   $sql = "SELECT MAX(srl_no) AS last_srl FROM [Complaint].[dbo].[training_mast_com]";
   $stmt = sqlsrv_query($conn, $sql);
   $last_srl = 0; // Default value
   
   
   if ($stmt !== false) {
       $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
       $last_srl = $row['last_srl'] ? $row['last_srl'] + 1 : 1; // Increment last Srl No
   }
   
   //echo $last_srl;

    // Fetch the last Srl No
    $sql = "SELECT MAX(training_code) AS last_training_code FROM [Complaint].[dbo].[training_mast_com]";
    $stmt = sqlsrv_query($conn, $sql);
    $last_training_code = 0; // Default value
    
    
    if ($stmt !== false) {
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        $last_training_code = $row['last_training_code'] ? $row['last_training_code'] + 1 : 1; // Increment last Srl No
    }

    // echo "<pre>";
    // var_dump($_POST);
    // echo "</pre>";
    // exit;


// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $srl_no = $_POST['srl_no'];
    $Program_name = $_POST['Program_name'];
    $nature_training = $_POST['nature_training'];
    $duration = $_POST['duration'];
    $faculty = $_POST['faculty'];
    $training_mode = $_POST['training_mode'];
    //$tentative_date = $_POST['tentative_date'];
    $internal_external = $_POST['Internal_external'] ?? '';
    $year = $_POST['year'];
    $target_group = $_POST['target_group'];
    $venue = $_POST['venue'];
    $hostel_reqd = $_POST['hostel_reqd'];
    $coordinator = $_POST['coordinator'];
    $remarks = $_POST['admin_remarks'];
    $Closed_date = $_POST['Closed_date'];
    $day_from = $_POST['day_from'];
    $day_to = $_POST['day_to'];
    $NS01 = $_POST['NS01'];
    $NS02 = $_POST['NS02'];
    $NS03 = $_POST['NS03'];
    $NS04 = $_POST['NS04'];
    $E0 = $_POST['E0'];
    $E1 = $_POST['E1'];
    $E2 = $_POST['E2'];
    $E3 = $_POST['E3'];
    $E4 = $_POST['E4'];
    $E5 = $_POST['E5'];
    $E6 = $_POST['E6'];
    $E7 = $_POST['E7'];
    $E8 = $_POST['E8'];
    $E9 = $_POST['E9'];
    $Employee_grp = $_POST['Employee_grp'];

    // extra INPUT FIELD ADDED AFTER INOGRATION 

    $open_for = $_POST['open_for'];
    $training_code = $_POST['training_code'];
    $faculty_Intrnl_extrnl = $_POST['faculty_Intrnl_extrnl'];
    $training_subtype = $_POST['training_subtype'];
    $available_seats = $_POST['available_seats'];
    $flag ='1';

    // ✅ Insert into `training_mast`
    // $query1 = "INSERT INTO [Complaint].[dbo].[training_mast] 
    //     (srl_no, Program_name, nature_training, duration, faculty, training_mode, 
    //      internal_external, year, target_group, venue, hostel_reqd, coordinator, remarks, open_for, training_code, faculty_Intrnl_extrnl, training_subtype, available_seats,
    //      Closed_date, day_from, day_to, upload_date, ip_address)
    //     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE(), ?)";

    $query1 = "INSERT INTO [Complaint].[dbo].[training_mast] 
(srl_no, Program_name, nature_training, duration, faculty, training_mode, 
internal_external, year, target_group, venue, hostel_reqd, coordinator, admin_remarks, 
open_for, training_code, faculty_Intrnl_extrnl, training_subtype, available_seats, flag,
Closed_date, day_from, day_to, upload_date, ip_address)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? , ?, ?, CONVERT(date, GETDATE(), 23), ?)";


    $params1 = array($srl_no, $Program_name, $nature_training, $duration, $faculty, 
                     $training_mode,  $internal_external, $year, 
                     $target_group, $venue, $hostel_reqd, $coordinator, $remarks, $open_for, $training_code, $faculty_Intrnl_extrnl, $training_subtype, $available_seats, $flag,
                     $Closed_date, $day_from, $day_to, $_SERVER['REMOTE_ADDR']);
    
    $stmt1 = sqlsrv_query($conn, $query1, $params1);

    if ($stmt1) {
        // ✅ Insert into `training_mast_com`
        $query2 = "INSERT INTO [Complaint].[dbo].[training_mast_com] 
            (srl_no, NS01, NS02, NS03, NS04, E0, E1, E2, E3, E4, E5, E6, E7, E8, E9, Employee_grp, flag, training_code)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? ,?)";

        $params2 = array($srl_no, $NS01, $NS02, $NS03, $NS04, $E0, $E1, $E2, $E3, 
                         $E4, $E5, $E6, $E7, $E8, $E9, $Employee_grp, $flag, $training_code);

        $stmt2 = sqlsrv_query($conn, $query2, $params2);

        if ($stmt2) {
            echo "<script>
                alert('Training Added Successfully!');
                window.parent.location.reload();
                window.parent.document.getElementById('editModal').classList.remove('show');
                window.parent.document.body.classList.remove('modal-open');
                window.parent.document.querySelector('.modal-backdrop').remove();
            </script>";
        } else {
            echo "Error inserting into training_mast_com: " . print_r(sqlsrv_errors(), true);
        }
    } else {
        echo "Error inserting into training_mast: " . print_r(sqlsrv_errors(), true);
    }

 
}
?>


<!DOCTYPE html>

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
    <style>
     .container{
            font-weight: 600;
            font-style: normal;
            font-family: "Nunito Sans", sans-serif;
            background-color: #e8eef3;
            }
    </style>
    </head>
    <!-- <script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelector("form").addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent actual submission

            let formData = new FormData(this);
            let object = {};
            formData.forEach((value, key) => object[key] = value);
            console.log("Form Data Before Submitting:", object);

            // Send the data to the server using AJAX
            fetch(this.action, {
                method: this.method,
                body: formData
            }).then(response => response.text())
            .then(data => console.log("Server Response:", data))
            .catch(error => console.error("Error:", error));
        });
    });
</script> -->


<!-- 📝 HTML Form for Adding Training -->
<div class="container" >
    <form method="post">
        <div class="row">
            <!-- Left Column -->
            <div class="col-md-6">
                <div class="mb-2">
                    <label>Srl No:</label>
                    <input type="text" name="srl_no" class="form-control" value="<?php echo $last_srl; ?>" readonly>
                </div>

                <div class="mb-2">
                    <label>Training Code:</label>
                    <input type="number" name="training_code" class="form-control" value="<?php echo $last_training_code; ?>" readonly>
                </div>



                <div class="mb-2">
                    <label>Programme Name:</label>
                    <input type="text" name="Program_name" class="form-control" required>
                </div>

                <?php
$serverName = "192.168.100.240";
$connectionOptions = [
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch distinct Nature of Training
$trainingOptions = '';
$sql = "SELECT DISTINCT nature_of_Training FROM [Complaint].[dbo].[Training_Types]";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $val = htmlspecialchars($row['nature_of_Training']);
        // Check if the current value matches the selected nature_training, if so, set it as selected
        $selected = (isset($_GET['nature_training']) && $_GET['nature_training'] == $val) ? 'selected' : '';
        $trainingOptions .= "<option value=\"$val\" $selected>$val</option>";
    }
}

// Initialize the Training Subtype options
$subtypeOptions = "<option value=''>Select Training Subtype</option>";

// Fetch the subtypes based on the selected Nature of Training
if (isset($_GET['nature_training']) && !empty($_GET['nature_training'])) {
    $nature = $_GET['nature_training'];
    $sql2 = "SELECT DISTINCT Training_Subtype FROM [Complaint].[dbo].[Training_Types] WHERE nature_of_Training = ? AND flag = 1";
    $stmt2 = sqlsrv_query($conn, $sql2, [$nature]);
    if ($stmt2) {
        while ($row = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC)) {
            $val = htmlspecialchars($row['Training_Subtype']);
            $subtypeOptions .= "<option value=\"$val\">$val</option>";
        }
    }
}
?>

<div class="mb-2">
    <label>Nature of Training:</label>
    <select name="nature_training" id="nature_training" class="form-control" onchange="window.location.href = '?nature_training=' + this.value;">
        <option value="">Select Nature of Training</option>
        <?php
            // Add the selected option logic for selected nature
            echo $trainingOptions;
        ?>
    </select>
</div>

<div class="mb-2">
    <label>Training Subtype:</label>
    <select name="training_subtype" class="form-control">
        <?php echo $subtypeOptions; ?>
    </select>
</div>


             

                <div class="mb-2">
                    <label>Duration: <i style='color:#ff0000b8'>&nbsp;(Note : Use days like 02 days)</i></label>
                    <input type="text" name="duration" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label>Day From:<i style='color:#ff0000b8'>&nbsp;(Note : If date not decided then put 1990-01-01)</i></label>
                    <input type="date" name="day_from" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label>Day To:<i style='color:#ff0000b8'>&nbsp;(Note : If date not decided then put 1990-01-01)</i></label>
                    <input type="date" name="day_to" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label>Faculty:</label>
                    <input type="text" name="faculty" class="form-control" required>
                </div>  
                
                <div class="mb-2">
    <label>Training Mode:</label>
    <select name="training_mode" class="form-control" required>
        <option value="">Select Training Mode</option>
        <option value="Offline">Offline</option>
        <option value="Online">Online</option>
        <option value="Others">Others</option>
    </select>
</div>

            </div>

            <!-- Right Column -->
            <div class="col-md-6">          

            <div class="mb-2">
    <label>Faculty Type (Internal/External):</label>
    <select name="faculty_Intrnl_extrnl" class="form-control" required>
        <option value="">Select Faculty Type</option>
        <option value="Internal">Internal</option>
        <option value="External">External</option>
        <option value="Both Internal and External">Both Internal and External</option>
        <option value="Others">Others</option>
    </select>
</div>


                <div class="mb-2">
                    <label>Year:</label>
                    <input type="text" name="year" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label>Target Group:</label>
                    <input type="text" name="target_group" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label>Venue:</label>
                    <input type="text" name="venue" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label>Coordinator:</label>
                    <input type="text" name="coordinator" class="form-control" required>
                </div>

                <!-- <div class="mb-2">
                    <label>Tentative Date:</label>
                    <input type="text" name="tentative_date" class="form-control">
                </div> -->

                <div class="mb-2">
    <label>Programme Type (Internal/External):</label>
    <select name="Internal_external" class="form-control" required>
        <option value="">Select Programme Type</option>
        <option value="Internal">Internal</option>
        <option value="External">External</option>
        <option value="Both Internal and External">Both Internal and External</option>
        <option value="Others">Others</option>
    </select>
</div>


                <div class="mb-2">
                    <label>Open For:</label>
                    <input type="text" name="open_for" class="form-control" required>
                </div>           

                <div class="mb-2">
                    <label>Admin Remarks:</label>
                    <input type="text" name="admin_remarks" class="form-control" required>
                </div>

                <div class="mb-2">
                    <label>Available seats:</label>
                    <input type="number" name="available_seats" class="form-control" required>
                </div>
               
                <div class="mb-2">                   
                    <input type="hidden" name="hostel_reqd" value='NO Data ' class="form-control"> <!--hidden part not for use-->
                </div>

               
                <div class="mb-2">
                    <label>Closed Date:<i style='color:#ff0000b8'>&nbsp;(Note : If date not decided then put 1990-01-01)</i></label>
                    <input type="date" name="Closed_date" class="form-control" required>
                </div>
                
            </div>
        </div>

        <!-- Plant Selection (Yes/No) -->
        <div class="mb-3 d-flex">
            <?php
            $plants = ["NS01" => "NS01", "NS02" => "NS02", "NS03" => "NS03", "NS04" => "NS04"];
            foreach ($plants as $key => $label) {
                echo '<div class="me-3 p-2 border rounded">';
                echo "<label class='me-2'>$label:</label>";
                echo "<input type='radio' name='$key' value='1'> Yes ";
                echo "<input type='radio' name='$key' value='0' checked> No";
                echo '</div>';
            }
            ?>
        </div>

        <!-- Grade Selection (E0 - E9) -->
        <div class="mb-3 d-flex flex-wrap">
            <?php
            for ($i = 0; $i <= 9; $i++) {
                echo '<div class="me-3 p-2 border rounded">';
                echo "<label class='me-2'>E$i:</label>";
                echo "<input type='radio' name='E$i' value='1'> Yes ";
                echo "<input type='radio' name='E$i' value='0' checked> No";
                echo '</div>';
            }
            ?>
        </div>

        <!-- Employee Group -->
        <div class="mb-3">
    <label>Employee Group:</label>
    <div class="d-flex">
        <div class="me-3">
            <input type="radio" name="Employee_grp" value="A" required> All Executives
        </div>                
        <div class="me-3">
            <input type="radio" name="Employee_grp" value="B"> All Non-Executives
        </div>
        <div class="me-3">
            <input type="radio" name="Employee_grp" value="C"> All Employees
        </div>
        <!-- <div class="me-3"><input type="radio" name="Employee_grp" value="All Females"> All Females</div> -->
        <!-- <div class="me-3"><input type="radio" name="Employee_grp" value="No One" checked> No One</div> -->
    </div>
</div>


        <button type="submit" class="btn btn-primary">Add Training</button>
    </form>
</div>


</html>