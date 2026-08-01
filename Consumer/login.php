<?php
session_start();
include("../db.php");

/*=========================================
    ALREADY LOGGED IN
=========================================*/

if(isset($_SESSION['consumer'])){

    header("Location: dashboard.php");
    exit();

}

/*=========================================
    VARIABLES
=========================================*/

$error = "";
$success = "";

/*=========================================
    SUCCESS MESSAGE
=========================================*/

if(isset($_SESSION['success'])){

    $success = '
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill me-2"></i>
        '.$_SESSION['success'].'
        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert"></button>
    </div>';

    unset($_SESSION['success']);

}

/*=========================================
    CONSUMER LOGIN
=========================================*/

if(isset($_POST['login'])){

    $consumer_no = trim($_POST['consumer_no']);
    $password    = trim($_POST['password']);

    if(empty($consumer_no) || empty($password)){

        $error='
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            Please enter Consumer Number and Password.
        </div>';

    }else{

        $stmt = mysqli_prepare($conn,"
            SELECT *
            FROM users
            WHERE consumer_no=?
            LIMIT 1
        ");

        mysqli_stmt_bind_param($stmt,"s",$consumer_no);

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result)==1){

            $user = mysqli_fetch_assoc($result);

            // Plain Password
            if($password == $user['password']){

                session_regenerate_id(true);

                $_SESSION['consumer']       = $user['consumer_no'];
                $_SESSION['consumer_name']  = $user['name'];
                $_SESSION['consumer_email'] = $user['email'];
                $_SESSION['consumer_mobile']= $user['mobile'];
                $_SESSION['meter_no']       = $user['meter_no'];
                $_SESSION['category']       = $user['category'];

                header("Location: dashboard.php");
                exit();

            }else{

                $error='
                <div class="alert alert-danger">
                    <i class="bi bi-x-circle-fill me-2"></i>
                    Invalid Password.
                </div>';

            }

        }else{

            $error='
            <div class="alert alert-danger">
                <i class="bi bi-person-x-fill me-2"></i>
                Consumer Number not found.
            </div>';

        }

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>APDCL Consumer Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

:root{

--primary:#0056b3;
--secondary:#1976d2;
--dark:#0B2C74;
--light:#eef5ff;
--white:#fff;

}

body{

background:#eef4fb;

overflow:hidden;

}

/*==========================
MAIN LAYOUT
==========================*/

.container-fluid{

height:100vh;

padding:0;

}

.row{

height:100%;

}

/*==========================
LEFT PANEL
==========================*/

.left-panel{

background:

linear-gradient(rgba(0,33,71,.55),
rgba(0,33,71,.65)),

url("../assets/images/consumer-bg.jpg");

background-size:cover;

background-position:center;

display:flex;

align-items:center;

justify-content:center;

padding:60px;

color:#fff;

}

.left-content{

max-width:700px;

animation:fadeLeft .8s;

}

.logo{

display:flex;

align-items:center;

margin-bottom:40px;

}

.logo img{

width:90px;

height:90px;

background:#fff;

padding:8px;

border-radius:50%;

margin-right:20px;

}

.logo h1{

font-size:52px;

font-weight:700;

margin:0;

}

.logo p{

margin:0;

opacity:.9;

}

.hero-title{

font-size:50px;

font-weight:700;

line-height:1.2;

margin-bottom:25px;

}

.hero-title span{

color:#ffd54f;

}

.hero-text{

font-size:19px;

line-height:32px;

margin-bottom:40px;

}

.feature{

display:flex;

gap:20px;

margin-bottom:20px;

padding:20px;

background:rgba(255,255,255,.12);

backdrop-filter:blur(12px);

border-radius:18px;

transition:.3s;

}

.feature:hover{

transform:translateY(-6px);

}

.feature i{

font-size:36px;

color:#ffd54f;

}

/*==========================
RIGHT PANEL
==========================*/

.right-panel{

display:flex;

justify-content:center;

align-items:center;

background:linear-gradient(135deg,#003c8f,#1565c0,#42a5f5);

padding:30px;

}

.login-card{

width:100%;

max-width:460px;

background:#fff;

border-radius:24px;

overflow:hidden;

box-shadow:0 25px 60px rgba(0,0,0,.25);

animation:fadeUp .8s;

}

.login-header{

text-align:center;

padding:35px;

background:linear-gradient(135deg,#0056b3,#1976d2);

color:#fff;

}

.login-header img{

width:90px;

height:90px;

background:#fff;

padding:8px;

border-radius:50%;

margin-bottom:15px;

}

.login-header h3{

font-weight:700;

margin:0;

}

.login-header p{

margin-top:8px;

opacity:.9;

}

.form-control{

height:56px;

border-radius:14px;

}

.input-group-text{

background:#0B2C74;

color:#fff;

border:none;

}

.btn-login{

height:56px;

border-radius:14px;

font-size:18px;

font-weight:600;

background:linear-gradient(135deg,#0056b3,#1976d2);

border:none;

color:#fff;

transition:.3s;

}

.btn-login:hover{

transform:translateY(-3px);

box-shadow:0 15px 35px rgba(0,86,179,.3);

}

.footer{

font-size:13px;

color:#666;

text-align:center;

margin-top:25px;

}

@keyframes fadeUp{

from{

opacity:0;

transform:translateY(40px);

}

to{

opacity:1;

transform:translateY(0);

}

}

@keyframes fadeLeft{

from{

opacity:0;

transform:translateX(-40px);

}

to{

opacity:1;

transform:translateX(0);

}

}

@media(max-width:992px){

.left-panel{

display:none;

}

.right-panel{

width:100%;

}

}

</style>

</head>

<body>

<div class="container-fluid">

<div class="row">

<!-- LEFT PANEL -->

<div class="col-lg-7 left-panel">

<div class="left-content">

<div class="logo">

<img src="../assets/images/logo-circle.png">

<div>

<h1>APDCL</h1>

<p>Assam Power Distribution Company Limited</p>

</div>

</div>

<h2 class="hero-title">

Powering Assam,

<span>Empowering Consumers</span>

</h2>

<p class="hero-text">

Welcome to the APDCL Consumer Portal.

Pay bills, register complaints,

track outages and manage your

electricity connection securely.

</p>

<div class="feature">

<i class="bi bi-lightning-charge-fill"></i>

<div>

<h5>Online Bill Payment</h5>

<p>Pay electricity bills securely from anywhere.</p>

</div>

</div>

<div class="feature">

<i class="bi bi-chat-left-text-fill"></i>

<div>

<h5>Complaint Tracking</h5>

<p>Monitor complaint status in real time.</p>

</div>

</div>

<div class="feature">

<i class="bi bi-bell-fill"></i>

<div>

<h5>Latest Notices</h5>

<p>Stay informed about outages and announcements.</p>

</div>

</div>

</div>

</div>

<!-- RIGHT PANEL -->

<div class="col-lg-5 right-panel">

<div class="login-card">

<div class="login-header">

<img src="../assets/images/logo-circle.png">

<h3>Consumer Login</h3>

<p>Sign in securely to your APDCL account</p>

</div>

<div class="p-4">

<?= $success ?>

<?= $error ?>

<form method="POST" autocomplete="off">
<!-- Consumer Number -->

<div class="mb-3">

<label class="form-label fw-semibold">

Consumer Number

</label>

<div class="input-group">

<span class="input-group-text">

<i class="bi bi-person-badge-fill"></i>

</span>

<input

type="text"

name="consumer_no"

class="form-control"

placeholder="Enter Consumer Number"

autocomplete="off"

spellcheck="false"

required>

</div>

</div>

<!-- Password -->

<div class="mb-3">

<label class="form-label fw-semibold">

Password

</label>

<div class="input-group">

<span class="input-group-text">

<i class="bi bi-lock-fill"></i>

</span>

<input

type="password"

id="password"

name="password"

class="form-control"

placeholder="Enter Password"

autocomplete="new-password"

required>

<button

type="button"

class="btn btn-outline-secondary"

onclick="togglePassword()">

<i class="bi bi-eye-fill" id="eyeIcon"></i>

</button>

</div>

</div>

<!-- Remember -->

<div class="d-flex justify-content-between align-items-center mb-4">

<div class="form-check">

<input

class="form-check-input"

type="checkbox"

id="remember"

autocomplete="off">

<label

class="form-check-label"

for="remember">

Remember Me

</label>

</div>

<a

href="forgot_password.php"

class="text-decoration-none">

Forgot Password?

</a>

</div>

<!-- Login Button -->

<div class="d-grid">

<button

type="submit"

name="login"

class="btn btn-login">

<i class="bi bi-shield-lock-fill me-2"></i>

Continue to OTP

</button>

</div>

</form>

<hr class="my-4">

<div class="d-grid">

<a

href="../index.php"

class="btn btn-outline-primary btn-lg">

<i class="bi bi-house-door-fill me-2"></i>

Back to Home

</a>

</div>

<div class="footer">

© <?= date("Y") ?>

APDCL Consumer Portal

<br>

Internship Demo Project

</div>

</div>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

function togglePassword(){

    const password=document.getElementById("password");
    const eye=document.getElementById("eyeIcon");

    if(password.type==="password"){

        password.type="text";
        eye.classList.replace("bi-eye-fill","bi-eye-slash-fill");

    }else{

        password.type="password";
        eye.classList.replace("bi-eye-slash-fill","bi-eye-fill");

    }

}

window.onload=function(){

    document.querySelector("form").reset();

};

</script>

</body>

</html>
