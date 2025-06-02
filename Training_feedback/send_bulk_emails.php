<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

// Database connection
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

$data = json_decode(file_get_contents('php://input'), true);
$emailsToSend = $data['emails'] ?? [];

$results = [
    'feedback_not_given' => ['sent' => 0, 'failed' => 0],
    'feedback_to_hod' => ['sent' => 0, 'failed' => 0],
    'errors' => []
];

foreach ($emailsToSend as $emailData) {
    try {
        // Format empno - add '00' prefix if it's 6 digits
        $formattedEmpno = (strlen($emailData['empno']) == 6) ? '00' . $emailData['empno'] : $emailData['empno'];

        if ($emailData['email_type'] === 'feedback_not_given') {
            // Process feedback not given emails
            $result = sendFeedbackEmail($conn, $formattedEmpno, $emailData);
            if ($result['success']) {
                $results['feedback_not_given']['sent']++;
                
                // Update database flag
                $updateSql = "UPDATE [Complaint].[dbo].[attendance_records] 
                             SET email_flag = 8 
                             WHERE empno = ? AND program_name = ? AND CONVERT(VARCHAR, to_date, 120) = ?";
                
                $params = array($emailData['empno'], $emailData['program_name'], $emailData['to_date']);
                $updateStmt = sqlsrv_query($conn, $updateSql, $params);
                
                if ($updateStmt === false) {
                    throw new Exception("Failed to update record for empno: {$emailData['empno']}");
                }
                sqlsrv_free_stmt($updateStmt);
            } else {
                throw new Exception($result['error']);
            }
        } else {
            // Process HOD notification emails
            $result = sendHodNotification($conn, $formattedEmpno, $emailData);
            if ($result['success']) {
                $results['feedback_to_hod']['sent']++;
            } else {
                throw new Exception($result['error']);
            }
        }
        
    } catch (Exception $e) {
        if ($emailData['email_type'] === 'feedback_not_given') {
            $results['feedback_not_given']['failed']++;
        } else {
            $results['feedback_to_hod']['failed']++;
        }
        $results['errors'][] = "Empno {$emailData['empno']}: " . $e->getMessage();
        
        // Log error
        file_put_contents('email_error_log.txt', 
            date('Y-m-d H:i:s') . " - Error for {$emailData['empno']}: " . $e->getMessage() . "\n", 
            FILE_APPEND);
    }
}

sqlsrv_close($conn);

echo json_encode([
    'success' => ($results['feedback_not_given']['failed'] + $results['feedback_to_hod']['failed']) === 0,
    'summary' => [
        'feedback_sent' => $results['feedback_not_given']['sent'],
        'feedback_failed' => $results['feedback_not_given']['failed'],
        'hod_sent' => $results['feedback_to_hod']['sent'],
        'hod_failed' => $results['feedback_to_hod']['failed']
    ],
    'errors' => $results['errors']
]);

function sendFeedbackEmail($conn, $formattedEmpno, $emailData) {
    // Fetch employee details
    $query = "SELECT name, email FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
    $stmt = sqlsrv_query($conn, $query, [$formattedEmpno]);

    if ($stmt === false) {
        return ['success' => false, 'error' => 'Employee query failed'];
    }

    $employee = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmt);
    
    if (!$employee) {
        return ['success' => false, 'error' => 'Employee not found'];
    }

    $name = $employee['name'];
    $email = $employee['email'];
    
    // Initialize PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.nspcl.co.in';
        $mail->SMTPAuth = true;
        $mail->Username = 'unifiedhrtraining@nspcl.co.in';
        $mail->Password = 'Nspcl@123';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('unifiedhrtraining@nspcl.co.in', 'HR Training');
        $mail->addAddress('itnspcl@gmail.com'); // Testing address
        $mail->addCC('unifiedhrtraining@nspcl.co.in');
        
        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Action Required: Feedback for Your Recent Training Program';
        $mail->Body = generateFeedbackEmailBody($name, $emailData['program_name'], $emailData['to_date']);
        
        $mail->send();
        
        // ✅ Update email flag after successful email sending
        $updateSql = "UPDATE [Complaint].[dbo].[attendance_records] 
                      SET email_flag = 8 
                      WHERE empno = ? AND program_name = ? AND CONVERT(VARCHAR, to_date, 120) = ?";
        
        $params = array($emailData['empno'], $emailData['program_name'], $emailData['to_date']);
        sqlsrv_query($conn, $updateSql, $params);
        
        return ['success' => true];

    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}


function sendHodNotification($conn, $formattedEmpno, $emailData) {
    // Fetch employee and HOD details
    $query = "SELECT e.name as emp_name, e.email as emp_email, e.hod_ro, h.name as hod_name, h.email as hod_email
              FROM [Complaint].[dbo].[emp_mas_sap] e
              LEFT JOIN [Complaint].[dbo].[emp_mas_sap] h ON e.hod_ro = h.empno
              WHERE e.empno = ?";
    $stmt = sqlsrv_query($conn, $query, [$formattedEmpno]);

    if ($stmt === false) {
        return ['success' => false, 'error' => 'Employee/HOD query failed'];
    }

    $data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    sqlsrv_free_stmt($stmt);
    
    if (!$data) {
        return ['success' => false, 'error' => 'Employee not found'];
    }

    if (empty($data['hod_email'])) {
        return ['success' => false, 'error' => 'HOD email not found'];
    }

    // Initialize PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.nspcl.co.in';
        $mail->SMTPAuth = true;
        $mail->Username = 'unifiedhrtraining@nspcl.co.in';
        $mail->Password = 'Nspcl@123';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
    
        // Recipients
        $mail->setFrom('unifiedhrtraining@nspcl.co.in', 'HR Training');
        $mail->addAddress('itnspcl@gmail.com'); // HOD's email (testing)
        // $mail->addAddress($data['hod_email']); // Uncomment for production
        //$mail->addCC('itnspcl@gmail.com'); // Employee's email (testing)
        // $mail->addCC($data['emp_email']); // Uncomment for production
        $mail->addCC('unifiedhrtraining@nspcl.co.in');
        
        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Notification: Employee Training Absence - ' . $data['emp_name'];
        
        $mail->Body = generateHodEmailBody(
            $data['emp_name'],
            $formattedEmpno,
            $data['hod_name'],
            $emailData['program_name'],
            $emailData['to_date']
        );
        
        $mail->send();
        
        // Log success
        file_put_contents('hod_email_log.txt', 
            date('Y-m-d H:i:s') . " - Sent HOD notification for {$data['emp_name']} to {$data['hod_name']}\n", 
            FILE_APPEND);
            
        return ['success' => true];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function generateFeedbackEmailBody($name, $programName, $toDate) {
    return '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { color: #0066cc; font-size: 18px; margin-bottom: 20px; }
            .content { margin-bottom: 20px; }
            .button {
                background-color: #0066cc;
                color: #ffffff;
                padding: 10px 15px;
                text-decoration: none;
                border-radius: 4px;
                display: inline-block;
                margin: 10px 0;
            }
            .footer { font-size: 12px; color: #666; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">Training Program Feedback Request</div>
            
            <div class="content">
                <p>Respected Sir/Mam '.$name.',</p>
                
                <p>We hope you found your last training program <strong>'.$programName.'</strong> (completed on '.$toDate.') beneficial and informative.</p>
                
                <p>Your feedback is extremely valuable to us as it helps:</p>
                <ul>
                    <li>Evaluate the training effectiveness</li>
                    <li>Improve future programs</li>
                    <li>Enhance the learning experience</li>
                </ul>
                
                <p>Please take 5 minutes to share your feedback by clicking below:</p>
                
                <p><a href="http://192.168.100.9:8080/training/login.php" class="button">Submit Feedback</a></p>
                
                <p>We kindly request your response by before <strong>'.date('d-M-Y', strtotime('+2 days')).'</strong>.</p>
            </div>
            
            <div class="footer">
                <p>For any queries, please contact HR Training Team at unifiedhrtraining@nspcl.co.in or call [HR_CONTACT].</p>
                
                <p>Best regards,<br>
                <strong>HR Training Team</strong><br>
                NSPCL</p>
            </div>
        </div>
    </body>
    </html>';
}

function generateHodEmailBody($empName, $empNo, $hodName, $programName, $toDate) {
    return '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { color: #d9534f; font-weight: bold; font-size: 18px; margin-bottom: 20px; }
            .important { color: #d9534f; font-weight: bold; }
            .details { margin: 15px 0; }
            .footer { font-size: 12px; color: #666; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <p>Respected Sir/Madam <span class="important">'.$hodName.'</span>,</p>
            
            <div class="header">URGENT: Employee Training Program Absence Notification</div>
            
            <div class="details">
                <p>This formal communication serves to bring to your immediate attention that:</p>
                
                <p><strong class="important">'.$empName.'</strong> (Employee ID: <strong>'.$empNo.'</strong>) 
                was <span class="important">marked absent</span> for the following <span class="important">mandatory training program</span>:</p>
                
                <ul>
                    <li><strong>Program Name:</strong> <span class="important">'.$programName.'</span></li>
                    <li><strong>Scheduled Date:</strong> '.$toDate.'</li>
                    <li><strong>Program Importance:</strong> [HIGH/MEDIUM] priority training initiative</li>
                    <li><strong>Impact:</strong> Non-attendance may affect [COMPLIANCE/PERFORMANCE METRICS]</li>
                </ul>
                
                <p>As the <span class="important">Head of Department</span>, your prompt action is requested to:</p>
                <ol>
                    <li>Investigate the reason for this absence</li>
                    <li>Ensure the employee completes any make-up requirements</li>
                    <li>Submit a formal response to HR within <strong>3 working days</strong></li>
                </ol>
                
                <p>Please note this absence has been recorded in the employee\'s <span class="important">training compliance record</span>.</p>
            </div>
            
            <div class="footer">
                <p>For any clarifications or to discuss alternative arrangements, please contact the HR Training Team immediately.</p>
                
                <p>Best regards,<br>
                <strong>HR Training Team</strong><br>
                NSPCL<br>
                <i>Email:</i> unifiedhrtraining@nspcl.co.in</p>
                
                <p style="font-size: 12px; color: #999; margin-top: 20px;">
                    <strong>Note:</strong> This is an auto-generated communication. Please do not reply to this email.
                </p>
            </div>
        </div>
    </body>
    </html>';
}
?>