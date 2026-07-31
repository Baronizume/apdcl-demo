<?php
session_start();
include("../db.php");

$message="";

if(isset($_POST['reset'])){

    $username=mysqli_real_escape_string($conn,$_POST['username']);
    $password=mysqli_real_escape_string($conn,$_POST['password']);

    $check=mysqli_query($conn,"
    SELECT * FROM admin
    WHERE username='$username'
    ");

    if(mysqli_num_rows($check)>0){

        mysqli_query($conn,"
        UPDATE admin
        SET password='$password'
        WHERE username='$username'
        ");

        $message="<div class='alert alert-success'>
        Password Updated Successfully.
        <br><a href='login.php'>Login Now</a>
        </div>";

    }else{

        $message="<div class='alert alert-danger'>
        Username Not Found.
        </div>";

    }

}
?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Forgot Password</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-5">

<div class="card">

<div class="card-header bg-primary text-white">

<h3>Admin Forgot Password</h3>

</div>

<div class="card-body">

<?= $message; ?>

<form method="POST">

<div class="mb-3">

<label>Username</label>

<input
type="text"
name="username"
class="form-control"
required>

</div>

<div class="mb-3">

<label>New Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<button
class="btn btn-primary"
name="reset">

Reset Password

</button>

<a href="login.php" class="btn btn-secondary">

Back

</a>

</form>

</div>

</div>

</div>

</div>

</div>

</body>

</html>