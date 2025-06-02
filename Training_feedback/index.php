<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp=$_SESSION["emp_num"];
   //echo $_SESSION["emp_num"];

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
?>

<!---------------------------------Start Header Area------------------------------------>
<html>

<head>
    <title>Training | Home</title>
    <link rel="icon" href="../images/analysis.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>


</head>
<style>
.form-section {
    margin-bottom: 20px;
}

.form-section table {
    width: 100%;
    border-collapse: collapse;
}

.form-section table,
.form-section th,
.form-section td {
    border: 1px solid #000;
    padding: 8px;
    text-align: center;
}

.form-section th {
    background-color: #f2f2f2;
}

p {
    background-color: yellow;
}
</style>
<?php include 'header.php';?>

<div class="container">
    <center>
        <p>Please provide feedback on following parameters on a scale of 1 - 10, where <i><b>1 - Not Satisfied</b></i>
            and in the increasing order of satisfaction <i><b>10 - Highly satisfied</b></i></p>
    </center>

    <form id="feedback-form" action="save_feedback.php" method="post" enctype="multipart/form-data">
        <br>
        <!-- Section 1 -->
        <div class="form-section">
            <center>
                <h4>Program Feedback</h4>
            </center>
            <table class="table table-bordered">
                <tr>
                    <th>Program Title</th>
                    <th>Program Duration</th>
                    <th>Faculty</th>
                </tr>
                <tr>
                    <td>
                        <select name="program_title" id="program_title" class="form-control" required
                            onchange="fetchProgramDetails(this.value)">
                            <option value="">Select Program</option>
                            <?php
                            // Fetch program titles from database
                            session_start();
                            if (!isset($_SESSION["emp_num"])) {   
                                header("location:login.php");
                            }
                            $sessionemp = $_SESSION["emp_num"];
                            
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

                            // $query = "SELECT r.[srl_no], r.[empno], r.[Program_name], r.[duration], r.Faculty, r.[id]
                            // FROM [Complaint].[dbo].[request] r
                            // LEFT JOIN [Complaint].[dbo].[program_feedback] pf ON r.[srl_no] = pf.[srl_no]
                            // WHERE pf.[srl_no] IS NULL and flag='7' AND empno='$sessionemp'";

                            $query = " Select program_id, Program_name, faculty,duration FROM [Complaint].[dbo].[attendance_records] where empno='$sessionemp' AND training_feedback_flag = '7'";


                            $stmt = sqlsrv_query($conn, $query);

                            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                echo "<option value='{$row['program_id']}'>{$row['Program_name']}</option>";
                            }

                            sqlsrv_free_stmt($stmt);
                            sqlsrv_close($conn);
                            ?>
                        </select>
                    </td>
                    <td><input type="text" id="duration" name="program_duration" class="form-control" readonly required>
                    </td>
                    <td><input type="text" id="faculty" name="faculty" class="form-control" readonly required></td>
                    <!-- <input type="text" id="id" name="id"> -->
                    <input type="hidden" id="program_id" name="srl_no"> <!-- set this with selected program_id -->
                    <input type="hidden" id="Program_name" name="Program_name">

                </tr>
            </table>
        </div>

        <!-- Section 2 -->
        <div class="form-section">
            <center>
                <h4>Overall Program</h4>
            </center>
            <table class="table table-bordered">
                <tr>
                    <th>Dimensions</th>
                    <th>Rating (1 to 10)</th>
                </tr>
                <tr>
                    <td>Overall Program Objectives</td>
                    <td><input type="number" name="overall_objectives[]" class="form-control rating-input" min="1"
                            max="10" required></td>
                </tr>
                <!-- Add more rows as needed up to 10 points -->
            </table>
        </div>

        <!-- Section 3 -->
        <div class="form-section">
            <center>
                <h4>Program Execution</h4>
            </center>
            <table class="table table-bordered">
                <tr>
                    <th>Dimensions</th>
                    <th>Rating (1 to 10)</th>
                </tr>
                <tr>
                    <td>Content & Depth of Coverage</td>
                    <td><input type="number" name="content_depth[]" class="form-control rating-input" min="1" max="10"
                            required></td>
                </tr>
                <tr>
                    <td>Program Duration</td>
                    <td><input type="number" name="program_duration_feedback[]" class="form-control rating-input"
                            min="1" max="10" required></td>
                </tr>
                <tr>
                    <td>Relevance to Your Role</td>
                    <td><input type="number" name="relevance[]" class="form-control rating-input" min="1" max="10"
                            required></td>
                </tr>
                <tr>
                    <td>Program Coordinated </td>
                    <td><input type="number" name="program_coordinated[]" class="form-control rating-input" min="1"
                            max="10" required></td>
                </tr>
            </table>
        </div>

        <!-- Section 4 -->
        <div class="form-section">
            <center>
                <h4>Faculty</h4>
            </center>
            <table class="table table-bordered">
                <tr>
                    <th>Dimensions</th>
                    <th>Rating (1 to 10)</th>
                </tr>
                <tr>
                    <td>Faculty</td>
                    <td><input type="number" name="faculty_feedback[]" class="form-control rating-input" min="1"
                            max="10" required></td>
                </tr>
                <!-- Add more rows as needed up to 10 points -->
            </table>
        </div>

        <div class="form-section">
            <center>
                <h4>Hospitality Arrangements</h4>
            </center>
            <table class="table table-bordered">
                <tr>
                    <th>Dimensions</th>
                    <th>Rating (1 to 10)</th>
                </tr>
                <tr>
                    <td>Hospitality Arrangements</td>
                    <td><input type="number" name="hospitality_arrangements[]" class="form-control rating-input" min="1"
                            max="10" required></td>
                </tr>
                <tr>
                    <td>Overall Administrative Arrangements</td>
                    <td><input type="number" name="administrative_arrangements[]" class="form-control rating-input"
                            min="1" max="10" required></td>
                </tr>
                <tr>
                    <td>Stay Arrangements</td>
                    <td><input type="number" name="stay_arrangements[]" class="form-control rating-input" min="1"
                            max="10" required></td>
                </tr>
            </table>
        </div>

        <div class="form-section">
            <center>
                <h4>Suggestions</h4>
            </center>
            <table class="table table-bordered">
                <tr>
                    <th>Dimensions</th>
                    <th>Text Area Suggestions</th>
                </tr>

                <tr>
                    <td>Employee Suggestion Area</td>
                    <td><textarea name="suggestions[]" class="form-control" rows="3" required></textarea></td>
                </tr>

            </table>
        </div>

        <div class="form-section mt-4">
            <center>
                <h4>Upload PDF File</h4>
            </center>
            <div class="mb-3">
                <label for="pdfFile" class="form-label">Upload PDF:</label>
                <input class="form-control" type="file" name="pdfFile" accept="application/pdf" required>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="form-section">
            <button type="submit" class="btn btn-primary">Submit</button>
            <a href="view_feedback.php" class="btn btn-secondary">View Feedback</a>
        </div>
    </form>
</div><br><br>
<div></div>

<script>
document.querySelectorAll('.rating-input').forEach(function(input) {
    input.addEventListener('input', function() {
        if (this.value > 10 || this.value < 1) {
            alert('Please enter a value between 1 and 10.');
            this.value = ''; // Clear the input field
        }
    });

    input.addEventListener('change', function() {
        if (this.value > 10 || this.value < 1) {
            alert('Please enter a value between 1 and 10.');
            this.value = ''; // Clear the input field
        }
    });
});
</script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
function fetchProgramDetails(programId) {
    if (programId === '') {
        document.getElementById('program_id').value = '';
        document.getElementById('duration').value = '';
        document.getElementById('faculty').value = '';
        return;
    }

    // Set selected program ID in hidden input
    document.getElementById('program_id').value = programId;

    $.ajax({
        url: 'fetch_programs.php',
        type: 'POST',
        data: {
            action: 'fetch_program_details',
            program_id: programId
        },
        success: function(response) {
            var data = JSON.parse(response);
            document.getElementById('Program_name').value = data.Program_name;
            document.getElementById('duration').value = data.program_duration;
            document.getElementById('faculty').value = data.faculty;
        }
    });
}
</script>


</body>

</html>

<?php include 'footer.php';?>