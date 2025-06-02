<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serverName = "192.168.100.240";
    $connectionOptions = array(
        "Database" => "complaint",
        "UID" => "sa",
        "PWD" => "Intranet@123"
    );
    $conn = sqlsrv_connect($serverName, $connectionOptions);

    // Check if connection is established successfully
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }
  
    // Loop through the selected IDs and update corresponding records in the database
    $selectedIds = explode(',', $_POST['ids']);
    $selectedDates = explode(',', $_POST['date']);

    // Variable to keep track of whether any records were updated
    $updated = false;

    // Loop through the selected IDs and update corresponding records in the database
    for ($i = 0; $i < count($selectedIds); $i++) {
        $id = $selectedIds[$i];
        $date = $selectedDates[$i];
        //echo 'id:' .$id;
        // Check if the date is not empty (i.e., a date is selected for this ID)
        if (!empty($date)) {
            // Perform SQL update operation
            $sql = "UPDATE training_mast SET Closed_date = ? WHERE id = ?";
            $params = array($date, $id);
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                // Handle error if update fails
                echo "Error updating record: " . sqlsrv_errors();
            } else {
                // Set flag indicating at least one record was updated
                $updated = true;
            }
        }
    }

    // If at least one record was updated, display alert and redirect
    if ($updated) {
        //echo "<script>alert('Date updated successfully.')</script>";
        echo "<script>alert('Date updated successfully');window.location.href = 'excel_upload.php';</script>";
    }

    // Redirect back to excel_upload.php
    //header("Location: excel_upload.php");
    exit();
} else {
    // Redirect or display an error message if accessed directly without submitting the form
    echo "Error: Form not submitted.";
}
?>
