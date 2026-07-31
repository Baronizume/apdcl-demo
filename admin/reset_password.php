<?php
session_start();
include("../db.php");

if(
    !isset($_SESSION['reset_admin_id']) ||
    !isset($_SESSION['otp_verified'])
){
    header("Location: forgot_password.php");
    exit();
}

$message="";

if(isset($_POST['save'])){

    $password=trim($_POST['password']);
    $confirm=trim($_POST['confirm']);

    if(empty($password) || empty($confirm)){

        $message="<div class='alert alert-danger'>
        Please fill all fields.
        </div>";

    }elseif($password!=$confirm){

        $message="<div class='alert alert-danger'>
        Passwords do not match.
        </div>";

    }elseif(strlen($password)<5){

        $message="<div class='alert alert-warning'>
        Password should be at least 5 characters.
        </div>";

    }else{

        $stmt=mysqli_prepare($conn,
        "UPDATE admin SET password=? WHERE id=?");

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $password,
            $_SESSION['reset_admin_id']
        );

        if(mysqli_stmt_execute($stmt)){

    // Delete used OTPs
    mysqli_query($conn,"
    DELETE FROM otp_verification
    WHERE admin_id='$id'
    ");

    // Clear all reset sessions
    unset($_SESSION['reset_admin_id']);
    unset($_SESSION['reset_username']);
    unset($_SESSION['reset_mobile']);
    unset($_SESSION['otp_verified']);
    unset($_SESSION['demo_otp']);

    $_SESSION['success']="Password changed successfully.";

    header("Location: login.php");
    exit();

}else{

        }else{

            $message="<div class='alert alert-danger'>
            Unable to update password.
            </div>";

        }

    }

}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Reset Password</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI',sans-serif;
}

body{

height:100vh;

display:flex;

justify-content:center;

align-items:center;

background:linear-gradient(135deg,#0d47a1,#1565c0,#42a5f5);

overflow:hidden;

}

body:before{

content:"";

position:absolute;

width:400px;

height:400px;

background:rgba(255,255,255,.08);

border-radius:50%;

left:-120px;

top:-120px;

}

body:after{

content:"";

position:absolute;

width:500px;

height:500px;

background:rgba(255,255,255,.05);

border-radius:50%;

right:-180px;

bottom:-180px;

}

.card{

width:430px;

background:rgba(255,255,255,.15);

backdrop-filter:blur(18px);

border:1px solid rgba(255,255,255,.20);

border-radius:20px;

box-shadow:0 20px 45px rgba(0,0,0,.35);

overflow:hidden;

animation:fade .6s;

position:relative;

z-index:10;

}

@keyframes fade{

from{

opacity:0;

transform:translateY(30px);

}

to{

opacity:1;

transform:translateY(0);

}

}

.card-header{

background:rgba(255,255,255,.10);

padding:25px;

text-align:center;

color:#fff;

}

.card-header h3{

font-weight:700;

}

.card-body{

padding:30px;

}

.form-label{

color:#fff;

font-weight:600;

}

.form-control{

height:48px;

border-radius:10px;

}

.input-group-text{

background:#0d47a1;

color:#fff;

border:none;

}

.btn-success{

height:50px;

font-weight:600;

border-radius:10px;

}

#strength{

font-weight:600;

}

a{

color:#fff;

text-decoration:none;

}

a:hover{

text-decoration:underline;

}

</style>

</head>

<body>

<div class="card">

<div class="card-header">

<h3>

<i class="bi bi-shield-lock-fill"></i>

Reset Password

</h3>

<p>

<?= htmlspecialchars($_SESSION['reset_username']) ?>

</p>

</div>

<div class="card-body">

<?= $message ?>

<form method="post">

<div class="mb-3">

<label class="form-label">

New Password

</label>

<div class="input-group">

<input
type="password"
id="password"
name="password"
class="form-control"
required>

<button
class="btn btn-light"
type="button"
onclick="togglePassword('password','eye1')">

<i class="bi bi-eye-fill"
id="eye1"></i>

</button>

</div>

<div class="mt-2">

<small id="strength"></small>

</div>

</div>

<div class="mb-4">

<label class="form-label">

Confirm Password

</label>

<div class="input-group">

<input
type="password"
id="confirm"
name="confirm"
class="form-control"
required>

<button
class="btn btn-light"
type="button"
onclick="togglePassword('confirm','eye2')">

<i class="bi bi-eye-fill"
id="eye2"></i>

</button>

</div>

</div>

<button
type="submit"
name="save"
id="saveBtn"
class="btn btn-success w-100">

<i class="bi bi-check-circle-fill"></i>

Update Password

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

<script>

function togglePassword(id,eye){

let input=document.getElementById(id);

let icon=document.getElementById(eye);

if(input.type==="password"){

input.type="text";

icon.classList.replace("bi-eye-fill","bi-eye-slash-fill");

}else{

input.type="password";

icon.classList.replace("bi-eye-slash-fill","bi-eye-fill");

}

}

document.getElementById("password").addEventListener("keyup",function(){

let p=this.value;

let s=document.getElementById("strength");

if(p.length<5){

s.innerHTML="<span class='text-danger'>Weak Password</span>";

}else if(p.length<8){

s.innerHTML="<span class='text-warning'>Medium Password</span>";

}else{

s.innerHTML="<span class='text-success'>Strong Password</span>";

}

});

document.querySelector("form").addEventListener("submit",function(){

document.getElementById("saveBtn").innerHTML="<span class='spinner-border spinner-border-sm'></span> Updating...";

document.getElementById("saveBtn").disabled=true;

});

window.onload=function(){

document.getElementById("password").focus();

};

</script>

</body>

</html>