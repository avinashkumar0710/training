<?php
// Database connection
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "Complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}


?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
   
    <title>Edit Attendance Records</title>
   
    <link rel="icon" href="../images/analysis.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    

    <style>
       table{font-size: 0.9pc}
       body{ font-family: "Nunito", sans-serif; font-optical-sizing: auto;}
    </style>
</head>
<?php include 'header.php';?>
<body>
<div class="container-fluid mt-4">
        <!-- Scroll Buttons -->



<!-- Scroll Controls and Heading -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
    <button onclick="scrollToTop()" class="btn btn-sm btn-primary">↑ Top</button>
    <h4 class="text-center flex-grow-1 mb-0">All Attendance Records</h4>  <span style="background-color: gold;">Active (1) / Non-Active(0)</span>
    <button onclick="scrollToBottom()" class="btn btn-sm btn-primary">↓ Bottom</button>
</div>


    <div class="table-responsive" id="scrollBox"  style="max-height: 660px; overflow-y: auto;">



    <form method="post" action="update_multiple_attendance.php" onsubmit="return confirm('Are you sure you want to update selected records?');">

    <table class="table table-bordered border-success" border="2">
            <thead style="position: sticky; top: 0; background-color: beige; z-index: 1;">
                <tr class="bg-success" style="color:#ffffff">
                    <th>Sl</th>
                    <th style="display:none;">Record ID</th>
                    <th>Program ID</th>
                    <th>Name</th>
                    <th>Dept</th>
                    <th>Location</th>                   
                    <th>Program Name</th>
                    <th>Duration</th>                                                                                          
                    <th>Dept Code</th>                   
                    <th>Plant</th>
                    <th>Training Location</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>Mandays</th>
                    <th>Nature of Training</th>
                    <th>Training Subtype</th>
                    <th>Training Mode</th>
                    <th>Attendance</th>
                    <th>Faculty</th>
                    <th>Year</th>
                    <th>Active/Non-Active</th>       
                           
                    
                    <th><input type="checkbox" id="select-all">Select</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            $sno = 1;

$sql = "SELECT record_id, user_id, name, dept, location, program_id, program_name, duration,
day, attendance_status, attendance_fraction, total_attendance, srl_no, attend_date, flag,
dept_code, empno, loc_desc, training_location, from_date, to_date, mandays,
nature_of_training, training_subtype, training_mode, attendance, faculty, year,
act_Nact_flag, training_feedback_flag
FROM [Complaint].[dbo].[attendance_records] WHERE [act_Nact_flag]='1' and training_feedback_flag='7'";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die("Query failed: " . print_r(sqlsrv_errors(), true));
}     

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { 
    $recordId = $row['record_id'];
    $locCode = $row['loc_desc'] ?? '';
    $locations = [
        "NS01" => "Corporate Center",
        "NS02" => "Durgapur",
        "NS03" => "Rourkela",
        "NS04" => "Bhilai"
    ];
    $locText = $locations[$locCode] ?? $locCode;
?>
<tr id="row-<?= $recordId ?>">
<td><?= $sno ?></td>

    <td style="display:none;">
        <span><?= htmlspecialchars($recordId) ?></span>
    </td>
    <td>
        <span><?= htmlspecialchars($row['user_id']) ?></span>
        <input type="text" name="user_id[<?= $recordId ?>]" value="<?= htmlspecialchars($row['user_id']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['name']) ?></span>
        <input type="text" name="name[<?= $recordId ?>]" value="<?= htmlspecialchars($row['name']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['dept']) ?></span>
        <input type="text" name="dept[<?= $recordId ?>]" value="<?= htmlspecialchars($row['dept']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['location']) ?></span>
        <input type="text" name="location[<?= $recordId ?>]" value="<?= htmlspecialchars($row['location']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['program_name']) ?></span>
        <input type="text" name="program_name[<?= $recordId ?>]" value="<?= htmlspecialchars($row['program_name']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['duration']) ?></span>
        <input type="text" name="duration[<?= $recordId ?>]" value="<?= htmlspecialchars($row['duration']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['dept_code']) ?></span>
        <input type="text" name="dept_code[<?= $recordId ?>]" value="<?= htmlspecialchars($row['dept_code']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($locText) ?></span>
        <input type="text" name="loc_desc[<?= $recordId ?>]" value="<?= htmlspecialchars($locCode) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['training_location']) ?></span>
        <input type="text" name="training_location[<?= $recordId ?>]" value="<?= htmlspecialchars($row['training_location']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= ($row['from_date'] instanceof DateTime) ? $row['from_date']->format('Y-m-d') : '' ?></span>
        <input type="date" name="from_date[<?= $recordId ?>]" value="<?= ($row['from_date'] instanceof DateTime) ? $row['from_date']->format('Y-m-d') : '' ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= ($row['to_date'] instanceof DateTime) ? $row['to_date']->format('Y-m-d') : '' ?></span>
        <input type="date" name="to_date[<?= $recordId ?>]" value="<?= ($row['to_date'] instanceof DateTime) ? $row['to_date']->format('Y-m-d') : '' ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['mandays']) ?></span>
        <input type="text" name="mandays[<?= $recordId ?>]" value="<?= htmlspecialchars($row['mandays']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['nature_of_training']) ?></span>
        <input type="text" name="nature_of_training[<?= $recordId ?>]" value="<?= htmlspecialchars($row['nature_of_training']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['training_subtype']) ?></span>
        <input type="text" name="training_subtype[<?= $recordId ?>]" value="<?= htmlspecialchars($row['training_subtype']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['training_mode']) ?></span>
        <input type="text" name="training_mode[<?= $recordId ?>]" value="<?= htmlspecialchars($row['training_mode']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['attendance']) ?></span>
        <input type="text" name="attendance[<?= $recordId ?>]" value="<?= htmlspecialchars($row['attendance']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['faculty']) ?></span>
        <input type="text" name="faculty[<?= $recordId ?>]" value="<?= htmlspecialchars($row['faculty']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
    <td>
        <span><?= htmlspecialchars($row['year']) ?></span>
        <input type="text" name="year[<?= $recordId ?>]" value="<?= htmlspecialchars($row['year']) ?>" style="display:none;" class="form-control form-control-sm">
    </td>
   <td>
        <span><?= htmlspecialchars($row['act_Nact_flag']) ?></span>
        <input type="text" name="act_Nact_flag[<?= $recordId ?>]" value="<?= htmlspecialchars($row['act_Nact_flag']) ?>" style="display:none;" class="form-control form-control-sm">
    </td> 
    <!-- <td>
        <a href="edit_attendance.php?record_id=<?= $recordId ?>" class="btn btn-sm btn-primary">Edit</a>
    </td> -->
    <td>
        <input type="checkbox" class="edit-toggle" data-row-id="<?= $recordId ?>" name="edit_ids[]" value="<?= $recordId ?>">
    </td>
</tr>
<?php $sno++; } ?>

            </tbody>
        </table>
        </div> <button type="submit" class="btn btn-danger mt-2">Update Selected</button>
</form>
    
</div>
<script>
document.querySelectorAll('.edit-toggle').forEach(cb => {
    cb.addEventListener('change', function () {
        const rowId = this.dataset.rowId;
        const row = document.getElementById('row-' + rowId);
        const inputs = row.querySelectorAll('input:not(.edit-toggle), select, textarea');
        const spans = row.querySelectorAll('span');

        if (this.checked) {
            inputs.forEach(el => el.style.display = 'inline');
            spans.forEach(el => el.style.display = 'none');
        } else {
            inputs.forEach(el => el.style.display = 'none');
            spans.forEach(el => el.style.display = 'inline');
        }
    });
});
</script>


<script>
document.getElementById('select-all').addEventListener('change', function () {
    const checkboxes = document.querySelectorAll('input[name="edit_ids[]"]');
    for (const cb of checkboxes) {
        cb.checked = this.checked;
        cb.dispatchEvent(new Event('change')); // Trigger change event to toggle row edit mode
    }
});
</script>


<script>
function scrollToTop() {
    const scrollBox = document.getElementById("scrollBox");
    if (scrollBox) {
        scrollBox.scrollTo({ top: 0, behavior: "smooth" });
    }
}

function scrollToBottom() {
    const scrollBox = document.getElementById("scrollBox");
    if (scrollBox) {
        scrollBox.scrollTo({ top: scrollBox.scrollHeight, behavior: "smooth" });
    }
}
</script>


</body>
<?php include '../footer.php';?>
</html>

<?php 
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn); 
?>