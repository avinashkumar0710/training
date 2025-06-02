<?php
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
    exit;
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

// Check if ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch Training Data
    $query = "SELECT tm.id, tm.srl_no, tm.Program_name, tm.nature_training, tm.duration, tm.faculty, 
                     tm.training_mode,  tm.Internal_external, tm.year, tm.target_group, tm.admin_remarks, tm.faculty_Intrnl_extrnl, tm.training_subtype, tm.available_seats, tm.open_for, tm.training_code,
                     tm.venue, tm.hostel_reqd, tm.coordinator,  tm.Closed_date, tm.day_from, tm.day_to, 
                     tmc.NS01, tmc.NS02, tmc.NS03, tmc.NS04, 
                     tmc.E0, tmc.E1, tmc.E2, tmc.E3, tmc.E4, tmc.E5, tmc.E6, tmc.E7, tmc.E8, tmc.E9, 
                     tmc.Employee_grp
              FROM [Complaint].[dbo].[training_mast] AS tm
              INNER JOIN [Complaint].[dbo].[training_mast_com] AS tmc ON tm.srl_no = tmc.srl_no
              WHERE tm.id = ?";

    // Prepare and Execute Query
    $params = array($id);
    $stmt = sqlsrv_prepare($conn, $query, $params);
    if (!$stmt) {
        die(print_r(sqlsrv_errors(), true));
    }
    
    if (!sqlsrv_execute($stmt)) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch Result
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if (!$row) {
        echo "<h3>No Record Found</h3>";
        exit;
    }

} else {
    echo "<h3>Invalid Request</h3>";
    exit;
}

//  var_dump($row['day_to']);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Training</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!---scroll javascript---->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<style>
label {
    font-style: bold;
}
</style>

<?php
// Connection
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

$selectedNature = $row['nature_training'] ?? '';
$selectedSubtype = $row['training_subtype'] ?? '';

// Fetch all nature-subtype pairs
$trainingData = [];
$sql = "SELECT DISTINCT nature_of_Training, Training_Subtype FROM [Complaint].[dbo].[Training_Types] WHERE flag = 1";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt) {
    while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $nature = $r['nature_of_Training'];
        $subtype = $r['Training_Subtype'];
        $trainingData[] = ['nature' => $nature, 'subtype' => $subtype];
    }
}

// Fetch distinct nature options
$natureOptions = '';
$natures = [];
foreach ($trainingData as $rowData) {
    $n = $rowData['nature'];
    if (!in_array($n, $natures)) {
        $selected = ($n == $selectedNature) ? 'selected' : '';
        $natureOptions .= "<option value=\"$n\" $selected>$n</option>";
        $natures[] = $n;
    }
}
?>



<body>

    <div class="container">
        <form id="updateForm" action="update_training.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2">
                        <label class="fw-bold">Srl_no:</label>
                        <input type="number" name="srl_no" value="<?php echo $row['srl_no']; ?>" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label class="fw-bold">Programme Name:</label>
                        <input type="text" name="Program_name" value="<?php echo $row['Program_name']; ?>"
                            class="form-control">
                    </div>

                   <!-- Nature of Training -->
<div class="mb-2">
    <label class="fw-bold">Nature of Training:</label>
    <div class="input-group">
        <input type="text" name="nature_training" id="natureInput" value="<?php echo $selectedNature; ?>" class="form-control">
    
        <select class="form-select" id="natureSelect">
            <option value="">Select</option>
            <?php echo $natureOptions; ?>
        </select>
    </div>
    
</div>

<!-- Training Subtype -->
<div class="mb-2">
    <label class="fw-bold">Training Subtype:</label>
    <div class="input-group">
    <input type="text" name="training_subtype" id="subtypeInput" value="<?php echo $selectedSubtype; ?>" class="form-control">
        <select class="form-select" id="subtypeSelect">
            <option value="">Select</option>
        </select>
    
       
    </div>
</div>

<script>
    const trainingData = <?php echo json_encode($trainingData); ?>;
    const subtypeSelect = document.getElementById('subtypeSelect');
    const subtypeInput = document.getElementById('subtypeInput');
    const natureSelect = document.getElementById('natureSelect');
    const natureInput = document.getElementById('natureInput');

    // On page load: set subtype options for the selected nature
    window.addEventListener('DOMContentLoaded', () => {
        filterSubtypes(natureSelect.value);
        subtypeSelect.value = "<?php echo $selectedSubtype; ?>";
    });

    // When nature changes
    natureSelect.addEventListener('change', function () {
        const selectedNature = this.value;
        natureInput.value = selectedNature;
        filterSubtypes(selectedNature);
        subtypeInput.value = ""; // Clear old value
    });

    // When subtype is selected, update input
    subtypeSelect.addEventListener('change', function () {
        subtypeInput.value = this.value;
    });

    function filterSubtypes(nature) {
        subtypeSelect.innerHTML = '<option value="">Select</option>';
        trainingData.forEach(item => {
            if (item.nature === nature) {
                const opt = document.createElement('option');
                opt.value = item.subtype;
                opt.text = item.subtype;
                subtypeSelect.appendChild(opt);
            }
        });
    }
</script>


                    <div class="mb-2">
                        <label class="fw-bold">Duration:</label>
                        <input type="text" name="duration" value="<?php echo $row['duration']; ?>" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label class="fw-bold">Day From:</label>
                        <input type="date" name="day_from" value="<?php echo $row['day_from']->format('Y-m-d'); ?>"
                            class="form-control">
                    </div>

                    <div class="mb-2">
                        <label class="fw-bold">Day To:</label>
                        <input type="date" name="day_to" value="<?php echo $row['day_to']->format('Y-m-d'); ?>"
                            class="form-control">
                    </div>


                    <div class="mb-2">
                        <label class="fw-bold">Faculty:</label>
                        <input type="text" name="faculty" value="<?php echo $row['faculty']; ?>" class="form-control">
                    </div>

                    <div class="mb-2">
    <label class="fw-bold">Training Mode:</label>
    <div class="input-group">
        <input type="text" name="training_mode" id="training_mode_input"
               value="<?php echo htmlspecialchars($row['training_mode'] ?? ''); ?>" class="form-control">
        <select class="form-select" onchange="document.getElementById('training_mode_input').value = this.value;">
            <option value="">Select Mode</option>
            <option value="Offline">Offline</option>
            <option value="Online">Online</option>
            <option value="Others">Others</option>
        </select>
    </div>
</div>



<div class="mb-2">
    <label class="fw-bold">Faculty Type _Internal/External :</label>
    <div class="input-group">
        <input type="text" name="faculty_Intrnl_extrnl" id="faculty_input"
               value="<?php echo htmlspecialchars($row['faculty_Intrnl_extrnl'] ?? ''); ?>" class="form-control">
        <select class="form-select" onchange="document.getElementById('faculty_input').value = this.value;">
            <option value="">Select Type</option>
            <option value="Internal">Internal</option>
            <option value="External">External</option>
            <option value="Both Internal and External">Both Internal and External</option>
            <option value="Others">Others</option>
        </select>
    </div>
</div>





                    <div class="mb-2">
                        <label class="fw-bold">Year:</label>
                        <input type="text" name="year" value="<?php echo $row['year']; ?>" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label class="fw-bold">Target Group:</label>
                        <input type="text" name="target_group" value="<?php echo $row['target_group']; ?>"
                            class="form-control">
                    </div>

                    <div class="mb-2">
                        <label class="fw-bold">Open For:</label>
                        <input type="text" name="open_for" value="<?php echo $row['open_for']; ?>" class="form-control">
                    </div>


                </div>

                <div class="col-md-6">

                    <div class="mb-2">
                        <label class="fw-bold">Training Code:</label>
                        <input type="text" name="training_code" value="<?php echo $row['training_code']; ?>"
                            class="form-control" readonly>
                    </div>

                    <div class="mb-2">
    <label class="fw-bold">Programme Type _Internal/External:</label>
    <div class="input-group">
        <input type="text" name="Internal_external" id="program_type_input"
               value="<?php echo htmlspecialchars($row['Internal_external'] ?? ''); ?>" class="form-control">
        <select class="form-select" onchange="document.getElementById('program_type_input').value = this.value;">
            <option value="">Select Type</option>
            <option value="Internal">Internal</option>
            <option value="External">External</option>
            <option value="Both Internal and External">Both Internal and External</option>
            <option value="Others">Others</option>
        </select>
    </div>
</div>


                    <div class="mb-2">
                        <label class="fw-bold">Venue:</label>
                        <input type="text" name="venue" value="<?php echo $row['venue']; ?>" class="form-control">
                    </div>



                    <div class="mb-2">
                        <label class="fw-bold">Coordinator:</label>
                        <input type="text" name="coordinator" value="<?php echo $row['coordinator']; ?>"
                            class="form-control">
                    </div>

                    <div class="mb-2">
                        <label class="fw-bold">Admin Remarks:</label>
                        <input type="text" name="admin_remarks" value="<?php echo $row['admin_remarks']; ?>"
                            class="form-control">
                    </div>

                    <div class="mb-2">
                        <label class="fw-bold">Available Seats:</label>
                        <input type="text" name="available_seats" value="<?php echo $row['available_seats']; ?>"
                            class="form-control">
                    </div>

                    <label class="fw-bold">Plant:</label>
                    <div class="mb-2 d-flex justify-content-between">
                        <div class="p-2 text-white text-center rounded" style="background-color: Green; width: 24%;">
                            <label class="fw-bold">NS01:</label>
                            <div>
                                <input type="radio" name="NS01" value="1"
                                    <?php echo ($row['NS01'] == 1) ? "checked" : ""; ?>> Yes
                                <input type="radio" name="NS01" value="0"
                                    <?php echo ($row['NS01'] == 0) ? "checked" : ""; ?>> No
                            </div>
                        </div>

                        <div class="p-2 text-white text-center rounded" style="background-color: Blue; width: 24%;">
                            <label class="fw-bold">NS02:</label>
                            <div>
                                <input type="radio" name="NS02" value="1"
                                    <?php echo ($row['NS02'] == 1) ? "checked" : ""; ?>> Yes
                                <input type="radio" name="NS02" value="0"
                                    <?php echo ($row['NS02'] == 0) ? "checked" : ""; ?>> No
                            </div>
                        </div>

                        <div class="p-2 text-black text-center rounded"
                            style="background-color: LightGreen; width: 24%;">
                            <label class="fw-bold">NS03:</label>
                            <div>
                                <input type="radio" name="NS03" value="1"
                                    <?php echo ($row['NS03'] == 1) ? "checked" : ""; ?>> Yes
                                <input type="radio" name="NS03" value="0"
                                    <?php echo ($row['NS03'] == 0) ? "checked" : ""; ?>> No
                            </div>
                        </div>

                        <div class="p-2 text-black text-center rounded" style="background-color: Orange; width: 24%;">
                            <label class="fw-bold">NS04:</label>
                            <div>
                                <input type="radio" name="NS04" value="1"
                                    <?php echo ($row['NS04'] == 1) ? "checked" : ""; ?>> Yes
                                <input type="radio" name="NS04" value="0"
                                    <?php echo ($row['NS04'] == 0) ? "checked" : ""; ?>> No
                            </div>
                        </div>
                    </div>

                    <label class="fw-bold">Grades:</label>
                    <div class="mb-2 d-flex align-items-center flex-wrap"
                        style="gap: 15px; background-color: #e3f2fd; padding: 10px; border-radius: 5px;">
                        <div style="background-color: #90caf9; padding: 5px; border-radius: 5px;">
                            <label class="me-2"><strong>E0:</strong></label>
                            <label class="me-2"><input type="radio" name="E0" value="1"
                                    <?php echo ($row['E0'] == 1) ? "checked" : ""; ?>> Yes</label>
                            <label><input type="radio" name="E0" value="0"
                                    <?php echo ($row['E0'] == 0) ? "checked" : ""; ?>> No</label>
                        </div>

                        <div style="background-color: #90caf9; padding: 5px; border-radius: 5px;">
                            <label class="me-2"><strong>E1:</strong></label>
                            <label class="me-2"><input type="radio" name="E1" value="1"
                                    <?php echo ($row['E1'] == 1) ? "checked" : ""; ?>> Yes</label>
                            <label><input type="radio" name="E1" value="0"
                                    <?php echo ($row['E1'] == 0) ? "checked" : ""; ?>> No</label>
                        </div>

                        <div style="background-color: #90caf9; padding: 5px; border-radius: 5px;">
                            <label class="me-2"><strong>E2:</strong></label>
                            <label class="me-2"><input type="radio" name="E2" value="1"
                                    <?php echo ($row['E2'] == 1) ? "checked" : ""; ?>> Yes</label>
                            <label><input type="radio" name="E2" value="0"
                                    <?php echo ($row['E2'] == 0) ? "checked" : ""; ?>> No</label>
                        </div>

                        <div style="background-color: #90caf9; padding: 5px; border-radius: 5px;">
                            <label class="me-2"><strong>E3:</strong></label>
                            <label class="me-2"><input type="radio" name="E3" value="1"
                                    <?php echo ($row['E3'] == 1) ? "checked" : ""; ?>> Yes</label>
                            <label><input type="radio" name="E3" value="0"
                                    <?php echo ($row['E3'] == 0) ? "checked" : ""; ?>> No</label>
                        </div>

                        <div style="background-color: #90caf9; padding: 5px; border-radius: 5px;">
                            <label class="me-2"><strong>E4:</strong></label>
                            <label class="me-2"><input type="radio" name="E4" value="1"
                                    <?php echo ($row['E4'] == 1) ? "checked" : ""; ?>> Yes</label>
                            <label><input type="radio" name="E4" value="0"
                                    <?php echo ($row['E4'] == 0) ? "checked" : ""; ?>> No</label>
                        </div>

                        <div style="background-color: #90caf9; padding: 5px; border-radius: 5px;">
                            <label class="me-2"><strong>E5:</strong></label>
                            <label class="me-2"><input type="radio" name="E5" value="1"
                                    <?php echo ($row['E5'] == 1) ? "checked" : ""; ?>> Yes</label>
                            <label><input type="radio" name="E5" value="0"
                                    <?php echo ($row['E5'] == 0) ? "checked" : ""; ?>> No</label>
                        </div>

                        <div style="background-color: #90caf9; padding: 5px; border-radius: 5px;">
                            <label class="me-2"><strong>E6:</strong></label>
                            <label class="me-2"><input type="radio" name="E6" value="1"
                                    <?php echo ($row['E6'] == 1) ? "checked" : ""; ?>> Yes</label>
                            <label><input type="radio" name="E6" value="0"
                                    <?php echo ($row['E6'] == 0) ? "checked" : ""; ?>> No</label>
                        </div>

                        <div style="background-color: #90caf9; padding: 5px; border-radius: 5px;">
                            <label class="me-2"><strong>E7:</strong></label>
                            <label class="me-2"><input type="radio" name="E7" value="1"
                                    <?php echo ($row['E7'] == 1) ? "checked" : ""; ?>> Yes</label>
                            <label><input type="radio" name="E7" value="0"
                                    <?php echo ($row['E7'] == 0) ? "checked" : ""; ?>> No</label>
                        </div>

                        <div style="background-color: #90caf9; padding: 5px; border-radius: 5px;">
                            <label class="me-2"><strong>E8:</strong></label>
                            <label class="me-2"><input type="radio" name="E8" value="1"
                                    <?php echo ($row['E8'] == 1) ? "checked" : ""; ?>> Yes</label>
                            <label><input type="radio" name="E8" value="0"
                                    <?php echo ($row['E8'] == 0) ? "checked" : ""; ?>> No</label>
                        </div>

                        <div style="background-color: #90caf9; padding: 5px; border-radius: 5px;">
                            <label class="me-2"><strong>E9:</strong></label>
                            <label class="me-2"><input type="radio" name="E9" value="1"
                                    <?php echo ($row['E9'] == 1) ? "checked" : ""; ?>> Yes</label>
                            <label><input type="radio" name="E9" value="0"
                                    <?php echo ($row['E9'] == 0) ? "checked" : ""; ?>> No</label>
                        </div>
                    </div>






                    <div class="mb-2">
                        <label class="fw-bold">Closed Date:</label>
                        <input type="date" name="Closed_date"
                            value="<?php echo $row['Closed_date']->format('Y-m-d'); ?>" class="form-control">
                    </div>

                    <div class="mb-2" style="background-color: #e3f2fd; padding: 10px; border-radius: 5px;">
                        <label class="me-2"><strong>Employee Group:</strong></label>

                        <label class="me-2">
                            <input type="radio" name="Employee_grp" value="A"
                                <?php echo ($row['Employee_grp'] == "A") ? "checked" : ""; ?>> All Executives
                        </label>

                        <label class="me-2">
                            <input type="radio" name="Employee_grp" value="B"
                                <?php echo ($row['Employee_grp'] == "B") ? "checked" : ""; ?>> All Non-Executives
                        </label>

                        <label class="me-2">
                            <input type="radio" name="Employee_grp" value="C"
                                <?php echo ($row['Employee_grp'] == "C") ? "checked" : ""; ?>> All Employees
                        </label>


                        <!-- <label class="me-2">
        <input type="radio" name="Employee_grp" value="Female" <?php echo ($row['Employee_grp'] == "Female") ? "checked" : ""; ?>> All Females
    </label> -->

                        <!-- <label>
        <input type="radio" name="Employee_grp" value="No One" <?php echo ($row['Employee_grp'] == "No One") ? "checked" : ""; ?>> No One
    </label> -->
                    </div>

                </div>
            </div>


            <button type="submit" class="btn btn-primary mt-3">Update</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>