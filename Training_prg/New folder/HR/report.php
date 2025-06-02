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

$query = "SELECT r.id,   r.PROGRAM_NAME,  r.year,  r.tentative_date, e.name, e.dept, e.email,  e.loc_desc,   r.flag,  r.hostel_book,  e.location
        FROM  [Complaint].[dbo].[request] r JOIN   [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
        WHERE r.flag = '8' ORDER BY  r.id DESC";

$result = sqlsrv_query($conn, $query);

if ($result === false) {
    die("Error fetching data: " . sqlsrv_errors());
}

// Fetch distinct program names for dropdown within the same query
$program_dropdown = "SELECT DISTINCT PROGRAM_NAME FROM [Complaint].[dbo].[request] WHERE flag = '8' ";
$program_result= sqlsrv_query($conn, $program_dropdown);

if ($program_result === false) {
    die("Error fetching program data: " . sqlsrv_errors());
}

?>


<div class='container'>
    <!-- <form class="updateForm" id="programForm">
        <select name="program_name" id="programDropdown">
            <?php
            while ($program_row = sqlsrv_fetch_array($program_result, SQLSRV_FETCH_ASSOC)) {
                echo "<option value='" . $program_row['PROGRAM_NAME'] . "'>" . $program_row['PROGRAM_NAME'] . "</option>";
            }
            
            ?>
        </select>
    </form> -->
    <br>
    <div class='scrollable'>
        <table id="programTable" class="table table-bordered border-success" border="3">
            <thead style="position: sticky; top: 0; background-color: beige; z-index: 1;">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Program Name</th>
                    <th>Year</th>
                    <th>Tentative Date</th>
                    <th>Department</th>
                    <th>Location</th>                   
                    <th>Status</th>
                    <th>Hostel Provided</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be dynamically loaded here -->
                <?php while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) { ?>
                            <tr class="table-light">
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['PROGRAM_NAME']; ?></td>
                                <td><?php echo $row['year']; ?></td>
                                <td><?php echo $row['tentative_date']; ?></td>
                                <td><?php echo $row['dept']; ?></td>
                                <td><?php echo $row['loc_desc']; ?></td>
                                
                                <td><?php echo ($row['flag'] == '8') ? 'OverAll Approved' : $row['flag']; ?></td>
                                <td style="color: <?php echo ($row['hostel_book'] == 2) ? 'green' : 'red'; ?>">
                                    <?php echo ($row['hostel_book'] == 2) ? 'Yes' : 'No'; ?>
                                </td>                               
                            </tr>
                        <?php } ?>
            </tbody>
        </table>
      
    </div>
    <br>
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


<script>
document.addEventListener('DOMContentLoaded', function() {
    var programDropdown = document.getElementById('programDropdown');
    var programTableBody = document.querySelector('#programTable tbody');
    var selectedProgramInput = document.getElementById('selectedProgram');

    programDropdown.addEventListener('change', function() {
        var selectedProgram = this.value;
        selectedProgramInput.value = selectedProgram;
        fetchProgramData(selectedProgram);
    });

    function fetchProgramData(programName) {
        programTableBody.innerHTML = ''; // Clear previous data
        fetch('fetch_program_data.php?program=' + programName)
            .then(response => response.text())
            .then(data => {
                programTableBody.innerHTML = data;
            })
            .catch(error => console.error('Error fetching program data:', error));
    }
});
</script>

</div><?php include '../footer.php';?>