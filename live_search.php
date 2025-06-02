
<?php
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp=$_SESSION["emp_num"];
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

// Check if the search query is set in the request
if (isset($_GET['year']) && isset($_GET['search'])) {
    $selectedYear = $_GET['year'];
    $searchParam = $_GET['search'];

    // Modify the SQL query to include the selected year and program_name in the search condition
    //$sql = "SELECT * FROM [Complaint].[dbo].[training_mast]  WHERE year = ? AND Program_name LIKE ?";
    $sql = "SELECT t.[srl_no],
    t.[Program_name],
    t.[nature_training],
    t.[duration],
    t.[faculty],
    t.[tentative_date],
    t.[year],
    t.[target_group],
    t.[id],
    t.[Closed_date]
FROM [Complaint].[dbo].[training_mast] t
WHERE t.srl_no NOT IN (
 SELECT [srl_no]
 FROM [Complaint].[dbo].[request] where empno='$sessionemp'
) and t.[year] = ? AND t.[Program_name] LIKE ?";

    $params = array($selectedYear, '%' . $searchParam . '%');
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // Display the live search results as table rows
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

         // Check if Closed_date is less than current date
         if (($row['Closed_date']->format('Y-m-d')) < (date('Y-m-d'))) {
            $disabled = 'disabled';
            $rowClass = 'disabled-row'; // Add class for styling disabled rows
        } else {
            $disabled = ''; // Reset disabled if Closed_date is greater than or equal to current date
            $rowClass = ''; // Reset row class if row is not disabled
        }

         echo "<tr class='table-light $rowClass'>"; 
        echo "<td>{$row['srl_no']}</td>";
        echo "<td>{$row['Program_name']}</td>";
        echo "<td>{$row['nature_training']}</td>";
        echo "<td>{$row['duration']}</td>";
        echo "<td>{$row['faculty']}</td>";
        echo "<td>{$row['tentative_date']}</td>";
        echo "<td>{$row['year']}</td>";
        echo "<td>{$row['target_group']}</td>";
        echo "<td>
        <select name='hostel_required[]' data-id='{$row['srl_no']}' $disabled>
            <option value='1'>Yes</option>
            <option value='0'>No</option>
        </select>
    </td>";
        echo "<td><input type='text' name='remarks[]' data-id='{$row['srl_no']}' placeholder='Enter remarks' $disabled></td>";
        echo "<td>
                <label class='checkbox-container'>
                    <input type='checkbox' name='selectedIds[]' value='{$row['srl_no']}' onchange='updateSubmitButton()' $disabled>
                    <span class='checkmark'></span>
                </label>
            </td>";
        echo "</tr>";
    }
}

// Close the connection
sqlsrv_close($conn);
?>
