<head>
    <style>
          .scrollable {
            height: 650px;
            overflow-y: auto;
            border-color: black;
        }
    </style>
</head><?php
// Database connection code
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

// Get department and location from the form submission
if (isset($_POST['department']) && isset($_POST['location'])) 
    $selectedDepartment = $_POST['department'];
    $location = $_POST['location'];
    //echo '' .$location;

// Build and execute SQL query based on selected department and location
$sql = "SELECT 
            r.id,
            r.empno,
            STUFF(r.empno, 1, 0, CASE WHEN LEN(r.empno) = 6 THEN '00' ELSE '' END) AS update_empno,
            r.PROGRAM_NAME,
            r.[year],
            r.duration,
            r.faculty,
            r.hostel_book,
            r.plant,
            e.email,
            e.name,
            e.dept,
            tm.[Closed_date],
            DATEADD(day, 15, tm.[Closed_date]) AS [Closed_date_plus_15_days]
        FROM 
            [Complaint].[dbo].[request] r
        JOIN 
            [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
        JOIN
            [Complaint].[dbo].[training_mast] tm ON r.srl_no = tm.srl_no
        WHERE 
            r.flag = '5' AND r.plant=? AND (e.dept = ? OR ? = 'all') -- Use parameterized query
        ORDER BY 
            r.id DESC,
            r.[year] DESC";

// Prepare and bind parameters
$params = array($location, $selectedDepartment, $selectedDepartment);
$stmt = sqlsrv_query($conn, $sql, $params);

// Check for errors
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
                <th scope='col'>Name</th>
                <th scope='col'>Program Name</th>
                <th scope='col'>Year</th>
                <th scope='col'>Duration</th>
                <th scope='col'>Faculty</th>
                <th scope='col' style='width: 150px;'>Hostel Book</th>  
                <th style='display:none;' scope='col'>Closed Date</th>
                <th style='display:none;' scope='col'>DATEADD</th>
                <th style='display:none;' scope='col'>Email</th>
                <th style='display:none;' scope='col'>Emp No</th>
                <th style='display:none;' scope='col'>Plant</th>
                <th style='width: 100px;'>Select All <br> <input class='row-checkbox' style='transform: scale(1.5);' type='checkbox' id='selectAllCheckbox' onclick='selectAllCheckboxes(this)'> </th>                                                                                  
            </tr>
        </thead>
        <tbody>";

$currentDate = date('Y-m-d'); // Get the current date in 'Y-m-d' format

// Fetch and display data rows
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $closedDatePlus15Days = date('Y-m-d', strtotime($row['Closed_date']->format('Y-m-d') . '+15 days')); // Calculate Closed_date_plus_15_days

    // Check if the current date matches Closed_date_plus_15_days
    $isDisabled = ($closedDatePlus15Days === $currentDate) ? 'disabled' : '';

    echo "<tr class='$isDisabled'>"; 
    echo "<td class='id' style='display:none;'>" . $row['id'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['PROGRAM_NAME'] . "</td>";
    echo "<td>" . $row['year'] . "</td>";
    echo "<td>" . $row['duration'] . "</td>";
    echo "<td>" . $row['faculty'] . "</td>";
    echo "<td>" . ($row['hostel_book'] == '1' ? 'Yes' : 'No') . "</td>"; // Assuming hostel_book is stored as 1 or 0
    echo "<td style='display:none;'>" . $row['Closed_date']->format('Y-m-d H:i:s') . "</td>";
    echo "<td style='display:none;'>" . $row['Closed_date_plus_15_days']->format('Y-m-d H:i:s') . "</td>";
    echo "<td style='display:none;'>" . $row['email'] . "</td>";
    echo "<td style='display:none;'>" . $row['empno'] . "</td>";
    echo "<td style='display:none;'>" . $row['plant'] . "</td>";
    echo "<td><input type='checkbox' class='row-checkbox' style='transform: scale(1.5);' name='selectedIds[]' value='" . $row['id'] . "'></td>"; // Checkbox with ID as value
    echo "</tr>";
}

echo "</tbody></table>";
echo "</form>";
echo "</div><br>";

// Button to update selected checkboxes
echo "<div class='button-container'>";
echo "<button type='submit' form='updateForm' class='btn btn-primary'>Update Selected</button>";
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

