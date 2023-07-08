<?php 
require_once "controllerUserData.php"; 

// Security Headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
// header("Content-Security-Policy: default-src 'self'");

$email = $_SESSION['email'];
$password = $_SESSION['password'];
if($email != false && $password != false){
    // $sql = "SELECT * FROM users WHERE email = '$email'";
    // $run_Sql = mysqli_query($con, $sql);
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $run_Sql = mysqli_stmt_get_result($stmt);
    if($run_Sql){
        $fetch_info = mysqli_fetch_assoc($run_Sql);
        $status = $fetch_info['status'];
        $code = $fetch_info['code'];
        if($status == "verified"){
            if($code != 0){
                header('Location: reset-code.php');
            }
        }else{
            header('Location: user-otp.php');
        }
    }
}else{
    header('Location: login-user.php');
}
?>

<?php
if (isset($_SESSION["email"])) {
    if ((time() - $_SESSION['last_login_timestamp']) > 3600) { //timeout is 1 hour
        header("location: logout-user.php");
    } else {
        $_SESSION['last_login_timestamp'] = time();
    }
} else {
    header('location: login-user.php');
}

$sql = "SELECT * FROM orders";
$result = mysqli_query($con, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($fetch_info['name']); ?> | Home</title>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

</head>
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
    
    table {
    width: 90%;
    }

    table,th,td {
        border:1px solid black;

    }

    thead,tbody{
        text-align: center;
    }
    </style>
<body>
    <nav class="navbar">
    <a class="navbar-brand" href="#"><?php echo $fetch_info['name'] ?></a>
    <button type="button" class="btn btn-light"><a href="logout-user.php">Logout</a></button>
    </nav>
    <br>
    <center>
    <table class="order-table">
        <h3>Customer's Order</h3>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Name</th>
                <th>Telephone</th>
                <th>Email</th>
                <th>Flavor</th>
                <th>Size</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Order Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $orderId = $row['id'];
                    $customerName = $row['customer_name'];
                    $telephone = $row['telephone'];
                    $email = $row['email'];
                    $flavor = $row['flavor'];
                    $size = $row['size'];
                    $quantity = $row['quantity'];
                    $totalPrice = $row['total_price'];
                    $orderDate = $row['order_date'];
                    ?>
                    <tr>
                        <td><?php echo $orderId; ?></td>
                        <td><?php echo $customerName; ?></td>
                        <td><?php echo $telephone; ?></td>
                        <td><?php echo $email; ?></td>
                        <td><?php echo $flavor; ?></td>
                        <td><?php echo $size; ?></td>
                        <td><?php echo $quantity; ?></td>
                        <td><?php echo $totalPrice; ?></td>
                        <td><?php echo $orderDate; ?></td>
                    </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='9'>No orders found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    </center>
</body>

</html>

