<?php 
// start a new session
// Allow any origin to access this resource

session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }

    $sessionemp= $_SESSION["emp_num"];

    // Add '00' in front if session value has only 6 digits
    if(strlen($sessionemp) == 6) {
        $sessionemp = '00' . $sessionemp;
    }
    //echo 'empno' .$sessionemp;
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
?>
<!---------------------------------Start Header Area------------------------------------>
<html>
<head>
    <title>Overall Training Report</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
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
            height: 600px;
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
      <h6><i class='fa fa-home'></i>&nbsp;<i><u>HR->Final Report</u></i></h6>        
      <?php
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
    exit();
}

$sessionemp = $_SESSION["emp_num"] ?? '';

//echo $sessionemp;

// Database Connection
$serverName = "192.168.100.240";
$connectionInfo = [
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch Plant from EA_webuser_tstpp
$plantQuery = "SELECT Plant FROM [Complaint].[dbo].[EA_webuser_tstpp] WHERE emp_num LIKE ?";
$plantParams = ["$sessionemp%"];
$plantResult = sqlsrv_query($conn, $plantQuery, $plantParams);
$plantRow = sqlsrv_fetch_array($plantResult, SQLSRV_FETCH_ASSOC);
$userPlant = $plantRow['Plant'] ?? null;

//echo $userPlant;

// Fetch distinct program names for dropdown based on user plant
$programQuery = "SELECT DISTINCT r.PROGRAM_NAME  ,e.location
                 FROM [Complaint].[dbo].[request] r
                 JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
                 LEFT JOIN [Complaint].[dbo].[training_mast] t ON r.srl_no = t.srl_no
                 WHERE r.flag = '7'";
                 
if ($userPlant !== 'NS04') {
    $programQuery .= " AND e.location = ?";
    $programParams = [$userPlant];
} else {
    $programParams = [];
}
$programResult = sqlsrv_query($conn, $programQuery, $programParams);

?>


<div class='container'>
    <form id="filterForm">
        <label for="programDropdown"><b>Filter by Program Name:</b></label>
        <select name="program" id="programDropdown" class="form-select">
    <option value="">-- All Programs --</option>
    <?php
    while ($programRow = sqlsrv_fetch_array($programResult, SQLSRV_FETCH_ASSOC)) {
        echo "<option value='" . $programRow['PROGRAM_NAME'] . "'>" . $programRow['PROGRAM_NAME'] . "</option>";
    }
    ?>
</select>
    </form>

    <br>
    <div class='scrollable'>
        <table id="programTable" class="table table-bordered border-success">
            <thead style="position: sticky; top: 0; background-color: beige; z-index: 1;">
                <tr>
                    <th>SL</th>
                    <th>Name</th>
                    <th>Program Name</th>
                    <th>Year</th>                    
                    <th>Day From</th>
                    <th>Day To</th>
                    <th>Department</th>
                    <th>Location</th>                   
                    <th>Status</th>
                    <th>Hostel Provided</th>
                </tr>
            </thead>
            <tbody id="tableData">
                <!-- Data will be dynamically loaded here -->
            </tbody>
        </table>
    </div>

    
    <form class="updateForm" action="downloadexcel.php" method="post" id="downloadForm">
    <input type="hidden" name="selected_program" id="selectedProgram" value="">
    <input type="hidden" name="location" value="<?php echo htmlspecialchars($location); ?>">
    <input type="hidden" name="year" value="<?php echo htmlspecialchars($selected_year); ?>">
    <button type="submit" id="downloadButtonExcel" class="btn btn-success">
        <i class="fas fa-file-excel"></i> Download as Excel
    </button>
    <button type="submit" id="downloadButtonPDF" class="btn btn-danger" formaction="downloadpdf.php">
        <i class="fas fa-file-pdf"></i> Download as PDF
    </button>
</form>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function loadTable(programName = '', userPlant = '') {
        $.ajax({
            url: "fetch_all_data.php",
            type: "POST",
            data: { program_name: programName, user_plant: userPlant },
            success: function(data) {
                $("#tableData").html(data);
            }
        });
    }

    // Load all data initially
    loadTable('', '<?php echo $userPlant; ?>');

    // Filter when dropdown changes
    $("#programDropdown").change(function() {
        var selectedProgram = $(this).val();
        $("#selectedProgram").val(selectedProgram); // Set hidden input for download
        loadTable(selectedProgram, '<?php echo $userPlant; ?>');
    });
});
</script>

</div><?php include '../footer.php';?>