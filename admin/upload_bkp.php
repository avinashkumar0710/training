<?php

session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }


    $name = "SELECT emp_name, access, dept_code, emp_num FROM EA_webuser_tstpp WHERE emp_num = ?";
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
        $empno =$row['emp_num'];
    } 

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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input
    $program_files = $_FILES["program_files"];
    $year = $_POST["year"];
    
    // Get current date and IP address
    $uploaded_date = date("Y-m-d H:i:s");
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $flag ='A';

    // File upload handling
    $targetDirectory = "../uploads/";
    $targetFile = $targetDirectory . basename($program_files["name"]);

    // Check if the file already exists in the database
    $checkDuplicateSql = "SELECT COUNT(*) AS count FROM [Complaint].[dbo].[excel] WHERE program_files = ? and flag ='A'";
    $checkDuplicateParams = array($targetFile);
    $checkDuplicateStmt = sqlsrv_query($conn, $checkDuplicateSql, $checkDuplicateParams);
    if ($checkDuplicateStmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    $countResult = sqlsrv_fetch_array($checkDuplicateStmt, SQLSRV_FETCH_ASSOC)['count'];

    if ($countResult > 0) {
        echo '<script>alert("Error: File with the same name already exists!");</script>';
    } else {
        // Move the uploaded file to the specified directory
        if (move_uploaded_file($program_files["tmp_name"], $targetFile)) {
            // File uploaded successfully, now insert data into the database
          // Assuming $empno is defined somewhere


          









          $sql = "INSERT INTO [Complaint].[dbo].[excel] (program_files, year, uploaded_date, ip_address, flag, empno, venue, Faculty, Prog_Content) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = array($targetFile, $year, $uploaded_date, $ip_address, $flag, $empno, $_POST["venue"], $_POST["Faculty"], $_POST["Programme_Content"]);

        $stmt = sqlsrv_query($conn, $sql, $params);

  


            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            } else {
                echo '<script>alert("Data inserted successfully!");</script>';
                header("Location: {$_SERVER['PHP_SELF']}");
                exit();
            }
        } else {
            echo "Error uploading file.";
        }
    }
}

// Close the connection
sqlsrv_close($conn);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <title>Excel Upload Page</title>
    <style>  
        .scrollable-table {
            height: 600px;
            overflow-y: auto;
        }
        .container{
            padding:20px;
        }
</style>
</head>
<body>

    <div class="container">  
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">

        <div class="input-group mb-3">
        <input type="file" name="program_files" class="form-control" id="formFile" accept=".xls, .xlsx" required>&nbsp;

        <!-- <span class="input-group-text" for="venue">Venue:</span>
        <input type="text" class="form-control" id="venue"  name="venue" placeholder="Enter Venue" aria-label="Username" aria-describedby="basic-addon1">&nbsp;

        <span for="Faculty" class="input-group-text" for="Faculty">Faculty:</span>
        <input type="text" class="form-control" id="Faculty" name="Faculty" placeholder="Enter Faculty Name" required>&nbsp;

        <span for="Programme_Content" class="input-group-text" for="Programme_Content">Programme Content:</span>
        <input type="text" id="Programme_Content" class="form-control" name="Programme_Content" placeholder="Programme Content" required>&nbsp; -->

        <label for="year" class="input-group-text">Validity Year:&nbsp;</label>
                <select name="year" required>
                    <?php
                    // Generate options for years from 2020 to 2024
                    $currentYear = date("Y");
                    for ($year = 2020; $year <= 2024; $year++) {
                        echo "<option value=\"$year\"";
                        if ($year == $currentYear) {
                            echo " selected"; // Pre-select the current year
                        }
                        echo ">$year</option>";
                    }
                    ?>
                </select>
    </div>

    <input type="submit" class="btn btn-success" value="Insert Data">       
    </form>
    </div>

<div class="container">    
    <h2>Uploaded Excel Files</h2>
    <div class="scrollable-table">
    <table class="table table-bordered " border="1" >
    <thead>
        <tr class="bg-primary">
            <th scope="col">Serial No.</th>
            <th scope="col">Program Files</th>
            <th scope="col">Year</th>
            <th scope="col">Venue</th>
            <th scope="col">Faculty</th>
            <th scope="col">Programme Content</th>
            <th scope="col">Actions</th>
        </tr>
    </thead>
    <?php
    $serverName = "NSPCL-AD\SQLEXPRESS";
    $connectionOptions = array(
        "Database" => "complaint",
        "UID" => "",
        "PWD" => ""
    );

    // Establishes the connection
    $conn = sqlsrv_connect($serverName, $connectionOptions);

    if (!$conn) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Fetch data from the database
    $sql = "SELECT * FROM [Complaint].[dbo].[excel] where flag = 'A' order by year desc";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Initialize a counter for serial number
    $serialNo = 1;

    // Fetch and display each row
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        echo "<tr class='table-success'>";
        echo "<td>{$serialNo}</td>";
        echo "<td><a href='{$row['program_files']}' target='_blank'>" . basename($row['program_files']) . "</a></td>";
        echo "<td>{$row['year']}</td>";
        echo "<td>{$row['venue']}</td>";
        echo "<td>{$row['Faculty']}</td>";
        echo "<td>{$row['Prog_Content']}</td>";
        echo "<td>
        <form action='delete.php' method='post' style='display:inline;'>
            <input type='hidden' name='idToDelete' value='{$row['id']}'>
            <button type='submit' name='delete' class='btn btn-danger'><i class='fa fa-trash' aria-hidden='true'></i></button>
        </form>
    </td>";
    
        echo "</tr>";

        // Increment the serial number for the next iteration
        $serialNo++;
    }

    // Close the connection
    sqlsrv_close($conn);
    ?>
</table>
    </div>
    </div>
</body>
</html>





