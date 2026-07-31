<?php
session_start();
require_once "db.php";

$message="";

if(isset($_POST['reset'])){

$password=$_POST['password'];
$confirm=$_POST['confirm'];

if($password!=$confirm){

$message="Passwords do not match.";

}else{

$consumer=$_SESSION['consumer_no'];

mysqli_query($conn,"
UPDATE users
SET password='$password'
WHERE consumer_no='$consumer'
");

session_destroy();

$message="Password Changed Successfully.";

}

}
?>

<!DOCTYPE html>

<html>

<head>

<title>Reset Password</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="container mt-5">

<div class="card col-md-6 mx-auto">

<div class="card-header bg-primary text-white">

Reset Password

</div>

<div class="card-body">

<?php

if($message!=""){

echo "<div class='alert alert-success'>$message</div>";

}

?>

<form method="POST">

<label>New Password</label>

<input
type="password"
name="password"
class="form-control"
required>

<br>

<label>Confirm Password</label>

<input
type="password"
name="confirm"
class="form-control"
required>

<br>

<button
class="btn btn-primary w-100"
name="reset">

Reset Password

</button>

</form>

</div>

</div>

</div>

</body>

</html>