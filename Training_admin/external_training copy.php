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

$lastProgramId = 20251001; // Default if no data found

$sql = "SELECT TOP 1 program_id FROM [Complaint].[dbo].[attendance_records] WHERE flag = '3' ORDER BY program_id DESC";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt && $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $lastProgramId = intval($row['program_id']) + 1; // optionally increment
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/css/bootstrap-datepicker.min.css" integrity="sha512-3YtCnVXG09wW44ucnq9mxwznbgZBcvvjJ/jy/ygaQfQD+JMYrtxU/iYiywyc+Dsxzk81m1bB3mYv+qYf+mYaaHw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.10.0/js/bootstrap-datepicker.min.js" integrity="sha512-LsnSjD/drzYNRvlfrk/GxL+mJWfWj7Yjf4XMXa/fS27t6ozvvgLWE/YsyPKkt90d9SwaBcjsoX64lK/jMYOy3w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    let lastProgramId = <?php echo json_encode($lastProgramId); ?>;
</script>
<style>
    .fixed-bottom-right {
        position: fixed;
        bottom: 50px;
        right: -3px;
        z-index: 1000; /* Ensures it stays above other content */
    }

    .custom-modal-header {
    background-color: #ff5733; /* Change this color as needed */
    color: white; /* Ensures text remains visible */
}
.autocomplete-suggestions {
    
    max-height: 200px;
    overflow-y: auto;
    position: absolute;
    z-index: 9999;
    background-color: #d6e5e9;
    width: 200px;
}

.autocomplete-suggestions div {
    padding: 8px;
    cursor: pointer;
}

.autocomplete-suggestions div:nth-child(even) {
    background-color: #f8f9fa; /* Light gray */
}

.autocomplete-suggestions div:hover {
    background-color: #e9ecef; /* Highlight on hover */
}


</style>

<title>Excel Upload </title>
<?php include 'header.php';?>
<h6><i class='fa fa-home'></i>&nbsp;<i><u>Training Administration->External Online Training Attendance</u></i></h6>
<br>

<body>
<div class="container-fluid">
       
<form id="training-form" action="save_external_attendance.php" method="POST">
<div style="max-height: 650px; overflow-y: auto;">
    <table class="table table-bordered border-dark">
    <thead class="bg-success border-dark" style="position: sticky; top: 0; z-index: 1; background-color: #198754;">
            <tr class="bg-success border-dark">
                <th>Sl. No</th>
                <th>Plant</th>
                <th>Dept</th>
                <th>Dept Code</th>
                <th>Emp Name</th>
                <th>Emp No.</th>
                <th>ProgramID</th>
                <th>Program Name</th>
                <th>Nature of Training</th>
                <th>Training Subtype</th>
                <th>Training Mode</th>
                <th>Faculty Name</th>               
                <th>Training Location</th>
                <th>From Date</th>
                <th>To Date</th>
                <th>Duration(Days)</th>
                <th>Mandays</th>
                <th>Attendance</th>
                <th>Year</th>
                <th>Action</th> 
            </tr>
        </thead>
        <tbody id="request-table">
            </tbody>
    </table>
    </div>
    <button type="button" class="btn btn-success" onclick="addRow()">Add Row</button>
    <button type="submit" class="btn btn-primary">Save</button>
      
</form>
<!-- Button to Open Modal -->
<button class="btn btn-danger btn-sm fixed-bottom-right" data-bs-toggle="modal" data-bs-target="#futureProgramsModal">
    <i class="bi bi-arrow-left"></i> Edit Future Programs &nbsp;
</button>

<!-- Full-Width Modal -->
<div class="modal fade" id="futureProgramsModal" tabindex="-1" aria-labelledby="futureProgramsModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-fullscreen">  <!-- Fullscreen Modal -->
        <div class="modal-content">
            <div class="modal-header custom-modal-header"> <!-- Apply custom class -->
                <h5 class="modal-title" id="futureProgramsModalLabel">External Edit Future Programs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe src="edit_future_programs.php" style="width: 100%; height: 90vh; border: none;"></iframe>
            </div>
        </div>
    </div>
</div>



<script>
   let rowCount = 1;
   let programData = []; // Array to store program data fetched from the database

   // Function to fetch program data from the database (replace with your actual AJAX call)
function fetchProgramData() {
    // Assuming you have a PHP script (e.g., get_programs.php) that returns JSON data
    fetch('get_programs_external.php')
        .then(response => response.json())
        .then(data => {
            programData = data;
        })
        .catch(error => {
            console.error('Error fetching program data:', error);
        });
}

// Fetch program data when the page loads
fetchProgramData();

function fetchTrainingTypes() {
    return fetch('get_training_types.php') // Create a PHP file to fetch data
        .then(response => response.json())
        .catch(error => {
            console.error('Error fetching training types:', error);
            return []; // Return an empty array in case of an error
        });
}

async function fetchTrainingSubtypes(natureOfTraining) {
    if (!natureOfTraining) {
        return []; // Return empty if no nature of training is selected
    }
    return fetch(`get_training_subtypes.php?nature=${encodeURIComponent(natureOfTraining)}`)
        .then(response => response.json())
        .catch(error => {
            console.error('Error fetching training subtypes:', error);
            return [];
        });
}

async function addRow() {
    $lastProgramId = 20251001;
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

   

   

   // Program ID (hidden initially)
//    const programIdCell = newRow.insertCell();
//     const programIdInput = document.createElement('input');
//     programIdInput.type = 'text';
//     programIdInput.classList.add('form-control');
//     programIdInput.name = 'program_id[]';
//     programIdInput.id = `program_id_${newRow.id}`;
//     programIdInput.required = true;
//     programIdInput.style.width = '120px';
//     programIdCell.appendChild(programIdInput);

const programIdCell = newRow.insertCell();
    const programIdInput = document.createElement('input');
    programIdInput.type = 'text';
    programIdInput.classList.add('form-control');
    programIdInput.name = 'program_id[]';
    programIdInput.id = `program_id_${newRow.id}`;
    programIdInput.required = true;
    programIdInput.style.width = '120px';
    programIdInput.value = lastProgramId++; // Assign and increment
    programIdCell.appendChild(programIdInput);


    // Program Name (autocomplete)
    const programNameCell = newRow.insertCell();
    const programNameInput = document.createElement('input');
    programNameInput.type = 'text';
    programNameInput.classList.add('form-control');
    programNameInput.name = 'program_name[]';
    programNameInput.id = `program_name_${newRow.id}`;
    programNameInput.required = true;
    programNameInput.style.width = '200px';

    // Create a container for the autocomplete suggestions
    const suggestionsDiv = document.createElement('div');
    suggestionsDiv.classList.add('autocomplete-suggestions');
    suggestionsDiv.id = `suggestions_${newRow.id}`;
    programNameCell.appendChild(programNameInput);
    programNameCell.appendChild(suggestionsDiv);

    // Add event listener for input changes to trigger autocomplete
    programNameInput.addEventListener('input', function() {
        autocompleteProgram(this, programIdInput, suggestionsDiv);
    });
    // Add event listener to handle selection from suggestions
    programNameInput.addEventListener('blur', function() {
        // Slight delay to allow click on suggestion to register
        setTimeout(() => {
            suggestionsDiv.innerHTML = '';
        }, 200);
    });

    // Nature of Training Dropdown
    const natureOfTrainingCell = newRow.insertCell();
    const natureOfTrainingDropdown = document.createElement('select');
    natureOfTrainingDropdown.classList.add('form-control');
    natureOfTrainingDropdown.name = 'nature_of_training[]';
    natureOfTrainingDropdown.required = true;
    natureOfTrainingDropdown.style.width = '150px';

    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = '-- Select --';
    natureOfTrainingDropdown.appendChild(defaultOption);

    const trainingTypes = await fetchTrainingTypes(); // Assuming this function exists and works
    trainingTypes.forEach(type => {
        const option = document.createElement('option');
        option.value = type.nature_of_Training;
        option.textContent = type.nature_of_Training;
        natureOfTrainingDropdown.appendChild(option);
    });

    natureOfTrainingCell.appendChild(natureOfTrainingDropdown);

      // Training Subtype Dropdown
    const trainingSubtypeCell = newRow.insertCell();
    const trainingSubtypeDropdown = document.createElement('select');
    trainingSubtypeDropdown.classList.add('form-control');
    trainingSubtypeDropdown.name = 'training_subtype[]';
    trainingSubtypeDropdown.required = true;
    trainingSubtypeDropdown.style.width = '150px';

    const defaultSubtypeOption = document.createElement('option');
    defaultSubtypeOption.value = '';
    defaultSubtypeOption.textContent = '-- Select --';
    trainingSubtypeDropdown.appendChild(defaultSubtypeOption);

    // Initially, the subtype dropdown will be empty.
    trainingSubtypeCell.appendChild(trainingSubtypeDropdown);

    // Event listener to fetch subtypes when nature of training changes
    natureOfTrainingDropdown.addEventListener('change', async function() {
        const selectedNature = this.value;
        const subtypes = await fetchTrainingSubtypes(selectedNature);

        // Clear existing options
        trainingSubtypeDropdown.innerHTML = '';
        trainingSubtypeDropdown.appendChild(defaultSubtypeOption);

        // Populate subtype dropdown
        subtypes.forEach(subtype => {
            const option = document.createElement('option');
            option.value = subtype.Training_Subtype;
            option.textContent = subtype.Training_Subtype;
            trainingSubtypeDropdown.appendChild(option);
        });
    });

        // training_mode
        newRow.insertCell().innerHTML = `<input type="text" class="form-control" name="training_mode[]" value="External" required style="width: 150px;">`;

    // Faculty
    newRow.insertCell().innerHTML = `<input type="text" class="form-control" name="faculty[]" required style="width: 150px;">`;    

    // Location
    newRow.insertCell().innerHTML = `<input type="text" class="form-control" name="training_location[]" required style="width: 150px;">`;

    // From date
    let fromDateCell = newRow.insertCell();
    let fromDateInput = document.createElement('input');
    fromDateInput.type = 'date';
    fromDateInput.classList.add('form-control');
    fromDateInput.name = 'from_date[]';
    fromDateInput.required = true;
    fromDateInput.style.width = '150px';
    fromDateInput.addEventListener('change', function() {
        calculateDuration(newRow.id);
    });
    fromDateCell.appendChild(fromDateInput);

    // To date
    let toDateCell = newRow.insertCell();
    let toDateInput = document.createElement('input');
    toDateInput.type = 'date';
    toDateInput.classList.add('form-control');
    toDateInput.name = 'to_date[]';
    toDateInput.required = true;
    toDateInput.style.width = '150px';
    toDateInput.addEventListener('change', function() {
        calculateDuration(newRow.id);
    });
    toDateCell.appendChild(toDateInput);

    // Duration
    newRow.insertCell().innerHTML = `<input type="number" class="form-control" name="duration[]" style="width: 80px;" >`;

    // mandays
    //newRow.insertCell().innerHTML = `<input type="text" class="form-control" name="mandays[]" id="mandays_${newRow.id}" style="width: 80px;" readonly>`;

    // mandays
    newRow.insertCell().innerHTML = `<input type="text" class="form-control" name="mandays[]" required style="width: 80px;">`;

    // Attend/No Attend dropdown
    const attendCell = newRow.insertCell();
    const attendDropdown = document.createElement('select');
    attendDropdown.classList.add('form-control');
    attendDropdown.name = 'attendance[]'; // Use an array name for multiple rows

    const attendOption = document.createElement('option');
    attendOption.value = 'A';
    attendOption.textContent = 'Attend';
    attendDropdown.appendChild(attendOption);

    const noAttendOption = document.createElement('option');
    noAttendOption.value = 'NA';
    noAttendOption.textContent = 'Not Attend';
    attendDropdown.appendChild(noAttendOption);

    attendCell.appendChild(attendDropdown);

    // Location
    newRow.insertCell().innerHTML = `<input type="text" class="form-control" name="year[]" required style="width: 100px;">`;

    // Action - Remove Button
    const actionCell = newRow.insertCell();
    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.classList.add('btn', 'btn-danger', 'btn-sm');
    removeButton.textContent = 'Remove';
    removeButton.onclick = function() {
        removeRow(newRow.id);
    };
    actionCell.appendChild(removeButton);

    rowCount++;
}

// Ensure you have the fetchTrainingTypes() function defined as before:
    function fetchTrainingTypes() {
    return fetch('get_training_types.php') // Create a PHP file to fetch data
        .then(response => response.json())
        .catch(error => {
            console.error('Error fetching training types:', error);
            return []; // Return an empty array in case of an error
        });
}

function removeRow(rowId) {
    const rowToRemove = document.getElementById(rowId);
    if (rowToRemove) {
        rowToRemove.remove();
        // Re-index the remaining rows
        const rows = document.querySelectorAll('#request-table tr');
        rows.forEach((row, index) => {
            row.cells[0].textContent = index + 1;
            row.id = `row-${index + 1}`;
            // Update IDs of elements within the row if needed for other functionalities
            const deptCodeInput = row.querySelector('[name="dept_code[]"]');
            if (deptCodeInput) {
                deptCodeInput.id = `dept_code_row-${index + 1}`;
            }
            const empNoInput = row.querySelector('[name="emp_no[]"]');
            if (empNoInput) {
                empNoInput.id = `emp_no_row-${index + 1}`;
            }
            const durationInput = row.querySelector('[name="duration[]"]');
            if (durationInput) {
                durationInput.id = `duration_row-${index + 1}`;
            }
            const mandaysInput = row.querySelector('[name="mandays[]"]');
            if (mandaysInput) {
                mandaysInput.id = `mandays_row-${index + 1}`;
            }
            const fromDateInput = row.querySelector('[name="from_date[]"]');
            if (fromDateInput) {
                fromDateInput.removeEventListener('change', function() { calculateDuration(row.id); });
                fromDateInput.addEventListener('change', function() { calculateDuration(row.id); });
            }
            const toDateInput = row.querySelector('[name="to_date[]"]');
            if (toDateInput) {
                toDateInput.removeEventListener('change', function() { calculateDuration(row.id); });
                toDateInput.addEventListener('change', function() { calculateDuration(row.id); });
            }
            const removeButton = row.querySelector('.btn-danger');
            if (removeButton) {
                removeButton.onclick = function() { removeRow(row.id); };
            }
        });
        rowCount--;
    }
}



function updateElementId(row, selector, newId) {
    const element = row.querySelector(selector);
    if (element) {
        element.id = newId;
    }
}

function calculateDuration(rowId) {
    const fromDateInput = document.querySelector(`#${rowId} input[name="from_date[]"]`);
    const toDateInput = document.querySelector(`#${rowId} input[name="to_date[]"]`);
    const durationInput = document.getElementById(`duration_${rowId}`);
    //const mandaysInput = document.getElementById(`mandays_${rowId}`);

    if (fromDateInput.value && toDateInput.value) {
        const fromDate = new Date(fromDateInput.value);
        const toDate = new Date(toDateInput.value);

        // Calculate the difference in milliseconds
        const timeDifference = toDate.getTime() - fromDate.getTime();

        // Calculate the difference in days
        const durationDays = Math.ceil(timeDifference / (1000 * 3600 * 24)) + 1; // Adding 1 to include both start and end dates

        durationInput.value = durationDays;
        mandaysInput.value = durationDays; // Assuming mandays is equal to duration for each employee
    } else {
        durationInput.value = '';
        mandaysInput.value = '';
    }
}


function autocompleteProgram(programNameInput, programIdInput, suggestionsDiv) {
    const inputText = programNameInput.value.toLowerCase();
    suggestionsDiv.innerHTML = ''; // Clear previous suggestions

    if (inputText.length > 0) {
        const matchingPrograms = programData.filter(program =>
            program.program_name.toLowerCase().includes(inputText)
        );

        if (matchingPrograms.length > 0) {
            matchingPrograms.forEach(program => {
                const suggestionItem = document.createElement('div');
                suggestionItem.classList.add('autocomplete-item');
                suggestionItem.textContent = program.program_name;
                suggestionItem.addEventListener('click', function() {
                    programNameInput.value = program.program_name;
                    programIdInput.value = program.program_id;
                    suggestionsDiv.innerHTML = ''; // Clear suggestions after selection
                });
                suggestionsDiv.appendChild(suggestionItem);
            });
        } else {
            const noMatchItem = document.createElement('div');
            noMatchItem.classList.add('autocomplete-item', 'no-match');
            noMatchItem.textContent = 'No matching programs';
            suggestionsDiv.appendChild(noMatchItem);
        }
    }
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


// fetch Dept name
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


// fetch employee name
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
            updateEmpSelect(rowId, data[0].id, selectedPlant); // Pass both dept_code and plant
        }

        // Update dept code and fetch employees on change
        deptSelect.onchange = () => {
            const selectedDept = deptSelect.options[deptSelect.selectedIndex];
            deptCodeInput.value = selectedDept.value;
            updateEmpSelect(rowId, selectedDept.value, selectedPlant); // Pass both dept_code and plant on change
        };
    })
    .catch(error => console.error("Error fetching departments:", error));
}



function updateEmpSelect(rowId, selectedDeptCode, selectedPlant) {
    const empSelect = document.getElementById(`emp_name_${rowId}`);
    const empNoInput = document.getElementById(`emp_no_${rowId}`);

    if (!selectedDeptCode || !selectedPlant) {
        empSelect.innerHTML = "";
        empSelect.disabled = true;
        empNoInput.value = "";
        return;
    }

    // Construct the request body with both parameters
    const requestBody = `dept_code=${encodeURIComponent(selectedDeptCode)}&plant=${encodeURIComponent(selectedPlant)}`;

    // Fetch employees from server
    fetch("get_employees.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: requestBody
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