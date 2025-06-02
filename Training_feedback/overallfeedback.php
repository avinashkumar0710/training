<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["emp_num"])) {
    header("Location: login.php");
    exit;
}

$serverName = "192.168.100.240";
$connectionOptions = [
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Only fetch records for the logged-in user
//$empNum = $_SESSION["emp_num"];

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Feedback Status</title>
    <link rel="icon" href="../images/analysis.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add this in your <head> section -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        table { border-collapse: collapse; width: 90%; margin: auto; }
        th, td { border: 1px solid #aaa; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        .green { color: green; font-weight: bold; }
        .orange { color: orange; font-weight: bold; }
        .red { color: red; font-weight: bold; }
        .blue { color: blue; font-weight: bold; }
        .btn-sm .spinner-border {
            vertical-align: text-top;
            margin-right: 5px;
        }
    </style>
</head>
<?php include 'header.php';?>
<body>

 <!-- Progress Modal -->
 <div class="modal fade" id="progressModal" tabindex="-1" aria-labelledby="progressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="progressModalLabel">Sending Emails</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="progress mb-3">
                        <div id="emailProgress" class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <div id="progressDetails" class="small">
                        <div>Total: <span id="totalCount">0</span></div>
                        <div>Sent: <span id="sentCount" class="text-success">0</span></div>
                        <div>Failed: <span id="failedCount" class="text-danger">0</span></div>
                        <div>Current: <span id="currentItem"></span></div>
                    </div>
                    <div id="errorMessages" class="mt-3 small text-danger"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <br>
    <h2 style="text-align:center;">Training Feedback Status</h2>
    <div style="max-height: 700px; overflow-y: auto;">
    <table class="table table-bordered border-success">
        <thead style="position: sticky; top: 0; background-color: beige; z-index: 1;">
            <tr class="bg-primary" style="color:#ffffff">
                <th>SL No</th>               
                <th>Employee Name</th>
                <th>Emp No</th>
                <th>Dept</th>
                <th>Training Location</th>
                <th>Program ID</th>
                <th>Program Name</th>
                <th>Nature of Training</th>
                <th>Training Subtype</th>
                <th>Training Mode</th>
                <th>Faculty</th>
                <th>Mandays</th>
                <th>Duration</th>
                <th>Attendance</th>
                <th>From Date</th>
                <th>To Date</th>
                <th>Status</th>
                <!-- <th>Action</th> -->
                <th>
                    <button id="sendAllBtn" class="btn btn-warning btn-sm">
                        <i class="bi bi-envelope-fill"></i> Send All
                    </button>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Input validation for the query
            $validStatus = 7;
            if(!is_numeric($validStatus)) {
                die("Invalid query parameter");
            }

            $sql = "SELECT *
            FROM [Complaint].[dbo].[attendance_records]  
            WHERE training_feedback_flag=?";

            $params = array($validStatus);
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                error_log("SQL Error: " . print_r(sqlsrv_errors(), true));
                die("Error fetching training records");
            }

            $serial = 1;
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Convert DateTime objects to strings first
    foreach ($row as $key => $value) {
        if ($value instanceof DateTime) {
            $row[$key] = $value->format('Y-m-d');
        }
    }
    
    // Now sanitize all values
    $row = array_map(function($value) {
        return is_string($value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
    }, $row);

    $status = '';
    $class = '';

    if ($row['training_feedback_flag'] == 8) {
        $status = "Feedback Given";
        $class = "green";
    } elseif ($row['attendance'] == 'A' && $row['training_feedback_flag'] == 7) {
        $status = "Feedback Not Given";
        $class = "blue";
    } elseif ($row['attendance'] == 'NA') {
        $status = "Not Attended";
        $class = "red";
    }

    echo "<tr>";
    echo "<td>{$serial}</td>";
    echo "<td>{$row['name']}</td>";
    echo "<td>{$row['empno']}</td>";
    echo "<td>{$row['dept']}</td>";
    echo "<td>{$row['location']}</td>";
    echo "<td>{$row['program_id']}</td>";
    echo "<td>{$row['program_name']}</td>";
    echo "<td>{$row['nature_of_training']}</td>";
    echo "<td>{$row['training_subtype']}</td>";
    $modeClass = ($row['training_mode'] === 'External') ? 'blue' : 'green';
    echo "<td class='$modeClass'>{$row['training_mode']}</td>";
    echo "<td>{$row['faculty']}</td>";
    echo "<td>{$row['mandays']}</td>";
    echo "<td>{$row['duration']}</td>";
    echo "<td>{$row['attendance']}</td>";
    echo "<td>{$row['from_date']}</td>";  // Now already formatted as string
    echo "<td>{$row['to_date']}</td>";    // Now already formatted as string
    echo "<td class='$class'>$status</td>";
    echo "<td>";
    if ($row['training_feedback_flag'] == 8) {
        echo "<button class='btn btn-success btn-sm' disabled>Feedback Given</button>";
    } elseif ($row['attendance'] == 'A' && $row['training_feedback_flag'] == 7) {
        $programNameEncoded = urlencode($row['program_name']);
        $toDateFormatted = urlencode($row['to_date']);
        echo "<a href='#' onclick=\"confirmSendEmail('feedback_not_given.php?empno={$row['empno']}&program_name={$programNameEncoded}&to_date={$toDateFormatted}')\" class='btn btn-primary btn-sm'>
                  <i class='bi bi-envelope text-warning'></i>&nbsp;Send Mail
            </a>";
    } else {
        $programNameEncoded = urlencode($row['program_name']);
        $toDateFormatted = urlencode($row['to_date']);
        echo "<a href='#' onclick=\"confirmSendEmail('feedback_to_hod.php?empno={$row['empno']}&program_name={$programNameEncoded}&to_date={$toDateFormatted}')\" class='btn btn-danger btn-sm'>
                  <i class='bi bi-envelope'></i>&nbsp;Send Mail to Hod
            </a>";
    }
    echo "</td>";
    echo "</tr>";
    $serial++;
}
            ?>
        </tbody>
    </table>
    </div>
    <script>
    // Global variables for tracking progress
    // Global variables for tracking progress
let emailQueue = [];
let processedCount = 0;
let successCount = 0;
let failureCount = 0;
const RETRY_LIMIT = 2;
const RATE_LIMIT_DELAY = 1000; // 1 second between batches

// Initialize modal (only once)
const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));

// Confirm before sending individual email
function confirmSendEmail(url) {
    if(confirm('Are you sure you want to send this email?')) {
        // Show loading state
        const btn = event.target.closest('a');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
        btn.classList.add('disabled');
        
        // Send the email
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(text || 'Request failed');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data && data.success) {
                showAlert('success', 'Email sent successfully!');
                setTimeout(() => location.reload(), 1500);
            } else {
                throw new Error(data?.message || 'Failed to send email');
            }
        })
        .catch(error => {
            showAlert('danger', 'Error: ' + (error.message || 'Failed to send email'));
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.classList.remove('disabled');
        });
    }
}

// Enhanced Send All functionality
document.getElementById('sendAllBtn').addEventListener('click', function() {
    if(confirm('Are you sure you want to send ALL pending emails?\n\nThis will send both feedback reminders and HOD notifications.')) {
        // Collect all pending emails with validation
        emailQueue = [];
        
        document.querySelectorAll('tbody tr').forEach(row => {
            const statusCell = row.querySelector('td:nth-child(17)'); // Status column
            const attendanceCell = row.querySelector('td:nth-child(14)'); // Attendance column
            const actionBtn = row.querySelector('a.btn-primary, a.btn-danger'); // Action buttons
            const empnoCell = row.querySelector('td:nth-child(3)');
            const programNameCell = row.querySelector('td:nth-child(7)');
            const toDateCell = row.querySelector('td:nth-child(16)');
            
            if(statusCell && attendanceCell && actionBtn && empnoCell && programNameCell && toDateCell) {
                const status = statusCell.textContent.trim();
                const attendance = attendanceCell.textContent.trim();
                const empno = empnoCell.textContent.trim();
                const programName = programNameCell.textContent.trim();
                const toDate = toDateCell.textContent.trim();
                
                // Only process rows with actionable buttons
                if(status.includes('Not Given') || status.includes('Not Attended')) {
                    emailQueue.push({
                        empno: empno,
                        program_name: programName,
                        to_date: toDate,
                        email_type: (attendance === 'A') ? 'feedback_not_given' : 'feedback_to_hod',
                        retries: 0
                    });
                }
            }
        });
        
        if(emailQueue.length === 0) {
            alert('No pending emails to send!');
            return;
        }
        
        // Initialize progress tracking
        processedCount = 0;
        successCount = 0;
        failureCount = 0;
        
        // Update progress UI
        document.getElementById('totalCount').textContent = emailQueue.length;
        document.getElementById('sentCount').textContent = '0';
        document.getElementById('failedCount').textContent = '0';
        document.getElementById('errorMessages').innerHTML = '';
        
        // Show progress modal
        progressModal.show();
        
        // Disable send all button
        const sendAllBtn = document.getElementById('sendAllBtn');
        const originalText = sendAllBtn.innerHTML;
        sendAllBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
        sendAllBtn.disabled = true;
        
        // Start processing
        processBatch();
    }
});

// Process emails in batches with rate limiting
function processBatch() {
    if(processedCount >= emailQueue.length) {
        // Processing complete
        progressModal.hide();
        const sendAllBtn = document.getElementById('sendAllBtn');
        sendAllBtn.innerHTML = '<i class="bi bi-envelope-fill"></i> Send All';
        sendAllBtn.disabled = false;
        
        showAlert('success', `Bulk email processing completed:<br>
               Successfully sent: ${successCount}<br>
               Failed: ${failureCount}`);
        setTimeout(() => location.reload(), 2000);
        return;
    }
    
    const currentItem = emailQueue[processedCount];
    document.getElementById('currentItem').textContent = 
        `Sending ${currentItem.email_type === 'feedback_not_given' ? 'reminder' : 'HOD notification'} to ${currentItem.empno}`;
    
    sendEmailWithRetry(currentItem)
        .then(result => {
            if(result.success) {
                successCount++;
            } else {
                failureCount++;
                // Log error
                const errorMsg = document.createElement('div');
                errorMsg.textContent = `Failed to send to ${currentItem.empno}: ${result.message || 'Unknown error'}`;
                document.getElementById('errorMessages').appendChild(errorMsg);
            }
        })
        .catch(error => {
            failureCount++;
            // Log error
            const errorMsg = document.createElement('div');
            errorMsg.textContent = `Error sending to ${currentItem.empno}: ${error.message}`;
            document.getElementById('errorMessages').appendChild(errorMsg);
        })
        .finally(() => {
            processedCount++;
            updateProgress();
            // Rate limiting delay before next item
            setTimeout(processBatch, RATE_LIMIT_DELAY);
        });
}

// Send email with retry logic
function sendEmailWithRetry(item) {
    return new Promise((resolve, reject) => {
        const url = item.email_type === 'feedback_not_given' ? 
            'feedback_not_given.php' : 'feedback_to_hod.php';
        
        const params = new URLSearchParams({
            empno: item.empno,
            program_name: item.program_name,
            to_date: item.to_date
        });
        
        const attemptSend = (retryCount) => {
            fetch(`${url}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if(!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                if(data && typeof data.success !== 'undefined') {
                    if(data.success) {
                        resolve(data);
                    } else if(retryCount < RETRY_LIMIT) {
                        // Retry after delay
                        setTimeout(() => attemptSend(retryCount + 1), 1000 * (retryCount + 1));
                    } else {
                        const error = new Error(data.message || 'Request failed');
                        error.response = data;
                        throw error;
                    }
                } else {
                    throw new Error('Invalid response format');
                }
            })
            .catch(error => {
                if(retryCount < RETRY_LIMIT) {
                    // Retry after delay
                    setTimeout(() => attemptSend(retryCount + 1), 1000 * (retryCount + 1));
                } else {
                    reject(error);
                }
            });
        };
        
        // Start first attempt
        attemptSend(0);
    });
}

// Update progress UI
function updateProgress() {
    const progressPercent = Math.round((processedCount / emailQueue.length) * 100);
    const progressBar = document.getElementById('emailProgress');
    progressBar.style.width = `${progressPercent}%`;
    progressBar.textContent = `${progressPercent}%`;
    
    document.getElementById('sentCount').textContent = successCount;
    document.getElementById('failedCount').textContent = failureCount;
}

// Helper function to show alerts
function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.classList.remove('show');
        setTimeout(() => alertDiv.remove(), 150);
    }, 5000);
}
    </script>
</body>
<?php include 'footer.php';?>
</html>

<?php
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
