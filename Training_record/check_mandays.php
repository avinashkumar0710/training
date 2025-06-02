<?php
// Start a new session
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
}

$sessionemp = $_SESSION["emp_num"];

// Add '00' in front if session value has only 6 digits
if (strlen($sessionemp) == 6) {
    $sessionemp = '00' . $sessionemp;
}

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

// Fetch Employee Data for Dropdown
$sql = "SELECT DISTINCT ar.EMPNO, ea.emp_name
        FROM [Complaint].[dbo].[attendance_records] AS ar
        JOIN [Complaint].[dbo].[EA_webuser_tstpp] AS ea ON ar.EMPNO = ea.emp_num
        WHERE
            CASE
                WHEN ea.emp_num LIKE '%[^0-9]%' THEN 0 -- Contains non-numeric characters
                ELSE 1 -- All numeric
            END = 1";
$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$employees = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $employees[] = $row;
}

// Fetch Program and Attendance Data for Selected Employee
if (isset($_GET['empno'])) {
    $empno = $_GET['empno'];
    $query = "SELECT PROGRAM_NAME, attendance_fraction 
              FROM [Complaint].[dbo].[attendance_records] 
              WHERE EMPNO = ? AND flag = '1'";
    $params = array($empno);
    $stmt = sqlsrv_query($conn, $query, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $programData = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $programData[] = $row;
    }
}
?>

<html>
<head>
    <title>Training | Home</title>
    <link rel="icon" href="../images/analysis.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>

    <!-- Include jQuery and jQuery UI for Autocomplete -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    <link href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        body {
            background-color: #c6ead0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <h6><i class='fa fa-home'></i>&nbsp;<u><i>Home->Training Record & MIS->Check Mandays</i></u></h6>

    <!-- Searchable Input Field -->
    <div class="container mt-4">
        <label for="employeeSearch">Search Employee:</label>
        <input type="text" class="form-control" id="employeeSearch" name="employeeSearch" placeholder="Type to search employees...">
    </div>

    <!-- Table to Display Program and Attendance Data -->
    <div class="container mt-4">
        <?php if (isset($programData) && !empty($programData)): ?>
            <?php
// Fetch employee name based on empno
$empNameQuery = "SELECT emp_name FROM [Complaint].[dbo].[EA_webuser_tstpp] WHERE emp_num = ?";
$empNameParams = array($empno);
$empNameStmt = sqlsrv_query($conn, $empNameQuery, $empNameParams);
if ($empNameStmt === false) {
    die(print_r(sqlsrv_errors(), true));
}
$empNameRow = sqlsrv_fetch_array($empNameStmt, SQLSRV_FETCH_ASSOC);
$empName = $empNameRow ? $empNameRow['emp_name'] : 'Unknown Employee';
?>
<h4>Program and Attendance Data for Employee: <?php echo htmlspecialchars($empName); ?></h4>
<?php
$totalAttendance = 0; // Initialize total attendance fraction
?>
            <table class='table table-bordered border-dark' border='3'>
            <thead style='position: sticky; top: 0; background-color: beige; z-index: 1;'>
    <tr class='bg-primary'>
        <th scope='col'>Sl.No</th>
        <th scope='col'>Program Name</th>
        <th scope='col'>Attendance Fraction</th>
    </tr>
</thead>
<tbody>
    <?php
    $slNo = 1; // Initialize serial number
    $totalAttendance = 0; // Initialize total attendance fraction
    ?>
    <?php foreach ($programData as $row): ?>
        <?php
        $attendanceFraction = (float)$row['attendance_fraction'];
        $totalAttendance += $attendanceFraction; // Add to total
        ?>
        <tr>
            <td><?php echo $slNo++; ?></td> <!-- Display and increment serial number -->
            <td><?php echo htmlspecialchars($row['PROGRAM_NAME']); ?></td>
            <td style='background-color: orange; font-style: italic; font-weight: bold;'><?php echo htmlspecialchars($attendanceFraction); ?></td>
        </tr>
    <?php endforeach; ?>
    <!-- Display total attendance fraction -->
    <tr>
        <td><strong>Total Mandays</strong></td>
        <td></td> <!-- Empty cell for Program Name column -->
        <td><strong><?php echo htmlspecialchars($totalAttendance); ?></strong></td>
    </tr>
</tbody>
</table>
        <?php elseif (isset($empno)): ?>
            <p>No records found for the selected employee.</p>
        <?php endif; ?>
    </div>

    <!-- Initialize Autocomplete -->
    <script>
        $(document).ready(function() {
            // Initialize Autocomplete
            $("#employeeSearch").autocomplete({
                source: function(request, response) {
                    // Fetch data from the server using AJAX
                    $.ajax({
                        url: "search_employees.php", // PHP script to fetch employee data
                        type: "GET",
                        data: {
                            term: request.term // Search term
                        },
                        success: function(data) {
                            // Ensure the data is in the correct format
                            if (Array.isArray(data)) {
                                response(data); // Pass the data to the autocomplete
                            } else {
                                console.error("Invalid data format:", data);
                                response([]); // Return an empty array if data is invalid
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error("AJAX Error: " + status + error); // Log errors
                        }
                    });
                },
                minLength: 2, // Minimum characters to trigger search
                select: function(event, ui) {
                    // Redirect to the same page with the selected employee number
                    window.location.href = "?empno=" + ui.item.empno;
                }
            });
        });
    </script>
</body>
</html>

<?php include '../footer.php';?>