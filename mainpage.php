<?php 
// start a new session
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }

    $sessionemp= $_SESSION["emp_num"];
    $user_role = $_SESSION['user_role']; 
    //echo "<p>Your role: $user_role</p>";   
    // Add '00' in front if session value has only 6 digits
    if(strlen($sessionemp) == 6) {
        $sessionemp = '00' . $sessionemp;
    }
    //echo "<p>Your role: $sessionemp</p>";  
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

    $name = "SELECT emp_name, access, dept_code, Plant FROM EA_webuser_tstpp WHERE emp_num = ?";    //for user name show in header
            $params = array($_SESSION['emp_num']);
            $stmt = sqlsrv_query($conn, $name, $params);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            if (sqlsrv_has_rows($stmt)) {
                // Get the user name from the result set
                $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                $username = $row['emp_name'];
                $dept_code =$row['dept_code'];
                $Plant =$row['Plant'];
                // echo '$username' .$username;
                // echo '$dept_code' .$dept_code;
                // echo 'empno' .$sessionemp;
                // echo 'Plant' .$Plant;
            } 

            $compare = "SELECT [empno],[name], [design], [location] FROM [Complaint].[dbo].[emp_mas_sap] WHERE empno = $sessionemp";    //for user name show in header
            $params = array($_SESSION['emp_num']);
            $stmt = sqlsrv_query($conn, $compare, $params);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            if (sqlsrv_has_rows($stmt)) {
                // Get the user name from the result set
                $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
              
                $design =$row['design'];
               
                //echo 'design' .$design;
                
            }     
?>
<!DOCTYPE html>


<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" sizes="32x32" href="employee.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="mainpagebody.css">
    <title>Training | HomePage</title>
</head>
<style>
#upload-form {
    height: 600px;
    /* Set a fixed height */
    overflow-y: auto;
    /* Enable vertical scrolling */
    border: 1px solid #ccc;
    /* Optional: add a border for better visibility */
    padding: 15px;
    /* Optional: add padding inside the form */
    background-color: antiquewhite;
}
</style>

<header>
    <h1 class="title">Welcome <span style="color: yellow"><i><?php echo $username ?></i></span> to Training Portal</h1>
</header>

<div class="container-fluid" style="display: flex; height: 90vh;">
    <!-- Full height container -->
    <!-- Left Section: 30% width -->
    <div class="left-section"
        style="flex: 0 0 20%; overflow-y: auto;background-color: #000000d1; color: white; height: 90vh; padding: 20px;  box-shadow: inset -20px 0px 20px 0px #8b8b8b;">
        <div class="fixed-header"
            style="position: fixed; top: 80px; color: white; text-align: center; background-color: darkgrey; border-radius: 0px 0px 15px 15px;">
            <h5>&nbsp;Link for Online Training Programme&nbsp;</h5>
        </div><br>
        <?php 
        // Fetch the employee number and access level before outputting the links
        $employeeNumber = $_SESSION['emp_num'];
        $upload_time = date("Y-m-d H:i:s");
        $sql = "SELECT empno, access FROM [Complaint].[dbo].[Training_HR_User] WHERE empno = ?";
        $params = array($employeeNumber);
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        $accessLevel = 0; // Default access level
        if (sqlsrv_has_rows($stmt)) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $accessLevel = $row['access'];
            //echo 'access' .$accessLevel;
        }

        // Free statement resources for the first query
        sqlsrv_free_stmt($stmt);

        // Now fetch the links and titles
        $sql = "SELECT id, link, title, date, timing, target_group, faculty, open_time, close_time, duration, program_id, Training_Subtype, duration_select , nature_of_training ,
        Internal_External FROM [Complaint].[dbo].[link_tracking] WHERE flag='U' ORDER BY id DESC";
        $stmt = sqlsrv_query($conn, $sql);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        echo '<div  style="width: 100%;">'; // List-style navbar
        if (sqlsrv_has_rows($stmt)) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $Link = $row['link'];
                $title = $row['title'];
                $date = $row['date'];
                $Duration = $row['duration'];
                $timing = $row['timing'];
                $target_group = $row['target_group'];
                $faculty = $row['faculty'];
                $id = $row['id'];
                $displayLink = (strlen($Link) > 30) ? substr($Link, 0, 30) . '...' : $Link;
                $open_time = $row['open_time']->format('Y-m-d H:i');
                $close_time = $row['close_time']->format('Y-m-d H:i');
                $duration = $row['duration'];
                $program_id = $row['program_id'];
                $Training_Subtype = $row['Training_Subtype'];
                $duration_select = $row['duration_select'];
                $nature_of_training = $row['nature_of_training'];
                $Internal_External = $row['Internal_External'];
              

                echo '
                <div class="link-item" style="padding: 1px; border: 0px solid #ccc;">
                    <div class="link">
                        <span><strong>Programme Name:</strong> ' . htmlspecialchars($title) . '</span><br>
                        <span><strong>Duration (in Days) :</strong> ' . $Duration . '</span><br>
                        <span><strong>Open Time:</strong> ' . $open_time . '</span><br>
                        <span><strong>Close Time:</strong> ' . $close_time . '</span><br>
                        <span><strong>Target Group:</strong> ' . htmlspecialchars($target_group) . '</span><br>
                        <span><strong>Faculty:</strong> ' . htmlspecialchars($faculty) . '</span><br>
                        <span><strong>Link:</strong> <a href="' . htmlspecialchars($Link) . '" onclick="recordClick(' . $id . ', \'' . addslashes($title) . '\', \'' . addslashes($Link) . '\')" target="_blank">' . htmlspecialchars($displayLink) . '</a></span><br>
                        
                    </div>';
                
                // Conditionally display the delete button based on access level
                if ($accessLevel == 1) {
                    echo '
                    <div>
                        <button class="btn btn-danger" onclick="deleteLink(' . $id . ')"><i class="fa fa-trash"></i></button>
                    </div>';
                }
               
                echo '</div>';
                echo '<hr>';
            }
        } else {
            echo '<div style="padding: 20px; text-align: center; width: 100%;">No links found.</div>';
        }
        
        echo '</div>';

        // Free statement and connection resources for the second query
        sqlsrv_free_stmt($stmt);
        // Update expired links before fetching them
$updateSql = "UPDATE [Complaint].[dbo].[link_tracking]
SET flag = 'D'
WHERE flag = 'U' AND close_time < GETDATE()";
$updateStmt = sqlsrv_query($conn, $updateSql);

if ($updateStmt === false) {
die(print_r(sqlsrv_errors(), true));
}

sqlsrv_free_stmt($updateStmt); // Optional: free this update query

        sqlsrv_close($conn);
        ?>
       <script>
    // Record click time
    function recordClick(id, title, link) {
        var user_id = <?php echo $employeeNumber; ?>;
        var click_time = '<?php echo $upload_time; ?>';

        console.log('Data being sent:');
        console.log('ID:', id);
        console.log('Title:', title);
        console.log('User ID:', user_id);
        console.log('Link:', link);
        console.log('Click Time:', click_time);

        var xhttp = new XMLHttpRequest();
        xhttp.open('POST', 'record_click.php', true);
        xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhttp.send('id=' + id + '&user_id=' + user_id + '&title=' + encodeURIComponent(title) + '&link=' + encodeURIComponent(link) + '&click_time=' + click_time);
    }

    // Record close time
    window.addEventListener('beforeunload', function () {
        var close_time = new Date().toISOString();
        var id = ''; // Placeholder, you may want to track the specific ID or handle multiple IDs if needed

        navigator.sendBeacon('record_close_time.php', 'id=' + id + '&user_id=' + <?php echo $employeeNumber; ?> + '&close_time=' + close_time);
    });
</script>
       

        <script>
        function deleteLink(id) {
            if (confirm('Are you sure you want to delete this link?')) {
                var xhttp = new XMLHttpRequest();
                xhttp.open("POST", "delete_link.php", true);
                xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhttp.send("id=" + id);

                xhttp.onload = function() {
                    if (xhttp.status === 200) {
                        alert("Link deleted successfully.");
                        location.reload();
                    } else {
                        alert("An error occurred while deleting the link.");
                    }
                };
            }
        }
        </script>
    </div> <!-- End of Left Section -->

    <!-- Right Section: 70% width (optional content) -->
    <div class="right-section" style="flex: 0 0 80%; padding: 20px; background-color: #f4f4f4;">


        <div class="container">

            <!-- <a href="TNI/TNI_home.php" class="no-underline">
        <div class="rectangle">
            <span>Training Need Identification</span>
        </div>
    </a> -->


            <!-- <a href="Training_prg/Training_prg_home.php" class="no-underline">
            <div class="rectangle">
                <span>Training Program</span>
            </div>
        </a> -->
        <?php
//echo 'Debug: Design received - [' . $design . ']';

// Trim and handle comparison
$design = trim($design);

if ($user_role === "33"): ?>
    <a href="Training_prg/buh_approval.php" class="no-underline">
        <div class="rectangle">
            <span>BUH / CEO Approval</span>
        </div>
    </a>
    <?php endif; ?>


    <?php if (in_array($user_role, ["00", "11", "22", "44"])): ?>
    <a href="Training_prg/Training_prg_home.php" class="no-underline">
        <div class="rectangle">
            <span>Training Program</span>
        </div>
    </a>
    <?php endif; ?>

    <?php if ($user_role === "44"): ?>

<a href="Training_admin/attendancebyHR.php" class="no-underline">
    <div class="rectangle">
        <span>Training Administration</span>
    </div>
</a>


<?php endif; ?>

    <?php if (in_array($user_role, ["00", "11", "22", "44"])): ?>
    <a href="Training_feedback/index.php" class="no-underline">
        <div class="rectangle">
            <span>Training Feedback & Evaluation System</span>
        </div>
    </a>
<?php endif; ?>





            <!-- <?php if ( $accessLevel == 1): ?>
            <a href="Training_admin/attendancebyHR.php" class="no-underline">
                <div class="rectangle">
                    <span>Training Administration</span>
                </div>
            </a>
            <?php endif; ?> -->

            <?php 
$design = trim($design);
if (!in_array($design, ['GM & BUH',  'Chief Executive Officer']) && $accessLevel == 1): ?>
   
<?php endif; ?>



            <?php 
            //echo 'Debug: Design received - [' . $design . ']';

// Trim and handle comparison
$design = trim($design);
            if (!in_array($design, ['GM & BUH',  'Chief Executive Officer'])): ?>
    
<?php endif; ?>



<?php if (in_array($user_role, ["00", "11", "22", "33", "44"])): ?>
            <a href="Training_record/training_dashboard.php" class="no-underline">
                <div class="rectangle">
                    <span>Training Record & MIS</span>
                </div>
            </a>

           
            <a href="Training_resources/index.php" class="no-underline">
                <div class="rectangle">
                    <span>Training Resources</span>
                </div>
            </a>
            <?php endif; ?>       


        </div>
    </div>


    <!-- Fixed button -->
    <?php  if ($accessLevel == 1): ?> <button class="fixed-btn" onclick="showPopup()">External Link</button>
    <?php endif; ?>
    <link rel="stylesheet" href="mainpage.css">
    <!-- Popup container -->
    <div class="popup" id="popup">
        <div class="popup-content">
            <div class="uploadlink">
                <p class="popup-title"><u>Upload Link for Online Training Programme</u></p>
            </div>
            <form id="upload-form" action="upload_link.php" method="post">
            <div class="form-group">
                <label for="program_id">Program ID:</label>
                <input type="text" name="program_id" id="program_id" value="<?php 
                    // SQL Server connection
                    $serverName = "192.168.100.240";
                    $connectionOptions = array(
                        "Database" => "Complaint",
                        "UID" => "sa",
                        "PWD" => "Intranet@123"
                    );
                    
                    // Establish connection
                    $conn = sqlsrv_connect($serverName, $connectionOptions);
                    
                    if ($conn === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }
                    
                    // Get the last program_id
                    $sql = "SELECT TOP 1 program_id FROM [Complaint].[dbo].[link_tracking] ORDER BY program_id DESC";
                    $stmt = sqlsrv_query($conn, $sql);
                    
                    if ($stmt === false) {
                        die(print_r(sqlsrv_errors(), true));
                    }
                    
                    if (sqlsrv_has_rows($stmt)) {
                        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                        $last_id = $row['program_id'];
                        echo $last_id + 1;
                    } else {
                        // If no records exist, start with 20253001
                        echo "20253001";
                    }
                    
                    // Free the statement and close connection
                    sqlsrv_free_stmt($stmt);
                    sqlsrv_close($conn);
                ?>" readonly required>
            </div>
                <div class="form-group">
                    <label for="title">Programme Name:</label>
                    <input type="text" name="title" id="title" required>
                </div>
                <!-- <div class="form-group">
                    <label for="date">Upload Date:</label>
                    <input type="date" name="date" id="date" required>
                </div> -->
                <div class="form-group">
                    <label for="timing_to_date">FROM (Date & Time):</label>
                    <div style="display: flex; gap: 3px; flex-wrap: no-wrap;">
                        <input type="date" id="timing_to_date" name="timing_to_date" required>
                        <select name="hour_to" id="hour_to" required>
                            <option value="">H</option>
                            <?php for ($i = 1; $i <= 12; $i++) echo "<option value='{$i}'>{$i}</option>"; ?>
                        </select>
                        <select name="minute_to" id="minute_to" required>
                            <option value="">M</option>
                            <?php for ($i = 0; $i <= 59; $i++) {
                                $minute = str_pad($i, 2, '0', STR_PAD_LEFT);
                                echo "<option value='{$minute}'>{$minute}</option>";
                            } ?>
                        </select>
                        <select name="ampm_to" id="ampm_to" required>
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="timing_from_date">TO (Date & Time):</label>
                    <div style="display: flex; gap: 3px; flex-wrap: no-wrap;">
                        <input type="date" id="timing_from_date" name="timing_from_date" required>
                        <select name="hour_from" id="hour_from" required>
                            <option value="">H</option>
                            <?php for ($i = 1; $i <= 12; $i++) echo "<option value='{$i}'>{$i}</option>"; ?>
                        </select>
                        <select name="minute_from" id="minute_from" required>
                            <option value="">M</option>
                            <?php for ($i = 0; $i <= 59; $i++) {
                    $minute = str_pad($i, 2, '0', STR_PAD_LEFT);
                    echo "<option value='{$minute}'>{$minute}</option>";
                } ?>
                        </select>
                        <select name="ampm_from" id="ampm_from" required>
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="target_group">Target Group:</label>
                    <input type="text" name="target_group" id="target_group" required>
                </div>
                <div class="form-group">
                    <label for="faculty">Faculty:</label>
                    <input type="text" name="faculty" id="faculty" required>
                </div>
                <div class="form-group">
    <label for="nature_of_training">Nature of Training:</label>
    <select name="nature_of_training" id="nature_of_training" required onchange="loadTrainingSubtypes()">
        <option value="">Select Nature of Training</option>
        <?php
        // SQL Server connection
        $serverName = "192.168.100.240";
        $connectionOptions = array(
            "Database" => "Complaint",
            "UID" => "sa",
            "PWD" => "Intranet@123"
        );
        
        $conn = sqlsrv_connect($serverName, $connectionOptions);
        
        if ($conn === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        
        // Fetch distinct nature_of_training values
        $sql = "SELECT DISTINCT nature_of_Training FROM [Complaint].[dbo].[Training_Types]";
        $stmt = sqlsrv_query($conn, $sql);
        
        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }
        
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            echo '<option value="' . htmlspecialchars($row['nature_of_Training']) . '">' . 
                 htmlspecialchars($row['nature_of_Training']) . '</option>';
        }
        
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        ?>
    </select>
</div>

<div class="form-group">
    <label for="training_subtype">Training Subtype:</label>
    <select name="training_subtype" id="training_subtype" required disabled>
        <option value="">Select Nature of Training first</option>
    </select>
</div>

<script>
function loadTrainingSubtypes() {
    var nature = document.getElementById('nature_of_training').value;
    var subtypeDropdown = document.getElementById('training_subtype');
    
    if (nature === '') {
        subtypeDropdown.innerHTML = '<option value="">Select Nature of Training first</option>';
        subtypeDropdown.disabled = true;
        return;
    }
    
    // Fetch subtypes via AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'get_subtypes.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            subtypeDropdown.innerHTML = xhr.responseText;
            subtypeDropdown.disabled = false;
        }
    };
    xhr.send('nature=' + encodeURIComponent(nature));
}
</script>

                <div class="form-group">
                    <label for="training_mode">Training Mode:</label>
                    <select id="training_mode" name="training_mode" required>
                        <option value="">Select Mode</option>
                        <option value="Online">Online</option>
                        <option value="Offline">Offline</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="duration">Duration:</label>
                    <input type="text" name="duration" id="duration" required>
                    <select name="duration_select" id="duration_select" onchange="document.getElementById('duration').value = this.value;" style="width: 100px;">
                    <option value="">Select</option>
                    <option value="0.25">0.25</option>
                    <option value="0.50">0.50</option>
                    <option value="0.75">0.75</option>
                    <option value="1.00">1.00</option>
                </select>
                </div>
                <div class="form-group">
                    <label for="internal_external">Internal / External:</label>
                    <select id="internal_external" name="internal_external" required>
                        <option value="">Select</option>
                        <option value="Internal">Internal</option>
                        <option value="External">External</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="link">Enter Link:</label>
                    <input type="url" name="link" id="link" required>
                </div>
                <div class="form-group">
                    <label for="remarks">Remarks:</label>
                    <input type="text" name="remarks" id="remarks" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="submit-btn">Submit</button>
                    <button type="button" class="close-btn" onclick="closePopup()">Close</button>
                </div>
            </form>

        </div>
    </div>



    <!-- JavaScript to show/hide popup -->
    <script>
    function showPopup() {
        document.getElementById('popup').style.display = 'block';
    }

    function closePopup() {
        document.getElementById('popup').style.display = 'none';
    }
    </script>

    <footer>
        <?php include 'footermainpage.php';?>
    </footer>
    </body>

    </html>