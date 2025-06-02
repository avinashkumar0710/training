
<!---------------------------------Start Header Area------------------------------------>
<html>
<head>
    <title>Administrator</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"  rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-Nqsaw4xAiIHKD5Kl8XnI4SvRehhe2Q1zY4Kmz65+Io5yirI9fM95exW5bts/wPZt+WTfc1OQLdP35N0ZjoXKcA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
    body {
        font-weight: 600;
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        margin: 0; /* Remove default body margin */
        padding: 0; /* Remove default body padding */
        background-color: #e8eef3;
    }

    /* Ensure the video fills the width */
    video {
        width: 100%;
        height: auto; /* Maintain aspect ratio */
        display: block; /* Ensure the video is treated as a block element */
    }

    .my-custom-scrollbar {
        position: relative;
        height: 400px;
        overflow: auto;
        width: 650px;
        border-radius: 10px;
        border: 1px solid black;
        box-shadow: 5px 5px 5px #888888;
    }

    #dtBasicExample {
        border-radius: 25px;
        border: 2px solid yellowgreen;
    }

    .nav-link {
        color: #F8F9F9;
    }

    img {
        width: 100%;
        /* Set width to 100% to make the image responsive */
        height: 860px;
        /* Maintain aspect ratio */
        display: block;
        margin: 0 auto;
    }

    video {
      width: 100%;
      height: 90%;
      object-fit: contain; /* Adjusts the video to fit without distorting */
    }

    .scrollable {
            height: 650px;
            overflow-y: auto;
            border-color: black;
    }

    form#updateForm {
        display: inline-block;
    }

    form#updateForm button {
        margin-right: 10px; /* Adjust as needed */
    }
    </style>
</head>
<?php include '../header_HR.php';?>
<h6><i class='fa fa-home'></i>&nbsp;<i><u>HR->Administrator</u></i></h6>

<?php
session_start();
if (!isset($_SESSION["emp_num"])) {
    header("location:login.php");
    exit();
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

// Fetch Plant from EA_webuser_tstpp
$plantQuery = "SELECT Plant FROM [Complaint].[dbo].[EA_webuser_tstpp] WHERE emp_num LIKE ?";
$plantParams = array("%$sessionemp%");
$plantResult = sqlsrv_query($conn, $plantQuery, $plantParams);
$plantRow = sqlsrv_fetch_array($plantResult, SQLSRV_FETCH_ASSOC);
$userPlant = $plantRow['Plant'] ?? null;

// Fetch distinct locations for the plant dropdown
$plantQuery = "SELECT DISTINCT location FROM [Complaint].[dbo].[emp_mas_sap] ORDER BY location";
$plantResult = sqlsrv_query($conn, $plantQuery);

// Fetch distinct program names based on plant
$selectedPlant = ($userPlant !== "NS04") ? $userPlant : (isset($_POST['plant']) ? $_POST['plant'] : 'ALL');
$programQuery = "SELECT DISTINCT PROGRAM_NAME FROM [Complaint].[dbo].[request] WHERE flag='4'";

if ($selectedPlant !== 'ALL') {
    $programQuery .= " AND plant = '$selectedPlant'";
}

$programQuery .= " ORDER BY PROGRAM_NAME";
$programResult = sqlsrv_query($conn, $programQuery);

// Fetch data based on selected plant and program
$sql = "SELECT r.srl_no, r.id, r.empno, 
            CASE WHEN LEN(r.empno) = 6 THEN '00' + r.empno ELSE r.empno END AS update_empno, 
            r.PROGRAM_NAME, r.year, r.duration, r.faculty, r.plant, r.hostel_book, r.flag, t.day_from, t.day_to, r.uploaded_date, r.aprroved_time,
            e.email, e.name, e.dept, t.Closed_date 
        FROM [Complaint].[dbo].[request] r
        JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
        LEFT JOIN [Complaint].[dbo].[training_mast] t ON r.srl_no = t.srl_no
        WHERE r.flag = '4'";

if ($userPlant !== "NS04") {
    $sql .= " AND r.plant = '$userPlant'";
} elseif ($selectedPlant !== 'ALL') {
    $sql .= " AND r.plant = '$selectedPlant'";
}

if (isset($_POST['program']) && $_POST['program'] !== 'ALL') {
    $sql .= " AND r.PROGRAM_NAME = '" . $_POST['program'] . "'";
}

$sql .= " ORDER BY r.id DESC, r.year DESC";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Mapping plant codes to readable names
$plantNames = [
    'NS04' => 'Bhilai',
    'NS03' => 'Rourkela',
    'NS02' => 'Durgapur',
    'NS01' => 'Corporate Center'
];
?>

<div class="container mt-6">
    <!-- Main Form -->
    <div class="container mt-6">
    <!-- Form for Filtering (Auto-submit on Change) -->
    <form method="post" class="d-flex align-items-center gap-2">
        <!-- Plant Dropdown -->
        <label for="plant" class="fw-bold">Select Plant:</label>
        <select name="plant" id="plant" class="form-select w-auto" onchange="this.form.submit()" <?= ($userPlant !== "NS04") ? 'disabled' : '' ?>>
            <option value="ALL" <?= ($selectedPlant === 'ALL') ? 'selected' : '' ?>>All</option>
            <?php while ($row = sqlsrv_fetch_array($plantResult, SQLSRV_FETCH_ASSOC)) {
                $plantValue = $row['location'];
                $plantText = $plantNames[$plantValue] ?? $plantValue;
                $selected = ($plantValue == $selectedPlant) ? 'selected' : '';
                echo "<option value='$plantValue' $selected>$plantText</option>";
            } ?>
        </select>

        <!-- Program Dropdown -->
        <label for="program" class="fw-bold">Select Program:</label>
        <select name="program" id="program" class="form-select w-auto" onchange="this.form.submit()">
            <option value="ALL" <?= (!isset($_POST['program']) || $_POST['program'] == 'ALL') ? 'selected' : '' ?>>All</option>
            <?php while ($row = sqlsrv_fetch_array($programResult, SQLSRV_FETCH_ASSOC)) {
                $programValue = $row['PROGRAM_NAME'];
                $selected = (isset($_POST['program']) && $_POST['program'] == $programValue) ? 'selected' : '';
                echo "<option value='$programValue' $selected>$programValue</option>";
            } ?>
        </select>
    </form>

    <!-- Separate Form for Downloading Excel -->
    <form method="post" action="export_excel.php">
        <input type="hidden" name="selected_plant" value="<?= $selectedPlant ?>">
        <input type="hidden" name="selected_program" value="<?= isset($_POST['program']) ? $_POST['program'] : 'ALL' ?>">
        <button type="submit" class="btn btn-success">Download Excel</button>
    </form>
</div>


</div>


<!-- Wrap the table and button inside a form -->
<!-- Table Display -->
<form id="approveForm" action="approve_HOD.php" method="post">
    <div class='container-fluid' style="height: 600px; overflow: auto; width:100%;">
        <table class="table table-striped table-hover table-bordered border-primary" border="3">
            <thead class="table-warning" style="position: sticky; top: 0;  z-index: 2;">
                <tr>          
                    <th>Sl. No</th>                    
                    <th>Name</th>
                    <th>Program Name</th>
                    <th>Year</th>
                    <th>Duration</th>
                    <th>Day From</th>
                    <th>Day To</th>
                    <th>Faculty</th>
                    <th>Hostel Book</th>
                    <th>Department</th>
                    <th>Plant</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Approved Date</th>
                    <th>Approve / Reject</th>                   
                </tr>
            </thead>
            <tbody>
                <?php
                $serialNo = 1;
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    echo "<tr class='table-light'>";
                    echo "<td>" . $serialNo . "</td>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . $row['PROGRAM_NAME'] . "</td>";
                    echo "<td>" . $row['year'] . "</td>";
                    echo "<td>" . $row['duration'] . "</td>";
                    echo "<td>" . $row['day_from']->format('Y-m-d') . "</td>";
                    echo "<td>" . $row['day_to']->format('Y-m-d') . "</td>";
                    echo "<td>" . $row['faculty'] . "</td>";
                    echo "<td>" . ($row['hostel_book'] == 1 ? 'Yes' : 'No') . "</td>";
                    echo "<td>" . $row['dept'] . "</td>";
                    echo "<td>" . ($plantNames[$row['plant']] ?? "Unknown") . "</td>";
                    echo "<td>" . $row['email'] . "</td>";
                    echo "<td>" . ($row['flag'] == 4 ? 'Approve From Plant HOD' : 'Reject From HOD') . "</td>";  
                    echo "<td style='color:blue;'>" . $row['aprroved_time']->format('Y-m-d H:i:s') . "</td>";          
                    echo "<td>";
                    echo "<select name='approvalStatus[".$row['id']."]' class='approval-dropdown'>";
                    echo "<option></option>";
                    echo "<option value='99'>Approve by HR</option>";
                    echo "<option value='88'>Reject by HR</option>";
                    echo "</select>";
                    echo "</td>";                  
                    echo "</tr>";
                    $serialNo++;
                }                
                ?>
            </tbody>
        </table>
    </div><br>
    <div class="container">
    <div class="d-grid gap-2 col-6 mx-auto">
        <button type="submit" id="approveButton" name="approve" class="btn btn-primary">Submit</button>
        
    </div>
    
    </div>
</form>

<script>
        // Function to handle dropdown change event
        function handleDropdownChange(event) {
            // Retrieve the selected value from the dropdown
            const selectedValue = event.target.value;

            // Retrieve the row ID from the data attribute
            const rowId = event.target.dataset.rowId;

            // Retrieve the empno from the data attribute
            const empno = event.target.dataset.empno;

            // Log the selected value, row ID, and empno to the console
            console.log("Selected Value:", selectedValue);
            //console.log("Row ID:", rowId);
            //console.log("Empno:", empno);
        }

        // Get all dropdown elements with the class 'approval-dropdown'
        const dropdowns = document.querySelectorAll('.approval-dropdown');

        // Add event listener to each dropdown
        dropdowns.forEach(dropdown => {
            dropdown.addEventListener('change', handleDropdownChange);
        });
        </script>
        <script>
    // Function to handle form submission
    function validateForm(event) {
        // Get all dropdown elements
        const dropdowns = document.querySelectorAll('.approval-dropdown');
        
        // Variable to track if at least one dropdown is selected
        let isSelectionMade = false;
        
        // Check each dropdown
        dropdowns.forEach(dropdown => {
            // If a dropdown has a value selected
            if (dropdown.value !== '') {
                isSelectionMade = true;
            }
        });

        // If no dropdown has a value selected
        if (!isSelectionMade) {
            // Prevent form submission
            event.preventDefault();
            
            // Show error message or alert
            alert("Please select at least one 'Approve' or 'Reject' status.");
        }
    }

    // Get the approve form
    const approveForm = document.getElementById('approveForm');

    // Add event listener for form submission
    approveForm.addEventListener('submit', validateForm);
</script>



<?php include '../footer.php';?>