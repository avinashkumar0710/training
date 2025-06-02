<?php
// Database connection
$serverName = "192.168.100.240";
$connectionInfo = array("Database" => "Complaint", "UID" => "sa", "PWD" => "Intranet@123");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Calendar PDF Viewer</title>
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
         body {
            font-weight: 600;
            font-family: "Nunito Sans", sans-serif;
            margin: 0;
            background-color: #e8eef3;
        } 
        .pdf-icon {
            color: #e74c3c;
            font-size: 2rem;
            transition: transform 0.2s;
        }
        .pdf-icon:hover {
            transform: scale(1.1);
        }
        .file-name {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(231, 76, 60, 0.1);
        }
    </style>
</head>
<body>
<?php include '../header_HR.php'; ?>
    <div class="container mt-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h3 class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Training Calendar PDF Files
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">SL.No</th>
                                <th width="65%">File Name</th>
                                <th width="30%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT 
                                        ROW_NUMBER() OVER (ORDER BY id) AS slno,
                                        original_name AS file_name,
                                        pdf_file_path 
                                    FROM [Complaint].[dbo].[upload_External_trg_calender] 
                                    WHERE flag = 1
                                    ORDER BY id";
                            
                            $stmt = sqlsrv_query($conn, $sql);
                            
                            if ($stmt === false) {
                                die("Query failed: " . print_r(sqlsrv_errors(), true));
                            }
                            
                            $serialNo = 1; // Initialize serial number counter

                            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                echo "<tr>
                                  <td>$serialNo</td>
                                    <td class='file-name' title='{$row['file_name']}'>{$row['file_name']}</td>
                                    <td>
                                        <a href='{$row['pdf_file_path']}' target='_blank' class='btn btn-sm btn-outline-danger'>
                                            <i class='fas fa-file-pdf pdf-icon me-1'></i>
                                            View PDF
                                        </a>
                                    </td>
                                </tr>";
                                $serialNo++; // Increment serial number
                            }
                            
                            sqlsrv_free_stmt($stmt);
                            sqlsrv_close($conn);
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted">
                <small>Click on any PDF icon to view the file in a new tab</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

<?php include '../footer.php';?>
</html>