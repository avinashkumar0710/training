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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['sendmail'])) {
    // Fetch the Reporting Officer's email based on 'rep_ofcr'
    $employeeno = $_SESSION["emp_num"];
    $sql = "SELECT rep_ofcr FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
    $params = array($sessionemp);
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if (sqlsrv_fetch($stmt) === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Get rep_ofcr email
    $repOfcr = sqlsrv_get_field($stmt, 0); // assuming rep_ofcr is in the first column
    $emailQuery = "SELECT [email] FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
    $emailStmt = sqlsrv_query($conn, $emailQuery, array($repOfcr));

    if ($emailStmt === false || !sqlsrv_has_rows($emailStmt)) {
        echo 'Reporting Officer email not found.';
        exit;
    }

    $emailRow = sqlsrv_fetch_array($emailStmt, SQLSRV_FETCH_ASSOC);
    $reportingOfficerEmail = $emailRow['email'];

    // Check if the email has already been sent
    $srl_no = $_POST['request_id']; // Assuming the unique ID is passed via hidden input
    $checkQuery = "SELECT * FROM send_mail_list WHERE request_id = ? AND empno = ?";
    $checkParams = array($srl_no, $sessionemp);
    $checkStmt = sqlsrv_query($conn, $checkQuery, $checkParams);

    if ($checkStmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Check if record already exists
    if (sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
        //echo "<script>alert('Email already sent for this request.');</script>";
        echo "<script>
                    alert('Email already sent for this request : " . $reportingOfficerEmail . "');
                    window.location.href = 'training_prg_home.php';
                </script>";
    } else {
        // Send the email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'mail.nspcl.co.in'; // Specify your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'unifiedhrtraining@nspcl.co.in'; // Your email username
            $mail->Password = 'hrvision@2024'; // Your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587; // TCP port to connect to

            // Set sender and recipient
            $mail->setFrom('unifiedhrtraining@nspcl.co.in', 'HR Training');
            //$mail->addAddress($reportingOfficerEmail); // Reporting Officer's email
            $mail->addAddress('it.bhilai@nspcl.co.in');

            // Mail content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'Pending Training Request ';
            $mail->Body = '
            <p>Dear Madam/Sir,</p>
           
            <p>The training request(s) of employee(s) is/are pending at your end for decision/action.</p>
            <p>You are requested to take action on the pending training request(s). You may take the action through the follow link.</p>
            <p>
                <a href="http://192.168.100.9:8080/training/login.php" style="display: inline-block; padding: 10px 20px; font-size: 16px; font-weight: bold; color: white; background-color: #28a745; border-radius: 5px; text-decoration: none;">
                    Review Training Request
                </a>
            </p>
            <p>Best regards,<br>HR Department</p>';

            // Send the email
            $mail->send();

            // Insert record into send_mail_list
            $insertQuery = "INSERT INTO send_mail_list (request_id, empno, mailid, currentdate) VALUES (?, ?, ?, ?)";
            $insertParams = array($srl_no, $sessionemp, $reportingOfficerEmail, date('Y-m-d H:i:s'));
            $insertStmt = sqlsrv_query($conn, $insertQuery, $insertParams);

            // If mail sent successfully and logged
            if ($insertStmt) {
                echo "<script>
                    document.getElementById('sendMailButton_$srl_no').style.backgroundColor = 'grey';
                    document.getElementById('sendMailButton_$srl_no').innerHTML = 'Mail Sent';
                    document.getElementById('sendMailButton_$srl_no').disabled = true;
                </script>";
                echo "<script>
                    alert('Email sent successfully to " . $reportingOfficerEmail . "');
                    window.location.href = 'training_prg_home.php';
                </script>";
            } else {
                echo "<script>alert('Failed to log email send action.');</script>";
            }
        } catch (Exception $e) {
            echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'training_prg_home.php';
                }, 2000); // Redirects after 2 seconds
            </script>";
        }
    }
}
?>
