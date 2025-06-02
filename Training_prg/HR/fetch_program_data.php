<?php
$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);           

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die("Error connecting to database: " . sqlsrv_errors());
}

// Get the selected program name from the request
$selectedProgram = $_GET['program'];

// Query to fetch data based on the selected program
$query = "SELECT 
            r.id, 
            r.PROGRAM_NAME, 
            r.year, 
            r.tentative_date,
            e.name, 
            e.dept, 
            e.email, 
            e.loc_desc, 
            r.flag, 
            r.hostel_book
        FROM 
            [Complaint].[dbo].[request] r
        JOIN 
            [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
        WHERE 
            r.flag = '7' 
            AND r.PROGRAM_NAME = ?
        ORDER BY 
            r.id DESC";

$params = array($selectedProgram);
$stmt = sqlsrv_query($conn, $query, $params);

if ($stmt === false) {
    die("Error executing query: " . sqlsrv_errors());
}

// Output data in HTML format
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo "<tr class='table-light'>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['PROGRAM_NAME'] . "</td>";
    echo "<td>" . $row['year'] . "</td>";
    echo "<td>" . $row['tentative_date'] . "</td>";
    echo "<td>" . $row['dept'] . "</td>";
    echo "<td>" . $row['loc_desc'] . "</td>";
   
    echo "<td>" . ($row['flag'] == '4' ? 'TNI Approved' : $row['flag']) . "</td>";
    echo "<td style='color: " . ($row['hostel_book'] == 1 ? 'green' : 'red') . ";'>" . ($row['hostel_book'] == 1 ? 'Yes' : 'No') . "</td>";
    echo "</tr>";
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>


