<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popup Example</title>
    <style>
        /* Fixed Button */
        .fixed-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 50px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        /* Popup Background */
        .popup-background {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        /* Popup Window */
        .popup {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 300px;
        }

        .popup h2 {
            margin-top: 0;
        }

        .popup label {
            display: block;
            margin-bottom: 5px;
        }

        .popup input {
            width: 100%;
            padding: 5px;
            margin-bottom: 10px;
        }

        .popup button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            width: 100%;
        }

        .popup button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <!-- Fixed Button -->
    <button class="fixed-btn" onclick="showPopup()">External Link</button>

    <!-- Popup Background -->
    <div class="popup-background" id="popupBackground">
        <div class="popup">
            <h2>Employee Info</h2>
            <label for="empno">Employee Number:</label>
            <input type="text" id="empno" readonly>

            <label for="empname">Employee Name:</label>
            <input type="text" id="empname" readonly>

            <label for="dept">Department:</label>
            <input type="text" id="dept" readonly>

            <button onclick="submitForm()">Submit</button>
        </div>
    </div>

    <script>
        // Function to show the popup
        function showPopup() {
            document.getElementById('popupBackground').style.display = 'flex';

            // Fetch employee data (example with static data)
            // Replace with actual data fetching logic
            document.getElementById('empno').value = '12345';
            document.getElementById('empname').value = 'John Doe';
            document.getElementById('dept').value = 'Sales';
        }

        // Function to submit the form
        function submitForm() {
            // Add your form submission logic here
            alert('Form submitted!');

            // Hide the popup after submission
            document.getElementById('popupBackground').style.display = 'none';
        }
    </script>

</body>
</html>
