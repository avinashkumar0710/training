<?php
session_start();
if (!isset($_SESSION["emp_num"])) {   
    header("location:login.php");
}
$sessionemp = $_SESSION["emp_num"];

// Establish the connection
$serverName = "192.168.100.240";
$connectionOptions = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die(print_r(sqlsrv_errors(), true));
}

// Count the total training requests for the employee
$sqlCount = "SELECT COUNT(empno) AS total_count FROM [Complaint].[dbo].[request] WHERE empno = ?";
$paramsCount = array($sessionemp);
$stmtCount = sqlsrv_query($conn, $sqlCount, $paramsCount);

if ($stmtCount === false) {
    die(print_r(sqlsrv_errors(), true));
}

$totalCountRow = sqlsrv_fetch_array($stmtCount, SQLSRV_FETCH_ASSOC);
$totalCount = $totalCountRow['total_count'];

// Determine if checkboxes should be disabled
$disableCheckboxes = $totalCount >= 8 ? 'disabled' : '';

// Check if the search query is set in the request
if (isset($_GET['year']) && isset($_GET['search'])) {
    $selectedYear = $_GET['year'];
    $searchParam = $_GET['search'];

    // SQL query to fetch training details
    $sql = "SELECT t.[srl_no],
                t.[Program_name],
                t.[nature_training],
                t.[duration],
                t.[faculty],
                t.[training_mode],
                t.[tentative_date],
                t.[Internal_external],
                t.[year],
                t.[target_group],
                t.[venue],                  
                t.[coordinator],
                t.[remarks],
                t.[Closed_date],
                r.rep_ofcr,
                r.ordinate_req,
                r.[srl_no] AS request_srl_no
            FROM [Complaint].[dbo].[training_mast] t
            LEFT JOIN [Complaint].[dbo].[request] r ON t.srl_no = r.srl_no AND r.empno = ?
            WHERE t.[year] = ? AND t.[Program_name] LIKE ?";

    $params = array($sessionemp, $selectedYear, '%' . $searchParam . '%');
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    
    $serialNo = 1; // Initialize serial number
    $disabledRowCount = 0;
    // Display the live search results as table rows
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Check if the row is present in the 'request' table or Closed_date has passed
        $disabled = ''; // Default state
        $rowClass = ''; // Default row class

        if ($row['request_srl_no'] || ($row['Closed_date']->format('Y-m-d') < date('Y-m-d'))) {
            $disabled = 'disabled'; // Disable if present in request table or Closed_date has passed
            $rowClass = 'disabled-row'; // Class for styling disabled rows
            $disabledRowCount++; // Increment the disabled row counter
        }

        // Check if Closed_date is less than the current date
        if (($row['Closed_date']->format('Y-m-d')) < (date('Y-m-d'))) {
            $disabled = 'disabled';  // Disable if the Closed_date has passed
            $rowClass = 'disabled-row';  // Add class for styling disabled rows
            $disabledRowCount++; //extra add
        }

        
        if (!empty($row['rep_ofcr'])) {
            $rowClass = 'blue-row';
        } elseif (!empty($row['ordinate_req'])) {
            $rowClass = 'green-row';
        }

        // Output table row with dynamic styling
        echo "<tr class='table-light $rowClass'>"; 
        echo "<td>{$serialNo}</td>";
        echo "<td>{$row['Program_name']}</td>";
        echo "<td>{$row['nature_training']}</td>";
        echo "<td>{$row['duration']}</td>";
        echo "<td>{$row['faculty']}</td>";
        echo "<td>{$row['training_mode']}</td>";
        echo "<td>{$row['tentative_date']}</td>";
        echo "<td>{$row['Internal_external']}</td>";
        echo "<td>{$row['year']}</td>";
        echo "<td>{$row['target_group']}</td>";
        echo "<td>{$row['venue']}</td>";
        echo "<td>{$row['coordinator']}</td>"; 
        echo "<td>{$row['remarks']}</td>"; 
        echo "<td>
                <select name='hostel_required[]' data-id='{$row['srl_no']}' $disabled>
                    <option value='1'>Yes</option>
                    <option value='0'>No</option>
                </select>
            </td>";
        echo "<td><input type='text' name='remarks[]' data-id='{$row['srl_no']}' placeholder='Enter remarks' $disabled></td>";
        echo "<td>
                <label class='checkbox-container'>
                    <input type='checkbox' name='selectedIds[]' value='{$row['srl_no']}' onchange='updateSubmitButton()' $disableCheckboxes>
                    <span class='checkmark'></span>
                </label>
            </td>";
        echo "</tr>";

        $serialNo++; // Increment the serial number
    }
}

// Close the connection
sqlsrv_close($conn);
?>
