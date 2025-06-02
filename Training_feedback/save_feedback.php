<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["emp_num"])) {
    header("location:login.php");
    exit;
}

// Function to sanitize input data
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data); // Recursively sanitize each element
    } else {
        return htmlspecialchars(strip_tags(trim($data)));
    }
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve data from form
    $empno = $_SESSION['emp_num'];
    $Program_name = sanitizeInput($_POST['Program_name']);
    $program_duration = sanitizeInput($_POST['program_duration']);
    $faculty = sanitizeInput($_POST['faculty']);
    $program_id = sanitizeInput($_POST['srl_no']); // srl_no = program_id

    // Sanitize feedback ratings
    $overall_objectives = sanitizeInput($_POST['overall_objectives'][0]);
    $content_depth = sanitizeInput($_POST['content_depth'][0]);
    $program_duration_feedback = sanitizeInput($_POST['program_duration_feedback'][0]);
    $relevance = sanitizeInput($_POST['relevance'][0]);
    $program_coordinated = sanitizeInput($_POST['program_coordinated'][0]);
    $faculty_feedback = sanitizeInput($_POST['faculty_feedback'][0]);
    $hospitality_arrangements = sanitizeInput($_POST['hospitality_arrangements'][0]);
    $administrative_arrangements = sanitizeInput($_POST['administrative_arrangements'][0]);
    $stay_arrangements = sanitizeInput($_POST['stay_arrangements'][0]);
    $suggestions = sanitizeInput($_POST['suggestions'][0]);

    // Handle PDF Upload
    $filePath = '';
    if (isset($_FILES["pdfFile"]) && $_FILES["pdfFile"]["error"] == 0) {
        $uploadDir = "pdf_files/";
        $fileName = $empno . "_" . $program_id . ".pdf";
        $targetPath = $uploadDir . $fileName;

        $fileType = strtolower(pathinfo($_FILES["pdfFile"]["name"], PATHINFO_EXTENSION));

        if ($fileType == "pdf") {
            if (!move_uploaded_file($_FILES["pdfFile"]["tmp_name"], $targetPath)) {
                die("Failed to upload PDF file.");
            } else {
                $filePath = $targetPath;
            }
        } else {
            die("Only PDF files are allowed.");
        }
    } else {
        die("Please upload a valid PDF file.");
    }

    // Insert feedback and file path
    $query = "INSERT INTO program_feedback (
                emp_num, srl_no, program_title, program_duration, faculty,
                overall_objectives, content_depth, program_duration_feedback,
                relevance, program_coordinated, faculty_feedback,
                hospitality_arrangements, administrative_arrangements, stay_arrangements, 
                created_at, suggestion, file_path
              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, GETDATE(), ?, ?)";

    $params = array(
        $empno, $program_id, $Program_name, $program_duration, $faculty,
        $overall_objectives, $content_depth, $program_duration_feedback,
        $relevance, $program_coordinated, $faculty_feedback,
        $hospitality_arrangements, $administrative_arrangements, $stay_arrangements,
        $suggestions, $filePath
    );

    $stmt = sqlsrv_query($conn, $query, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    } else {
        // After successful insert, update training_feedback_flag to '8'
        $updateQuery = "UPDATE [Complaint].[dbo].[attendance_records] 
                        SET training_feedback_flag = '8' 
                        WHERE empno = ? AND program_id = ?";
        $updateParams = array($empno, $program_id);
        $updateStmt = sqlsrv_query($conn, $updateQuery, $updateParams);

        if ($updateStmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        echo "<script>
                alert('Feedback saved, file uploaded, and status updated successfully!');
                window.location.href = 'index.php';
              </script>";
    }

    sqlsrv_free_stmt($stmt);
    if (isset($updateStmt)) {
        sqlsrv_free_stmt($updateStmt);
    }
}

sqlsrv_close($conn);
?>
