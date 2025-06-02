<head>
    <style>
          .scrollable {
            height: 650px;
            overflow-y: auto;
            border-color: black;
        }
    </style>
</head><?php
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
    exit(); // Ensures script stops execution after redirect
}

$sessionemp = $_SESSION["emp_num"];

// Add '00' in front if session value has only 6 digits
if (strlen($sessionemp) == 6) {
    $sessionemp = '00' . $sessionemp;
}

// Database connection
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

// Ensure all POST values exist before using them
// Ensure POST values exist before using them
if (!isset($_POST['department'], $_POST['location'])) {
    die("Error: Missing department or location.");
}

$selectedDepartment = $_POST['department'];
$location = $_POST['location'];


// **Step 1: Get empno based on location and designation**
$sql_emp = "SELECT empno, name, design 
            FROM [Complaint].[dbo].[emp_mas_sap] 
            WHERE location = ? 
            AND (design LIKE '%BUH%' OR design LIKE '%Chief%') 
            AND status = 'A'";

$params_emp = array($location);
$stmt_emp = sqlsrv_query($conn, $sql_emp, $params_emp);

if ($stmt_emp === false) {
    die(print_r(sqlsrv_errors(), true));
}

$empnos = [];
while ($row = sqlsrv_fetch_array($stmt_emp, SQLSRV_FETCH_ASSOC)) {
    $empnos[] = $row['empno'];
}

// echo "<pre>";
// print_r($empnos);
// echo "</pre>";

// Step 2: Choose SQL query based on sessionemp match
if (in_array($sessionemp, $empnos)) {
    // Query if sessionemp matches one of the retrieved empnos
    $sql_query = "SELECT 
                    r.id, r.empno,
                    STUFF(r.empno, 1, 0, CASE WHEN LEN(r.empno) = 6 THEN '00' ELSE '' END) AS update_empno,
                    r.PROGRAM_NAME, r.[year], r.duration, r.faculty, r.hostel_book, r.plant, r.aprroved_time, r.uploaded_date,
                    e.email, e.name, e.dept, tm.[Closed_date]
                    
                  FROM 
                    [Complaint].[dbo].[request] r
                  JOIN 
                    [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
                  JOIN
                    [Complaint].[dbo].[training_mast] tm ON r.srl_no = tm.srl_no
                  WHERE 
                    r.plant = ?
                    AND (
                        (r.flag = '5') 
                        OR 
                        (r.flag = '2' AND r.rep_ofcr = ?)
                    )
                  ORDER BY 
                    r.id DESC, r.[year] DESC;";
    $params_query = array($location, $sessionemp);
} else {
    // Query if sessionemp does NOT match any retrieved empno
    $sql_query = "SELECT 
                    r.id, r.empno,
                    STUFF(r.empno, 1, 0, CASE WHEN LEN(r.empno) = 6 THEN '00' ELSE '' END) AS update_empno,
                    r.PROGRAM_NAME, r.[year], r.duration, r.faculty, r.hostel_book, r.plant, r.aprroved_time, r.uploaded_date,
                    e.email, e.name, e.dept, tm.[Closed_date] 
                    
                  FROM 
                    [Complaint].[dbo].[request] r
                  JOIN 
                    [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
                  JOIN
                    [Complaint].[dbo].[training_mast] tm ON r.srl_no = tm.srl_no
                  WHERE 
                    r.plant = ?
                    AND (
                        (r.flag = '5') 
                    )
                  ORDER BY 
                    r.id DESC, r.[year] DESC;";
    $params_query = array($location);
}
// Prepare and execute the query with parameters
// **Step 3: Execute the final query**
$stmt = sqlsrv_query($conn, $sql_query, $params_query);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Display results in a table
echo "<div class='scrollable'>";
echo "<form id='updateForm' action='update_dept_data.php' method='POST'>"; // Form for updating data
echo "<table id='dataTable' class='table table-bordered border-dark' border='3'>
        <thead style='position: sticky; top: 0; background-color: beige;z-index: 1;'>
            <tr class='bg-primary' style='color:#ffffff'>  
                <th style='display:none;' scope='col'>ID</th>
                <th scope='col'>Sl. No</th>
                <th scope='col'>Name</th>
                <th scope='col'>Program Name</th>
                <th scope='col'>Year</th>
                <th scope='col'>Duration</th>
                <th scope='col'>Dept</th>
                <th scope='col'>Faculty</th>
                <th scope='col'>Last Approved Time</th>
                <th scope='col' style='width: 50px;'>Hostel Book</th>  
                <th scope='col'>BUH Approve Closed Date</th>
                <th style='display:none;' scope='col'>DATEADD</th>
                <th style='display:none;' scope='col'>Email</th>
                <th style='display:none;' scope='col'>Emp No</th>
                <th style='display:none;' scope='col'>Plant</th>
                <th style='width: 100px;'>Select All <br> 
                    <input class='row-checkbox' style='transform: scale(1.5);' type='checkbox' id='selectAllCheckbox' onclick='selectAllCheckboxes(this)'> 
                </th>                                                                                  
            </tr>
        </thead>
        <tbody>";

$currentDate = date('Y-m-d'); // Get the current date in 'Y-m-d' format
$serialNo = 1;

// Fetch and display data rows
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Fetch and format dates
    $approvedDate = isset($row['aprroved_time']) ? $row['aprroved_time']->format('Y-m-d') : null;
    $uploadedDate = isset($row['uploaded_date']) ? $row['uploaded_date']->format('Y-m-d') : null;

    // Use aprroved_time if available, otherwise fall back to uploaded_date
    $closedDate = $approvedDate ?? $uploadedDate;

    // Calculate closedDatePlus15Days if closedDate is not null
    $closedDatePlus15Days = null;
    if ($closedDate !== null) {
        $closedDatePlus15Days = (new DateTime($closedDate))->modify('+15 days')->format('Y-m-d');
    }

// Output the results (for debugging or further use)
//echo "Closed Date: " . ($closedDate ?? 'N/A') . "<br>";
//echo "Closed Date + 15 Days: " . ($closedDatePlus15Days ?? 'N/A') . "<br>";

echo "<tr>"; 
echo "<td class='id' style='display:none;'>" . htmlspecialchars($row['id']) . "</td>";
echo "<td>" . $serialNo . "</td>"; // Serial Number
echo "<td>" . htmlspecialchars($row['name']) . "</td>";
echo "<td>" . htmlspecialchars($row['PROGRAM_NAME']) . "</td>";
echo "<td>" . htmlspecialchars($row['year']) . "</td>";
echo "<td>" . htmlspecialchars($row['duration']) . "</td>";
echo "<td>" . htmlspecialchars($row['dept']) . "</td>";
echo "<td>" . htmlspecialchars($row['faculty']) . "</td>";
echo "<td>" . (!empty($row['aprroved_time']) ? htmlspecialchars($row['aprroved_time']->format('Y-m-d H:i:s')) : htmlspecialchars($row['uploaded_date']->format('Y-m-d H:i:s'))) . "</td>";
echo "<td>" . ($row['hostel_book'] == '1' ? 'Yes' : 'No') . "</td>"; 
echo "<td style='color:red;' class='closed-date'>" . htmlspecialchars($closedDatePlus15Days) . "</td>";
echo "<td style='display:none;'>" . htmlspecialchars($row['email']) . "</td>";
echo "<td style='display:none;'>" . htmlspecialchars($row['empno']) . "</td>";
echo "<td style='display:none;'>" . htmlspecialchars($row['plant']) . "</td>";

// Hidden input field to store expired nominations
echo "<input type='hidden' class='expired-id' value='" . htmlspecialchars($row['id']) . "'>";


echo "<td><input type='checkbox' class='row-checkbox' style='transform: scale(1.5);' name='selectedIds[]' value='" . htmlspecialchars($row['id']) . "'></td>";
echo "</tr>";
    $serialNo++;
}

echo "</tbody></table>";
echo "</form>";
echo "</div><br>";

// Buttons
echo "<div class='button-container'>";
echo "<button type='submit' form='updateForm' class='btn btn-primary'>Update Selected</button>&nbsp;";
echo "<button type='button' class='btn btn-danger' onclick=\"window.location.href='http://192.168.100.9:8080/training/Training_prg/Training_prg_home.php'\">Go Back</button>";
echo "</div>";

// Close the database connection
sqlsrv_close($conn);
?>



<script>
    function updateSelectedIds() {
        var selectedIds = [];
        // Loop through all checkboxes with class 'row-checkbox' and check if they are checked
        $('.row-checkbox').each(function() {
            if ($(this).is(':checked')) {
                var id = $(this).closest('tr').find('.id').text(); // Retrieve the ID from the cell with class 'id'
                selectedIds.push(id.trim()); // Trim any extra whitespace and add the ID to the array
            }
        });
        console.log("Selected IDs:", selectedIds);
    }

    // Call the updateSelectedIds() function when any checkbox is clicked
    $(document).ready(function() {
        $('.row-checkbox').change(function() {
            updateSelectedIds();
        });
    });
</script>


<script>
    <script>
document.getElementById('updateForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Stop default form submission

    let expiredIds = [];
    let currentDate = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD

    document.querySelectorAll("tbody tr").forEach(row => {
        let checkbox = row.querySelector(".row-checkbox");
        let closedDate = row.querySelector(".closed-date").textContent.trim(); // Get BUH Approve Closed Date

        if (checkbox.checked && closedDate && closedDate < currentDate) {
            expiredIds.push(checkbox.value);
            checkbox.checked = false; // Uncheck expired records
        }
    });

    if (expiredIds.length > 0) {
        // Redirect expired records to expired_nomination_date.php
        let expiredForm = document.createElement('form');
        expiredForm.method = 'POST';
        expiredForm.action = 'expired_nomination_date.php';

        expiredIds.forEach(id => {
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'expiredIds[]';
            input.value = id;
            expiredForm.appendChild(input);
        });

        document.body.appendChild(expiredForm);
        expiredForm.submit();
    }

    // If any valid records remain checked, submit the original form
    if (document.querySelectorAll(".row-checkbox:checked").length > 0) {
        event.target.submit(); // Submit the form normally
    } else if (expiredIds.length === 0) {
        alert("No records selected.");
    }
});
</script>

    </script>

