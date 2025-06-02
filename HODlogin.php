<html>

<head>
    <title>Welcome to Training portal</title>
    <link rel="icon" href="images/analysis.png">
   
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>
<style>

body {
    display: flex;
    justify-content: center;
    align-items: center;
    
    margin: 0;
    padding: 0;
    background-color: #ab4f4fad;
    font-family: "Nunito Sans", sans-serif;
  font-optical-sizing: auto;
  font-weight: 200;
  font-style: normal;
  font-variation-settings:
    "wdth" 100,
    "YTLC" 500;
}

.container {
    display: flex;
    /* border-radius: 15px; */
    overflow: hidden;
    /* Prevent line overflow */
    /* background-color: #ffffff; */
    height: 90%;
    box-shadow: 0px 0px 5px 0px #E8F2EA;
    width: 600px;
}

.image-section {
    flex: 1;
    padding: 20px;
    border-right: 1px solid #ccc;
    /* Add a border on the right side */
}

.login-section {
    flex: 1;
    padding: 20px;
    background-color: white;
    border-radius: 0px 15px 15px 0px;
    box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.2);
}

.img-fluid {
    max-width: 80%;
    height: auto;
}

.note {
    text-align: left;
    /* font-weight: 100; */
    height:35%;
}

.slider-container {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 600px;
    width:100%;
    overflow: hidden;
}

.slider {
    display: flex;
    transition: transform 0.5s ease-in-out;
}

.slider img {
    width: 100%; /* Make images cover full width */
        height: 100%;
        object-fit: cover; /* Maintain aspect ratio */
}
</style>

<center>

    <body onload="display_ct();">
        <div class="container">
            <div class="row">
                <!-- Left Section: Image Display -->
               

                <!-- Right Section: Login Form -->
                <div class="col-12" style="background-color:#323943;margin: 0; padding: 0;">
                    <div style="width:600px; background-color:#323943;"><br>
                    <img src="images/nspcl_logo1.png" alt="NSPCL" style="height:96px;width:150px;">
                        <legend style="font-size:40px;color:#ffffff;">TRAINING LOGIN</legend><br><br>

                        <form action="HODloginprocess.php" method="POST" id="form_id" style="width:450px;">

                            <div class="input-group input-group-lg" style="width:450px; color:#B1B6E7;">
                                <input type="text" class="form-control" required="" name="emp_num"
                                    placeholder=" Username" aria-label="Large">
                            </div><br>

                            <div class="input-group input-group-lg" style="width:450px; color:#B1B6E7">
                                <input type="password" class="form-control" name="passwd"
                                    placeholder=" Password" aria-label="Large" required="">

                            </div><br>
                            <?php
                                if (isset($_SESSION['login_error']) && $_SESSION['login_error']) {
                                    echo '<p style="color: red;">Invalid username or password. Please try again.</p>';
                                    $_SESSION['login_error'] = false; // Reset the login error session variable
                                }
                            ?>
                           
                            <input type="submit" class="btn btn-success btn-lg" value="LOGIN" name="sub">
                                <input type="button" class="btn btn-info btn-lg" onclick="resetForm()" value="RESET"><br><br>

                            <!-- <h6 style="color:#ffffff"><u>(Please Login to Proceed)</u></h6> -->
                            <div class="note">
                                <b><br>
                                     <center>
                                        
                                    </center><br><br>
                                        
                                    </p>
                                </b>                              
                            </div>
                            <script>
                            function resetForm() {
                                document.getElementById("form_id").reset();
                            }
                            </script>                         
                        </form>  
                        
                    </div>
                    <b><span id='ct' style="color:#ffffff"></span></b>
                </div>
                
            </div>
        </div>
       
        <script type="text/javascript">
        function display_c() {
            var refresh = 1000; // Refresh rate in milli seconds
            mytime = setTimeout('display_ct()', refresh)
        }

        function display_ct() {
            var x = new Date();
            document.getElementById('ct').innerHTML = x;
            display_c();
        }
        display_c(); // added to call the display_c function on page load
        </script>

    </body>
  

</center>

</html>