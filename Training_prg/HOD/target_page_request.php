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
// Retrieve data from the form submission
$sessionemp = $_POST['sessionemp'];
$location = $_POST['location'];
$selectedrep_ofcr = $_POST['selectedrep_ofcr'];
$selectedEmpNo = $_POST['selectedEmpNo'];
$selectedData = json_decode($_POST['selectedData'], true); // Decode the JSON string

// Remove leading zeros
$sessionemp = ltrim($sessionemp, '0');
$selectedrep_ofcr = ltrim($selectedrep_ofcr, '0');
$selectedEmpNo = ltrim($selectedEmpNo, '0');
$currentdate = date("Y-m-d H:i:s");

// Display the data (for debugging)
echo "<pre>";
echo "Session Employee: " . htmlspecialchars($sessionemp) . "\n";
echo "Location: " . htmlspecialchars($location) . "\n";
echo "Selected Reporting Officer: " . htmlspecialchars($selectedrep_ofcr) . "\n";
echo "Selected Employee Number: " . htmlspecialchars($selectedEmpNo) . "\n";
echo "Selected Data:\n";

print_r($selectedData);
echo "</pre>";
// Flag to track if all inserts are successful
$allInsertsSuccessful = true;
echo "$currentdate";
foreach ($selectedData as $data) {
    $srl_no = $data['srl_no'];
    $programName = $data['programName'];
    $natureTraining = $data['natureTraining'];
    $duration = $data['duration'];
    $faculty = $data['faculty'];
    $year = $data['year'];
    $tentativeDate = $data['tentativeDate'];
    $targetGroup = $data['targetGroup'];
    $remarks = $data['remarks'];
    $hostelRequired = $data['hostelRequired'];
    $employeeRemarks = $data['employeeRemarks'];
    $flag = 2;
    $currentdate = date("Y-m-d H:i:s");

    $checkQuery = "SELECT COUNT(*) AS record_count 
                   FROM [Complaint].[dbo].[request] 
                   WHERE srl_no = ? AND empno = ? and flag not in ('1','3','88','000')";
    $checkParams = array($srl_no, $selectedEmpNo);
    $checkStmt = sqlsrv_query($conn, $checkQuery, $checkParams);

    if ($checkStmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
    if ($row['record_count'] > 0) {
        // Record already exists, display an alert and redirect
        echo "<script>
                alert('A record with srl_no = $srl_no and empno = $selectedEmpNo already exists!');
                window.location.href = 'TNI_approval.php';
              </script>";
        $allInsertsSuccessful = false; // Mark as failed
        continue; // Skip this record and proceed to the next one
    }

    // Prepare the SQL INSERT query
    $query = "INSERT INTO [Complaint].[dbo].[request] 
              (empno, rep_ofcr, srl_no, program_name, nature_training, duration, faculty, year, tentative_date, target_group, remarks, hostel_book, plant, appr_empno, aprroved_time, flag, uploaded_date) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $params = array(
        $selectedEmpNo, // empno
        $selectedrep_ofcr, // rep_ofcr
        $srl_no, // srl_no
        $programName, // program_name
        $natureTraining, // nature_training
        $duration, // duration
        $faculty, // faculty
        $year, // year
        $tentativeDate, // tentative_date
        $targetGroup, // target_group
        $employeeRemarks, // remarks
        $hostelRequired, // hostel_required
       
        $location, // plant
        $sessionemp, // appr_empno (assuming this is nullable or not required)
        $currentdate, // approved_time
        $flag, // flag
        $currentdate
    );

    // Execute the query
    $stmt = sqlsrv_query($conn, $query, $params);
    if ($stmt === false) {
        $allInsertsSuccessful = false; // Mark as failed
        die(print_r(sqlsrv_errors(), true)); // Stop execution and show errors
    }
}


// Redirect to target_page.php if all inserts are successful
if ($allInsertsSuccessful) {
    echo "<script>
            alert('Data inserted successfully!');
            window.location.href = 'TNI_approval.php';
          </script>";
    exit(); // Stop further execution
}
?>