<?php
require_once "controllerUserData.php";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Retrieve the form data
    $customer_name = $_POST["customer_name"];
    $telephone = $_POST["telephone"];
    $email = $_POST["email"];
    $flavor = $_POST["flavours"];
    $size = $_POST["size"];
    $quantity = $_POST["quantity"];
    $total_price = calculateTotalPrice($_POST["size"], $_POST["quantity"]);

    // Database connection configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "userdetails";

    // Create a new PDO instance
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepare the SQL statement
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, telephone, email, flavor, size, quantity, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Bind the parameters to the statement
    $stmt->bindParam(1, $customer_name);
    $stmt->bindParam(2, $telephone);
    $stmt->bindParam(3, $email);
    $stmt->bindParam(4, $flavor);
    $stmt->bindParam(5, $size);
    $stmt->bindParam(6, $quantity);
    $stmt->bindParam(7, $total_price);

    // Execute the statement
    $stmt->execute();

    // Close the database connection
    $conn = null;
}

// Function to calculate the total price based on the size and quantity
function calculateTotalPrice($size, $quantity) {
    $price = 0;

    if ($size === 'Small') {
        $price = 6.00;
    } elseif ($size === 'Medium') {
        $price = 12.00;
    } elseif ($size === 'Large') {
        $price = 18.00;
    }

    return $price * $quantity;
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
    <a class="navbar-brand" href="#">Payment</a>
    <button type="button" class="btn btn-light"><a href="logout-user.php">Logout</a></button>
    </nav>
    <br>

    <center>
	<table>
		<tr>
			<td>
                <form id="paymentForm"  action="process_payment.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <h2>Total Price: RM <?php echo $total_price ?>.00</h2><br>
                    <label for="cardNumber">Card Number:</label>
                    <input type="text" id="cardNumber" name="cardNumber" required pattern="\d{16}" maxlength="16"><br>
                    <br>
                    <label for="expiryDate">Expiration Date:</label>
                    <input type="text" id="expiryDate" name="expiryDate" required pattern="(0[1-9]|1[0-2])\/\d{2}" onchange="checkExpirationDate()">
                    <br><br>
                    <label for="cvv">CVV:</label>
                    <input type="text" id="cvv" name="cvv" required pattern="\d{3}">
                    <br><br>
                    <input type="submit" value="Submit"><br><br>
                </form>
            </td>
        </tr>
    </table>

    <script>
        function checkExpirationDate() {
            var today = new Date();
            var enteredDate = document.getElementById("expiryDate").value;
            
            var enteredMonth = parseInt(enteredDate.substr(0, 2));
            var enteredYear = parseInt(enteredDate.substr(3,2));

            var currentMonth = today.getMonth() + 1; // Adding 1 since getMonth() returns zero-based month
            var currentYear = today.getFullYear();
            currentYear = currentYear.toString().substr(-2);

            // Check if the entered date is in the past
            if (enteredYear < currentYear || (enteredYear == currentYear && enteredMonth < currentMonth)) {
                alert("Expiration date should be a future date.");
                document.getElementById("expiryDate").value = ""; // Clear the invalid value
            }
        }

    </script>
</body>
</html>