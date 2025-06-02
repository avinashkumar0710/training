
<?php 
// start a new session
// Allow any origin to access this resource

session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
}

$hodempno = $_SESSION["emp_num"];

// Ensure $hodempno has 8 digits (prepend 00 if it starts with 0) or 7 digits
$hodempno = str_pad($hodempno, 8, "0", STR_PAD_LEFT);

//echo 'employeeno' . $hodempno;
?>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve'])) {
    // Check if the 'approvalStatus' array is set in the POST data
    if (isset($_POST['approvalStatus'])) {
        // Connect to the database
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

        // Loop through each dropdown selection
        foreach ($_POST['approvalStatus'] as $rowId => $selectedValue) {
            // Validate and sanitize the input as needed
            $rowId = filter_var($rowId, FILTER_SANITIZE_NUMBER_INT);

            // Output only selected values
            if (!empty($selectedValue)) {
                //echo "Row ID: " . $rowId . ", Selected Value: " . $selectedValue . "<br>";

                //$sql = "UPDATE request SET flag = '$selectedValue' WHERE id = '$rowId'";
                $sql = "UPDATE request_TNI SET flag = '$selectedValue', aprroved_time = GETDATE(), appr_empno='$hodempno' WHERE id = '$rowId'";
                $params = array($selectedValue, $rowId);
                $stmt = sqlsrv_query($conn, $sql, $params);
   
               // Check if the query was successful
                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
            }

             // Example database update (you should replace this with your actual database update code)
            
        }

        // Close the database connection
        sqlsrv_close($conn);

        echo '<script>alert("Form submitted successfully!"); window.location.href = "TNI_index.php";</script>';


    } else {
        // Handle the case where 'approvalStatus' array is not set
        echo '<script>alert("No approval statuses were submitted."); window.location.href = "TNI_index.php";</script>';
    }
} else {
    // Handle the case where the form was not submitted via POST method
    echo '<script>alert("Form was not submitted."); window.location.href = "TNI_index.php";</script>';
}
?>


