<?php require_once "controllerUserData.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<style>
        html,body{
        background:#fd7e14;
        font-family: 'Poppins', sans-serif;
    }
    
    ::selection{
        color: #fff;
        background: #fd7e14;
    }

    .container .form form .button{
        background:#fd7e14;
        color: #fff;
        font-size: 17px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .container .form form .link a{
        color: #ee6e05;
    }
    .container .form form .button:hover{
        background:#ee6e05;
    }

    /* The message box is shown when the user clicks on the password field */
    #message {
    display:none;
    background: #fff;
    color: #000;
    position: relative;
    padding: 20px;
    margin-top: 10px;
    margin-left: 25%;
    }

    #message p {
    padding: 10px 35px;
    font-size: 18px;
    }

    /* Add a green text color and a checkmark when the requirements are right */
    .valid {
    color: green;
    }

    .valid:before {
    position: relative;
    left: -35px;
    content: "✔";
    }

    /* Add a red text color and an "x" when the requirements are wrong */
    .invalid {
    color: red;
    }

    .invalid:before {
    position: relative;
    left: -35px;
    content: "✖";
    }
</style>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form">
                <form action="forgot-password.php" method="POST" autocomplete="">
                    <h2 class="text-center">Forgot Password</h2>
                    <p class="text-center">Enter your email address</p>
                    <?php
                        if(count($errors) > 0){
                            ?>
                            <div class="alert alert-danger text-center">
                                <?php 
                                    foreach($errors as $error){
                                        echo $error;
                                    }
                                ?>
                            </div>
                            <?php
                        }
                    ?>
                    <div class="form-group">
                        <input class="form-control" type="email" name="email" placeholder="Enter email address" required value="<?php echo $email ?>">
                    </div>
                    <div class="form-group">
                        <input class="form-control button" type="submit" name="check-email" value="Continue">
                    </div>
                </form>
            </div>
        </div>
    </div>
    
</body>
</html>