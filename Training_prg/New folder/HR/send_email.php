<?php
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
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

if (isset($_POST['selected_email']) && !empty($_POST['selected_email'])) {
    // Fetch the Reporting Officer's email based on 'rep_ofcr'
    $selectedEmail = $_POST['selected_email'];
    $empno = $_POST['empno'];
    $name = $_POST['name'];
    $id = $_POST['id'];
    $program_name = $_POST['program_name'];
    $flag = $_POST['flag'];  // Retrieve the flag value
    $currentDate = date('Y-m-d H:i:s');

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
        //$mail->addAddress($selectedEmail); // Send to the selected email 
        $mail->addAddress('it.bhilai@nspcl.co.in');

        // Email content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'Pending Training Request for ' . $name;
        $mail->Body = "
        <p>Respected Sir/Madam,</p>
        <p>The training request(s) of the following employee(s) is/are pending at your end for decision/action:</p>
        <p><b>Employee No:</b> $empno</p>
        <p><b>Name:</b> $name</p>
        <p><b>Program Name:</b> $program_name</p>
       
        <p>You are requested to take action on the pending training request(s). You may take the action through the link below:</p>
        <p>
            <a href='http://192.168.100.9:8080/training/login.php' style='display: inline-block; padding: 10px 20px; font-size: 16px; font-weight: bold; color: white; background-color: #28a745; border-radius: 5px; text-decoration: none;'>
                Review Training Request
            </a>
        </p>
        <p>Best regards,<br>HR Department</p>";

        // Send email
        if($mail->send()) {
            //echo "Email sent successfully!";
            echo "<script>alert('Email sent successfully!'); setTimeout(function(){ window.location.href = 'pending_status.php'; }, 3000);</script>";

            // Database insertion using sqlsrv_query
            $insertQuery = "INSERT INTO send_mail_list (request_id, empno, empname, Programname, mailid, flag, currentdate) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = array($id, $empno, $name, $program_name, $selectedEmail, $flag, $currentDate);

            // Execute the query
            $stmt = sqlsrv_query($conn, $insertQuery, $params);

            if ($stmt) {
                echo "<script>alert('Mail details successfully saved in the database!'); setTimeout(function(){ window.location.href = 'pending_status.php'; }, 1000);</script>";
            } else {
                echo "<script>alert('Failed to insert mail details into the database. Error: " . print_r(sqlsrv_errors(), true) . "');</script>";
            }

        } else {
            echo "<script>alert('Failed to send email.');</script>";
        }

    } catch (Exception $e) {
        // Display error message if email couldn't be sent
        echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}');</script>";
    }
} else {
    // Display message if no email was selected
    echo "<script>alert('No email address selected.');</script>";
}
?>
