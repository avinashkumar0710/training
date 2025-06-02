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
    <title>Training | BUH</title>
    <link rel="icon" href="../images/analysis.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


    <style>
    body {
        font-weight: 600;
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
    }
    .nav-link {
        color: #F8F9F9;
    }
    </style>
</head>
<?php include 'headerBUH.php';?>
        
        <br>
        <div class="container">
            <Note style="background-color:yellow;"><i>
            *The BUH Approve Closed Date is set 15 days after approval. If this date has passed, the nomination will be automatically rejected.*</i></Note>
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

//echo ''.$location;

// $query = "SELECT DISTINCT e.dept  
//           FROM [Complaint].[dbo].[request] r
//           JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
//           JOIN [Complaint].[dbo].[training_mast] tm ON r.srl_no = tm.srl_no
//           WHERE r.flag = '5' AND r.plant='$location'";

// $result = sqlsrv_query($conn, $query);

// if ($result === false) {
//     echo "Error fetching departments: " . sqlsrv_errors();
// } else {
//     $departments = array();
//     while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
//         $departments[] = $row['dept'];
//     }
// }
?>

<form id="dataForm" method="POST" action="fetch_dept_data.php"> <!-- Form for submitting department selection -->
    <!-- <select name="departmentSelect" id="departmentSelect">
        <option value="all">All Departments</option>
        <?php foreach ($departments as $dept) : ?>
            <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
        <?php endforeach; ?>
    </select> -->

    <!-- <button type="submit" id="updateButton" name="updateButton" class='btn btn-success'>Show Data</button> Submit button -->
</form>

<div id="dataTable">
    <!-- Table data will be displayed here -->
</div>

<script>
    $(document).ready(function() {
        // Fetch data on form submission
        $('#dataForm').submit(function(e) {
            e.preventDefault(); // Prevent default form submission
            var location = '<?php echo $location; ?>'; // Get the location from PHP
            var selectedDepartment = $("#departmentSelect").val(); // Get selected department
            fetchData(location, selectedDepartment); // Call fetchData function
        });
        
        // Function to fetch data based on selected department
        function fetchData(location, selectedDepartment) {
            // Submit the form using AJAX
            $.ajax({
                url: $('#dataForm').attr('action'),
                type: $('#dataForm').attr('method'),
                data: {
                    department: selectedDepartment,
                    location: location
                },
                success: function(response) {
                    // Update the table with the fetched data
                    $('#dataTable').html(response);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }

        // Fetch data with 'all' selected by default
        var location = '<?php echo $location; ?>';
        fetchData(location, 'all');
    });
</script>
</div>

<br>

 
<script>
    function selectAllCheckboxes(checkbox) {
    // Get all checkboxes in the table
    var checkboxes = document.querySelectorAll('.row-checkbox');

    // Loop through each checkbox and set its checked state
    checkboxes.forEach(function(cb) {
        cb.checked = checkbox.checked;
    });
}
    </script>


    </body>
</html>

<?php include '../footer.php';?>