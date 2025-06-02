<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

header('Content-Type: application/json');

try {
    // Validate inputs exist
    if (!isset($_GET['empno'], $_GET['program_name'], $_GET['to_date'])) {
        throw new Exception('Missing required parameters');
    }

    // Sanitize inputs - modern approach (replaces FILTER_SANITIZE_STRING)
    $empno = htmlspecialchars($_GET['empno'], ENT_QUOTES, 'UTF-8');
    $program_name = htmlspecialchars(urldecode($_GET['program_name']), ENT_QUOTES, 'UTF-8');
    $to_date = htmlspecialchars($_GET['to_date'], ENT_QUOTES, 'UTF-8');

    // Additional validation
    if (empty($empno) || empty($program_name) || empty($to_date)) {
        throw new Exception('Invalid parameters');
    }

    // Validate date format
    if (!DateTime::createFromFormat('Y-m-d', $to_date)) {
        throw new Exception('Invalid date format');
    }

    // Format empno
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
    $query = "SELECT [name], [email] FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = ?";
    $stmt = sqlsrv_query($conn, $query, [$formattedEmpno]);

    if ($stmt === false) {
        throw new Exception('Query failed: ' . print_r(sqlsrv_errors(), true));
    }

    $employee = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    
    if (!$employee) {
        throw new Exception('Employee not found');
    }

    $name = $employee['name'];
    $email = $employee['email'];
    
    // Initialize PHPMailer with debug output
    $mail = new PHPMailer(true);
    
    try {
        // Server settings with debug
        $mail->isSMTP();
        $mail->Host = 'mail.nspcl.co.in';
        $mail->SMTPAuth = true;
        $mail->Username = 'unifiedhrtraining@nspcl.co.in';
        $mail->Password = 'Nspcl@123'; // Verify this password is correct
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Enable verbose debug output
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->Debugoutput = function($str, $level) {
            file_put_contents('smtp_debug.log', date('Y-m-d H:i:s') . " [$level] $str\n", FILE_APPEND);
        };

        // Recipients
        $mail->setFrom('unifiedhrtraining@nspcl.co.in', 'HR Training');
        //$mail->addAddress($email); // Primary recipient
        $mail->addAddress('itnspcl@gmail.com');
        $mail->addCC('unifiedhrtraining@nspcl.co.in'); // CC to HR
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Training Program Feedback Request: ' . $program_name;
        
        $mail->Body = sprintf('
        <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Program Feedback Request</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Ancizar+Sans:ital,wght@0,100..1000;1,100..1000&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

    <style>
        body { font-family: "Nunito", sans-serif; color: #333; }
        .container { max-width: 600px; margin: 40px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f9f9f9; font-family: "Nunito", sans-serif;}
        .header { color: #0066cc; font-size: 20px; font-weight: bold; margin-bottom: 20px; text-align: center; }
        .button {
            display: block;
            width: max-content;
            margin: 20px auto;
            padding: 12px 20px;
            background-color: #0066cc;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .footer { font-size: 12px; color: #666; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Training Program Feedback Request</div>
        
        <p>Respected Sir/Mam <strong><i>%s</i></strong>,</p>
        
        <p>We hope you found your last training program <strong style="background-color:yellow;"><i> %s </i></strong> (completed on <i>%s</i>) beneficial and informative.</p>
        
        <p>Your feedback is extremely valuable to us as it helps:</p>
        <ul>
            <li>Evaluate the training effectiveness</li>
            <li>Improve future programs</li>
            <li>Enhance the learning experience</li>
        </ul>
        
        <p>Please take 5 minutes to share your feedback by clicking below:</p>
        
        <a href="http://192.168.100.9:8080/training/login.php" class="btn btn-warning">Submit Feedback</a>
        
        <p class="footer">
            For any queries, please contact HR Training Team at 
            <a href="mailto:unifiedhrtraining@nspcl.co.in">unifiedhrtraining@nspcl.co.in</a> 
            or call [HR_CONTACT].
        </p>
        
        <p class="footer">
            Best regards,<br>
            <strong>HR Training Team</strong><br>
            NSPCL
        </p>
        
        <hr>
        <p class="footer">
            <strong>Note:</strong> 
            <span style="background-color:yellow;">Login Credentials: Select User role as "Employee platform" → then select Training feedback & Evaluation System → 
            Select tab Program feedback (Choose program, give rating, select PDF, and submit).</span>
        </p>
    </div>
</body>
</html>
',
            htmlspecialchars($name),
            htmlspecialchars($program_name),
            htmlspecialchars($to_date)
        );

       // Attempt to send email
    if ($mail->send()) {
        // ✅ Update `email_flag = 8` **only if email was sent successfully**
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
        file_put_contents('email_log.txt', date('Y-m-d H:i:s') . " - Email sent to $employee_name & flag updated.\n", FILE_APPEND);
        
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
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
}
?>