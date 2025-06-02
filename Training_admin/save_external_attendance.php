<?php
// Database connection settings for SQL Server
$serverName = "192.168.100.240"; // Example: "localhost\SQLEXPRESS"
$database = "Complaint";
$username = "sa";
$password = "Intranet@123";

try {
    // Establishing connection using PDO for SQL Server
    $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        foreach ($_POST['program_id'] as $index => $program_id) {
            $user_id = $_POST['program_id'][$index]; // Store program_id in user_id (Review if this is correct)
            $name = $_POST['emp_name_text'][$index]; // Employee Name (Dropdown Text)
            $dept = $_POST['dept_text'][$index];     // Department Name (Dropdown Text)
            $location = $_POST['plant_text'][$index]; // Plant Name (Dropdown Text)
            $program_name = $_POST['program_name'][$index];
            $duration = $_POST['duration'][$index];
            $dept_code = $_POST['dept_code'][$index];
            $empno = $_POST['emp_no'][$index];
            $loc_desc = $_POST['plant'][$index];      // Assuming 'plant' is the value of the plant dropdown
            $srl_no = $_POST['program_id'][$index];
            $training_location = $_POST['training_location'][$index];
            $from_date = $_POST['from_date'][$index];
            $to_date = $_POST['to_date'][$index];
            $mandays = $_POST['mandays'][$index];
            $nature_of_training = $_POST['nature_of_training'][$index];
            $training_subtype = $_POST['training_subtype'][$index];
            $training_mode = $_POST['training_mode'][$index];
            $attendance = $_POST['attendance'][$index];
            $faculty = $_POST['faculty'][$index];
            $year = $_POST['year'][$index];
            $total_attendance = $_POST['mandays'][$index];
    
            // SQL INSERT statement
            $sql = "INSERT INTO attendance_records
                        (user_id, name, dept, location, program_id, program_name, duration, day,
                         attendance_status, attendance_fraction, total_attendance, srl_no,
                         attend_date, flag, dept_code, empno, loc_desc, training_location,
                         from_date, to_date, mandays, nature_of_training, training_subtype,
                         training_mode, attendance,faculty,year,act_Nact_flag, training_feedback_flag)
                    VALUES
                        (:user_id, :name, :dept, :location, :program_id, :program_name, :duration, :day,
                         1, 1.00, :total_attendance, :srl_no,
                         :attend_date, 3, :dept_code, :empno, :loc_desc, :training_location,
                         :from_date, :to_date, :mandays, :nature_of_training, :training_subtype,
                         :training_mode, :attendance, :faculty, :year, 1, 7)";
    
            // Prepare statement
            $stmt = $conn->prepare($sql);
    
            // Bind parameters (using bindValue for clarity with $_POST values)
            $stmt->bindValue(':user_id', $user_id);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':dept', $dept);
            $stmt->bindValue(':location', $location);
            $stmt->bindValue(':program_id', $program_id);
            $stmt->bindValue(':program_name', $program_name);
            $stmt->bindValue(':duration', $duration);
            $stmt->bindValue(':day', 1); // Set day to 1
            $stmt->bindValue(':total_attendance', $total_attendance);
            $stmt->bindValue(':srl_no', $srl_no);
            $stmt->bindValue(':attend_date', $from_date); // Use 'from_date' as attendance date
            $stmt->bindValue(':dept_code', $dept_code);
            $stmt->bindValue(':empno', $empno);
            $stmt->bindValue(':loc_desc', $loc_desc);
            $stmt->bindValue(':training_location', $training_location);
            $stmt->bindValue(':from_date', $from_date);
            $stmt->bindValue(':to_date', $to_date);
            $stmt->bindValue(':mandays', $mandays);
            $stmt->bindValue(':nature_of_training', $nature_of_training);
            $stmt->bindValue(':training_subtype', $training_subtype);
            $stmt->bindValue(':training_mode', $training_mode); // Corrected typo
            $stmt->bindValue(':attendance', $attendance);
            $stmt->bindValue(':faculty', $faculty);
            $stmt->bindValue(':year', value: $year);
    
            // Execute the statement
            if ($stmt->execute()) {
                // Handle successful insertion (e.g., display a success message)
                echo "Record inserted successfully for Program ID: " . htmlspecialchars($program_id) . "<br>";
            } else {
                // Handle insertion error (e.g., display an error message and log details)
                echo "Error inserting record for Program ID: " . htmlspecialchars($program_id) . "<br>";
                print_r($stmt->errorInfo()); // Display detailed error information
            }
        }
    }
} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage());
}
// Close connection
$conn = null;
?>