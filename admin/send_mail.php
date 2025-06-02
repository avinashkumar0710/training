<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/analysis.png">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>   <!---scroll javascript---->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
    body{
        font-weight: 600;
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        }

        table {
        border-collapse: collapse;
        width: 100%;
    }
    th, td {
        border: 1px solid black;
        padding: 8px;
        text-align: left;
    }
    th {
        background-color: beige;
        text-align: center;
    }
    </style>
    <body>
<?php

$serverName = "NSPCL-AD\SQLEXPRESS";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "",
    "PWD" => ""
);           
$conn = sqlsrv_connect($serverName, $connectionInfo);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Include PHPMailer autoloader
require 'vendor/autoload.php';

// Retrieve selected rows' data from POST request
if(isset($_POST['selectedRows'])) {
    // Decode JSON data
    $selectedRows = json_decode($_POST['selectedRows'], true);

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
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;              // TCP port to connect to

        // Recipients
        $mail->setFrom('hruss@nspcl.co.in', 'HR');
        $mail->addAddress('avinashkumar07101992@gmail.com'); // Add recipient email address

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Selected Data';
        // Compose email body
        
        $emailBody = "<style>";
        $emailBody .= "table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }"; // Specify font-family
        $emailBody .= "th, td { border: 1px solid black; padding: 8px; text-align: left; }";
        $emailBody .= "th { background-color: beige; text-align: center; }";
        $emailBody .= "</style>";
        $emailBody .= "<h1 style='font-family: Arial, sans-serif;'>Selected Candidates Information</h1>"; // Specify font-family for the heading
        $emailBody .= "<table border=1 cellspacing=0 cellpadding=5 >";
        $emailBody .= "<thead style='background-color: beige; text-align: center;'>";
        $emailBody .= "<tr><th style='display:none'>id</th><th>Sl. No</th><th>Name</th><th>Program Name</th><th>Year</th><th>Duration</th><th>Faculty</th><th>Hostel Book</th><th>Email</th><th>Status</th><th>Plant</th></tr>";
        $emailBody .= "</thead>";
        $emailBody .= "<tbody>";
        
        
        $serialNo = 1; // Initialize serial number
        foreach($selectedRows as $row) {
            // Update the flag value for the current row
            $id = $row['id'];
            // Assuming you have a database connection established already
            $updateQuery = "UPDATE [Complaint].[dbo].[request] SET flag = '5' WHERE id = '$id'";
            
            // Execute the update query
            // Note: Make sure to use proper error handling and SQL injection prevention techniques
            $result = sqlsrv_query($conn, $updateQuery);
            
            // Check if the query was executed successfully
            if ($result === false) {
                // Handle the error
                echo "Error updating record: " . sqlsrv_errors();
            } else {
                // Row updated successfully
                // Now, construct the email body for this row
                $emailBody .= "<tr>";
                $emailBody .= "<td style='display:none'>" . $row['id'] . "</td>";
                $emailBody .= "<td>" . $serialNo . "</td>"; // Display serial number
                $emailBody .= "<td>" . $row['name'] . "</td>";
                $emailBody .= "<td>" . $row['programName'] . "</td>";
                $emailBody .= "<td>" . $row['year'] . "</td>";
                $emailBody .= "<td>" . $row['duration'] . "</td>";
                $emailBody .= "<td>" . $row['faculty'] . "</td>";
                $emailBody .= "<td>" . ($row['hostelBook'] == 'Yes' ? 'Yes' : 'No') . "</td>"; // Display 'Yes' for 1 and 'No' for 0
                $emailBody .= "<td>" . $row['email'] . "</td>";
               
                $emailBody .= "<td>" . (isset($row['flag']) && $row['flag'] == 4 ? 'Approved From HOD' : 'Pending From BUH') . "</td>";
                $emailBody .= "<td>" . $row['plant'] . "</td>";
                $emailBody .= "</tr>";
                $serialNo++; // Increment serial number for next row
            }
        }
        
        // Close the database connection if needed
        
        $emailBody .= "</tbody>";
        $emailBody .= "</table>";
        $emailBody .= "<div style='margin-top: 20px; text-align: center;'>";
        $emailBody .= "<a href='http://192.168.100.9:8080/training/login.php' style='display: inline-block; text-decoration: none; background-color: #28a745; color: #fff; padding: 8px 16px; border-radius: 4px;'>Approve / Reject</a>";
        //emailBody .= "<a href='http://192.168.100.9:8080/training/login.php?action=reject' style='display: inline-block; text-decoration: none; background-color: #dc3545; color: #fff; padding: 8px 16px; border-radius: 4px;'>Reject</a>";
        $emailBody .= "</div>";

        $mail->Body = $emailBody;

        // Send email
        $mail->send();
        sqlsrv_close($conn);
        //echo 'Email sent successfully.';
        echo "<script>alert('Email sent successfully.');window.location.href = 'buh_nomin.php';</script>";
        
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "No data received.";
}
?>

    </body>
    </html>
