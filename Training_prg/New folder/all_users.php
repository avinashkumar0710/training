<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
    // Redirect to login.php if 'emp_num' is not set
    header("location:login.php");
    exit; // Ensure no further code is executed after the redirect
}

// Get the session value
$sessionemp = $_SESSION["emp_num"];
//echo "Session Employee Number: " . $sessionemp . "<br>";

// Add '00' in front if the session value has exactly 6 digits
if (strlen($sessionemp) === 6) {
    $sessionemp1 = '00' . $sessionemp;
} else {
    $sessionemp1 = $sessionemp; // If not 6 digits, keep the original value
}

//echo "Modified Employee Number: " . $sessionemp1 . "<br>";

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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script> -->
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>  

    
   
      

    <title>Training Nomination</title>
    <style> 
    
    body{
        background-color: #e8eef3;
    }
        .scrollable-table {
            height: 580px;
            overflow-y: auto;
            border-color: black;
            
        }

        .scrollable1 {
            height: 760px;
            overflow-y: auto;
            border-color: black;
        }
        .row{
            padding:0px;
            display: flex;
        justify-content: space-between;
        max-width:100%;
        }

        .checkbox-container {
            display: inline-block;
            position: relative;
            padding-left: 15px;
            margin-bottom: 15px;
            cursor: pointer;
        }

        .checkbox-container input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .checkmark {
            position: absolute;
            top: 5;
            left: 20;
            height: 25px;
            width: 25px;
            background-color: #fff;
            border: 1px solid #ccc;
        }

        .checkbox-container input:checked + .checkmark {
            background-color: #28a745; /* Set the color you want when the checkbox is checked */
            border-color: #28a745; /* Set the border color you want when the checkbox is checked */
        }

      
        th {color: white;}

        table table-bordered border-success tbody thead{
            width:40%;
        }
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 12px;
            z-index: 1;
            width: 200px;
        }

        .dropdown-item {
            cursor: pointer;
           
        }

        #searchInput {
            margin-top: 10px;
            padding: 8px;
        }

        body{
        font-weight: 600;
    font-style: normal;
    font-family: "Nunito Sans", sans-serif;
    }

   .disabled-row {
    color: red; /* Red color */
}

.blue-row {
    color: blue; /* Red color */
}

.green-row {
    color: green; /* Red color */
}

.action-column {
        width: 100px; /* Adjust the width as needed */
    }

.custom-modal-body {
            max-height: 400px; /* Set the maximum height of the modal body */
            overflow-y: auto;  /* Enable vertical scrolling if content exceeds max height */
}    

.pdf-list-item {
    line-height: 1.5; /* Set line height */
}

</style>
</head>
<?php include 'header.php';
//echo "Exact HOD Empno: " . $exactHodEmpno;?>
    <h6><i class='fa fa-home'></i>&nbsp;<u><i>Home->Training Nomination</i></u></h6>
<?php           
            // Check if the user is authenticated
            if (!isset($_SESSION["emp_num"])) {
                header("location: login.php");
                exit;
            }
            $deptcodebhilai= '0300';
            $deptcodecoorp= '6300';
            $name = "SELECT emp_name, access, dept_code FROM EA_webuser_tstpp WHERE emp_num = ?";
            $params = array($_SESSION['emp_num']);
            $stmt = sqlsrv_query($conn, $name, $params);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            if (sqlsrv_has_rows($stmt)) {
                // Get the user name from the result set
                 $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                $username = $row['emp_name'];
                $access = $row['access'];
                $deptcode =$row['dept_code'];
            } 
    ?>

    <?php
   
    ?>
    
    <!-- <div class="container">
    
</div> -->

 <!------------------------------------------------------------------------------------------------------------------------------------>   
<body>
<div class="container-fluid">
<div style="display: flex;align-content: space-around;flex-direction: row-reverse;justify-content: center;align-items: center; ">
<button id="openModal" class="btn btn-primary btn-sm" style="margin-left: 15px;"><i class="fa fa-file-pdf-o" style="color:red" aria-hidden="true"></i> View Program Details</button>
    <form method="POST">
        <label for="year">Select a year:</label>
        <select name="year" id="year">
            <option value="" disabled selected>Select year</option>
            <?php
            // Establishes the connection
            $serverName = "192.168.100.240";
            $connectionOptions = array(
                "Database" => "complaint",
                "UID" => "sa",
                "PWD" => "Intranet@123"
            );

            $conn = sqlsrv_connect($serverName, $connectionOptions);

            if (!$conn) {
                die(print_r(sqlsrv_errors(), true));
            }

            // SQL query to fetch distinct years
            $distinctYearsQuery = "SELECT DISTINCT year FROM [Complaint].[dbo].[training_mast]";
            $yearsResult = sqlsrv_query($conn, $distinctYearsQuery);

            if ($yearsResult) {
                // Loop through distinct years and generate options
                while ($yearRow = sqlsrv_fetch_array($yearsResult, SQLSRV_FETCH_ASSOC)) {
                    $yearValue = $yearRow['year'];
                    $selectedAttr = (isset($_POST['year']) && $_POST['year'] == $yearValue) ? 'selected' : '';
                    echo "<option value=\"$yearValue\" $selectedAttr>$yearValue</option>";
                }
            }

            $sqlCount = "SELECT COUNT(empno) AS total_count FROM [Complaint].[dbo].[request] WHERE empno = ?";
            $paramsCount = array($employeeNumber);
            $stmtCount = sqlsrv_query($conn, $sqlCount, $paramsCount);

            if ($stmtCount === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            $totalCountRow = sqlsrv_fetch_array($stmtCount, SQLSRV_FETCH_ASSOC);
            $totalCount = $totalCountRow['total_count'];
            $remainingRequests = 8 - $totalCount; // Assuming the limit is 8

            // Check if the count exceeds the limit
            if ($totalCount >= 8) {
                echo '<script>alert("You have reached the maximum limit of training requests!");</script>';
            }

            sqlsrv_free_stmt($stmtCount);

            // Close the SQL Server connection
            sqlsrv_close($conn);


            ?>
        </select>&nbsp;&nbsp;
        <button type="submit" class="btn btn-info">Show Programs</button>
    </form>&nbsp;&nbsp;&nbsp;

    <div class="search-container">
        <input type="text" id="search_param" class="form-control" name="search" placeholder="Search Program Name">
        <!-- <div id="liveSearchResults"></div> -->
        
    </div>
        
    </div>


    <!-----------------------------------modalup program files-------------------------------------------------------->
    <!-- <div class="program-list">
    
</div> -->

<!-- Modal Structure -->
<div id="pdfModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Available PDF Files</h5>
                
            </div>
            <div class="modal-body custom-modal-body">
                <ul id="pdfList">
                    <!-- PDF files will be dynamically populated here -->
                </ul>
            </div>
           
        </div>
    </div>
</div>

        <?php
        // PHP code to list PDF files
        $directoryPath = 'HR/uploads/';
        $pdfFiles = glob($directoryPath . '*.pdf'); // Fetch all PDF files

        // Create a JSON array to hold the file names
        $fileArray = array();
        foreach ($pdfFiles as $file) {
            $fileName = basename($file); // Get the file name without path
            $fileArray[] = $fileName;
        }
        ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Wait for the document to be ready
document.addEventListener('DOMContentLoaded', function () {
    const pdfList = document.getElementById('pdfList');
    const pdfFiles = <?php echo json_encode($fileArray); ?>; // Pass PHP array to JavaScript

    // Populate the PDF list in the modal
    pdfFiles.forEach(function(file) {
    const li = document.createElement('li');
    li.style.lineHeight = '2.5'; // Set line height for each list item
    li.innerHTML = `<i class="fa fa-file-pdf-o" style="color:red" aria-hidden="true"></i> 
                    <a href="HR/uploads/${file}" target="_blank">${file}</a>`;
    pdfList.appendChild(li);
});


    // Show the modal on button click
    document.getElementById('openModal').addEventListener('click', function () {
        const modal = new bootstrap.Modal(document.getElementById('pdfModal'));
        modal.show();
    });
});
</script>
    
<!-----------------------------------modalup program files ends-------------------------------------------------------->

<?php 
// Count the total training requests for the employee


?>


    <br>
<h3>Training Nomination List&nbsp;<i style="font-size:small; background-color:yellow;">
&nbsp;&nbsp;(Note: <span style ="color:green"> <b>Green</b> </span>indicates Request from reporting Officer and <span style ="color:Blue"> <b>Blue</b></span> indicated request byself.)</i>
Your total training requests: <strong><?php echo $totalCount; ?></strong>
Remaining requests allowed: <strong><?php echo $remainingRequests; ?></strong></h3>

<div class="scrollable-table">  
    
<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['year'])) {
    // Establish the connection
    $conn = sqlsrv_connect($serverName, $connectionOptions);

    if (!$conn) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch data from the database based on the selected year
    $selectedYear = $_POST['year'];

    // SQL query with a LEFT JOIN to the request table
    $sql = "SELECT t.[srl_no],
    t.[Program_name],
    t.[nature_training],
    t.[duration],
    t.[faculty],
    t.[training_mode],
    t.[tentative_date],
    t.[Internal_external],
    t.[year],
    t.[target_group],
    t.[venue],                  
    t.[coordinator],
    t.[remarks],
    t.[Closed_date],
    r.rep_ofcr,
    r.ordinate_req,
    r.[srl_no] AS request_srl_no
FROM [Complaint].[dbo].[training_mast] t
LEFT JOIN [Complaint].[dbo].[request] r ON t.srl_no = r.srl_no AND r.empno = ?
WHERE t.[year] = ?
";

    $params = array($sessionemp, $selectedYear);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Initialize a counter for serial number
    $serialNo = 1;
    $disabledRowCount = 0;
    // Fetch and display each row
    echo "<table class='table table-bordered border-success' border='3'>";
    echo "<thead style='position: sticky; top: 0; background-color: beige;z-index: 1;'>";
    echo "<tr class='bg-primary'>";
    echo "<th scope='col'>SL. No</th>";
    echo "<th scope='col'>Program_name</th>";
    echo "<th scope='col'>Nature of Training</th>";
    echo "<th scope='col'>Duration</th>";
    echo "<th scope='col'>Faculty</th>";
    echo "<th scope='col'>Training Mode</th>";
    
    echo "<th scope='col'>Tentative Date</th>";
    echo "<th scope='col'>Internal / External</th>";
    echo "<th scope='col'>Year</th>";
    echo "<th scope='col'>Target Group</th>";
    echo "<th scope='col'>Venue</th>";
    echo "<th scope='col'>Coordinator</th>";
    echo "<th scope='col'>Remarks by Admin</th>";
    echo "<th scope='col'>Hostel Required</th>";
    echo "<th scope='col'>Remarks from Employee</th>";
    echo "<th scope='col' class='action-column'>Actions</th>";
    echo "</tr>";
    echo "</thead>";

    echo "<tbody id='tbl_body'>";
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Check if the row is present in the 'request' table or Closed_date has passed
        $disabled = ''; // Default state
        $rowClass = ''; // Default row class

        if ($row['request_srl_no'] || ($row['Closed_date'] instanceof DateTime && $row['Closed_date']->format('Y-m-d') < date('Y-m-d'))) {
            $disabled = 'disabled'; // Disable if present in request table or Closed_date has passed
            $rowClass = 'disabled-row'; // Class for styling disabled rows
            $disabledRowCount++; // Increment the disabled row counter
        } else {
            $disabled = ''; // Enable if not in request table and Closed_date has not passed or is null
            $rowClass = ''; // No specific class for enabled rows
        }
         

        // Check if Closed_date is less than the current date
        if ($row['Closed_date'] instanceof DateTime && $row['Closed_date']->format('Y-m-d') < date('Y-m-d')) {
            $disabled = 'disabled';  // Disable if Closed_date has passed
            $rowClass = 'disabled-row';  // Add class for styling disabled rows
            $disabledRowCount++; // Increment the disabled row counter
        } else {
            $disabled = '';  // Enable if Closed_date is null or has not passed
            $rowClass = '';  // No specific class for enabled rows
        }
        

        
        if (!empty($row['rep_ofcr'])) {
            $rowClass = 'blue-row';
        } elseif (!empty($row['ordinate_req'])) {
            $rowClass = 'green-row';
        }

        echo "<tr class='table-light $rowClass'>";
        echo "<td>$serialNo</td>";
        echo "<td>{$row['Program_name']}</td>";
        echo "<td>{$row['nature_training']}</td>";
        echo "<td>{$row['duration']}</td>";
        echo "<td>{$row['faculty']}</td>";
        echo "<td>{$row['training_mode']}</td>";
        echo "<td>{$row['tentative_date']}</td>";
        echo "<td>{$row['Internal_external']}</td>";
        echo "<td>{$row['year']}</td>";
        echo "<td>{$row['target_group']}</td>";
        echo "<td>{$row['venue']}</td>";
        echo "<td>{$row['coordinator']}</td>"; 
        echo "<td>{$row['remarks']}</td>"; 

        // Display Hostel Required dropdown
        echo "<td>
                <select name='hostel_required[]' data-id='{$row['srl_no']}' $disabled>
                    <option value='1'>Yes</option>
                    <option value='0'>No</option>
                </select>
            </td>";

        // Display Remarks input
        echo "<td><input type='text' name='remarks[]' data-id='{$row['srl_no']}' placeholder='Enter remarks' $disabled></td>";

        // Display Checkbox
        echo "<td>
                <label class='checkbox-container'>
                    <input type='checkbox' name='selectedIds[]' value='{$row['srl_no']}' onchange='updateSubmitButton()' $disabled>
                    <span class='checkmark'></span>
                </label>
            </td>";

        echo "</tr>";

        // Increment the serial number for the next iteration
        $serialNo++;
    }

    echo "</tbody>";
    echo "</table>";

    // Close the connection
    sqlsrv_close($conn);
    //echo ' $disabledRowCount1' . $disabledRowCount;
    // If disabledRowCount is greater than 8, disable all checkboxes using JavaScript
    if ($disabledRowCount >= 8) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                var checkboxes = document.querySelectorAll('input[type=checkbox]');
                checkboxes.forEach(function(checkbox) {
                    checkbox.disabled = true;
                });
            });
        </script>";
    }
}
?>


    </div>
    <script>
   function updateData() {
    var year = document.getElementById('year').value;
    var searchParam = document.getElementById('search_param').value;

    // Use AJAX to fetch data dynamically
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            // Replace the content of liveSearchResults with the live search results
            document.getElementById('tbl_body').innerHTML = this.responseText;
        }
    };
    xhttp.open("GET", "live_search.php?year=" + year + "&search=" + searchParam, true);
    xhttp.send();
}

// Trigger the updateData function on input in the search input
document.getElementById('search_param').addEventListener('input', updateData);
</script>




    <br>
    <form action='request.php' method='post'  id='dataForm'>
    <input type='hidden' name='selectedData' id='selectedDataInput'>
        <!-- <button type='submit' name='request' class='btn btn-success'>Submit Request</button> -->
        <input type='hidden' name='exactHodEmpno' id='exactHodEmpnoInput' value='<?php echo $exactHodEmpno; ?>'>
        <button type='submit' name='request' onclick='logSelectedData()' id='submitButton' class='btn btn-success' disabled>Submit Request</button>

    </form>
</div>
    <script>
        
    function logSelectedData() {
        // Array to store data of each selected checkbox
        var selectedData = [];

        // Get all checkboxes that are checked
        var checkboxes = document.querySelectorAll('input[name="selectedIds[]"]:checked');

        // Loop through each checkbox and extract data
        checkboxes.forEach(function (checkbox) {
            var srl_no = checkbox.value;
            var remarksInput = document.querySelector('input[name="remarks[]"][data-id="' + srl_no + '"]');
            var remarks = remarksInput ? remarksInput.value : '';  // Get remarks value or set it to an empty string if not found

            var hostelSelect = document.querySelector('select[name="hostel_required[]"][data-id="' + srl_no + '"]');
            var hostelRequired = hostelSelect ? hostelSelect.value : '';  // Get hostel_required value or set it to an empty string if not found

            var data = {
                srl_no: srl_no,
                remarks: remarks,
                hostel_required: hostelRequired
            };

            selectedData.push(data);
        });

        // Log the selected data to the console
        console.log('Selected Data:', selectedData);
        console.log('Debug - Selected Data (JS):', selectedData);

        // Set the selected data as a value of a hidden input field
    document.getElementById('selectedDataInput').value = JSON.stringify(selectedData);

    // Set the exact HOD Empno as a value of a hidden input field
    document.getElementById('exactHodEmpnoInput').value = '<?php echo $exactHodEmpno; ?>';

// Submit the form
document.getElementById('dataForm').submit();
    }


    function updateSubmitButton() {
        var checkboxes = document.querySelectorAll('input[name="selectedIds[]"]:checked');
        var submitButton = document.getElementById('submitButton');

        // Enable the button if any checkbox is checked, otherwise disable it
        submitButton.disabled = checkboxes.length === 0;
    }

    // Attach the updateSubmitButton function to the change event of checkboxes
    var checkboxes = document.querySelectorAll('input[name="selectedIds[]"]');
    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', updateSubmitButton);
    });
    </script>

<!-------------------------------------------Pending and approve and Reject request------------------------------------------------------------>

</div>
    


</body>
<?php include '../footer.php';?>
</html>
