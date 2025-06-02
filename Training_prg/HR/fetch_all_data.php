<?php
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

$programName = isset($_POST['program_name']) ? $_POST['program_name'] : '';
$userPlant = isset($_POST['user_plant']) ? $_POST['user_plant'] : '';

$query = "SELECT r.id, r.PROGRAM_NAME, r.year, r.tentative_date, 
                 e.name, e.dept, e.email, e.loc_desc, r.flag, r.hostel_book, 
                 e.location, t.day_from, t.day_to
          FROM [Complaint].[dbo].[request] r
          JOIN [Complaint].[dbo].[emp_mas_sap] e ON r.empno = e.empno
          LEFT JOIN [Complaint].[dbo].[training_mast] t ON r.srl_no = t.srl_no
          WHERE r.flag = '7'";

$params = [];

if (!empty($programName)) {
    $query .= " AND r.PROGRAM_NAME = ?";
    $params[] = $programName;
}

if (!empty($userPlant) && $userPlant !== 'NS04') {
    $query .= " AND e.location = ?";
    $params[] = $userPlant;
}

$result = sqlsrv_query($conn, $query, $params);
if ($result === false) {
    die("Error fetching data: " . print_r(sqlsrv_errors(), true));
}

$serialNo = 1;
while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    echo "<tr class='table-light'>";
    echo "<td>" . $serialNo . "</td>";
    echo "<td>" . $row['name'] . "</td>";
    echo "<td>" . $row['PROGRAM_NAME'] . "</td>";
    echo "<td>" . $row['year'] . "</td>";
    echo "<td>" . (isset($row['day_from']) ? $row['day_from']->format('Y-m-d') : 'N/A') . "</td>";
    echo "<td>" . (isset($row['day_to']) ? $row['day_to']->format('Y-m-d') : 'N/A') . "</td>";
    echo "<td>" . $row['dept'] . "</td>";
    echo "<td>" . $row['loc_desc'] . "</td>";
    echo "<td>" . ($row['flag'] == '7' ? 'OverAll Approved' : $row['flag']) . "</td>";
    echo "<td style='color: " . ($row['hostel_book'] == 2 ? 'green' : 'red') . "'>" . 
          ($row['hostel_book'] == 2 ? 'Yes' : 'No') . "</td>";
    echo "</tr>";

    $serialNo++;
}
sqlsrv_close($conn);
?>
