<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["emp_num"])) {
    header("Location: login.php");
    exit;
}

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

// Only fetch records for the logged-in user
//$empNum = $_SESSION["emp_num"];

?>


<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Mandays</title>
    <link rel="icon" href="../images/analysis.png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add this in your <head> section -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    </head>
    <style>
        .row { 
        display: flex; 
        flex-wrap: nowrap; 
    }

    </style>
<?php include 'header.php';?>
<body>

<br>
<center>
<div class="container my-4" style="color: #f8f9fa; border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);">
    <div class="row align-items-end gx-2">

        <div class="col-auto">
            <label for="empno" class="form-label" style="color: brown;">Emp No</label>
            <select class="form-select" id="empno">
                <option value="" disabled selected>Select Emp No</option>
            </select>
        </div>

        <div class="col-auto">
            <label for="employee_grp" class="form-label" style="color: brown;">Emp Group</label>
            <select class="form-select" id="employee_grp">
                <option value="" selected>Select Group</option>
                <option value="Executive">Executive</option>
                <option value="Non-executive">Non-executive</option>
            </select>
        </div>

        <div class="col-auto">
            <label for="grade" class="form-label" style="color: brown;">Grade</label>
            <select class="form-select" id="grade">
                <option value="" disabled selected>Select Grade</option>
            </select>
        </div>

        <div class="col-auto">
            <label for="dept" class="form-label" style="color: brown;">Department</label>
            <select class="form-select" id="dept">
                <option value="" disabled selected>Select Department</option>
            </select>
        </div>

        <div class="col-auto">
            <label for="plant" class="form-label" style="color: brown;">Plant</label>
            <select class="form-select" id="plant">
                <option value="" disabled selected>Select Plant</option>
            </select>
        </div>

        <div class="col-auto">
            <label for="duration" class="form-label" style="color: brown;">Mandays</label>
            <select class="form-select" id="duration">
                <option value="" disabled selected>Select Duration</option>
            </select>
        </div>

        <div class="col-auto">
            <label for="from_date" class="form-label" style="color: brown;">From Date</label>
            <input type="date" id="from_date" class="form-control">
        </div>

        <div class="col-auto">
            <label for="to_date" class="form-label" style="color: brown;">To Date</label>
            <input type="date" id="to_date" class="form-control">
        </div>

        <div class="col-auto">
            <label class="form-label invisible">Clear</label>
            <button type="button" class="btn btn-secondary" onclick="clearFilters()">Clear</button>
        </div>

        <div class="col-auto">
            <form method="post" action="export_excel.php" class="d-inline">
                <input type="hidden" name="selected_year" value="<?php echo htmlspecialchars($selectedYear); ?>">
                <button type="submit" class="btn btn-success">Download Excel</button>
            </form>
        </div>

    </div>
</div>

        <table class="table table-bordered border-success" border="1" style="font-size:14px; width: 100%;">
            <thead style="position: sticky; top: 0; color: beige; z-index: 1;">
                <tr class="bg-primary" style="color:#ffffff">
                    <th>SL No</th>
                    <th>Employee Name</th>
                    <th>Emp No</th>
                    <th>Plant</th>
                    <th>Dept</th>
                    <th>Grade</th>
                    <th>Employee Group</th>
                    <th>Training Location</th>
                    <th>Program ID</th>
                    <th>Program Name</th>
                    <th>Nature of Training</th>
                    <th>Training Subtype</th>
                    <th>Training Mode</th>
                    <th>Faculty</th>
                    <th>Duration</th>
                    <th>Mandays</th>
                    <th>Attendance</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Year</th>
                </tr>
            </thead>
            <tbody id="data-body">
                <!-- Data will be loaded here via AJAX -->
            </tbody>
        </table>
    </div>

    <script>
    function fetchData() {
        $.ajax({
            url: 'filter_data.php',
            method: 'GET',
            data: {
                empno: $('#empno').val(),
                employee_grp: $('#employee_grp').val(),
                grade: $('#grade').val(),
                dept: $('#dept').val(),
                plant: $('#plant').val(),
                duration: $('#duration').val(),
                from_date: $('#from_date').val(),
                to_date: $('#to_date').val()
            },
            success: function(response) {
                $('#data-body').html(response);
            }
        });
    }

    // Fetch data when any of the dropdowns change
    $('#empno, #employee_grp, #grade, #dept, #plant, #duration').on('change', function() {
        fetchData();
    });

    // Keep the existing behavior for date pickers (on input change)
    $('#from_date, #to_date').on('input change', function() {
        fetchData();
    });

    $(document).ready(function () {
        // Load initial data and populate the dropdowns
        $.ajax({
            url: 'get_dropdown_data.php', // New PHP file to fetch dropdown data
            method: 'GET',
            dataType: 'json', // Expect JSON response
            success: function(data) {
                populateDropdown('#empno', data.empno);
                populateDropdown('#grade', data.grade);
                populateDropdown('#dept', data.dept);
                populateDropdown('#plant', data.plant);
                populateDropdown('#duration', data.duration);
                fetchData(); // Load initial table data
            }
        });
    });

    function populateDropdown(selector, values) {
        var options = '<option value="" disabled selected>' + $(selector).find('label').text().replace(':', '') + '</option>';
        $.each(values, function(i, value) {
            options += '<option value="' + value + '">' + value + '</option>';
        });
        $(selector).html(options);
    }

    // Clear button functionality
    function clearFilters() {
        $('#empno').val('').prop('selectedIndex', 0);
        $('#employee_grp').val('').prop('selectedIndex', 0);
        $('#grade').val('').prop('selectedIndex', 0);
        $('#dept').val('').prop('selectedIndex', 0);
        $('#plant').val('').prop('selectedIndex', 0);
        $('#duration').val('').prop('selectedIndex', 0);
        $('#from_date').val('');
        $('#to_date').val('');
        fetchData();
    }
</script>
</body>
<?php include 'footer.php';?>
</html>
