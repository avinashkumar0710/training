<!DOCTYPE html>
<html>
<head>
    <title>Authenticate to View Issues</title>
    <script>
        function validateEmpno() {
            let empno = prompt("Enter your Employee Number to view issues:");

            if (empno === "99999999" || empno === "100031") {
                // Redirect with query param
                window.location.href = "view_issues.php?auth=" + empno;
            } else if (empno === null || empno === "") {
                alert("Employee number is required.");
            } else {
                alert("Unauthorized access. You are not allowed to view issues.");
            }
        }

        // Run validation on page load
        window.onload = function () {
            validateEmpno();
        };
    </script>
</head>
<body>
</body>
</html>
