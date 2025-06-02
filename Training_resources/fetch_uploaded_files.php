<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $year = $_POST['year'];
    $programId = $_POST['program_name'];

    // echo '$program_name' .$programId;
    // echo '$year' .$year;

    // Fetch the program name from the database
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

    // $query = "SELECT Program_name FROM [Complaint].[dbo].[training_mast] WHERE year = ?";
    // $params = array($programId);
    // $stmt = sqlsrv_query($conn, $query, $params);

    // if ($stmt === false) {
    //     die(print_r(sqlsrv_errors(), true));
    // }

    // $programName = '';
    // if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    //     $programName = $row['Program_name'];
    // }

    // sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

    // Define the upload directory
    $uploadDir = "uploads/" . $year . "/" . $programId . "/";

    // Check if the directory exists
    if (file_exists($uploadDir)) {
        $files = scandir($uploadDir);
        echo "<table class='table table-bordered'>
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Date Modified</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>";
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                $filePath = $uploadDir . $file;
                $dateModified = date("Y-m-d H:i:s", filemtime($filePath));
                echo "<tr>
                        <td><a href='" . $filePath . "' target='_blank'>" . $file . "</a></td>
                        <td>" . $dateModified . "</td>
                        <td><button class='btn btn-danger btn-sm' onclick='deleteFile(\"" . $year . "\", \"" . $programId . "\", \"" . $file . "\")'>Delete</button></td>
                      </tr>";
            }
        }
        echo "</tbody></table>";
    } else {
        echo "No files found.";
    }
} else {
    echo "Invalid request method.";
}
?>
