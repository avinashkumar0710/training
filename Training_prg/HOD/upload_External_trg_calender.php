<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION["emp_num"])) {
    header("location: HODlogin.php"); // Redirect to login page if not logged in
    exit();
}

// Pad employee number to 8 digits
$sessionemp = $_SESSION["emp_num"];
$sessionemp1 = str_pad($sessionemp, 8, "0", STR_PAD_LEFT);

//echo $sessionemp;

// Database connection
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);

$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true)); // Handle connection errors
}
$selectedEmpNo = '';

// Check if any active files exist
$checkSql = "SELECT COUNT(*) as count FROM [Complaint].[dbo].[upload_External_trg_calender] WHERE flag = 1";
$checkStmt = sqlsrv_query($conn, $checkSql);
$hasActiveFiles = sqlsrv_fetch_array($checkStmt)['count'] > 0;
?>

<!-- Section 2: HTML Head and CSS -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TNI Subordinate HOD</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>
    <style>
        body {
            font-weight: 600;
            font-family: "Nunito Sans", sans-serif;
            margin: 0;
            background-color: #e8eef3;
        }        
        .file-input-label {
            cursor: pointer;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border: 1px dashed #6c757d;
            border-radius: 5px;
            display: block;
        }
        .file-input-label:hover {
            background-color: #e9ecef;
        }
        .file-preview {
            margin-top: 15px;
        }
        .file-item {
            display: flex;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        </style>
</head>

<body>
    <?php include '../header_HR.php'; ?>
    <body>
    <div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Upload External Training Calendar PDFs</h2>
            <?php if ($hasActiveFiles): ?>
            <a href="deactivate_all.php" class="btn btn-danger" 
               onclick="return confirm('Are you sure you want to deactivate ALL files? This cannot be undone.');">
               <i class="fas fa-trash-alt"></i> Deactivate All Files
            </a>
            <?php endif; ?>
        </div>
        
        <form action="upload_handler.php" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="pdfFiles" class="form-label">Select PDF Files (Multiple allowed):</label>
                <input type="file" class="form-control" id="pdfFiles" name="pdfFiles[]" multiple accept=".pdf" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Upload Files</button>
        </form>
        
        <div class="mt-5">
            <h4>Previously Uploaded Files</h4>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>File Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $serverName = "192.168.100.240";
                        $connectionInfo = array("Database" => "Complaint", "UID" => "sa", "PWD" => "Intranet@123");
                        $conn = sqlsrv_connect($serverName, $connectionInfo);
                        
                        if ($conn) {
                            $sql = "SELECT [id], [pdf_file_path], [original_name] FROM [Complaint].[dbo].[upload_External_trg_calender] WHERE [flag] = 1 ORDER BY [id] DESC";
                            $stmt = sqlsrv_query($conn, $sql);
                            
                            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                $fileName = basename($row['original_name']);
                                echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>$fileName</td>
                                    <td>
                                        <a href='{$row['pdf_file_path']}' class='btn btn-sm btn-success' target='_blank'>View</a>
                                        <a href='delete_file.php?id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                                    </td>
                                </tr>";
                            }
                            sqlsrv_free_stmt($stmt);
                            sqlsrv_close($conn);
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

<?php include '../footer.php';?>