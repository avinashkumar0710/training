<?php
require 'ExcelReader/ExcelReader.php';

// Database connection details
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

// Check if a file has been uploaded
if (isset($_FILES['excelFile']) && $_FILES['excelFile']['error'] == 0) {
    $fileName = $_FILES['excelFile']['tmp_name'];

    // Load the Excel file
    $data = new Spreadsheet_Excel_Reader($fileName);
    $sheet = $data->sheets[0];
    
    // Loop through the rows and insert into the database
    for ($i = 1; $i < count($sheet['cells']); $i++) { // Assuming first row is header
        $row = $sheet['cells'][$i];

        // Extract values from the row
        $employeeNo = $row[1];
        $employeeName = $row[2];
        $personnelArea = $row[3];
        $personalSubarea = $row[4];
        $personnelSubareaDescription = $row[5];
        $employeeSubGroup = $row[6];
        $position = $row[7];
        $department = $row[8];
        $appraisalStartDate = date('Y-m-d', strtotime($row[9]));
        $appraisalEndDate = date('Y-m-d', strtotime($row[10]));
        $typeOfTrainingNeed = $row[11];
        $trainingName = $row[12];

        // Prepare the SQL statement
        $sql = "INSERT INTO TNI_PMS (
                    EmployeeNo, EmployeeName, PersonnelArea, PersonalSubarea,
                    PersonnelSubareaDescription, EmployeeSubGroup, Position, Department,
                    AppraisalStartDate, AppraisalEndDate, TypeOfTrainingNeed, TrainingName
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = array(
            $employeeNo, $employeeName, $personnelArea, $personalSubarea,
            $personnelSubareaDescription, $employeeSubGroup, $position, $department,
            $appraisalStartDate, $appraisalEndDate, $typeOfTrainingNeed, $trainingName
        );

        // Execute the SQL statement
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
    }

    echo "Data has been uploaded successfully!";
} else {
    echo "Please select a valid Excel file.";
}

// Close the connection
sqlsrv_close($conn);
?>
