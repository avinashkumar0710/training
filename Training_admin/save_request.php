<?php
// Database connection settings for SQL Server
$serverName = "192.168.100.240";
$database = "Complaint";
$username = "sa";
$password = "Intranet@123";

try {
    $conn = new PDO("sqlsrv:server=$serverName;Database=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        foreach ($_POST['program_id'] as $index => $program_id) {
            $user_id = $_POST['program_id'][$index];
            $name = $_POST['emp_name_text'][$index];
            $dept = $_POST['dept_text'][$index];
            $location = $_POST['plant_text'][$index];
            $program_name = $_POST['program_name'][$index];
            $duration = $_POST['duration'][$index];
            $flag_value = 2; // Explicitly set to 2
            $dept_code = $_POST['dept_code'][$index];
            $empno = $_POST['emp_no'][$index];
            $loc_desc = $_POST['plant'][$index];
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

            // **Debug: Print all values before insertion**
            echo "<h3>Debug Values Before Insertion:</h3>";
            echo "<pre>";
            print_r([
                'user_id' => $user_id,
                'name' => $name,
                'dept' => $dept,
                'location' => $location,
                'program_id' => $program_id,
                'program_name' => $program_name,
                'duration' => $duration,
                'flag_value' => $flag_value, // Should be 2
                'dept_code' => $dept_code,
                'empno' => $empno,
                'loc_desc' => $loc_desc,
                'srl_no' => $srl_no,
                'training_location' => $training_location,
                'from_date' => $from_date,
                'to_date' => $to_date,
                'mandays' => $mandays,
                'nature_of_training' => $nature_of_training,
                'training_subtype' => $training_subtype,
                'training_mode' => $training_mode,
                'attendance' => $attendance,
                'faculty' => $faculty,
                'year' => $year,
                'total_attendance' => $total_attendance,
            ]);
            echo "</pre>";

            // SQL INSERT statement
            $sql = "INSERT INTO attendance_records
                (user_id, name, dept, location, program_id, program_name, duration, day,
                 attendance_status, attendance_fraction, total_attendance, srl_no,
                 attend_date, flag, dept_code, empno, loc_desc, training_location,
                 from_date, to_date, mandays, nature_of_training, training_subtype,
                 training_mode, attendance, faculty, year, act_Nact_flag,training_feedback_flag )
            VALUES
                (:user_id, :name, :dept, :location, :program_id, :program_name, :duration, :day,
                 1, 1.00, :total_attendance, :srl_no,
                 :attend_date, :flag_value, :dept_code, :empno, :loc_desc, :training_location,
                 :from_date, :to_date, :mandays, :nature_of_training, :training_subtype,
                 :training_mode, :attendance, :faculty, :year, 1, 7)";

            $stmt = $conn->prepare($sql);

            // Bind parameters (corrected syntax)
            $stmt->bindValue(':user_id', $user_id);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':dept', $dept);
            $stmt->bindValue(':location', $location);
            $stmt->bindValue(':program_id', $program_id);
            $stmt->bindValue(':program_name', $program_name);
            $stmt->bindValue(':duration', $duration);
            $stmt->bindValue(':day', 1);
            $stmt->bindValue(':total_attendance', $total_attendance);
            $stmt->bindValue(':srl_no', $srl_no);
            $stmt->bindValue(':attend_date', $from_date);
            $stmt->bindValue(':flag_value', $flag_value); // Explicitly set to 2
            $stmt->bindValue(':dept_code', $dept_code);
            $stmt->bindValue(':empno', $empno);
            $stmt->bindValue(':loc_desc', $loc_desc);
            $stmt->bindValue(':training_location', $training_location);
            $stmt->bindValue(':from_date', $from_date);
            $stmt->bindValue(':to_date', $to_date);
            $stmt->bindValue(':mandays', $mandays);
            $stmt->bindValue(':nature_of_training', $nature_of_training);
            $stmt->bindValue(':training_subtype', $training_subtype);
            $stmt->bindValue(':training_mode', $training_mode);
            $stmt->bindValue(':attendance', $attendance);
            $stmt->bindValue(':faculty', $faculty);
            $stmt->bindValue(':year', $year);

            // Execute the statement
            if ($stmt->execute()) {
                echo "✅ Record inserted successfully for Program ID: " . htmlspecialchars($program_id) . "<br>";
            } else {
                echo "❌ Error inserting record for Program ID: " . htmlspecialchars($program_id) . "<br>";
                print_r($stmt->errorInfo());
            }
        }
    }
} catch (PDOException $e) {
    die("❌ Error: " . $e->getMessage());
}
$conn = null;
?>