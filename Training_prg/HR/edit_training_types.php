<?php 
// start a new session
// Allow any origin to access this resource
ob_start(); // Start output buffering
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp= $_SESSION["emp_num"];
    //echo 'empno' .$sessionemp;

    // Add '00' in front if session value has only 6 digits
    if(strlen($sessionemp) == 6) {
        $sessionemp = '00' . $sessionemp;
    }
    //echo 'empno' .$sessionemp;

  // Database connection
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

// Check if the connection failed
if ($conn === false) {
    die("Connection Error: " . print_r(sqlsrv_errors(), true));
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Training Types</title>
    <link rel="icon" type="image/png" sizes="32x32" href="employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    
        <!-- Font Awesome for icon -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<style>
    body {
        font-weight: 600;
        font-style: normal;
        font-family: "Nunito Sans", sans-serif;
        margin: 0;
        /* Remove default body margin */
        padding: 0;
        /* Remove default body padding */
        background-color: #e8eef3;
    }

    input[type="text"] {
            width: 340px;
            padding: 4px;
        }

        #addRowBtn {
            background-color: yellow;
            border: none;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        #addRowBtn i {
            margin-right: 5px;
        }

        table {
            border-collapse: collapse;
        }

        th, td {
            padding: 6px 10px;
        }
    </style>
<body>
<?php include '../header_HR.php';?>
<h6><i class="fa fa-home" aria-hidden="true"></i>&nbsp;<i><u>HR->Edit / Add Training Types</u></i></h6>
<div class='container'>
<h2>Training Types</h2>
&nbsp;&nbsp;&nbsp;&nbsp;<button id="addRowBtn" onclick="addNewRow()"><i class="fas fa-plus"></i>Add New Row</button></div><br>
<div class="container" style=' height: 600px; overflow: auto;'>
<table class="table table-bordered border-success" border="1" id="trainingTable">
    <thead style="position: sticky; top: 0; background-color: beige; z-index: 1;">
        <tr class="bg-primary" style="color:#ffffff">
            
        <th>ID</th>
            
            <th>Nature of Training</th>
            <th>Training Subtype</th>
            <th>Update</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody id="tableBody">
        <!-- Data will load here -->
    </tbody>
</table>
</div>
<script>
function fetchTrainingTypes() {
    $.ajax({
        url: 'fetch_training_types.php',
        method: 'GET',
        success: function(data) {
            $('#tableBody').html(data);
        }
    });
}

function addNewRow() {
    const newRow = `
        <tr>
            <td><input type="text" id="new_id" /></td>
            <td><input type="text" id="new_nature" /></td>
            <td><input type="text" id="new_subtype" /></td>
            <td><button class="btn btn-secondary" onclick="insertNewRow()">Save</button></td>
        </tr>`;
    $('#tableBody').append(newRow);
}

function insertNewRow() {
    const id = $('#new_id').val();
    const nature = $('#new_nature').val();
    const subtype = $('#new_subtype').val();

    $.post('insert_training_type.php', { id, nature, subtype }, function(response) {
        alert(response);
        fetchTrainingTypes();
    });
}

function updateRow(uniqueId) {
    const id = document.getElementById('id_' + uniqueId).value;
    const nature = document.getElementById('nature_' + uniqueId).value;
    const subtype = document.getElementById('subtype_' + uniqueId).value;

    $.post('update_training_type.php', {
    unique_id: id,   // Must match backend
    nature: nature,
    subtype: subtype
}, function(response) {
    alert(response);
    fetchTrainingTypes();
});

}


function deleteRow(uniqueId) {
    const id = document.getElementById('id_' + uniqueId).value;
    const nature = document.getElementById('nature_' + uniqueId).value;
    const subtype = document.getElementById('subtype_' + uniqueId).value;

    if (confirm("Are you sure you want to delete this row?")) {
        $.post('delete_training_type.php', {
    unique_id: id,   // Must match backend
    nature: nature,
    subtype: subtype
}, function(response) {
    alert(response);
    fetchTrainingTypes();
});
    }
}
 


// Initial fetch
$(document).ready(fetchTrainingTypes);
</script>
</body>
</html>
<?php include '../footer.php';?>
