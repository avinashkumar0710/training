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
    <title>Administrator</title>
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

$findplant = "SELECT location FROM emp_mas_sap WHERE empno = '$sessionemp'";
$stmt = sqlsrv_query($conn, $findplant);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch the location
$location = null;
if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $location = $row['location']; // Store the location in a variable
}

// Build the SQL query based on the session employee number
$sql = "SELECT 
    r.srl_no,
    r.id,
    r.empno,
    CASE 
        WHEN LEN(r.empno) = 6 THEN '00' + r.empno 
        ELSE r.empno 
    END AS update_empno,
    r.PROGRAM_NAME,
    r.year,
    r.duration,
    r.faculty,
    r.plant,
    r.hostel_book,
    r.plant,
    r.flag,
    e.email,
    e.name,
    t.Closed_date
FROM 
    [Complaint].[dbo].[request] r
JOIN 
    [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
LEFT JOIN 
    [Complaint].[dbo].[training_mast] t ON r.srl_no = t.srl_no
WHERE 
    r.flag = '4' ";

// If the session employee number is NOT '00100031', add the location condition
if ($sessionemp !== '00100031') {
    $sql .= "AND r.plant = '$location' ";
}

// Append the order by clause
$sql .= "ORDER BY 
    r.id DESC,
    r.year DESC;";

// Execute the SQL query
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

?>


<!-- Wrap the table and button inside a form -->
<form id="approveForm" action="approve_HOD.php" method="post">
    <div class='container' style="height: 680px; overflow: auto;">
        <!-- Checkbox for selecting all checkboxes -->
        
       
        <table class="table table-bordered border-success" border="3">
            <thead style="position: sticky; top: 0; background-color: beige; z-index: 2;">
                <tr>          
                    <th >id</th>                    
                    <th>Name</th>
                    <th>Program Name</th>
                    <th>Year</th>
                    <th>Duration</th>
                    <th>Faculty</th>
                    <th>Hostel Book</th>
                    <th>Plant</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Closed Date</th>
                    <th>Approve / Reject</th>                   
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch and display data rows
                //$serialNo = 1;
                $currentDate = new DateTime();
                $currentDate->modify('+1 days'); // Add 3 days to the current date
                // Get current date
                $today = new DateTime('today'); // Get today's date without time
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    echo "<tr class='table-light'>";
                    echo "<td>" . $row['id'] . "</td>";
                    //echo "<td>" . $serialNo . "</td>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . $row['PROGRAM_NAME'] . "</td>";
                    echo "<td>" . $row['year'] . "</td>";
                    echo "<td>" . $row['duration'] . "</td>";
                    echo "<td>" . $row['faculty'] . "</td>";
                    echo "<td>" . ($row['hostel_book'] == 1 ? 'Yes' : 'No') . "</td>"; // Display 'Yes' for 1 and 'No' for 0
                    echo "<td>";
                    $plant = $row['plant'];

                    if ($plant === 'NS01') {
                        echo "Corporate Center";
                    } elseif ($plant === 'NS02') {
                        echo "Durgapur";
                    } elseif ($plant === 'NS03') {
                        echo "Rourkela";
                    } elseif ($plant === 'NS04') {
                        echo "Bhilai";
                    } else {
                        echo "Unknown";
                    }
                    echo "</td>";

                    echo "<td>" . $row['email'] . "</td>";
                    echo "<td>" . ($row['flag'] == 4 ? 'Aprove From Plant HOD' : 'Reject  From HOD') . "</td>";
                   // Check if the closed date is exceeded
                   if ($row['Closed_date'] === null) {
                    echo "<td style='color:gray;'>NULL</td>"; // Display "NULL" in gray color for clarity
                } else {
                    $formattedDate = date_format($row['Closed_date'], 'Y-m-d');
                    if ($today > $row['Closed_date']) {
                        echo "<td style='color:red;'>$formattedDate</td>"; // Show date in red if it's past today
                    } else {
                        echo "<td>$formattedDate</td>"; // Show date normally if it's not past today
                    }
                }
                            
                 
                    echo "<td>";
                    echo "<select name='approvalStatus[".$row['id']."]' class='approval-dropdown'>";
                    echo "<option></option>";
                    echo "<option value='99'>Approve by HR</option>";
                    echo "<option value='88'>Reject by HR</option>";
                    echo "</select>";
                    echo "</td>";                  
                    echo "</tr>";
                    //$serialNo++;
                }
                
                ?>
            </tbody>
        </table>
        <!-- Button to submit the form -->
     
    </div><br>
    <div class="container">
        <button type="submit"  id="approveButton" name="approve"  class="btn btn-primary">Submit</button>
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