<?php
session_start();
include("../db.php");

$message = "";

if(isset($_POST['verify'])){

    $username = trim($_POST['username']);
    $mobile   = trim($_POST['mobile']);

    $stmt = mysqli_prepare($conn,
        "SELECT id,name,username
         FROM admin
         WHERE username=? AND mobile=?
         LIMIT 1");

    mysqli_stmt_bind_param($stmt,"ss",$username,$mobile);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result)==1){

        $user = mysqli_fetch_assoc($result);

        $_SESSION['reset_admin_id'] = $user['id'];
        $_SESSION['reset_username'] = $user['username'];

        header("Location: reset_password.php");
        exit();

    }else{

        $message = "
        <div class='alert alert-danger'>
            Username and Mobile Number do not match.
        </div>";

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Forgot Password - APDCL</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{

background:linear-gradient(135deg,#0d47a1,#1565c0,#42a5f5);

min-height:100vh;

display:flex;

justify-content:center;

align-items:center;

font-family:Segoe UI,sans-serif;

}

.card{

width:430px;

border:none;

border-radius:20px;

box-shadow:0 20px 40px rgba(0,0,0,.30);

overflow:hidden;

}

.card-header{

background:#0d47a1;

color:#fff;

text-align:center;

padding:25px;

}

.card-body{

padding:30px;

}

.form-control{

height:48px;

}

.btn-primary{

height:48px;

}

</style>

</head>

<body>

<div class="card">

<div class="card-header">

<h3>

<i class="bi bi-key-fill"></i>

Forgot Password

</h3>

<p class="mb-0">

Verify your account

</p>

</div>

<div class="card-body">

<?= $message ?>

<form method="post">

<div class="mb-3">

<label>

Username

</label>

<input
type="text"
name="username"
class="form-control"
required>

</div>

<div class="mb-4">

<label>

Registered Mobile Number

</label>

<input
type="text"
name="mobile"
class="form-control"
maxlength="10"
required>

</div>

<button
type="submit"
name="verify"
class="btn btn-primary w-100">

Verify

</button>

</form>

<hr>

<div class="text-center">

<a href="login.php">

<i class="bi bi-arrow-left"></i>

Back to Login

</a>

</div>

</div>

</div>

</body>

</html>