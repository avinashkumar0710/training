<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Fetch names from the database
$serverName = "192.168.100.240"; // serverName\instanceName
$connectionInfo = array("Database"=>"complaint", "UID"=>"sa", "PWD"=>"Intranet@123");

$ddt = date('d');
$mmt = date('m');
//echo "Day: $ddt, Month: $mmt";

try {
    // Establish a connection using sqlsrv_connect
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

	$query = "SELECT * FROM emp_mas_sap WHERE DAY(doj) = '$ddt' AND MONTH(doj) = '$mmt' AND email != ''";
    //$params = array($dob);

    $stmt = sqlsrv_query($conn, $query);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $users = array();

    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $users[] = $row;
    }

    sqlsrv_close($conn);

} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Send personalized birthday emails
$mail = new PHPMailer(true);

try {
    $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Set to SMTP::DEBUG_SERVER for maximum debugging
    $mail->isSMTP();
    $mail->Host = 'mail.nspcl.co.in';
    $mail->SMTPAuth = true;
    
     $mail->Username = 'hruss@nspcl.co.in';
     $mail->Password = 'Revew$454';
	//$mail->Username = 'hoderp@nspcl.co.in';
    //$mail->Password = 'NTpc@2023';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Customize sender's email and name
    $mail->setFrom('hruss@nspcl.co.in', 'Team NSPCL');

    // Iterate through the fetched names and send personalized emails
    foreach ($users as $user) {
        $recipientName = $user['name'];
        $emailadd = $user['email'];
        $title = $user['title'];
        if (trim($emailadd) !== "") {
            echo $emailadd;
            $mail->addAddress($emailadd);
            $mail->addCC('hruss@nspcl.co.in');

            $mail->isHTML(true);
            $mail->Subject = 'Happy Work Anniversary';

            //$imagePath = 'birthday/Anniversary.gif';
            //$imageData = base64_encode(file_get_contents($imagePath));
            //$imageMimeType = get_mime_type($imagePath);
			
			$gifPath = 'birthday/Anniversary.gif'; 
			$mail->addEmbeddedImage($gifPath, 'Anniversary.gif', 'Anniversary.gif', 'base64', 'image/gif');


            // Add the image directly to the HTML body along with customized message
            $mail->Body = "<font size=4 color=blue><b>Dear $title $recipientName,<br>
            Happy Work Anniversary! Your contributions have played a significant role in our success.<br> 
			Here\'s to another year of growth and accomplishments.<br>
            
			<img src='cid:Anniversary.gif' alt='Work Anniversary GIF' style='display:block; max-width:100%;'<br><br>
			 
            Regards,<br>
            Team NSPCL</b> </font>";

            $mail->AltBody = "Dear $recipientName,

            Happy work anniversary! Your contributions have played a significant role in our success.<br> 
			Here's to another year of growth and accomplishments.

            Regards,
            Team NSPCL";

            $mail->send();

            // Clear addresses for the next iteration
            $mail->clearAddresses();
        }
    }
    $mail->smtpClose();
    echo 'Work Anniversary emails have been sent';
} catch (Exception $e) {
    // Log the error instead of displaying it
    error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    // Display a user-friendly message
    echo "Message could not be sent. Please try again later.";
}

?>
