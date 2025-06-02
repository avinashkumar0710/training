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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/analysis.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>

    <title>Excel Upload Page</title>
    <style>  
        .scrollable-table {
            height: 600px;
            overflow-y: auto;
            border-color: black;
        }

        .scrollable1 {
            height: 600px;
            overflow-y: auto;
            border-color: black;
        }
        .container{
            padding:20px;
            display: flex;
        justify-content: space-between;
        max-width:100%;
        }

        .checkbox-container {
            display: inline-block;
            position: relative;
            padding-left: 25px;
            margin-right: 15px;
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

        .sixtyper{
                    width: 100%;                    
                }
        .fortyper{
            width:40%;
            padding:10px;
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
            padding: 8px;
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

</style>
</head>
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

            //echo 'empl' .$sessionemp;
            //echo 'dept' .$deptcode;

            $hod = 0;
            $sqlhod = "SELECT * FROM [Complaint].[dbo].[emp_mas_sap] WHERE hod_ro = $sessionemp";
            $stmt = sqlsrv_query($conn, $sqlhod);
            
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            // else {
            //     $hod = 1;
            //  }
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

                
                // $hod_ro = $row['hod_ro'];
                // echo 'hod:' .$hod_ro;
                // Now you can use $hod_ro as needed

                $hod = 1;
            }
            $hremp = 0;
            $accessValue = null;
            $sql = "SELECT empno, access FROM [Complaint].[dbo].[Training_HR_User] where empno= $sessionemp";
            $stmt = sqlsrv_query($conn, $sql);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                // Store the access value in a variable
                // $accessValue = $row['access'];            
                // echo 'Empno: ' . $row['empno'] . ', Access: ' . $accessValue . '<br>';

                $hremp = 1;
            }                                                                                                           
    ?>
<body>
<div class='card text-center'>
        <div class='card-header'>
               <b><i><SPAN style='background-color:yellow'> <?php echo $username; ?>
                    </SPAN></i></b>&nbsp;&nbsp;
            <a href='signout.php'><input type='submit' class='btn btn-success btn-sm' value='LOGOUT'></a>&nbsp;
        </div>
    </div>
    <ul class='nav justify-content-center' style='background-color: #34495E;'>
    <li class='nav-item' >
        <a class='nav-link' style='color: white;' href='Admin/home.php'>Home&nbsp;&nbsp;&nbsp;&nbsp;</a>
    </li>

    <li class='nav-item'>
        <a class='nav-link' style='color: white;' href='../all_users.php'>TNI Nominations&nbsp;&nbsp;&nbsp;&nbsp;</a>
    </li>
    
    <?php if ($hod == 1): ?>
   <li class='nav-item'>
        <a class='nav-link'style='color: white;'  href='HOD/index.php'>HOD&nbsp;&nbsp;&nbsp;&nbsp;</a>
    </li>
    <?php endif; ?>

    <?php if ($hremp == 1): ?>
   <li class='nav-item'>
        <a class='nav-link' style='color: white;' href='admin/upload.php'>HR Functions&nbsp;&nbsp;&nbsp;&nbsp;</a>
    </li>

   <?php endif; ?>

    </ul>    
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


<div class="container">
    <input type="text" id="search_param" class="form-control" name="search" placeholder="Search Program Name & Year" oninput="updateData()">
</div>
 <!------------------------------------------------------------------------------------------------------------------------------------>   

<div class="container">
<div class="sixtyper">
    
    <h2>Select TNI Files</h2>
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
<div class="fortyper">
    <center><h4>Request by you</h4>

 <?php
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);

// Establish the connection
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch data from the 'request' table for a specific empno
$employeeno = $_SESSION["emp_num"];
$sql = "SELECT  [srl_no]
,[empno]
,[Program_name]
,[Faculty]
,[nature_training]
,[year]
,[uploaded_date]
,[flag]

,[remarks]
,[duration]
,[tentative_date]
,[target_group]
    FROM [Complaint].[dbo].[request]
    WHERE empno = ?";

$params = array($employeeno);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Display the fetched data in table format
if (sqlsrv_has_rows($stmt)) {
    echo '<div class="scrollable1">
 <table class="table table-bordered border-success" border="3">
<tbody>
        <thead>
            <tr class="bg-primary">
            
            <th scope="col">Program_name</th>
               
                <th scope="col">Faculty</th>
                <th scope="col">Nature_of_training</th>
                <th scope="col">status</th>
                
                
                <th scope="col">Remarks</th>
                
            </tr>
        </thead>
        <tbody>
        </div>';
        $serialNo = 1;
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo "<tr class='table-light'>";
    
   
    echo "<td>{$row['Program_name']}</td>";
    echo "<td>{$row['Faculty']}</td>";
    echo "<td>{$row['nature_training']}</td>";
    echo "<td>{$row['flag']}</td>";    
    echo "<td>{$row['remarks']}</td>";
    // echo "<td>
    //     <form action='deleteuser.php' method='post' style='display:inline;'>
    //         <input type='hidden' name='idToDeleteUser' value='{$row['srl_no']}'>
    //         <button type='submit' name='delete' class='btn btn-danger'><i class='fa fa-trash' aria-hidden='true'></i></button>
    //     </form>
    // </td>";
    echo "</tr>";
    $serialNo++;
}

echo '</tbody></table>';

}
else {
    echo '<p>No Pending Request by You.</p>';
}


// Close the connection
sqlsrv_close($conn);
?> 
</div>
</div>
    


</body>
<?php include 'footer.php';?>
</html>
