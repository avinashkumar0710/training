<?php
session_start();

$serverName = "192.168.100.240";
$connectionInfo = array(
    "Database" => "complaint",
    "UID" => "sa",
    "PWD" => "Intranet@123"
);
$conn = sqlsrv_connect($serverName, $connectionInfo);

if (!$conn) {
    echo "<script>alert('Database connection failed. Please check your network.'); window.location.href='../login.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['ext_username']);
    $password = trim($_POST['ext_passwd']);
    $user_role = trim($_POST['user_role']);

    if (empty($username) || empty($password) || empty($user_role)) {
        //echo "<script>alert('All fields are required.'); window.location.href='../login.php';</script>";
        exit();
    }

    echo $user_role;

    // Store the original username
    $originalUsername = $username;

    // Apply 8-digit format **only** for user_role 11 (Manager) and 22 (HOD)
    if (($user_role == "11" || $user_role == "22") && strlen($username) == 6) {
        $username1 = "00" . $username;
    } else {
        $username1 = $username; // Keep it unchanged for other roles
    }

    // Debugging: Check values in console
    echo "<script>console.log('Changed Username: " . addslashes($username1) . "');</script>";
    echo "<script>console.log('Original Username: " . addslashes($originalUsername) . "');</script>";

    // ✅ First, Validate User Credentials (Check if user exists in EA_webuser_tstpp)
    $sql = "SELECT * FROM EA_webuser_tstpp WHERE emp_num = ? AND status = 'A'";
    $params = array($username); // Use $username1 only for 11 & 22
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        //echo "<script>alert('Error querying database. Please try again later.'); window.location.href='.../login.php';</script>";
        exit();
    }

    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    // ❌ If user does not exist or password is incorrect, show "Invalid username or password"
    if (!$row || $password !== $row['passwd']) {
        echo "<script>alert('Invalid username or password. Please try again.'); window.location.href='../login.php';</script>";
        exit();
    }

    


    if ($user_role == "11") { // Manager role check
        echo "<script>alert('Checking Manager Role for: " . addslashes($username1) . "');</script>";
    
        // First, check if the user is GM & BUH or CEO (Unauthorized)
        $checkUnauthorized = "SELECT 1 
                              FROM emp_mas_sap 
                              WHERE empno = ? 
                              AND design IN ('GM & BUH', 'Chief Executive Officer')";
    
        $stmtUnauthorized = sqlsrv_query($conn, $checkUnauthorized, array($username1));
        $isUnauthorized = sqlsrv_fetch_array($stmtUnauthorized, SQLSRV_FETCH_ASSOC);
    
        if ($isUnauthorized) {
            echo "<script>alert('Unauthorized Access: You do not have permission to view this information.'); window.location.href='../login.php';</script>";
            exit();
        }
    
        // Check if the user is ONLY a Reporting Officer and NOT an HOD
        $checkRepOfcrOnly = "SELECT DISTINCT e1.rep_ofcr 
                             FROM emp_mas_sap e1 
                             WHERE e1.rep_ofcr = ? 
                             AND e1.rep_ofcr NOT IN (SELECT DISTINCT hod_ro FROM emp_mas_sap WHERE hod_ro IS NOT NULL)";
    
        $stmtRepOfcrOnly = sqlsrv_query($conn, $checkRepOfcrOnly, array($username1));
        $repOfcrOnlyUser = sqlsrv_fetch_array($stmtRepOfcrOnly, SQLSRV_FETCH_ASSOC);
    
        // If the user is not ONLY a Rep_Ofcr, deny access
        if (!$repOfcrOnlyUser) {
            echo "<script>alert('Invalid username or password. Please try again.'); window.location.href='../login.php';</script>";
            exit();
        }
    }
    
    

    if ($user_role == "44") { // HR Admin role check (DO NOT modify username)
        $checkHRUser = "SELECT empno FROM Training_HR_User WHERE empno = ?";
        $stmtHR = sqlsrv_query($conn, $checkHRUser, array($username)); // Use original username
        $hrUser = sqlsrv_fetch_array($stmtHR, SQLSRV_FETCH_ASSOC);

        if (!$hrUser) {
            echo "<script>alert('You are not authorized to access the HR Admin platform.'); window.location.href='../login.php';</script>";
            exit();
        }
    }

    

    if ($user_role == "22") { // HOD role check
        echo "<script>console.log('Checking HOD Role for: " . addslashes($username1) . "');</script>";
    
        $sqlHOD = "
        DECLARE @empno VARCHAR(10) = ?;
    
        IF EXISTS (SELECT 1 FROM [Complaint].[dbo].[emp_mas_sap] 
                   WHERE empno = @empno AND design IN ('GM & BUH', 'Chief Executive Officer'))
        BEGIN
            SELECT 'Unauthorized' AS Is_HOD;
        END
        ELSE
        BEGIN
            SELECT 
                CASE 
                    WHEN EXISTS (SELECT 1 FROM [Complaint].[dbo].[emp_mas_sap] WHERE hod_ro = @empno) 
                    THEN 'Yes' 
                    ELSE 'No' 
                END AS Is_HOD;
        END;";
    
        $paramsHOD = array($username1); // Pass parameter safely
        $stmtHOD = sqlsrv_query($conn, $sqlHOD, $paramsHOD);
    
        if ($stmtHOD === false) {
            echo "<script>alert('Error checking HOD role. Please try again later.'); window.location.href='../login.php';</script>";
            exit();
        }
    
        $rowHOD = sqlsrv_fetch_array($stmtHOD, SQLSRV_FETCH_ASSOC);
    
        if ($rowHOD['Is_HOD'] === 'Unauthorized') {
            echo "<script>alert('Unauthorized Access: You do not have permission to view this information.'); window.location.href='../login.php';</script>";
            exit();
        } elseif ($rowHOD['Is_HOD'] === 'No') {
            echo "<script>alert('You are not authorized to access the HOD platform. Please try again.'); window.location.href='../login.php';</script>";
            exit();
        }
    }

    if ($user_role == "33") { // BUH/CEO role check
        echo "<script>console.log('Checking BUH/CEO Role for: " . addslashes($username1) . "');</script>";
    
        // Check if the user is GM & BUH or CEO
        $checkBUH_CEO = "SELECT 1 
                         FROM emp_mas_sap 
                         WHERE empno = ? 
                         AND design IN ('GM & BUH', 'Chief Executive Officer')";
    
        $stmtBUH_CEO = sqlsrv_query($conn, $checkBUH_CEO, array($username1));
        $isBUH_CEO = sqlsrv_fetch_array($stmtBUH_CEO, SQLSRV_FETCH_ASSOC);
    
        // If the user is NOT GM & BUH / CEO, deny access
        if (!$isBUH_CEO) {
            echo "<script>alert('You are not authorized to access the BUH/CEO platform.'); window.location.href='../login.php';</script>";
            exit();
        }
    }

    
    if ($user_role == "00") { // Employee Platform check
        echo "<script>console.log('Checking Employee Platform access for: " . addslashes($username1) . "');</script>";
    
        // Check if the user is GM & BUH or CEO
        $checkEmployee = "SELECT 1 
                          FROM emp_mas_sap 
                          WHERE empno = ? 
                          AND design IN ('GM & BUH', 'Chief Executive Officer')";
    
        $stmtEmployee = sqlsrv_query($conn, $checkEmployee, array($username1));
        $isRestricted = sqlsrv_fetch_array($stmtEmployee, SQLSRV_FETCH_ASSOC);
    
        // ❌ If the user is GM & BUH / CEO, deny access
        if ($isRestricted) {
            echo "<script>alert('Unauthorized Access: Employees with GM & BUH or CEO designation cannot access the Employee Platform.'); window.location.href='login.php';</script>";
            exit();
        }
    }
    
    

    // ✅ If all checks pass, login successful!
    $_SESSION['emp_num'] = $username; // Store modified username (8-digit if applicable)
    $_SESSION['user_role'] = $user_role;

    // Insert login activity
    $loginActivity = "INSERT INTO login_details (emp_num, login_time) VALUES (?, GETDATE())";
    sqlsrv_query($conn, $loginActivity, array($username1));

    echo "<script>window.location.href='External_mainpage.php';</script>";
    exit();
}



sqlsrv_close($conn);
?>
