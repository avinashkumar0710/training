<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Training Portal</title>
    <link rel="icon" type="image/png" sizes="32x32" href="employee.ico">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@200;400;600&display=swap" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    body {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
        background-color: #d9d0d0;
        font-family: "Nunito Sans", sans-serif;
    }

    .container {
        display: flex;
        flex-direction: row;
        justify-content: center;
        align-items: center;
        width: 1000px;
        box-shadow: 0px 0px 5px 0px #7aaed3ed;
        border-radius: 15px;
        background-color: #ffffff;
        overflow: hidden;
    }

    .image-section {
        flex: 1;
        text-align: center;
        padding: 20px;
        background-color: #fff;
    }

    .image-section img {
        max-width: 100%;
        height: auto;
        border-radius: 10px;
    }

    .login-section {
        flex: 1;
        padding: 40px;
        background-color: white;
    }

    .footer {
        text-align: center;
        padding: 10px;
        background-color: #fff;
        color: black;
        border-radius: 0 0 15px 15px;
    }

    .nav-tabs .nav-item .nav-link.active {
    background-color: green !important;
    color: white !important;
}

    
    </style>
</head>

<body onload="display_ct();">
<div class="container">
    <div class="image-section">
        <img src="images/signup-image.jpg" alt="Training">
    </div>
    
    <div class="login-section text-center">
        <img src="images/nspcl_logo1.png" alt="NSPCL" style="height:96px;width:150px;">
        <legend style="font-size:40px;color:black;">TRAINING LOGIN</legend>

        <!-- Bootstrap Tabs -->
        <ul class="nav nav-tabs mb-3" id="loginTabs" role="tablist">
    <!-- <li class="nav-item">
        <a class="nav-link active" id="inhouse-tab" data-toggle="tab" href="#inhouse" role="tab">Inhouse</a>
    </li> -->
    <!-- <li class="nav-item">
        <a class="nav-link" id="external-tab" data-toggle="tab" href="#external" role="tab">External</a>
    </li> -->
</ul>



        <div class="tab-content">
            <!-- Inhouse Login -->
            <div class="tab-pane fade show active" id="inhouse" role="tabpanel">
                <form action="loginprocess.php" method="POST" id="form_inhouse" class="mt-4">
                    <select class="form-control mb-3" name="user_role" required>
                        <option value="">Select User Role</option>
                        <option value="33"><b>BUH/CEO Platform</b></option><hr>
                        <option value="00">Employee Platform</option>
                        <option value="11">Manager Platform RO</option>
                        <option value="22">HOD Platform</option><hr>
                        <option value="44">HR Admin</option>
                    </select>
                    <div class="input-group input-group-lg mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" name="emp_num" placeholder="Username" required>
                    </div>
                    <div class="input-group input-group-lg mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" name="passwd" placeholder="Password" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg">LOGIN</button>
                    <button type="button" class="btn btn-info btn-lg" onclick="resetForm('form_inhouse')">RESET</button>
                </form>
            </div>

            <!-- External Login -->
            <div class="tab-pane fade" id="external" role="tabpanel">
                <form action="External/loginprocess_external.php" method="POST" id="form_external" class="mt-4">
                <select class="form-control mb-3" name="user_role" required>
                        <option value="">Select User Role</option>
                        <option value="33"><b>BUH/CEO Platform</b></option><hr>
                        <option value="22">HOD Platform</option>
                        <option value="44">HR Admin</option>
                    </select>
                    <div class="input-group input-group-lg mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                        </div>
                        <input type="text" class="form-control" name="ext_username" placeholder="External Username" required>
                    </div>
                    <div class="input-group input-group-lg mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        </div>
                        <input type="password" class="form-control" name="ext_passwd" placeholder="External Password" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-lg">LOGIN</button>
                    <button type="button" class="btn btn-info btn-lg" onclick="resetForm('form_external')">RESET</button>
                </form>
            </div>
        </div><br>
        <div>
            <p>Having Issue? Click <a href="training_issue.php">Here.</a></p>
            </div>
        <p class="mt-3"><b><span id='ct' style="color: black;"></span></b></p>
        <p><span style="background-color: Yellow;">Note * : Username & Password As same as OCMS</span></p>

        <div class="footer">
            <p>NSPCL &copy; <script>document.write(new Date().getFullYear());</script>. All Rights Reserved.</p>
        </div>
        
    </div>
</div>
<script>
    function resetForm(formId) {
        document.getElementById(formId).reset();
    }
</script>



   
</body>

</html>