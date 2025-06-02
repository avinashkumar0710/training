<?php 
session_start();
if (!isset($_SESSION["emp_num"])) {   
        header("location:login.php");
    }
    $sessionemp=$_SESSION["emp_num"];
//echo $_SESSION["emp_num"];

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
?> 
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="32x32" href="../employee.ico">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
<title>Excel Upload </title>
<?php include 'header.php';?>
<h6><i class='fa fa-home'></i>&nbsp;<i><u>Training Administration->External Online Training Attendance</u></i></h6>
<br>

<body>
<div class="container-fluid">
       
<form id="training-form" action="save_external_attendance.php" method="POST">
    <table class="table table-bordered border-dark">
        <thead>
            <tr class="bg-success border-dark">
                <th>Sl. No</th>
                <th>Plant</th>
                <th>Dept</th>
                <th>Dept_code</th>
                <th>Emp Name</th>
                <th>Emp No.</th>                   
                <th>ProgramID</th>
                <th>Program Name</th>                                     
                <th>Faculty</th>
                <th>Nature of Training</th>
                <th>Location</th>                                    
                <th>Duration (Days)</th>
            </tr>
        </thead>
        <tbody id="request-table">
           
        </tbody>
    </table>
    <button type="submit" class="btn btn-primary">Save</button>
</form>



</div>

<script>
   let rowCount = 1;

function addRow() {
    const tableBody = document.getElementById("request-table");
    const newRow = tableBody.insertRow();
    newRow.id = `row-${rowCount}`;

    // Serial Number
    newRow.insertCell().innerHTML = rowCount;

    // Plant Dropdown
    newRow.insertCell().appendChild(createPlantSelect(newRow.id));

    // Dept Dropdown (empty initially)
    newRow.insertCell().appendChild(createEmptyDeptSelect(newRow.id));

    // Dept Code (readonly)
    newRow.insertCell().innerHTML = `<input type="text" class="form-control" name="dept_code[]" id="dept_code_${newRow.id}" required style="width: 100px;" readonly>`;

    // Employee Name Dropdown
    newRow.insertCell().appendChild(createEmptyEmpSelect(newRow.id));

    // Employee Number
    newRow.insertCell().innerHTML = `<input type="text" class="form-control" name="emp_no[]" id="emp_no_${newRow.id}" required style="width: 120px;" readonly>`;

    // Program ID
    newRow.insertCell().innerHTML = `<input type="text" class="form-control" name="program_id[]" required style="width: 120px;">`;

    // Program Name
    newRow.insertCell().innerHTML = `<input type="text" class="form-control" name="program_name[]" required style="width: 200px;">`;

    // Faculty
    newRow.insertCell().innerHTML = `<input type="text" class="form-control" name="faculty[]" required style="width: 150px;">`;

    // Nature of Training
    newRow.insertCell().innerHTML = `<input type="text" class="form-control" name="nature_training[]" required style="width: 150px;">`;

    // Location
    newRow.insertCell().innerHTML = `<input type="text" class="form-control" name="training_location[]" required style="width: 150px;">`;

    // Duration input field
    const durationCell = newRow.insertCell();
    const durationInput = document.createElement("input");
    durationInput.type = "number";
    durationInput.classList.add("form-control");
    durationInput.name = "duration[]";
    durationInput.style.width = "90px";
    durationInput.min = 1;
    durationInput.required = true;
    durationInput.setAttribute("onchange", `updateDaysColumns('${newRow.id}', this.value)`); // Trigger column update
    durationCell.appendChild(durationInput);

    tableBody.appendChild(newRow);
    rowCount++;
}

const dayOptions = [
    { value: '0.25', text: '0.25' },
    { value: '0.50', text: '0.50' },
    { value: '0.75', text: '0.75' },
    { value: '1.00', text: '1.00' }
];

function createDayInput(dayNumber, rowId) {
    const container = document.createElement("div");
    container.classList.add("d-flex", "flex-column", "align-items-center");

    // Dropdown for Hours
    const selectElement = document.createElement("select");
    selectElement.name = `day_${dayNumber}_${rowId}[]`;
    selectElement.required = true;
    selectElement.classList.add("form-control", "mb-2"); // Add spacing below

    dayOptions.forEach(option => {
        const optionElement = document.createElement("option");
        optionElement.value = option.value;
        optionElement.textContent = option.text;
        selectElement.appendChild(optionElement);
    });

    // Date Picker (Appears Below Dropdown)
    const dateInput = document.createElement("input");
    dateInput.type = "date";
    dateInput.classList.add("form-control");
    dateInput.name = `date_${dayNumber}_${rowId}[]`;
    dateInput.required = true;

    container.appendChild(selectElement);
    container.appendChild(dateInput);

    selectElement.addEventListener("change", () => calculateTotal(rowId));

    return container;
}

function updateDaysColumns(rowId, duration) {
    const tableHead = document.querySelector("#training-form thead tr");
    const row = document.getElementById(rowId);

    // Remove old headers & dynamic day columns
    document.querySelectorAll(".dynamic-day").forEach(el => el.remove());
    document.querySelectorAll(".total-column").forEach(el => el.remove());

    // Add New Day Headers
    for (let i = 1; i <= duration; i++) {
        let th = document.createElement("th");
        th.classList.add("dynamic-day");
        th.textContent = `Day ${i}`;
        tableHead.appendChild(th);
    }

    // Add "Total" Header
    let totalTh = document.createElement("th");
    totalTh.classList.add("total-column");
    totalTh.textContent = "Total";
    tableHead.appendChild(totalTh);

    // Remove old day inputs from row
    document.querySelectorAll(`#${rowId} .dynamic-day`).forEach(col => col.remove());

    // Add new day dropdowns dynamically in the row
    for (let i = 1; i <= duration; i++) {
        let td = document.createElement("td");
        td.classList.add("dynamic-day", "text-center");
        let dayInput = createDayInput(i, rowId);
        td.appendChild(dayInput);
        row.appendChild(td);
    }

    // Add "Total" Cell
    let totalTd = document.createElement("td");
    totalTd.classList.add("total-column");
    totalTd.innerHTML = `<input type="text" class="form-control" name="total_${rowId}" readonly>`;
    row.appendChild(totalTd);
}

function calculateTotal(rowId) {
    const row = document.getElementById(rowId);
    let sum = 0;

    const dropdowns = row.querySelectorAll("select[name^='day_']");
    dropdowns.forEach(dropdown => {
        sum += parseFloat(dropdown.value || 0);
    });

    const totalCell = row.querySelector(`input[name="total_${rowId}"]`);
    totalCell.value = (sum / dropdowns.length).toFixed(2);
}






     // Function to remove a row
     function removeRow(rowId) {
        const rowToRemove = document.getElementById(rowId);
        rowToRemove.parentNode.removeChild(rowToRemove);
    }
    
    // Function to create the Plant Select dropdown
    function createPlantSelect(rowId) {
    const selectElement = document.createElement("select");
    selectElement.name = "plant[]";
    selectElement.required = true;
    selectElement.classList.add("form-control");
    selectElement.style.width = "150px";
    selectElement.setAttribute("onchange", `updateDeptSelect('${rowId}', this.value)`); // Trigger department update

    const plantOptions = [
        { value: "NS01", text: "Corporate Center" },
        { value: "NS02", text: "Durgapur" },
        { value: "NS03", text: "Rourkela" },
        { value: "NS04", text: "Bhilai" }
    ];

    plantOptions.forEach((option) => {
        const optionElement = document.createElement("option");
        optionElement.value = option.value;
        optionElement.textContent = option.text;
        selectElement.appendChild(optionElement);
    });

    return selectElement;
}

function updateDeptSelect(rowId, selectedPlant) {
    const deptSelect = document.getElementById(`dept_${rowId}`);
    const deptCodeInput = document.getElementById(`dept_code_${rowId}`);
    const empSelect = document.getElementById(`emp_name_${rowId}`);
    const empNoInput = document.getElementById(`emp_no_${rowId}`);

    if (!selectedPlant) {
        deptSelect.innerHTML = "";
        deptSelect.disabled = true;
        deptCodeInput.value = "";
        empSelect.innerHTML = "";
        empSelect.disabled = true;
        empNoInput.value = "";
        return;
    }

    // Fetch departments from server
    fetch("get_departments.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `plant=${encodeURIComponent(selectedPlant)}`
    })
    .then(response => response.json())
    .then(data => {
        deptSelect.innerHTML = "";
        deptSelect.disabled = false;

        data.forEach(dept => {
            const option = document.createElement("option");
            option.value = dept.id;
            option.textContent = dept.name;
            deptSelect.appendChild(option);
        });

        // Set default dept code for first item
        if (data.length > 0) {
            deptCodeInput.value = data[0].id;
            updateEmpSelect(rowId, data[0].id); // Fetch employees
        }

        // Update dept code and fetch employees on change
        deptSelect.onchange = () => {
            const selectedDept = deptSelect.options[deptSelect.selectedIndex];
            deptCodeInput.value = selectedDept.value;
            updateEmpSelect(rowId, selectedDept.value); // Fetch employees
        };
    })
    .catch(error => console.error("Error fetching departments:", error));
}



function createEmptyDeptSelect(rowId) {
    const selectElement = document.createElement("select");
    selectElement.name = "dept[]";
    selectElement.id = `dept_${rowId}`;
    selectElement.required = true;
    selectElement.classList.add("form-control");
    selectElement.style.width = "180px";
    selectElement.disabled = true; // Initially disabled
    return selectElement;
}



function createEmptyEmpSelect(rowId) {
    const selectElement = document.createElement("select");
    selectElement.name = "emp_name[]";
    selectElement.id = `emp_name_${rowId}`;
    selectElement.required = true;
    selectElement.classList.add("form-control");
    selectElement.style.width = "160px";
    selectElement.disabled = true; // Initially disabled
    selectElement.setAttribute("onchange", `updateEmpNo('${rowId}')`); // Update Employee No
    return selectElement;
}


function updateEmpSelect(rowId, selectedDeptCode) {
    const empSelect = document.getElementById(`emp_name_${rowId}`);
    const empNoInput = document.getElementById(`emp_no_${rowId}`);

    if (!selectedDeptCode) {
        empSelect.innerHTML = "";
        empSelect.disabled = true;
        empNoInput.value = "";
        return;
    }

    // Fetch employees from server
    fetch("get_employees.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `dept_code=${encodeURIComponent(selectedDeptCode)}`
    })
    .then(response => response.json())
    .then(data => {
        empSelect.innerHTML = "";
        empSelect.disabled = false;

        data.forEach(emp => {
            const option = document.createElement("option");
            option.value = emp.emp_num;
            option.textContent = emp.emp_name;
            empSelect.appendChild(option);
        });

        // Set default emp_no for first item
        if (data.length > 0) {
            empNoInput.value = data[0].emp_num;
        }

        // Update emp_no on change
        empSelect.onchange = () => {
            updateEmpNo(rowId);
        };
    })
    .catch(error => console.error("Error fetching employees:", error));
}

function updateEmpNo(rowId) {
    const empSelect = document.getElementById(`emp_name_${rowId}`);
    const empNoInput = document.getElementById(`emp_no_${rowId}`);

    // Get selected employee's emp_num from dropdown value
    const selectedEmpNo = empSelect.value;

    // Update the emp_no input field
    empNoInput.value = selectedEmpNo;
}


  
// Handle remarks field auto-fill on form submit
document.getElementById("training-form").addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent default form submission

    const formData = new FormData(this);

    // **Extract the dropdown text values and add to formData**
    document.querySelectorAll("select[name='emp_name[]']").forEach((dropdown, index) => {
        formData.append(`emp_name_text[]`, dropdown.options[dropdown.selectedIndex].text);
    });

    document.querySelectorAll("select[name='dept[]']").forEach((dropdown, index) => {
        formData.append(`dept_text[]`, dropdown.options[dropdown.selectedIndex].text);
    });

    document.querySelectorAll("select[name='plant[]']").forEach((dropdown, index) => {
        formData.append(`plant_text[]`, dropdown.options[dropdown.selectedIndex].text);
    });

    // **Check console output before sending to PHP**
    console.log("Final Form Data Before Sending:");
    for (let pair of formData.entries()) {
        console.log(pair[0] + ": " + pair[1]);
    }

    // Send the data to the server
    fetch('save_external_attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log(data);
        alert("Data saved successfully!");
        location.reload();
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Error saving data.");
    });
});





    // Add the first row on page load
    document.addEventListener('DOMContentLoaded', function() {
        addRow();
    });
</script>

</body>
   
<?php include '../footer.php';?>