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
    <link rel="icon" type="image/png" sizes="32x32" href="employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>    
    <link rel="stylesheet" href="css/Training_TNI.css">
    <title>Training TNI</title>
    
</head>
<?php include 'header.php';
//echo "Exact HOD Empno: " . $exactHodEmpno;?>

    
<h6><i class='fa fa-home'></i>&nbsp;<u><i>Home->Training Need Identification</i></u></h6>
 <!------------------------------------------------------------------------------------------------------------------------------------>   
 <body style="background-color: #5d87192e">
<div class="container">
<div style="display: flex; ">
    <form method="POST">
        <label for="year">Select a year:</label>
        <select name="year" id="year">
            <option value="" disabled selected>Select year</option>
            <?php
            // Establishes the connection
          
            //echo 'test' .$exactHodEmpno;
            // SQL query to fetch distinct years
            $distinctYearsQuery = "SELECT DISTINCT year FROM [Complaint].[dbo].[TNI_mast]";
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
        </select>&nbsp;&nbsp;
        <button type="submit" class="btn btn-info">Show Programs</button>
    </form>&nbsp;&nbsp;&nbsp;

    <div class="search-container">
        <input type="text" id="search_param" class="form-control" name="search" placeholder="Search Program Name">
        <!-- <div id="liveSearchResults"></div> -->
    </div>
        
    </div><br>
<h3>TNI List&nbsp;<i style="font-size:small; background-color:yellow;">&nbsp;&nbsp;
(Note: The disabled row with red color signifies that the date period has expired.)</i></h3>

<div class="scrollable-table">  
    
    <?php
    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['year'])) {
        // Establishes the connection
        $conn = sqlsrv_connect($serverName, $connectionInfo);

        if (!$conn) {
            die(print_r(sqlsrv_errors(), true));
        }
        //echo "Exact HOD Empno: " . $exactHodEmpno;
        // Fetch data from the database based on the selected year
        $selectedYear = $_POST['year'];
        //$sql = "SELECT * FROM [Complaint].[dbo].[training_mast] WHERE year = ?";
        $sql="SELECT t.[srl_no], t.[Program_name], t.[nature_training], t.[duration], t.[faculty], t.[tentative_date], t.[year], t.[target_group], t.[id], t.[Closed_date]
        FROM [Complaint].[dbo].[TNI_mast] t
        WHERE t.srl_no NOT IN (SELECT [srl_no] FROM [Complaint].[dbo].[request_TNI] where empno='$sessionemp')
        AND t.[year] = ?
        GROUP BY t.[nature_training], t.[srl_no], t.[Program_name], t.[duration], t.[faculty], t.[tentative_date], t.[year], t.[target_group], t.[id], t.[Closed_date]
        ORDER BY t.[srl_no] ASC
        ";

        $params = array($selectedYear);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

      
        // Fetch and display each row
        
        echo "<table class='table table-bordered border-success' border='3'>";
        echo "<thead style='position: sticky; top: 0; background-color: beige; z-index: 1;'>";
        echo "<tr class='table-success'>";
        echo "<th scope='col'>Code No</th>";
        echo "<th scope='col'>Program Name</th>";
        echo "<th scope='col'>Nature of Training</th>";
        echo "<th scope='col'>Duration</th>";
        echo "<th scope='col'>Faculty</th>";
        echo "<th scope='col'>Tentative Date</th>";
        echo "<th scope='col'>Year</th>";
        echo "<th scope='col'>Target Group</th>";
        // echo "<th scope='col'>Closed_date</th>";
        echo "<th scope='col'>Hostel Required</th>";
        echo "<th scope='col'>Remarks</th>";
        echo "<th scope='col' class='action-column'>Select</th>";
        echo "</tr>";
        echo "</thead>";

        echo "<tbody id='tbl_body'>";        
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Check if Closed_date is less than current date
            if (($row['Closed_date']->format('Y-m-d')) < (date('Y-m-d'))) {
                $disabled = 'disabled';
                $rowClass = 'disabled-row'; // Add class for styling disabled rows
            } else {
                $disabled = ''; // Reset disabled if Closed_date is greater than or equal to current date
                $rowClass = ''; // Reset row class if row is not disabled
            }
            
        
            echo "<tr class='table-light $rowClass'>";
            echo "<td>{$row['srl_no']}</td>";
            echo "<td>{$row['Program_name']}</td>";
            echo "<td>{$row['nature_training']}</td>";
            echo "<td>{$row['duration']}</td>";
            echo "<td>{$row['faculty']}</td>";
            echo "<td>{$row['tentative_date']}</td>";
            echo "<td>{$row['year']}</td>";
            echo "<td>{$row['target_group']}</td>";           
        
            // Display Closed_date
            // echo "<td>{$row['Closed_date']->format('Y-m-d')}</td>";
        
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
                        <input type='checkbox' name='selectedIds[]' value='{$row['srl_no']}'  data-group='{$row['nature_training']}' onchange='updateSubmitButton()' $disabled>
                        <span class='checkmark'></span>
                    </label>
                </td>";
        
            echo "</tr>";       
           
        }
        
        echo "</tbody>";        
        echo "</table>";

        // Close the connection
        sqlsrv_close($conn);
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
    <form action='request_TNI.php' method='post'  id='dataForm'>
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


// Function to update the submit button and handle checkbox selection
function updateSubmitButton() {
    var checkboxes = document.querySelectorAll('input[name="selectedIds[]"]');
    var selectedCounts = {};
    var submitButton = document.getElementById('submitButton');

    checkboxes.forEach(function(checkbox) {
        var group = checkbox.dataset.group;
        selectedCounts[group] = selectedCounts[group] || 0;
        if (checkbox.checked) {
            selectedCounts[group]++;
        }
    });

    var anyCheckboxChecked = Array.from(checkboxes).some(function(checkbox) {
        return checkbox.checked;
    });

    submitButton.disabled = !anyCheckboxChecked;

    checkboxes.forEach(function(checkbox) {
        var group = checkbox.dataset.group;
        var otherCheckboxes = document.querySelectorAll('input[name="selectedIds[]"][data-group="' + group + '"]');
        var count = selectedCounts[group];

        if (count >= 2) {
            otherCheckboxes.forEach(function(otherCheckbox) {
                if (!otherCheckbox.checked) {
                    otherCheckbox.disabled = true;
                }
            });
        } else {
            otherCheckboxes.forEach(function(otherCheckbox) {
                otherCheckbox.disabled = false;
            });
        }
    });
}



    var checkboxes = document.querySelectorAll('input[name="selectedIds[]"]');
    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            updateSubmitButton();
        });
    });

    </script>

<!-------------------------------------------Pending and approve and Reject request------------------------------------------------------------>

</div>
    


</body>
<?php include '../footer.php';?>
</html>
