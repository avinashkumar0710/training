<?php
// $dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=trdashboard.accdb;Server=192.168.100.240;Port=2007;";
// $username = '';  // as required
// $password = '';  // as required

// try {
//     $pdo = new PDO($dsn, $username, $password);
//     // Now you can use $pdo to interact with the Access database
// } catch (PDOException $e) {
//     echo 'Connection failed: ' . $e->getMessage();
// }


$folderPath = '\\\\192.168.100.240:2007\\files\\trdashboard.accdb';

// Example of accessing the file in PHP
if (file_exists($folderPath)) {
    echo "File exists!";
    // Open and interact with the file as needed
} else {
    echo "File not found.";
}

