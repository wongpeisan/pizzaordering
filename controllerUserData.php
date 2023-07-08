<?php
require "connection.php";

//Set the session cookie parameters
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict' // Added SameSite attribute
]);

session_start();  
if(isset($_POST["login"]))  
{  
     $_SESSION["email"] = $_POST["email"];  
     $_SESSION['last_login_timestamp'] = time();  
     header("location:home.php");       
}  

$email = "";
$name = "";
$errors = array();

// Function to validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Function to generate a random code
function generateCode() {
    return rand(100000, 999999);
}

// Function to generate the authentication cookie value
function generateAuthCookieValue() {
    // Generate a random string or use any other logic to create a unique value
    $cookieValue = uniqid();

    // You can also add additional logic to include user-specific information or encryption

    return $cookieValue;
}

// Check if the CSRF token is set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle signup form submission
if (isset($_POST['signup'])) {
    // Get form data and sanitize
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['psw']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);

    // Validate password confirmation
    if ($password !== $cpassword) {
        $errors['password'] = "Confirm password does not match!";
    }

    // Check if email already exists
    $email_check = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $email_check);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($res) > 0) {
        $errors['email'] = "Email already exists!";
    }

    // If no errors, proceed with registration
    if (count($errors) === 0) {
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $code = generateCode();
        $status = "notverified";
        $insert_data = "INSERT INTO users (name, email, password, code, status)
                        VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $insert_data);
        mysqli_stmt_bind_param($stmt, "sssis", $name, $email, $encpass, $code, $status);
        $data_check = mysqli_stmt_execute($stmt);

        if ($data_check) {
            $subject = "Email Verification Code";
            $message = "Your verification code is $code";
            $sender = "From: wongpeisan01@gmail.com";

            if (mail($email, $subject, $message, $sender)) {
                $_SESSION['info'] = "We've sent a verification code to your email - $email";
                $_SESSION['email'] = $email;
                $_SESSION['password'] = $password;
                header('location: user-otp.php');
                exit();
            } else {
                $errors['otp-error'] = "Failed while sending code!";
            }
        } else {
            $errors['db-error'] = "Failed while inserting data into the database!";
        }
    }
}

// Handle verification code submission
if (isset($_POST['check'])) {
    $_SESSION['info'] = "";
    $otp_code = mysqli_real_escape_string($con, $_POST['otp']);
    $check_code = "SELECT * FROM users WHERE code = ?";
    $stmt = mysqli_prepare($con, $check_code);
    mysqli_stmt_bind_param($stmt, "i", $otp_code);
    mysqli_stmt_execute($stmt);
    $code_res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($code_res) > 0) {
        $fetch_data = mysqli_fetch_assoc($code_res);
        $fetch_code = $fetch_data['code'];
        $email = $fetch_data['email'];
        $code = 0;
        $status = 'verified';
        $update_otp = "UPDATE users SET code = ?, status = ? WHERE code = ?";
        $stmt = mysqli_prepare($con, $update_otp);
        mysqli_stmt_bind_param($stmt, "isi", $code, $status, $fetch_code);
        $update_res = mysqli_stmt_execute($stmt);

        if ($update_res) {
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            header('location: home.php');
            exit();
        } else {
            $errors['otp-error'] = "Failed while updating code!";
        }
    } else {
        $errors['otp-error'] = "You've entered an incorrect code!";
    }
}

// Handle login form submission
if (isset($_POST['login'])) {
    // Sanitize form data
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['psw']);

    // Check if email exists in the database
    $check_email = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $check_email);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) > 0) {
        $fetch = mysqli_fetch_assoc($res);
        $fetch_pass = $fetch['password'];

        if (password_verify($password, $fetch_pass)) {
            $_SESSION['email'] = $email;
            $status = $fetch['status'];

            if ($status == 'verified') {
                $_SESSION['email'] = $email;
                $_SESSION['password'] = $password;

                // Generate the authentication cookie value
                $auth_cookie_value = generateAuthCookieValue();

                // Set the authentication cookie
                setcookie('auth_cookie', $auth_cookie_value, [
                    'expires' => time() + 3600,
                    'path' => '/',
                    'secure' => true,
                    'httponly' => true,
                    'samesite' => 'Strict' // Added SameSite attribute
                ]);

                // Redirect the user to the authenticated area or home page
                if($_SESSION['email'] == "wongthiansoon2014@hotmail.com"){
                    header('Location: adminHome.php');
                }
                else{
                    header('Location: home.php');
                }

            
               exit();
            
            } else {
                $info = "It looks like you haven't verified your email yet - $email";
                $_SESSION['info'] = $info;
                header('Location: user-otp.php');
                exit();
            }
        } else {
            $errors['email'] = "Incorrect email or password!";
        }
    } else {
        $errors['email'] = "It looks like you're not yet a member! Click the link below to sign up.";
    }
}

// Handle forgot password form submission
if (isset($_POST['check-email'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);

    // Validate email format
    if (!isValidEmail($email)) {
        $errors['email'] = "Invalid email format!";
    } else {
        $check_email = "SELECT * FROM users WHERE email=?";
        $stmt = mysqli_prepare($con, $check_email);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $code = generateCode();

            $insert_code = "UPDATE users SET code=? WHERE email=?";
            $stmt = mysqli_prepare($con, $insert_code);
            mysqli_stmt_bind_param($stmt, "is", $code, $email);
            $run_query = mysqli_stmt_execute($stmt);

            if ($run_query) {
                $subject = "Password Reset Code";
                $message = "Your password reset code is $code";
                $sender = "From: wongpeisan01@gmail.com";

                if (mail($email, $subject, $message, $sender)) {
                    $_SESSION['info'] = "We've sent a password reset otp to your email - $email";
                    $_SESSION['email'] = $email;
                    header('location: reset-code.php');
                    exit();
                } else {
                    $errors['otp-error'] = "Failed while sending code!";
                }
            } else {
                $errors['db-error'] = "Something went wrong!";
            }
        } else {
            $errors['email'] = "This email address does not exist!";
        }
    }
}

// Handle reset code verification form submission
if (isset($_POST['check-reset-otp'])) {
    $_SESSION['info'] = "";
    $otp_code = mysqli_real_escape_string($con, $_POST['otp']);
    $check_code = "SELECT * FROM users WHERE code = ?";
    $stmt = mysqli_prepare($con, $check_code);
    mysqli_stmt_bind_param($stmt, "i", $otp_code);
    mysqli_stmt_execute($stmt);
    $code_res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($code_res) > 0) {
        $fetch_data = mysqli_fetch_assoc($code_res);
        $email = $fetch_data['email'];
        $_SESSION['email'] = $email;
        $info = "Please create a new password that you don't use on any other site.";
        $_SESSION['info'] = $info;
        header('location: new-password.php');
        exit();
    } else {
        $errors['otp-error'] = "You've entered an incorrect code!";
    }
}

// Handle change password form submission
if (isset($_POST['change-password'])) {
    $_SESSION['info'] = "";
    $password = mysqli_real_escape_string($con, $_POST['psw']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);

    // Check password confirmation
    if ($password !== $cpassword) {
        $errors['password'] = "Confirm password does not match!";
    } else {
        $code = 0;
        $email = $_SESSION['email']; // Get the email from the session
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $update_pass = "UPDATE users SET code = ?, password = ? WHERE email = ?";
        $stmt = mysqli_prepare($con, $update_pass);
        mysqli_stmt_bind_param($stmt, "iss", $code, $encpass, $email);
        $run_query = mysqli_stmt_execute($stmt);

        if ($run_query) {
            $info = "Your password has been changed. You can now login with your new password.";
            $_SESSION['info'] = $info;
            header('Location: password-changed.php');
        } else {
            $errors['db-error'] = "Failed to change your password!";
        }
    }
}

// Handle login now button click
if (isset($_POST['login-now'])) {
    // Generate the authentication cookie value
    $auth_cookie_value = generateAuthCookieValue();

    // Set the authentication cookie
    setcookie('auth_cookie', $auth_cookie_value, [
        'expires' => time() + 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict' // Added SameSite attribute
    ]);

    // Redirect the user to the authenticated area or home page
    header('Location: home.php');
    exit();
}
?>

