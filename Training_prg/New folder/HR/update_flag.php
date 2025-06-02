<?php

// Create a new PHPMailer instance for sending emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Include PHPMailer autoload file
require '../vendor/autoload.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the selectedValues field exists in the POST data
    if (isset($_POST['selectedValues'])) {
        // Decode the JSON string to retrieve the selected values
        $selectedValues = json_decode($_POST['selectedValues'], true);

        // Connect to the database
        $serverName = "192.168.100.240";
        $connectionInfo = array(
            "Database" => "complaint",
            "UID" => "sa",
            "PWD" => "Intranet@123"
        );

        $conn = sqlsrv_connect($serverName, $connectionInfo);

        // Check if the connection is successful
        if ($conn === false) {
            echo "Error connecting to the database: " . sqlsrv_errors();
            exit(); // Exit the script if there's an error
        }

        // Initialize a flag to track whether all emails were sent successfully
        $allEmailsSent = true;

        // Iterate through the selected values and update the flag and hostel_book for each ID
        foreach ($selectedValues as $id => $data) {
            // Get the dropdown value, email ID, program name, and name for the current ID
            $dropdownValue = $_POST['dropdown_' . $id];
            $email = $_POST['email_' . $id];
            $programName = $_POST['programName_' . $id];
            $name = $_POST['name_' . $id];

            // Prepare the SQL statement to update the flag and hostel_book columns
            $sql = "UPDATE [Complaint].[dbo].[request] SET flag = ?, hostel_book = ? WHERE id = ?";

            // Prepare and execute the SQL statement
            $params = array(7, $dropdownValue, $id); // Set flag to 7
            $stmt = sqlsrv_query($conn, $sql, $params);

            // Check if the query was executed successfully
            if ($stmt === false) {
                echo "Error updating flag and hostel_book for ID " . $id . ": " . sqlsrv_errors();
                $allEmailsSent = false; // Update flag to indicate that not all emails were sent successfully
                continue; // Continue to the next iteration if there's an error
            }

            // Create a new PHPMailer instance for sending emails
            $mail = new PHPMailer(true);

            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = 'mail.nspcl.co.in'; // Your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'unifiedhrtraining@nspcl.co.in'; // Your SMTP username
            $mail->Password = 'hrvision@2024'; // Your SMTP password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Email content
            $mail->setFrom('unifiedhrtraining@nspcl.co.in', 'HR Department');
            $mail->addAddress($email);
            $mail->addCC('unifiedhrtraining@nspcl.co.in'); // CC email address
            $mail->Subject = "Approval of your training request for the Training Programme <strong>$programName</strong>";
            $mail->isHTML(true);

            $hostelBook = ($dropdownValue == 2) ? 'Yes' : 'No';

            // Email body
            $body = "This is Trail Test mail of Upcoming Online training System. Please ignore(Testing Mode),<br><br>"; // Use the employee's name in italic
            $body = "Dear <em>$name</em>,<br><br>"; // Use the employee's name in italic
            $body .= "The request for nomination for the training programme <strong>$programName</strong> scheduled and has been approved by the Compentent Authority for the following employee. This .<br>"; // Include program name in bold
            $body .= "Hostel Accommodation Provided: <strong>$hostelBook</strong><br>"; // Include hostel book status in bold
            $body .= "Please confirm your attendance.<br><br>";
            //$body .= "Best regards,<br>";
            $body .= "HR Team";
            $mail->Body = $body;

            // Send email
            try {
                $mail->send();
                echo "Email sent successfully to $email<br>";
                // Update flag to indicate that not all emails were sent successfully
            } catch (Exception $e) {
                echo "Error sending email to $email: {$mail->ErrorInfo}<br>";
                $allEmailsSent = false; // Update flag to indicate that not all emails were sent successfully
            }
        }

        // Check if all emails were sent successfully
        if ($allEmailsSent) {
            //echo '<script>alert("All emails sent successfully!");</script>'; // Display alert message
            echo "<script>alert('Email sent successfully.');window.location.href = 'mail_training.php';</script>";
        }

        // Close the database connection
        sqlsrv_close($conn);

        // Redirect back to the page where the form was submitted
        //header("Location: {$_SERVER['HTTP_REFERER']}");
        exit();
    } else {
        // Handle the case when selectedValues field is not present in the POST data
        echo "Selected values not found in the form data.";
    }
} else {
    // Handle the case when the form is not submitted
    echo "Form not submitted.";
}
?>
