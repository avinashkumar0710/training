<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp=$_SESSION["emp_num"];
//echo $_SESSION["emp_num"];

$sessionemp1 = str_pad($sessionemp, 8, "0", STR_PAD_LEFT);
//echo $sessionemp1;

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
<html lang="en" dir="ltr">
<head>
<title>BUH Approval</title>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>   <!---scroll javascript---->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    

<style>
    body{
        font-weight: 600;
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        background-color: #e8eef3;
        }
    </style>
</head>
<?php include '../header_HR.php';?>
    <h6><i class='fa fa-home'></i>&nbsp;<i><u>HR->Send Nominations for BUH Approval</u></i></h6>
    <?php
// Your database connection code here
$findplantlocation= "select location FROM [Complaint].[dbo].[emp_mas_sap] where empno='$sessionemp1'";
$params = array($sessionemp1);

// Prepare and execute the query
$query = sqlsrv_query($conn, $findplantlocation, $params);

// Check if the query executed successfully
if ($query === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Fetch the result
$location = '';
if ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
    $location = $row['location'];
}

// Optionally, print or use the $location value
//echo "The location for employee number $sessionemp1 is: " . $location;
// SQL query to fetch data
$sql = "SELECT r.id,
            r.empno,
            STUFF(r.empno, 1, 0, CASE WHEN LEN(r.empno) = 6 THEN '00' ELSE '' END) AS update_empno,
            r.PROGRAM_NAME,
            r.[year],
            r.duration,
            r.faculty,
            r.hostel_book,
            r.plant,
            r.flag,
            e.email,
            e.name,
            e.location
        FROM 
            [Complaint].[dbo].[request] r
        JOIN 
            [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
        WHERE 
            r.flag = '99'   and  e.location='$location'
        ORDER BY 
            r.id DESC,
            r.[year] DESC";

// Execute the SQL query
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<!-- Wrap the table and button inside a form -->
<form id="myForm" action="send_mail copy.php" method="post">
    <div class='container' style="height: 680px; overflow: auto;">
        <!-- Checkbox for selecting all checkboxes -->
        
       
        <table class="table table-bordered border-success" border="3">
            <thead style="position: sticky; top: 0; background-color: beige;z-index: 1;">
                <tr>          
                    <th >Id</th>                    
                    <th>Name</th>
                    <th>Program Name</th>
                    <th>Year</th>
                    <th>Duration</th>
                    <th>Faculty</th>
                    <th>Hostel Book</th>
                    <th>Plant</th>
                    <th>Email</th>
                    <th>Status</th>
                    
                    <th style="width: 150px;">
                    <label for="selectAllCheckbox">Select All</label>&nbsp;<br><input class='row-checkbox' style='transform: scale(1.5);' type="checkbox" id="selectAllCheckbox" onclick="selectAllCheckboxes(this)">
                        <!-- Hidden input field to store selected rows' data -->
                        <input type="hidden" name="selectedRows" id="selectedRows" >
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Fetch and display data rows
                //$serialNo = 1;
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
                    echo "<td>" . ($row['flag'] == 4 ? 'Approved From HOD' : 'Pending  From BUH') . "</td>";
                    // Checkbox with onclick event to update hidden input field
                    echo "<td><input type='checkbox' class='row-checkbox' style='transform: scale(1.5);' onclick='updateHiddenField(this)'></td>";
                    echo "</tr>";
                    //$serialNo++;
                }
                
                ?>
            </tbody>
        </table>
        <!-- Button to submit the form -->
     
    </div><br>
    <div class="container">
        <button type="submit" class="btn btn-primary">Send Mail</button>
    </div>
</form>

<script>
    // Function to update hidden input field with selected checkbox data
    function updateHiddenField(checkbox) {
        // Retrieve the corresponding row data
        var rowData = checkbox.parentNode.parentNode.cells;
        var data = {
            id: rowData[0].textContent,
            name: rowData[1].textContent,
            programName: rowData[2].textContent,
            year: rowData[3].textContent,
            duration: rowData[4].textContent,
            faculty: rowData[5].textContent,
            hostelBook: rowData[6].textContent,
            plant: rowData[7].textContent, // Include plant information
            email: rowData[8].textContent,
            flag: rowData[9].textContent
        };
        // Retrieve previously selected data or initialize as an empty array
        var selectedData = JSON.parse(document.getElementById("selectedRows").value || '[]');
        // Check if the checkbox is checked
        if (checkbox.checked) {
            // Push the new selected data to the array
            selectedData.push(data);
        } else {
            // Remove the unchecked data from the array
            var index = selectedData.findIndex(function(item) {
                return item.id === data.id;
            });
            if (index !== -1) {
                selectedData.splice(index, 1);
            }
        }
        // Update the value of hidden input field with all selected checkbox data
        document.getElementById("selectedRows").value = JSON.stringify(selectedData);
        // Display the selected data in the console
        console.log("Selected Data:", selectedData);
    }

    // Function to select or deselect all checkboxes
    function selectAllCheckboxes(checkbox) {
        var checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(function(item) {
            item.checked = checkbox.checked;
            // Call updateHiddenField function for each checkbox
            updateHiddenField(item);
        });
    }
</script>


<?php include '../footer.php';?>