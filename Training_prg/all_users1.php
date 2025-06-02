<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $employeeno=$_SESSION["emp_num"];
//echo $_SESSION["emp_num"];
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
    <link rel="stylesheet" href="allusers.css">
    <title>Training Nomination</title>
   
</head>
<body>
<?php include 'header.php';?>
<h6><i class='fa fa-home'></i>&nbsp;<u><i>Home->Training Nomination</i></u></h6>
<?php           
            // Check if the user is authenticated
            if (!isset($_SESSION["emp_num"])) {
                header("location: login.php");
                exit;
            }        
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






 <!------------------------------------------------------------------------------------------------------------------------------------>   

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
<div class="container-fluid">

    <div class="scrollable-table">
    <table class="table table-bordered border-success" border="3" >
    <thead>
        <tr class="bg-primary">
        <th scope="col">SL. No</th> 

            <th scope="col">Program_name</th>
            <th scope="col">Nature of Training</th>
            <th scope="col">Duration</th>
            <th scope="col">Faculty</th>
            <th scope="col">tentative_date</th>
            <th scope="col">year</th>
            <th scope="col">Target_group</th>
            <th scope="col">Remarks</th>
            <th scope="col">Actions</th>
        </tr>
    </thead>
    <?php
    $serverName = "192.168.100.240";
    $connectionOptions = array(
        "Database" => "complaint",
        "UID" => "sa",
        "PWD" => "Intranet@123"
    );

    // Establishes the connection
    $conn = sqlsrv_connect($serverName, $connectionOptions);

    if (!$conn) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch data from the database
    $sql = "SELECT * FROM [Complaint].[dbo].[training_mast]";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Initialize a counter for serial number
    $serialNo = 1;

    // Fetch and display each row
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo "<tbody id='tbl_body'>";
        echo "<tr class='table-light'>";
        echo "<td>{$row['srl_no']}</td>";
        echo "<td>{$row['Program_name']}</td>";
        echo "<td>{$row['nature_training']}</td>";
        echo "<td>{$row['duration']}</td>";
        echo "<td>{$row['faculty']}</td>";
        echo "<td>{$row['tentative_date']}</td>";
        echo "<td>{$row['year']}</td>";
        echo "<td>{$row['target_group']}</td>";
        echo "<td><input type='text' name='remarks[]' data-id='{$row['srl_no']}' placeholder='Enter remarks'></td>";
        echo "<td>
            <label class='checkbox-container'>
                <input type='checkbox' name='selectedIds[]' value='{$row['srl_no']}' onchange='updateSubmitButton()'>
                <span class='checkmark'></span>
            </label>
        </td>";
        echo "</tr>";
        
        // Increment the serial number for the next iteration
        $serialNo++;
    }
    echo "</tbody>";

    

    // Close the connection
    sqlsrv_close($conn);
    ?>
</table>
<script>
    // Function to update data based on user input
    function updateData() {
        var searchQuery = document.getElementById('search_param').value.trim().toLowerCase();
        console.log('Search Query:', searchQuery);

        // Check if there is a search query
        if (searchQuery !== '') {
            // Fetch and update data based on searchQuery
            fetch('fetch.php?search=' + searchQuery)
                .then(response => response.json())
                .then(data => {
                    // Update the HTML content with the fetched data and highlight the search term
                    var tableHtml = '';  // Initialize the table HTML
                    data.forEach(row => {
                        // Highlight the search term in each column
                        for (var key in row) {
                            if (row.hasOwnProperty(key) && typeof row[key] === 'string') {
                                row[key] = highlightSearchTerm(row[key], searchQuery);
                            }
                        }

                        // Construct the table row
                        tableHtml += '<tr class="table-light">';
                        tableHtml += '<td>' + row.srl_no + '</td>';
                        tableHtml += '<td>' + row.Program_name + '</td>';
                        tableHtml += '<td>' + row.nature_training + '</td>';
                        tableHtml += '<td>' + row.duration + '</td>';
                        tableHtml += '<td>' + row.faculty + '</td>';
                        tableHtml += '<td>' + row.tentative_date + '</td>';
                        tableHtml += '<td>' + row.year + '</td>';
                        tableHtml += '<td>' + row.target_group + '</td>';
                        tableHtml += '<td><input type="text" name="remarks[]" data-id="' + row.srl_no + '" placeholder="Enter remarks"></td>';
                        tableHtml += '<td><label class="checkbox-container"><input type="checkbox" name="selectedIds[]" value="' + row.srl_no + '" onchange="updateSubmitButton()"><span class="checkmark"></span></label></td>';
                        tableHtml += '</tr>';
                    });

                    // Update the HTML content
                    document.getElementById('tbl_body').innerHTML = tableHtml;
                })
                .catch(error => console.error('Error:', error));
        } else {
            // Fetch and update default data
            fetch('fetch.php')
                .then(response => response.json())
                .then(data => {
                    // Update the HTML content with the fetched data
                    var tableHtml = '';  // Initialize the table HTML
                    data.forEach(row => {
                        // Construct the table row
                        tableHtml += '<tr class="table-light">';
                        tableHtml += '<td>' + row.srl_no + '</td>';
                        tableHtml += '<td>' + row.Program_name + '</td>';
                        tableHtml += '<td>' + row.nature_training + '</td>';
                        tableHtml += '<td>' + row.duration + '</td>';
                        tableHtml += '<td>' + row.faculty + '</td>';
                        tableHtml += '<td>' + row.tentative_date + '</td>';
                        tableHtml += '<td>' + row.year + '</td>';
                        tableHtml += '<td>' + row.target_group + '</td>';
                        tableHtml += '<td><input type="text" name="remarks[]" data-id="' + row.srl_no + '" placeholder="Enter remarks"></td>';
                        tableHtml += '<td><label class="checkbox-container"><input type="checkbox" name="selectedIds[]" value="' + row.srl_no + '" onchange="updateSubmitButton()"><span class="checkmark"></span></label></td>';
                        tableHtml += '</tr>';
                    });

                    // Update the HTML content
                    document.getElementById('tbl_body').innerHTML = tableHtml;
                })
                .catch(error => console.error('Error:', error));
        }
    }

    // Function to highlight the search term in a string
    function highlightSearchTerm(text, searchTerm) {
        return text.replace(new RegExp(searchTerm, 'gi'), match => `<span style="background-color: yellow;">${match}</span>`);
    }

    // Initial data update
    // updateData();
</script>


    </div><br>
    <form action='request.php' method='post'  id='dataForm'>
    <input type='hidden' name='selectedData' id='selectedDataInput'>
        <!-- <button type='submit' name='request' class='btn btn-success'>Submit Request</button> -->
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

            var data = {
                srl_no: srl_no,
                remarks: remarks
            };

            selectedData.push(data);
        });

        // Log the selected data to the console
        console.log('Selected Data:', selectedData);
        console.log('Debug - Selected Data (JS):', selectedData);

        // Set the selected data as a value of a hidden input field
    document.getElementById('selectedDataInput').value = JSON.stringify(selectedData);

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

<!-------------------------------------------Pending and approve request------------------------------------------------------------>

</div>
    


</body>
</html>