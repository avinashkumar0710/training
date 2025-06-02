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

$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);           
$conn = sqlsrv_connect($serverName, $connectionInfo);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Include PHPMailer autoloader
require '../vendor/autoload.php';

if (isset($_POST['selectedRows'])) {
    // Decode JSON data
    $selectedRows = json_decode($_POST['selectedRows'], true);

    // Create a new instance of PHPMailer
    $mail = new PHPMailer(true);

    try {
        // SMTP Server settings
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        $mail->Host = 'mail.nspcl.co.in';
        $mail->SMTPAuth = true;
        $mail->Username = 'unifiedhrtraining@nspcl.co.in';
        $mail->Password = 'hrvision@2024';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender & Test Recipient (for now)
        $mail->setFrom('unifiedhrtraining@nspcl.co.in', 'HR Training');
        $mail->addAddress('hruss1@nspcl.co.in'); // **Only sending to this email for testing**

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Training Program : Pending BUH Approval';

        // Compose Email Body
        $emailBody = "<style>
            table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; } 
            th, td { border: 1px solid black; padding: 8px; text-align: left; }
            th { background-color: beige; text-align: center; }
        </style>";
        $emailBody .= "<p>Respected Sir/Mam,<br>Please Approve the list below:</p>";
        $emailBody .= "<h4 style='font-family: Arial, sans-serif;'>Selected Candidates Information</h4>";
        $emailBody .= "<table border='1' cellspacing='0' cellpadding='5'>";
        $emailBody .= "<thead style='background-color: beige; text-align: center;'>";
        $emailBody .= "<tr><th>Sl. No</th><th>Name</th><th>Program Name</th><th>Year</th><th>Duration</th><th>Program Date</th><th>Hostel Book</th><th>Dept</th><th>Status</th></tr>";
        $emailBody .= "</thead><tbody>";
        
        $serialNo = 1; // Serial number counter
        
        foreach ($selectedRows as $row) {
            $id = $row['id'];
        
            // **Corrected SQL Update Query**
            $updateQuery = "UPDATE [Complaint].[dbo].[request] SET flag = '5', aprroved_time = GETDATE() WHERE id = ?";
            $updateParams = [$id];
            $result = sqlsrv_query($conn, $updateQuery, $updateParams);
        
            if ($result === false) {
                echo "Error updating record: " . print_r(sqlsrv_errors(), true);
            } else {
                $emailBody .= "<tr>";
                $emailBody .= "<td>" . $serialNo . "</td>";
                $emailBody .= "<td>" . htmlspecialchars($row['name']) . "</td>";
                $emailBody .= "<td>" . htmlspecialchars($row['programName']) . "</td>";
                $emailBody .= "<td>" . htmlspecialchars($row['yearr']) . "</td>"; // Fixed key
                $emailBody .= "<td>" . htmlspecialchars($row['duration']) . "</td>";
                $emailBody .= "<td>" . htmlspecialchars($row['faculty']) . "</td>"; // Fixed key (Program Date)
                $emailBody .= "<td>" . ($row['hostelBook'] == 1 ? 'Yes' : 'No') . "</td>";
                $emailBody .= "<td>" . htmlspecialchars($row['dept']) . "</td>";
        
                // **Fix: Show Correct Status After Update**
                $emailBody .= "<td>" . ($row['flag'] == 4 ? 'Approved From HOD' : 'Pending From BUH') . "</td>";
                
                $emailBody .= "</tr>";
                $serialNo++;
            }
        }

        $emailBody .= "</tbody></table>";
        $emailBody .= "<div style='margin-top: 20px; text-align: center;'>";
        $emailBody .= "<a href='http://192.168.100.9:8080/training/login.php' style='display: inline-block; text-decoration: none; background-color: #28a745; color: #fff; padding: 8px 16px; border-radius: 4px;'>Approve / Reject</a>";
        $emailBody .= "</div>";

        $mail->Body = $emailBody;

        // **Email Query - Checking Plant & Role**
        $status = 'A';
        $plant = $selectedRows[0]['plant'] ?? ''; // Get first plant if exists

        $design = 'GM & BUH';
        $design1 = 'Chief Executive Officer       ';

        $deptCondition = ($plant == 'Corporate Center    ') ? $design1 : $design;

        // Debugging output
        echo "Plant: " . $plant . "<br>";
        echo "Status: " . $status . "<br>";
        echo "Dept Condition: " . $deptCondition . "<br>";

        $emailQuery = "SELECT [name], [location], [email] FROM [Complaint].[dbo].[emp_mas_sap] 
                       WHERE loc_desc = ? AND status = ? AND design LIKE ?";
        $params = [$plant, $status, "%$deptCondition%"];

        print_r($params);
        echo "<br>";

        $emailResult = sqlsrv_query($conn, $emailQuery, $params);

        if ($emailResult !== false) {
            if (sqlsrv_has_rows($emailResult)) {
                while ($emailRow = sqlsrv_fetch_array($emailResult, SQLSRV_FETCH_ASSOC)) {
                    if (!empty($emailRow['email'])) {
                        echo "Fetched email: " . $emailRow['email'] . "<br>";
                        // During testing, **DO NOT** send to these addresses
                        //$mail->addAddress($emailRow['email']);
                    }
                }
            } else {
                echo "No email addresses found for the selected criteria.";
            }
        } else {
            echo "Error fetching email addresses: " . print_r(sqlsrv_errors(), true);
        }

        // CC recipients
        $mail->addCC('hruss@nspcl.co.in');
        $mail->addCC('unifiedhrtraining@nspcl.co.in');

        // Send Email (For now, just test sending to one recipient)
         $mail->send();
        sqlsrv_close($conn);
        echo 'Email sent successfully.';
        echo "<script>alert('Email sent successfully.');window.location.href = 'buh_nomin.php';</script>";
        echo "<h3>Email Content:</h3>";
        echo $emailBody;
        exit();
    } catch (Exception $e) {
        echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo "No data received.";
}
?>


    </body>
    </html>
