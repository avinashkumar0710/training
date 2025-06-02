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
    <title>Mail Training Order</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


    <style>
    body {
        font-weight: 600;
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        background-color: #e8eef3;
    }
    .nav-link {
        color: #F8F9F9;
    }
    </style>
</head>
<?php include '../header_HR.php';?>
        <h6><i class='fa fa-home'></i>&nbsp;<i><u>HR->Mail Training Order</u></i></h6>
        <br>
        <?php
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

$query = "SELECT 
            r.id, 
            r.PROGRAM_NAME, 
            r.year, 
            e.name, 
            e.dept, 
            e.email, 
            e.loc_desc, 
            r.flag, 
            r.hostel_book,
            e.location
        FROM 
            [Complaint].[dbo].[request] r
        JOIN 
            [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
        WHERE 
            r.flag = '6'  order by id desc";

$result = sqlsrv_query($conn, $query);

if ($result === false) {
    die("Error fetching data: " . sqlsrv_errors());
}
?>

<!DOCTYPE html>
<html>
<head>
<style>
          .scrollable {
            height: 650px;
            overflow-y: auto;
            border-color: black;
        }
    </style>
</head>
<body>

<div class='container'>
    <div class='scrollable'>
        <?php
        // Check if there are rows to display
        if (sqlsrv_has_rows($result)) {
        ?>
            <form id="updateForm" action="update_flag.php" method="POST">
                <table class="table table-bordered border-success" border="3">
                    <thead style="position: sticky; top: 0; background-color: beige;z-index: 1;">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Program Name</th>
                            <th>Year</th>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Hostel Book by User</th>
                            <th>Hostel Availability</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) { ?>
                            <tr class="table-light">
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['PROGRAM_NAME']; ?></td>
                                <td><?php echo $row['year']; ?></td>
                                <td><?php echo $row['dept']; ?></td>
                                <td><?php echo $row['loc_desc']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td><?php echo ($row['flag'] == '6') ? 'Approved from BUH' : $row['flag']; ?></td>
                                <td style="color: <?php echo ($row['hostel_book'] == 1) ? 'green' : 'red'; ?>">
                                    <?php echo ($row['hostel_book'] == 1) ? 'Yes' : 'No'; ?>
                                </td>

                                <td>
                                    <select name="hostelAvailability" id="hostelAvailability">
                                        <option value="2">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </form>
        <?php
        } else {
            echo "<div class='container'>";
            // If no data is available, display a message
            echo "<p>No data available.</p>";
            echo "</div>";
        }
        ?>
    </div><br>
    <!-- Move the button outside the scrollable container -->
    <div class="container">
        <button type="submit" form="updateForm" id="updateButton" class="btn btn-primary" name="updateFlag" value="Update">Update</button>
    </div>
</div>


            <script>
    // Function to fetch data with selected dropdown values and update flag
    function updateFlag() {
        var selectedValues = {}; // Object to store selected values

        // Loop through all rows in the table
        $('tbody tr').each(function(index) {
            var id = $(this).find('td:eq(0)').text(); // Get the ID from the first cell of the current row
            var programName = $(this).find('td:eq(2)').text(); // Get the Program Name from the third cell
            var name = $(this).find('td:eq(1)').text(); // Get the Name from the second cell
            var dropdownValue = $(this).find('select').val(); // Get the selected dropdown value

            // Get the email from the current row
            var email = $(this).find('td:eq(6)').text(); // Assuming the email is in the 7th column

            // Log the email, program name, name, and dropdown value in the console
            console.log("Email:", email, "Program Name:", programName, "Name:", name, "Dropdown Value:", dropdownValue);

            // Store the ID, selected value, program name, name, and email in the object
            selectedValues[id] = { value: dropdownValue, programName: programName, name: name, email: email };

            // Append hidden input fields for dropdown values, program name, name, and email IDs to the form
            $('<input>').attr({
                type: 'hidden',
                name: 'dropdown_' + id, // Use a unique name for each dropdown value
                value: dropdownValue
            }).appendTo('#updateForm');

            $('<input>').attr({
                type: 'hidden',
                name: 'programName_' + id, // Use a unique name for each program name
                value: programName
            }).appendTo('#updateForm');

            $('<input>').attr({
                type: 'hidden',
                name: 'name_' + id, // Use a unique name for each name
                value: name
            }).appendTo('#updateForm');

            $('<input>').attr({
                type: 'hidden',
                name: 'email_' + id, // Use a unique name for each email ID
                value: email
            }).appendTo('#updateForm');
        });

        console.log("Selected Values:", selectedValues);

        // Append selected values to form data
        $('<input>').attr({
            type: 'hidden',
            name: 'selectedValues',
            value: JSON.stringify(selectedValues)
        }).appendTo('#updateForm');

        // Submit the form
        $('#updateForm').submit();
    }

    // Call the updateFlag() function when the update button is clicked
    $(document).ready(function() {
        // Use button click event instead of form submission event
        $('#updateButton').click(function(event) {
            event.preventDefault(); // Prevent the default form submission
            updateFlag(); // Call the updateFlag() function to update the flag
        });
    });
</script>


</body>
</html>

<?php
sqlsrv_free_stmt($result);
sqlsrv_close($conn);
?>

<?php include '../footer.php';?>

