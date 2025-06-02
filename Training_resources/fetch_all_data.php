<?php
if (isset($_POST['year'])) {
    $year = $_POST['year'];

    $baseDir = "uploads/$year/";
    if (is_dir($baseDir)) {
        $programs = scandir($baseDir);
        foreach ($programs as $program) {
            if ($program != '.' && $program != '..') {
                $programDir = $baseDir . $program;
                $files = scandir($programDir);
                if (count($files) > 2) {
                    echo "<h3>$year - <i><u>$program</u></i></h3>";
                    echo "<table class='table table-bordered'>";
                    echo "<thead><tr class='table-success border-dark'><th>File Name</th><th>Date</th><th>Actions</th></tr></thead>";
                    echo "<tbody>";
                    foreach ($files as $file) {
                        if ($file != '.' && $file != '..') {
                            $filePath = $programDir . '/' . $file;
                            $dateModified = date("F d Y H:i:s", filemtime($filePath));
                            echo "<tr class='bg-light border-dark'>";
                            echo "<td>$file</td>";
                            echo "<td>$dateModified</td>";
                            echo "<td>
                                    <a href='$filePath' class='btn btn-primary' download>Download</a>
                                    <!--<button class='btn btn-danger' onclick='deleteFile(\"$filePath\")'>Delete</button>-->
                                  </td>";
                            echo "</tr>";
                        }
                    }
                    echo "</tbody></table>";
                } else {
                    echo "<h3>No files found for $year - $program</h3>";
                }
            }
        }
    } else {
        echo "<h3>No files found for $year.</h3>";
    }
}
?>

<!-- <script>
    function deleteFile(filePath) {
        if (confirm("Are you sure you want to delete this file?")) {
            $.ajax({
                url: 'delete_file.php',
                type: 'POST',
                data: { filePath: filePath },
                success: function(response) {
                    alert(response);
                    location.reload();
                }
            });
        }
    }
</script> -->
