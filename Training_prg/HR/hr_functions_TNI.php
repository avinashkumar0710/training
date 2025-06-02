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
    <link rel="icon" href="../images/analysis.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Excel Upload Page</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>   <!---scroll javascript---->
    <style>
        body{
            font-weight: 600;
            font-style: normal;
            font-family: "Nunito Sans", sans-serif;
            }

            .scroll-button {            
            bottom: 20px;
            right: 20px;
            width: 100px;
            height: 30px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5%;
            cursor: pointer;
            text-align: center;         
            z-index: 1000;
        }

        .scrollable-table {
            height: 300px;
            overflow-y: auto;
        }
        .container{
            padding:10px;
        }
   </style>
</head>
<?php include '../header_HR.php';?>


<body style="background-color: #5d87192e">  
<h6><i class='fa fa-home'></i>&nbsp;<i><u>HR->HR Functions</u></i></h6>
<div class="container" style="height:50px;">    
    <div class="input-group mb-3">
        <form method="POST" id="showProgramForm">
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
                $distinctYearsQuery = "SELECT DISTINCT year FROM [Complaint].[dbo].[request_TNI] where flag='4'";
                $yearsResult = sqlsrv_query($conn, $distinctYearsQuery);

                if ($yearsResult) {
                    // Loop through distinct years and generate options
                    while ($yearRow = sqlsrv_fetch_array($yearsResult, SQLSRV_FETCH_ASSOC)) {
                        $yearValue = $yearRow['year'];
                        $selectedAttr = (isset($_POST['year']) && $_POST['year'] == $yearValue) ? 'selected' : '';
                        echo "<option value=\"$yearValue\" $selectedAttr>$yearValue</option>";
                    }
                }

                // Close the SQL Server connection
                sqlsrv_close($conn);
                ?>
            </select>
            <button type="submit" class="btn btn-info">Show Programs</button>
            <p id="errorMessage" style="color: red;"></p> <!-- Error message -->
        </form>
        &nbsp;
        <form action="download_excel_HR_function_TNI.php" method="post" id="downloadForm">
            <input type="hidden" name="year" id="selectedYear" value="<?php echo htmlspecialchars($_POST['year'] ?? ''); ?>">
            <button type="submit" class="btn btn-success" id="downloadButton">Download Excel</button>
        </form>&nbsp;&nbsp;&nbsp;
        <div class='scroll'>
        <button class='btn btn-primary' id='scrollUp'>ScrollUp&#8593;</button>&nbsp;
        <button class='btn btn-primary' id='scrollDown'>ScrollDown&#8595;</button>
        </div>
    </div>
</div>

<div class="container">
    <?php
    $serverName = "192.168.100.240";
    $connectionInfo = array(
        "Database" => "complaint",
        "UID" => "sa",
    "PWD" => "Intranet@123"
    );

    // Create connection
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    if (!$conn) {
        die(print_r(sqlsrv_errors(), true));
    }
    $serialNo = 1;
    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $selected_year = $_POST['year'];
    
        // Use prepared statement to prevent SQL injection
        $sql = "SELECT r.empno, r.Program_name, r.nature_training, r.year, r.remarks, r.duration, r.tentative_date, a.name, r.hostel_book
                FROM [Complaint].[dbo].[request_TNI] r 
                JOIN [Complaint].[dbo].[emp_mas_sap] a on r.empno = a.empno  
                WHERE flag = '4' and year = ?   order by r.Program_name";
    
        $params = array($selected_year);
        $result = sqlsrv_query($conn, $sql, $params);
    
        if ($result === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    
        if (sqlsrv_has_rows($result)) {
            // Output data in a table
          
            //echo "<div class='scroll'>";           
            //echo "<button class='scroll-button' id='scrollUp'>ScrollUp&#8593;</button>&nbsp;";            
            //echo "<button class='scroll-button' id='scrollDown'>ScrollDown&#8595;</button>";
            //echo "</div>";
            //echo  "<br>";
            echo "<h4>Approved Training List</h4>";
            echo "<div class='table' style='width: 1300px; height: 550px; overflow: auto;'>";
            echo "<table class='table table-bordered border-success' border='3' border='1'>";
            echo "<thead style='position: sticky; top: 0; background-color: beige;'>
                     <tr>           
                    <th scope='col'>Sl No</th>
                    <th scope='col'>Empno</th>
                    <th scope='col'>Program_name</th>
                    <th scope='col'>Year</th>
                    <th scope='col'>Duration</th>
                    <th scope='col'>Tentative_Date</th>
                    <th scope='col'>Hostel_Required</th>
                    <th scope='col'>Remarks</th>
                  </tr>
                  </thead>";
    
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                echo "<tbody style='background-color:#fff'><tr>
                        <td>". $serialNo++ ."</td>
                        <td>". $row['name'] ."</td>
                        <td>". $row['Program_name'] ."</td>
                        <td>". $row['year'] ."</td>
                        <td>". $row['duration'] ."</td>
                        <td>". $row['tentative_date'] ."</td>
                        
                        <td style='color: ". ($row['hostel_book'] == 1 ? 'green' : 'red') ."'>". ($row['hostel_book'] == 1 ? 'Yes' : 'No') ."</td>
                        <td>". $row['remarks'] ."</td>
                      </tr></tbody>";
            }
            echo "</table>";
        } else {
            echo "No programs found for $selected_year";
        }
    
        // Free the result set
        sqlsrv_free_stmt($result);
    }
    sqlsrv_close($conn);
    ?>
</div>
</div>
<script>
    // Scroll up function
    $('#scrollUp').on('click', function() {
        $('div').animate({ scrollTop: '-=1000' }, 'slow'); // Adjust scroll speed as needed
    });

    // Scroll down function
    $('#scrollDown').on('click', function() {
        $('div').animate({ scrollTop: '+=1000' }, 'slow'); // Adjust scroll speed as needed
    });
</script>
<script>
    const errorMessage = document.getElementById('errorMessage');

    // Add event listener to the form for validation
    document.querySelector('form').addEventListener('submit', function(event) {
        // Check if a year is selected
        if (document.getElementById('year').value === '') {
            // Prevent form submission
            event.preventDefault();
            // Display error message
            errorMessage.textContent = 'Please select a year.';
        }
    });

    // Add event listener to the download button for validation
    document.getElementById('downloadForm').addEventListener('submit', function(event) {
        // Check if a year is selected
        if (document.getElementById('selectedYear').value === '') {
            // Prevent form submission
            event.preventDefault();
            // Display error message
            errorMessage.textContent = 'Please select a year for download.';
        }
    });
</script>

</body>

</html>
<?php include '../footer.php';?>




