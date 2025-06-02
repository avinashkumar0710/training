<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Set header for JSON response
header('Content-Type: application/json; charset=UTF-8');

try {
    // Validate inputs
    if (!isset($_GET['empno'], $_GET['program_name'], $_GET['to_date'])) {
        throw new Exception('Missing required parameters');
    }

    // Sanitize inputs
    $empno = filter_var($_GET['empno'], FILTER_SANITIZE_STRING);
    $program_name = urldecode(filter_var($_GET['program_name'], FILTER_SANITIZE_STRING));
    $to_date = filter_var($_GET['to_date'], FILTER_SANITIZE_STRING);

    // Validate date format
    if (!DateTime::createFromFormat('Y-m-d', $to_date)) {
        throw new Exception('Invalid date format');
    }

    // Format empno - add '00' prefix if it's 6 digits
    $formattedEmpno = (strlen($empno) == 6) ? '00' . $empno : $empno;

    // Connect to database
    $conn = sqlsrv_connect("192.168.100.240", [
        "Database" => "Complaint",
        "UID" => "sa",
        "PWD" => "Intranet@123"
    ]);

    if ($conn === false) {
        throw new Exception('Database connection failed: ' . print_r(sqlsrv_errors(), true));
    }

    // Fetch employee details
    $query1 = "SELECT name, email, hod_ro FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
    $stmt1 = sqlsrv_query($conn, $query1, [$formattedEmpno]);

    if ($stmt1 === false) {
        throw new Exception('Employee query failed: ' . print_r(sqlsrv_errors(), true));
    }

    $employee = sqlsrv_fetch_array($stmt1, SQLSRV_FETCH_ASSOC);
    
    if (!$employee) {
        throw new Exception('Employee not found');
    }

    $employee_name = $employee['name'];
    $employee_email = $employee['email'];
    $hod_ro = $employee['hod_ro'];

    // Fetch HOD's email
    $query2 = "SELECT name, email FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
    $stmt2 = sqlsrv_query($conn, $query2, [$hod_ro]);

    if ($stmt2 === false) {
        throw new Exception('HOD query failed: ' . print_r(sqlsrv_errors(), true));
    }

    $hod = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
    
    if (!$hod) {
        throw new Exception('HOD not found');
    }

    $hod_name = $hod['name'];
    $hod_email = $hod['email'];

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
        $mail->CharSet = 'UTF-8';
        $mail->SMTPDebug = 0; // Set to 2 for verbose debugging

        // Recipients
        $mail->setFrom('unifiedhrtraining@nspcl.co.in', 'HR Training');
        $mail->addAddress('itnspcl@gmail.com'); // Testing
        //$mail->addAddress($hod_email); // HOD's actual email
        //$mail->addCC($employee_email); // Employee's actual email
        $mail->addCC('unifiedhrtraining@nspcl.co.in'); // HR team
        
        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'URGENT: Employee Training Absence - ' . $employee_name;
        
        // Bootstrap-styled HTML email
        $mail->Body = "<html>
        <head>
        <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Training Program Feedback Request</title>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css'>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js'></script>
    <link rel='preconnect' href='https://fonts.googleapis.com'>
<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
<link href='https://fonts.googleapis.com/css2?family=Ancizar+Sans:ital,wght@0,100..1000;1,100..1000&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap' rel='stylesheet'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333;}
                .header { color: #d9534f; font-weight: bold; }
                .details { margin: 15px 0; }
                .important { color: #d9534f; font-weight: bold; }
                .container { max-width: 600px; margin: 40px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9; font-family: 'Nunito', sans-serif;}
            </style>
        </head>
        <body>
        <div class='container'>
            <p>Respected Sir/Madam <span class='important'>$hod_name</span>,</p>
            
            <div class='header'>URGENT: Employee Training Program Absence Notification</div>
            
            <div class='details'>
                <p>This formal communication serves to bring to your immediate attention that:</p>
                
                <p><strong class='important'>$employee_name</strong> (Employee ID: <strong>$formattedEmpno</strong>) 
                was <span class='important'>marked absent</span> for the following <span class='important'>mandatory training program</span>:</p>
                
                <ul>
                    <li><strong>Program Name:</strong> <span class='important'>$program_name</span></li>
                    <li><strong>Scheduled Date:</strong> $to_date</li>
                    <li><strong>Program Importance:</strong> [HIGH/MEDIUM] priority training initiative</li>
                    <li><strong>Impact:</strong> Non-attendance may affect [COMPLIANCE/PERFORMANCE METRICS]</li>
                </ul>
                
                <p>As the <span class='important'>Head of Department</span>, your prompt action is requested to:</p>
                <ol>
                    <li>Investigate the reason for this absence</li>
                    <li>Ensure the employee completes any make-up requirements</li>
                    <li>Submit a formal response to HR within <strong>3 working days</strong></li>
                </ol>
                
                <p>Please note this absence has been recorded in the employee's <span class='important'>training compliance record</span>.</p>
            </div>
            
            <div class='footer'>
                <p>For any clarifications or to discuss alternative arrangements, please contact the HR Training Team immediately.</p>
                
                <p>Best regards,<br>
                <strong>HR Training Team</strong><br>
                NSPCL<br>
               
                <i>Email:</i> unifiedhrtraining@nspcl.co.in</p>
                
                <p style='font-size: 12px; color: #999; margin-top: 20px;'>
                    <strong>Note:</strong> This is an auto-generated communication. Please do not reply to this email.
                </p>
            </div>
            </div>
        </body>
    </html>";

    if ($mail->send()) {
        // ✅ Update `email_flag = 8` **only if email was sent**
        $updateSql = "UPDATE [Complaint].[dbo].[attendance_records] 
                      SET email_flag = 8 
                      WHERE empno = ? AND program_name = ? AND CONVERT(VARCHAR, to_date, 120) = ?";
        
        $params = array($formattedEmpno, $program_name, $to_date);
        $updateStmt = sqlsrv_query($conn, $updateSql, $params);

        if ($updateStmt === false) {
            throw new Exception("Failed to update email flag for empno: $formattedEmpno");
        }

        sqlsrv_free_stmt($updateStmt);

        // Log success
        file_put_contents('hod_notification_log.txt', 
            date('Y-m-d H:i:s') . " - Email sent to $hod_name ($hod_email) & flag updated.\n", 
            FILE_APPEND);
        
        // Return success response
        ob_clean();
        echo json_encode([
            'success' => true,
            'message' => 'Notification sent successfully & email flag updated!'
        ]);
    }

} catch (Exception $e) {
    // Log error
    file_put_contents('email_error_log.txt', date('Y-m-d H:i:s') . " - Error: " . $e->getMessage() . "\n", FILE_APPEND);
    
    // Return error response
    http_response_code(400);
    ob_clean();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send email or update flag: ' . $e->getMessage()
    ]);
}

    // Clean up
    sqlsrv_free_stmt($stmt1);
    sqlsrv_free_stmt($stmt2);
    sqlsrv_close($conn);

} catch (Exception $e) {
    http_response_code(400);
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
