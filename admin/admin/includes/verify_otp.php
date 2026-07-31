<?php
session_start();

$message="";

if(isset($_POST['verify'])){

    if($_POST['otp']==$_SESSION['otp']){

        header("Location: reset_password.php");
        exit();

    }else{

        $message="Invalid OTP";

    }

}
?>

<!DOCTYPE html>

<html>

<head>

<title>Verify OTP</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<div class="card col-md-5 mx-auto">

<div class="card-header bg-success text-white">

OTP Verification

</div>

<div class="card-body">

<?php

if($message!=""){

echo "<div class='alert alert-danger'>$message</div>";

}

?>

<form method="POST">

<label>Enter OTP</label>

<input
type="text"
name="otp"
class="form-control"
required>

<br>

<button
class="btn btn-success w-100"
name="verify">

Verify OTP

</button>

</form>

</div>

</div>

</div>

</body>

</html>