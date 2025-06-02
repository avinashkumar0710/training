<?php 

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
session_start();
if (!isset($_SESSION["emp_num"])) {
    header("location:login.php");
    exit();
}

$sessionemp = $_SESSION["emp_num"];

// Add '00' in front if session value has only 6 digits
$sessionemp1 = str_pad($sessionemp, 8, "0", STR_PAD_LEFT);

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

// Fetch user plant
$plantQuery = "SELECT Plant FROM [Complaint].[dbo].[EA_webuser_tstpp] WHERE emp_num LIKE ('$sessionemp%')";
$plantResult = sqlsrv_query($conn, $plantQuery);
$plantRow = sqlsrv_fetch_array($plantResult, SQLSRV_FETCH_ASSOC);
$userPlant = $plantRow['Plant'] ?? null;

// Fetch distinct program names for dropdown (filtered by userplant if not NS04)
$programQuery = "SELECT DISTINCT r.PROGRAM_NAME 
                FROM [Complaint].[dbo].[request] r
                JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
                WHERE r.flag = '99'";

$programParams = [];
if ($userPlant !== "NS04") {
    $programQuery .= " AND e.location = ?";
    $programParams[] = $userPlant;
}

$programResult = sqlsrv_query($conn, $programQuery, $programParams);
if ($programResult === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Handle program filter selection
$selectedProgram = $_POST['programFilter'] ?? '';

// Initialize parameters array
$params = [];

// SQL Query for fetching data
$sql = "SELECT r.id, r.empno, 
        STUFF(r.empno, 1, 0, CASE WHEN LEN(r.empno) = 6 THEN '00' ELSE '' END) AS update_empno, r.plant, t.day_from, t.day_to,
        r.PROGRAM_NAME, r.[year], r.duration, r.faculty, r.hostel_book, r.flag, e.location,
        e.email, e.name, e.loc_desc, e.dept
        FROM [Complaint].[dbo].[request] r
        JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
        LEFT JOIN 
        [Complaint].[dbo].[training_mast] t ON r.srl_no = t.srl_no
        WHERE r.flag = '99'";

// Apply plant filter if userPlant is not "NS04"
if ($userPlant !== "NS04") {
    $sql .= " AND e.location = ?";
    $params[] = $userPlant;
}

// Apply program filter if selected
if (!empty($selectedProgram)) {
    $sql .= " AND r.PROGRAM_NAME = ?";
    $params[] = $selectedProgram;
}

// Execute SQL query with parameters
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>


<!-- Dropdown Filters -->
<!-- Filter Form -->
<form method="POST">
    <div class="container">
        <div class="row mb-3">
            <!-- Program Name Filter -->
            <div class="col-md-6">
                <label for="programFilter">Select Program Name:</label>
                <select name="programFilter" id="programFilter" class="form-control">
                    <option value="">All</option>
                    <?php
                    // Fetch distinct program names for dropdown (filtered by userplant if not NS04)
                    $programQuery = "SELECT DISTINCT r.PROGRAM_NAME 
                                    FROM [Complaint].[dbo].[request] r
                                    JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
                                    WHERE r.flag = '99'";

                    $programParams = [];
                    if ($userPlant !== "NS04") {
                        $programQuery .= " AND e.location = ?";
                        $programParams[] = $userPlant;
                    }

                    $programResult = sqlsrv_query($conn, $programQuery, $programParams);
                    if ($programResult === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }

                    // Populate the dropdown
                    while ($programRow = sqlsrv_fetch_array($programResult, SQLSRV_FETCH_ASSOC)) {
                        $programName = $programRow['PROGRAM_NAME'];
                        $isSelected = ($selectedProgram == $programName) ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($programName) . "' $isSelected>" . htmlspecialchars($programName) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Apply Filter Button -->
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
            </div>
        </div>
    </div>
</form>

<!-- Wrap the table and button inside a form -->
<form id="myForm" action="send_mail copy.php" method="post">
<div class='container' style="height: 610px; overflow: auto;">
        <table class="table table-bordered border-success" border="3">
            <thead style="position: sticky; top: 0; background-color: beige; z-index: 1;">
                <tr>
                    <th>Sl.</th>
                    <th>Name</th>
                    <th>Program Name</th>
                    <th>Year</th>
                    <th>Duration</th>
                    <th>Day From</th>
                    <th>Day to</th>
                   
                    <th>Hostel Book</th>
                    <th>Plant</th>
                    <th>Dept</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th style="width: 150px;">
                        <label for="selectAllCheckbox">Select All</label>&nbsp;
                        <input class='row-checkbox' type="checkbox" id="selectAllCheckbox" onclick="selectAllCheckboxes(this)">
                        <input type="hidden" name="selectedRows" id="selectedRows">
                    </th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php
                $serialNo = 1;
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    echo "<tr class='table-light'>";
                    echo "<td style='display:none;'>" . $row['id'] . "</td>";
                    echo "<td>" . $serialNo . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td class='program-name'>" . htmlspecialchars($row['PROGRAM_NAME']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['year']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['duration']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['day_from']->format('Y-m-d')) . "</td>";
                    echo "<td>" . htmlspecialchars($row['day_to']->format('Y-m-d')) . "</td>";
                   
                    echo "<td>" . ($row['hostel_book'] == 1 ? 'Yes' : 'No') . "</td>";

                    echo "<td class='plant-name'>";
                    switch ($row['plant']) {
                        case 'NS01': echo "Corporate Center"; break;
                        case 'NS02': echo "Durgapur"; break;
                        case 'NS03': echo "Rourkela"; break;
                        case 'NS04': echo "Bhilai"; break;
                        default: echo "Unknown"; break;
                    }
                    echo "</td>";

                    echo "<td>" . htmlspecialchars($row['dept']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . ($row['flag'] == 4 ? 'Approved From HOD' : 'Pending From BUH') . "</td>";
                    echo "<td><input type='checkbox' class='row-checkbox' onclick='updateHiddenField(this)'></td>";
                    echo "</tr>";
                    $serialNo++;
                }
                ?>
            </tbody>
        </table>
    </div><br>
    <div class="container">
        <button type="submit" class="btn btn-primary">Send Mail to BUH</button>
    </div>
</form>

<script>

function filterTable() {
    var programFilter = document.getElementById("programFilter").value.toLowerCase();
    var plantFilter = document.getElementById("plantFilter").value.toLowerCase();
    var rows = document.getElementById("tableBody").getElementsByTagName("tr");

    for (var i = 0; i < rows.length; i++) {
        var program = rows[i].getElementsByClassName("program-name")[0].innerText.toLowerCase();
       

        if ((programFilter === "" || program.includes(programFilter))){
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }
    }
}
    // Function to update hidden input field with selected checkbox data
    function updateHiddenField(checkbox) {
    var row = checkbox.closest("tr").cells; // Get the row's cells

    var data = {
        id: row[0].textContent.trim(),  // Hidden ID
        name: row[2].textContent.trim(),
        programName: row[3].textContent.trim(),
        yearr: row[4].textContent.trim(),
        duration: row[5].textContent.trim(),
        faculty: row[6].textContent.trim(), // This should be Program Date (day_from)
        hostelBook: row[8].textContent.trim().toLowerCase() === 'yes' ? 1 : 0, // Fixed Index
        plant: row[9].textContent.trim(),  // Now correctly mapped to Plant
        dept: row[10].textContent.trim(),  // Now correctly mapped to Dept
        email: row[11].textContent.trim(), // Now correctly mapped to Email
        flag: row[12].textContent.trim()   // Now correctly mapped to Status
    };

    var hiddenField = document.getElementById("selectedRows");
    if (!hiddenField) {
        console.error("Error: Hidden input field with ID 'selectedRows' not found.");
        return;
    }

    var selectedData = JSON.parse(hiddenField.value || '[]');

    if (checkbox.checked) {
        // Add only if not already present
        if (!selectedData.some(item => item.id === data.id)) {
            selectedData.push(data);
        }
    } else {
        // Remove unchecked data
        selectedData = selectedData.filter(item => item.id !== data.id);
    }

    // Update hidden input field
    hiddenField.value = JSON.stringify(selectedData);

    // Debugging output
    console.log("Updated Selected Data:", selectedData);
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