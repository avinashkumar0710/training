<!DOCTYPE html>
<html>

<head>
    <title>Upload Employee Review</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        .table-container {
            margin-top: 30px;
        }
    </style>
</head>
<?php include 'header.php';?>
<h6><i class='fa fa-home'></i>&nbsp;<u><i>Training Resources->Upload By Hr Admin</i></u></h6>

<body>
    <div class="container">

    <form id="review-form" action="upload_review.php" method="post" enctype="multipart/form-data">
    <div class="form-row">
        <div class="form-group col-md-3">
            <center><b><label for="year">Select Year:</label></b></center>
            <select name="year" id="year" class="form-control" onchange="fetchPrograms(this.value)" required>
                <option value="">Select Year</option>
                <?php
                // Fetch distinct years from the database
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

                $query = "SELECT distinct year FROM [Complaint].[dbo].[training_mast]";                               
                $stmt = sqlsrv_query($conn, $query);

                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    echo "<option value='{$row['year']}'>{$row['year']}</option>";
                }

                sqlsrv_free_stmt($stmt);
                sqlsrv_close($conn);
                ?>
            </select>
        </div>
        
        <div class="form-group col-md-4">
            <center><b><label for="program_name">Select Program:</label></b></center>
            <select name="program_name" id="program_name" class="form-control" required>
                <option value="">Select Program</option>
                <!-- Options will be populated dynamically using JavaScript -->
            </select>
        </div>

        <div class="form-group col-md-3">
    <center><b><label for="file">Upload Documents:</label></b></center>
    <!-- Multiple file upload support, only allowing PDFs -->
    <input type="file" name="file[]" id="file" class="form-control" multiple accept=".pdf" required>
</div>

<script>
    document.getElementById('file').addEventListener('change', function() {
        var files = this.files;
        for (var i = 0; i < files.length; i++) {
            if (files[i].type !== "application/pdf") {
                alert("Only PDF files are allowed!");
                this.value = ''; // Clear the input
                return;
            }
        }
    });
</script>

        <button type="submit" class="btn btn-success">Upload</button>
    </div>
</form>

    </div>

    <div class="container mt-5">
        <h2>Uploaded Files</h2>
        <div id="uploaded-files"></div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        function fetchPrograms(year) {
            $.ajax({
                url: 'fetch_programs_for_employee.php',
                type: 'POST',
                data: { year: year },
                success: function(response) {
                    $('#program_name').html(response);
                }
            });
        }

        function createFolder() {
            var year = $('#year').val();
            var programId = $('#program_id').val();
            if (year && programId) {
                $.ajax({
                    url: 'create_folder.php',
                    type: 'POST',
                    data: { year: year, program_id: programId },
                    success: function(response) {
                        alert(response);
                        fetchUploadedFiles();
                    }
                });
            } else {
                alert("Please select an employee and program first.");
            }
        }

        function fetchUploadedFiles() {
            var year = $('#year').val();
            var program_name = $('#program_name').val();
            if (year && program_name) {
                $.ajax({
                    url: 'fetch_uploaded_files.php',
                    type: 'POST',
                    data: { year: year, program_name: program_name },
                    success: function(response) {
                        $('#uploaded-files').html(response);
                    }
                });
            } else {
                $('#uploaded-files').html("<p>No files found.</p>");
            }
        }

    //     $(document).ready(function() {
    //     fetchUploadedFiles(); // Fetch uploaded files on page load
    // });

        function deleteFile(year, program_name, fileName) {
            if (confirm("Are you sure you want to delete this file?")) {
                $.ajax({
                    url: 'delete_file.php',
                    type: 'POST',
                    data: { year: year, program_name: program_name, fileName: fileName },
                    success: function(response) {
                        alert(response);
                        fetchUploadedFiles();
                    }
                });
            }
        }

        $(document).ready(function() {
            $('#year').change(function() {
                $('#uploaded-files').html("");
            });

            $('#program_name').change(function() {
                fetchUploadedFiles();
            });
        });
    </script>
</body>

</html>
<?php include 'footer.php';?>