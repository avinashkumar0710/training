<?php 
// start a new session
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }

    $sessionemp= $_SESSION["emp_num"];

    // Add '00' in front if session value has only 6 digits
    if(strlen($sessionemp) == 6) {
        $sessionemp = '00' . $sessionemp;
    }

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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="mainpagebody.css">
    <title>Training | HomePage</title>  
</head>


<header>
    <h1 class="title">Welcome <span style="color: yellow"><i><?php echo $username ?></i></span> to Training Portal</h1>
</header>

<div class="container-fluid" style="display: flex; height: 90vh;"> <!-- Full height container -->
    <!-- Left Section: 30% width -->
    <div class="left-section" style="flex: 0 0 20%; overflow-y: auto;background-color: #333;color: white; height: 90vh; padding: 20px;  box-shadow: inset -20px 0px 20px 0px #8b8b8b;">
    <h5>Link for Online Training Programme</h5>
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
        }

        // Free statement resources for the first query
        sqlsrv_free_stmt($stmt);

        // Now fetch the links and titles
        $sql = "SELECT id, link, title, date, timing, target_group, faculty FROM [Complaint].[dbo].[link_tracking] WHERE flag='U' ORDER BY id DESC";
        $stmt = sqlsrv_query($conn, $sql);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        echo '<div class="d-flex flex-column align-items-center" style="width: 100%;">'; // List-style navbar
        if (sqlsrv_has_rows($stmt)) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $Link = $row['link'];
                $title = $row['title'];
                $date = $row['date'];
                $timing = $row['timing'];
                $target_group = $row['target_group'];
                $faculty = $row['faculty'];
                $id = $row['id'];
                $displayLink = (strlen($Link) > 30) ? substr($Link, 0, 30) . '...' : $Link;
              

                echo '
                <div class="link-item" style="padding: 5px; border: 0px solid #ccc;">
                    <div class="link">
                        <span><strong>Programme Name:</strong> ' . htmlspecialchars($title) . '</span><br>
                        <span><strong>Link:</strong> <a href="' . htmlspecialchars($Link) . '" onclick="recordClick()" target="_blank">' . htmlspecialchars($displayLink) . '</a></span><br>
                        <span><strong>Timing:</strong> ' . htmlspecialchars($timing) . '</span><br>
                    </div>';
                
                // Conditionally display the delete button based on access level
                if ($accessLevel == 1) {
                    echo '
                    <div>
                        <button class="btn btn-danger" onclick="deleteLink(' . $id . ')"><i class="fa fa-trash"></i></button><hr>
                    </div>';
                }
        
                echo '</div>';
            }
        } else {
            echo '<div style="padding: 20px; text-align: center; width: 100%;">No links found.</div>';
        }
        
        echo '</div>';

        // Free statement and connection resources for the second query
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);

        if (isset($title) && isset($Link)) {
            echo "<script>
            var title = '". addslashes($title) ."';
            var user_id = {$employeeNumber};
            var click_time = '{$upload_time}';

            // Record click time
            function recordClick() {
                var xhttp = new XMLHttpRequest();
                xhttp.open('POST', 'record_click.php', true);
                xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhttp.send('user_id=' + user_id + '&title=' + encodeURIComponent(title) + '&link=" . urlencode($Link) . "&click_time=' + click_time);
            }

            // Record close time
            window.addEventListener('beforeunload', function () {
                var close_time = new Date().toISOString();
                navigator.sendBeacon('record_close_time.php', 'user_id=' + user_id + '&title=' + encodeURIComponent(title) + '&close_time=' + close_time);
            });
            </script>";
        }
        ?>

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
    
        <a href="Training_prg/Training_prg_home.php" class="no-underline">
            <div class="rectangle">
                <span>Training Program</span>
            </div>
        </a>

        <?php if ($accessLevel == 1): ?>
        <a href="Training_admin/index.php" class="no-underline">
            <div class="rectangle">
                <span>Training Administration</span>
            </div>
        </a>
        <?php endif; ?>

        <a href="Training_feedback/index.php" class="no-underline">
            <div class="rectangle">
                <span>Training Feedback & Evaluation System</span>
            </div>
        </a>

        <a href="Training_record/index.php" class="no-underline">
            <div class="rectangle">
                <span>Training Record & MIS</span>
            </div>
        </a>

        <a href="Training_resources/index.php" class="no-underline">
            <div class="rectangle">
                <span>Training Resources</span>
            </div>
        </a>
    
    </div>
</div>


<!-- Fixed button -->
<?php  if ($accessLevel == 1): ?> <button class="fixed-btn" onclick="showPopup()">External Link</button> <?php endif; ?>
    <link rel="stylesheet" href="mainpage.css">
<!-- Popup container -->
<div class="popup" id="popup">
    <div class="popup-content">
        <div class="uploadlink">
            <p class="popup-title"><u>Upload Link for Online Training Programme</u></p>
        </div>
        <form id="upload-form" action="upload_link.php" method="post">
            <div class="form-group">
                <label for="title">Programme Name:</label>
                <input type="text" name="title" id="title" required>
            </div>
            <div class="form-group">
                <label for="date">Date:</label>
                <input type="date" name="date" id="date" required>
            </div>
            <div class="form-group">
    <label for="timing">Timing:</label>
    <div style="display: flex; gap: 10px;">
        <select name="hour" id="hour" required>
            <option value="">Hour</option>
            <?php
            for ($i = 1; $i <= 12; $i++) {
                echo "<option value='{$i}'>{$i}</option>";
            }
            ?>
        </select>

        <select name="minute" id="minute" required>
            <option value="">Minute</option>
            <?php
            for ($i = 0; $i <= 59; $i++) {
                $minute = str_pad($i, 2, '0', STR_PAD_LEFT); // Format minutes to always be two digits
                echo "<option value='{$minute}'>{$minute}</option>";
            }
            ?>
        </select>

        <select name="ampm" id="ampm" required>
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
                <label for="link">Enter Link:</label>
                <input type="url" name="link" id="link" required>
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