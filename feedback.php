
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Form</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Feedback Form</h2>
        <form id="feedbackForm" action="submit_feedback.php" method="POST">
            <div class="form-group">
                <label for="empNo">Employee Number:</label>
                <input type="text" class="form-control" id="empNo" name="empNo" required>
            </div>
            <div class="form-group">
                <label for="plant">Location:</label>
                <select class="form-control" id="plant" name="plant" required>
                    <option value="Bhilai">Bhilai</option>
                    <option value="Durgapur">Durgapur</option>
                    <option value="Rourkela">Rourkela</option>
                    <option value="Corporate">Corporate</option>
                </select>
            </div>
            <div class="form-group">
                <label for="feedback">Feedback/Issue:</label>
                <textarea class="form-control" id="feedback" name="feedback" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>

    <!-- Bootstrap JS (optional, for Bootstrap components that require JavaScript) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Script to show modal popup after 5 seconds -->
    <script>
        $(document).ready(function(){
            setTimeout(function(){
                // Show modal popup
                $("#myModal").modal("show");
            }, 5000);
        });
    </script>

    <!-- Modal popup markup -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Feedback Submitted</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Your feedback has been submitted successfully.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
