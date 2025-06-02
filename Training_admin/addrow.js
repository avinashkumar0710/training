
        function toggleAttendance(selectElement, userId, day) {
            var attendanceDiv = document.getElementById('attendance_' + userId + '_' + day);
            if (selectElement.value == '0') {
                attendanceDiv.style.display = 'none';
                attendanceDiv.querySelector('select').value = '';
                updateTotal(userId);
            } else {
                attendanceDiv.style.display = 'block';
            }
        }

        function updateTotal(userId) {
            var total = 0;
            var attendanceSelects = document.querySelectorAll('select[name^="attendance[' + userId + ']"]');
            attendanceSelects.forEach(function(select) {
                if (select.value) {
                    total += parseFloat(select.value);
                }
            });
            document.getElementById('total_' + userId).innerText = total.toFixed(2);
        }

        function addNewRow() {
            var tableBody = document.querySelector('table tbody');
            var newRow = document.createElement('tr');

            var serialNoCell = document.createElement('td');
            serialNoCell.innerText = document.querySelectorAll('table tbody tr').length + 1;
            newRow.appendChild(serialNoCell);

            var nameCell = document.createElement('td');
            nameCell.innerHTML = '<input type="text" name="new_name[]" class="form-control">';
            newRow.appendChild(nameCell);

            var programNameCell = document.createElement('td');
            programNameCell.innerHTML = '<input type="text" name="new_program_name[]" class="form-control">';
            newRow.appendChild(programNameCell);

            var deptCell = document.createElement('td');
            deptCell.innerHTML = '<input type="text" name="new_dept[]" class="form-control">';
            newRow.appendChild(deptCell);

            var plantCell = document.createElement('td');
            plantCell.innerHTML = '<input type="text" name="new_plant[]" class="form-control">';
            newRow.appendChild(plantCell);

            var tentativeDateCell = document.createElement('td');
            tentativeDateCell.innerHTML = '<input type="date" name="new_tentative_date[]" class="form-control">';
            newRow.appendChild(tentativeDateCell);

            var durationCell = document.createElement('td');
            durationCell.innerHTML = '<input type="number" name="new_duration[]" class="form-control" onchange="addDays(this)">';
            newRow.appendChild(durationCell);

            // Placeholder for dynamic days columns
            var daysCell = document.createElement('td');
            daysCell.className = 'days-column';
            newRow.appendChild(daysCell);

            var totalCell = document.createElement('td');
            totalCell.innerText = '0.00';
            newRow.appendChild(totalCell);

            var removeCell = document.createElement('td');
            removeCell.innerHTML = '<button type="button" class="btn btn-danger" onclick="removeRow(this)">Remove</button>';
            newRow.appendChild(removeCell);

            tableBody.appendChild(newRow);
        }

        function addDays(inputElement) {
            var duration = parseInt(inputElement.value);
            var row = inputElement.parentNode.parentNode;
            var daysColumn = row.querySelector('.days-column');
            daysColumn.innerHTML = '';
        
            for (var i = 1; i <= duration; i++) {
                var dayCell = document.createElement('td');
                dayCell.innerHTML = `
                    <select name="new_status[${i}][]" onchange="toggleAttendance(this, 'new', ${i})">
                        <option value="1">Attend</option>
                        <option value="0">Not Attend</option>
                    </select>
                    <div id="attendance_new_${i}" style="display: none;">
                        <select name="new_attendance[${i}][]">
                            <option value="">Select</option>
                            <option value="0.25">0.25</option>
                            <option value="0.50">0.50</option>
                            <option value="0.75">0.75</option>
                            <option value="1.00">1.00</option>
                        </select>
                    </div>
                `;
                daysColumn.appendChild(dayCell);
            }
        }
        

        function removeRow(button) {
            var row = button.parentNode.parentNode;
            row.parentNode.removeChild(row);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize attendance fields and totals
            var statusSelects = document.querySelectorAll('select[name^="status"]');
            statusSelects.forEach(function(select) {
                var userId = select.name.match(/status\[(\d+)\]/)[1];
                var day = select.name.match(/\[(\d+)\]$/)[1];
                toggleAttendance(select, userId, day);
            });

            var attendanceSelects = document.querySelectorAll('select[name^="attendance"]');
            attendanceSelects.forEach(function(select) {
                var userId = select.name.match(/attendance\[(\d+)\]/)[1];
                updateTotal(userId);
            });
        });
