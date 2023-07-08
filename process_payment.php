<?php
require_once "controllerUserData.php";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate the CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        // CSRF token is missing or invalid, handle the error
        // e.g., display an error message or redirect to an error page
        exit("Invalid CSRF token");
    }

    // Retrieve the form data
    $cardNumber = $_POST["cardNumber"];
    $expiryDate = $_POST["expiryDate"];
    $cvv = $_POST["cvv"];

    // Generate a random transaction ID
    $transactionId = generateTransactionId();

    // Database connection configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "userdetails";

    // Create a new PDO instance
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Encryption settings
    $encryptionKey = "Pizza@123"; // Replace with your own encryption key
    $encryptionMethod = "AES-256-CBC"; // Replace with a valid encryption algorithm and mode

    // Encrypt the sensitive data
    $encryptedCardNumber = openssl_encrypt($cardNumber, $encryptionMethod, $encryptionKey, 0, "Pizzaisdelicious");
    $encryptedExpiryDate = openssl_encrypt($expiryDate, $encryptionMethod, $encryptionKey, 0, "Pizzaisdelicious");
    $encryptedCVV = openssl_encrypt($cvv, $encryptionMethod, $encryptionKey, 0, "Pizzaisdelicious");

    $stmt = $conn->prepare("INSERT INTO payments (card_number, expiry_date, cvv, transaction_id) VALUES (?, ?, ?, ?) ");

    // Bind the parameters
    $stmt->bindParam(1, $encryptedCardNumber);
    $stmt->bindParam(2, $encryptedExpiryDate);
    $stmt->bindParam(3, $encryptedCVV);
    $stmt->bindParam(4, $transactionId);

    // Execute the statement
    $stmt->execute();

    // Close the database connection
    $conn = null;
    }

    // Function to generate a random transaction ID
    function generateTransactionId() {
        $characters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $transactionId = "";
        for ($i = 0; $i < 10; $i++) {
            $randomIndex = rand(0, strlen($characters) - 1);
            $transactionId .= $characters[$randomIndex];
        }
        return $transactionId;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $fetch_info['name'] ?> | Home</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    @import url('https://fonts.googleapis.com/css?family=Poppins:400,500,600,700&display=swap');

    nav{
        padding-left: 100px!important;
        padding-right: 100px!important;
        background: #fd7e14;
        font-family: 'Poppins', sans-serif;
    } 
    nav a.navbar-brand{
        color: black;
        font-size: 30px!important;
        font-weight: 500;
    }
    button a{
        color: #fd7e14;
        font-weight: 500;
    }
    button a:hover{
        text-decoration: none;
    }
    h1{
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        text-align: center;
        transform: translate(-50%, -50%);
        font-size: 50px;
        font-weight: 600;
    }
    </style>
</head>
<body>
<?php 
      if(isset($_SESSION["email"]))  
      {  
           if((time() - $_SESSION['last_login_timestamp']) > 600) // Automatic logout after 10 minutes
           {  
                header("location:logout-user.php");  
           }  
           else  
           {  
                $_SESSION['last_login_timestamp'] = time();  
           }  
      }  
      else  
      {  
           header('location:login-user.php');  
      }  
      ?>  
    <nav class="navbar">
    <a class="navbar-brand" href="#">Payment Result</a>
    <button type="button" class="btn btn-light"><a href="logout-user.php">Logout</a></button>
    </nav>
    <br>

    <center>
    <h2>Payment successful<br>Transaction ID:  <?php echo $transactionId ?></h2><br>


    <script>
    </script>
</body>
</html>


