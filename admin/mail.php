<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Include PHPMailer autoloader
require 'vendor/autoload.php';

// Create a new instance of PHPMailer
$mail = new PHPMailer(true); // Set to true for exceptions

try {
    // Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host = 'mail.nspcl.co.in';
    $mail->SMTPAuth = true;
    $mail->Username = 'hruss@nspcl.co.in';
    $mail->Password = 'Revew$454';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 587;              // TCP port to connect to

    // Recipients
    $mail->setFrom('hruss@nspcl.co.in', 'HR');
    $mail->addAddress('avinashkumar07101992@gmail.com'); // Add recipient email address

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body    = '<h1>This is a test email.</h1><p>Hello, this is a test email sent using PHPMailer.</p>';

    // Send email
    $mail->send();
    echo 'Email sent successfully.';
} catch (Exception $e) {
    echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
?>
