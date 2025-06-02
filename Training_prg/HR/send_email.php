<?php
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
    exit();
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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer library
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (isset($_POST['selected_ids']) && !empty($_POST['selected_ids'])) {
    // Fetch data for each selected row
    $selectedIds = $_POST['selected_ids'];
    //$currentDate = date('Y-m-d H:i:s');
    $currentDate = new DateTime();

    // Loop through each selected row
    foreach ($selectedIds as $id) {
        // Fetch data for the selected row
        $query = "SELECT r.id, r.empno, r.Program_name, r.nature_training, r.year, r.remarks, r.duration, r.tentative_date, t.day_from, t.day_to,
                         a.name, a.dept, r.aprroved_time, r.hostel_book, a.location, COALESCE(r.flag, 0) AS flag, r.uploaded_date,
                         a.rep_ofcr, rep.email AS rep_ofcr_email
                  FROM [Complaint].[dbo].[request] r
                  JOIN [Complaint].[dbo].[emp_mas_sap] a ON r.empno = a.empno
                  LEFT JOIN [Complaint].[dbo].[emp_mas_sap] rep ON a.rep_ofcr = rep.empno
                  LEFT JOIN [Complaint].[dbo].[training_mast] t ON r.srl_no = t.srl_no
                  WHERE r.id = ?";

        $params = array($id);
        $stmt = sqlsrv_query($conn, $query, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if ($row) {
            $empno = $row['empno'];
            $name = $row['name'];
            $program_name = $row['Program_name'];
            $selectedEmail = $row['rep_ofcr_email'];
            $flag = $row['flag'];



            $day_from = isset($row['day_from']) ? $row['day_from']->format('Y-m-d') : 'N/A';
            $day_to = isset($row['day_to']) ? $row['day_to']->format('Y-m-d') : 'N/A';

              // Calculate Pending Days
              $referenceDate = isset($row['aprroved_time']) ? $row['aprroved_time'] : $row['uploaded_date'];
              $referenceDateFormatted = isset($referenceDate) ? $referenceDate->format('Y-m-d') : null;
              $pendingDays = ($referenceDateFormatted !== null) ? $currentDate->diff(new DateTime($referenceDateFormatted))->days : 'N/A';
  


            // Send email
            $mail = new PHPMailer(true);

            try {
                // SMTP configuration
                $mail->isSMTP();
                $mail->Host = 'mail.nspcl.co.in'; // SMTP host
                $mail->SMTPAuth = true;
                $mail->Username = 'unifiedhrtraining@nspcl.co.in'; // SMTP username
                $mail->Password = 'hrvision@2024'; // SMTP password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587; // SMTP port

                // Set sender and recipient
                $mail->setFrom('unifiedhrtraining@nspcl.co.in', 'HR Training');
                //$mail->addAddress('unifiedhrtraining@nspcl.co.in'); // Send to the selected email
                $mail->addAddress($selectedEmail);
                $mail->addCC('unifiedhrtraining@nspcl.co.in');
                //$mail->addAddress('it.bhilai@nspcl.co.in'); // For testing

                // Email content
                $mail->isHTML(true); // Set email format to HTML
                $mail->Subject = 'Action Required – Pending Training Request for ' . $name;
                $mail->Body = "
                <p>Respected Sir/Madam,</p>
                <p>I hope this email finds you well.</p>
                <p>We would like to bring to your attention that the training request(s) for the following employee(s) is/are currently pending at your end for necessary review and action:</p>
               
                <table style='border-collapse: collapse; width: 100%;' border='1'>
                    <tr>
                        <th style='padding: 8px; background-color: #f2f2f2; text-align: left;'>Employee No</th>
                        <td style='padding: 8px;'>$empno</td>
                    </tr>
                    <tr>
                        <th style='padding: 8px; background-color: #f2f2f2; text-align: left;'>Employee Name</th>
                        <td style='padding: 8px;'>$name</td>
                    </tr>
                    <tr>
                        <th style='padding: 8px; background-color: #f2f2f2; text-align: left;'>Training Program</th>
                        <td style='padding: 8px;'>$program_name</td>
                    </tr>
                    <tr>
                        <th style='padding: 8px; background-color: #f2f2f2; text-align: left;'>Training Duration</th>
                        <td style='padding: 8px;'>From: $day_from To: $day_to</td>
                    </tr>
                    <tr>
                    <th style='padding: 8px; background-color: #f2f2f2; text-align: left;'>Total Pending Days</th>
                    <td style='padding: 8px;'><?= $pendingDays ?> days</td>
                </tr>
                </table>
                <p>We kindly request you to take the necessary action on the pending training request(s) at the earliest. You may review and process the request(s) by accessing the system through the link provided below:</p>
                <p>
                    <a href='http://192.168.100.9:8080/training/login.php' style='display: inline-block; padding: 10px 20px; font-size: 16px; font-weight: bold; color: white; background-color: #28a745; border-radius: 5px; text-decoration: none;'>
                        Review Training Request
                    </a>
                </p>
                <p>Best regards,<br>HR Department</p>";

                // Send email
                if ($mail->send()) {
                    // Insert into send_mail_list table
                    $insertQuery = "INSERT INTO send_mail_list (request_id, empno, empname, Programname, mailid, flag, currentdate) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $params = array($id, $empno, $name, $program_name, $selectedEmail, $flag, $currentDate);
                    $stmt = sqlsrv_query($conn, $insertQuery, $params);

                    if (!$stmt) {
                        echo "<script>alert('Failed to insert mail details into the database for ID: $id. Error: " . print_r(sqlsrv_errors(), true) . "');</script>";
                    }
                } else {
                    echo "<script>alert('Failed to send email for ID: $id.');</script>";
                }
            } catch (Exception $e) {
                echo "<script>alert('Message could not be sent for ID: $id. Mailer Error: {$mail->ErrorInfo}');</script>";
            }
        } else {
            echo "<script>alert('No data found for ID: $id.');</script>";
        }
    }

    // Redirect after processing all selected rows
    echo "<script>alert('Emails sent successfully!'); window.location.href = 'pending_status.php';</script>";
} else {
    // Display message if no rows were selected
    echo "<script>alert('No rows selected.'); window.location.href = 'pending_status.php';</script>";
}
?>