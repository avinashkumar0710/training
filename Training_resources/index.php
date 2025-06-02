<?php
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
}
$sessionemp = $_SESSION["emp_num"];
if (strlen($sessionemp) == 6) {
    $sessionemp = "00" . $sessionemp;
}

// Database connection
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
?>

<html>

<head>
    <title>Training Resources | Home</title>
    <link rel="icon" href="../images/analysis.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


</head>
<?php include 'header.php';?>
<h6><b><i class='fa fa-home'></i>&nbsp;<u><i>Training Resources->Available Training Resources</i></u></b></h6>
<body style="background-color: beige; font-family: Nunito Sans, sans-serif">
    <!-- Container for Year Selection (Fixed at the Top) -->
    <div class="container" style=" width: 100%; background-color: beige; z-index: 1000; padding: 10px;">
        <h2>Select Year to View Files</h2>
        <div class="form-row">
            <label for="year">Select Year:</label>
            <select name="year" id="year" class="form-control" onchange="fetchProgramFiles(this.value)" required>
                <option value="">Select Year</option>
                <?php
                // Fetch years from the database
                $query = "SELECT DISTINCT year FROM [Complaint].[dbo].[training_mast]";
                $stmt = sqlsrv_query($conn, $query);

                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    echo "<option value='{$row['year']}'>{$row['year']}</option>";
                }

                sqlsrv_free_stmt($stmt);
                ?>
            </select>
        </div>
    </div>

    <!-- Scrollable Frame for Program Files (Below the Fixed Header) -->
    <div class="container" style="margin-top: 10px;">
        <h2>Program Files</h2>
        <div id="file-list" style="max-height: 630px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;">
            <!-- Program files will be displayed here -->
        </div>
    </div>

    <script>
        function fetchProgramFiles(year) {
            $.ajax({
                url: 'fetch_all_data.php',
                type: 'POST',
                data: { year: year },
                success: function(response) {
                    $('#file-list').html(response);
                }
            });
        }
    </script>
</body>

<?php include 'footer.php';?>
