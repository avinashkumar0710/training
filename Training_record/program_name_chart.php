<?php
// dashboard.php

// ---------------------------------------------------------
// AJAX Endpoint: When ?action=getChartData is set,
// process the request and return JSON data for the selected program.
// ---------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'getChartData') {
    // Validate parameter: a program must be specified.
    if (!isset($_GET['program']) || trim($_GET['program']) === "") {
        header('Content-Type: application/json');
        echo json_encode(["labels" => [], "data" => [], "message" => "No program specified"]);
        exit;
    }
    
    $selectedProgram = trim($_GET['program']);
    
    // Database Connection Settings
    $serverName     = "192.168.100.240";
    $connectionInfo = ["Database" => "complaint", "UID" => "sa", "PWD" => "Intranet@123"];
    $conn           = sqlsrv_connect($serverName, $connectionInfo);
    if ($conn === false) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Database connection failed"]);
        exit;
    }
    
    // Query: Aggregate the monthly sum of total_attendance for the selected program.
    $sql = "SELECT DATEPART(MONTH, attend_date) AS month, SUM(total_attendance) AS total_sum 
            FROM [Complaint].[dbo].[attendance_records] 
            WHERE program_name = ?
            GROUP BY DATEPART(MONTH, attend_date)
            ORDER BY month ASC";
    $params = [$selectedProgram];
    $stmt   = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        header('Content-Type: application/json');
        echo json_encode(["error" => "Query failed"]);
        exit;
    }
    
    $monthlyData = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $monthlyData[(int)$row['month']] = (float)$row['total_sum'];
    }
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    
    // Prepare data for all month labels (January through December)
    $labels = [];
    $data = [];
    for ($m = 1; $m <= 12; $m++) {
        $labels[] = date("F", mktime(0, 0, 0, $m, 10));
        $data[]   = isset($monthlyData[$m]) ? $monthlyData[$m] : 0;
    }
    
    header('Content-Type: application/json');
    echo json_encode(["labels" => $labels, "data" => $data]);
    exit;
}

// ---------------------------------------------------------
// Regular Dashboard Page: Display header, dropdown, and the chart.
// ---------------------------------------------------------

// Get distinct program names for the dropdown filter.
$serverName     = "192.168.100.240";
$connectionInfo = ["Database" => "complaint", "UID" => "sa", "PWD" => "Intranet@123"];
$conn           = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$sql = "SELECT DISTINCT program_name FROM [Complaint].[dbo].[attendance_records] ORDER BY program_name ASC";
$stmt = sqlsrv_query($conn, $sql);
$programs = [];
if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $programs[] = $row['program_name'];
    }
}
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Interactive Attendance Dashboard</title>
    <style>
        /* Overall styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 20px;
        }
        .dashboard-container {
            max-width: 1200px;
            margin: auto;
        }
        .header {
            text-align: center;
            padding: 20px;
            margin-bottom: 20px;
        }
        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
            padding: 20px;
            margin-bottom: 20px;
        }
        .filter-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 10px;
        }
        .filter-section label {
            font-size: 18px;
            font-weight: 600;
        }
        .filter-section select {
            padding: 8px;
            font-size: 16px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .chart-wrapper {
            position: relative;
            height: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <div class="header">
        <h1>Attendance Dashboard</h1>
        <p>Interactive dashboard simulating a Power BI experience.</p>
    </div>
    
    <div class="card">
        <div class="filter-section">
            <label for="programDropdown">Select Program:</label>
            <select id="programDropdown">
                <option value="">--Select Program--</option>
                <?php foreach ($programs as $prog): ?>
                    <option value="<?php echo htmlspecialchars($prog); ?>">
                        <?php echo htmlspecialchars($prog); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="chart-wrapper">
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>
</div>

<!-- Include Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Global variable to hold the chart instance.
let attendanceChart;

// Function to update the chart with data from the server.
function updateDashboardChart(selectedProgram) {
    console.log("Updating chart for program:", selectedProgram);
    // If no program is selected, destroy the existing chart.
    if (!selectedProgram) {
        if (attendanceChart) {
            attendanceChart.destroy();
            attendanceChart = null;
        }
        return;
    }
    // Fetch data via AJAX from the same page.
    fetch('dashboard.php?action=getChartData&program=' + encodeURIComponent(selectedProgram))
    .then(response => response.json())
    .then(jsonData => {
        console.log("Received JSON data:", jsonData);
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        // Destroy existing chart if there is one.
        if (attendanceChart) {
            attendanceChart.destroy();
        }
        attendanceChart = new Chart(ctx, {
            type: 'line',  // Change to 'bar' or other types if desired.
            data: {
                labels: jsonData.labels,
                datasets: [{
                    label: 'Total Attendance for ' + selectedProgram,
                    data: jsonData.data,
                    backgroundColor: 'rgba(66, 135, 245, 0.6)',
                    borderColor: 'rgba(66, 135, 245, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    })
    .catch(error => {
        console.error("Error fetching chart data:", error);
    });
}

// Listen for changes in the dropdown.
document.getElementById('programDropdown').addEventListener('change', function() {
    updateDashboardChart(this.value);
});

// On page load, automatically select the first available program
document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.getElementById('programDropdown');
    if (dropdown.options.length > 1) {
        // Automatically select the first valid program option if available.
        dropdown.selectedIndex = 1;
        updateDashboardChart(dropdown.value);
    }
});
</script>
</body>
</html>
