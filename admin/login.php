<?php
session_start();
include("../db.php");

/*=========================================
    ALREADY LOGGED IN
=========================================*/

if (isset($_SESSION['logged_in'])) {
    header("Location: dashboard.php");
    exit();
}

$message = "";

/*=========================================
    PASSWORD RESET MESSAGE
=========================================*/

if (isset($_SESSION['success'])) {

    $message = '
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill"></i>
        '.$_SESSION['success'].'
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>';

    unset($_SESSION['success']);
}

/*=========================================
    LOGIN
=========================================*/

if(isset($_POST['login'])){

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if(empty($username) || empty($password)){

        $message='
        <div class="alert alert-danger">
            Please enter Username and Password.
        </div>';

    }else{

        $stmt=mysqli_prepare($conn,"
            SELECT *
            FROM admin
            WHERE username=?
            AND status='Active'
            LIMIT 1
        ");

        mysqli_stmt_bind_param($stmt,"s",$username);
        mysqli_stmt_execute($stmt);

        $result=mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result)==1){

            $admin=mysqli_fetch_assoc($result);

            if($password==$admin['password']){

                session_regenerate_id(true);

                $_SESSION['logged_in']=true;
                $_SESSION['admin']=$admin['username'];
                $_SESSION['admin_id']=$admin['id'];
                $_SESSION['username']=$admin['username'];
                $_SESSION['name']=$admin['name'];
                $_SESSION['role']=$admin['role'];
                $_SESSION['zone']=$admin['zone'];
                $_SESSION['circle']=$admin['circle'];
                $_SESSION['sub_division']=$admin['sub_division'];
                $_SESSION['photo']=$admin['photo'];

                $_SESSION['dashboard_type']=($admin['role']=="Super Admin") ? "super" : "sde";

                header("Location: dashboard.php");
                exit();

            }else{

                $message='
                <div class="alert alert-danger">
                    Invalid Password.
                </div>';

            }

        }else{

            $message='
            <div class="alert alert-danger">
                Username not found or account is inactive.
            </div>';

        }

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>APDCL Admin Login</title>

<!-- Bootstrap -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<!-- Google Font -->

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

:root{

    --primary:#0056b3;
    --secondary:#0d6efd;
    --yellow:#ffc107;
    --white:#ffffff;
    --dark:#0d1b2a;

}

body{

    font-family:'Poppins',sans-serif;
    overflow:hidden;
    background:#000;

}

.container-fluid{

    padding:0;
    margin:0;

}

/* ==========================
LEFT PANEL
========================== */

.left-side{

    position:relative;

    height:100vh;

    background:url("../assets/images/apdcl-bg.jpg");

    background-size:cover;

    background-position:center;

    overflow:hidden;

}

.left-side::before{

    content:"";

    position:absolute;

    inset:0;

    background:linear-gradient(
    rgba(0,42,92,.70),
    rgba(0,70,140,.65));

}

.left-content{

    position:relative;

    z-index:5;

    height:100%;

    display:flex;

    flex-direction:column;

    justify-content:center;

    align-items:center;

    color:#fff;

    text-align:center;

    padding:60px;

}

.left-logo{

    width:130px;

    height:130px;

    background:#fff;

    border-radius:50%;

    padding:10px;

    margin-bottom:30px;

    box-shadow:0 15px 35px rgba(0,0,0,.35);

}

.left-content h1{

    font-size:46px;

    font-weight:700;

    margin-bottom:10px;

}

.left-content h4{

    font-weight:400;

    margin-bottom:30px;

    opacity:.95;

}

.left-content p{

    font-size:18px;

    line-height:32px;

    max-width:600px;

    opacity:.95;

}

.feature-box{

    display:grid;

    grid-template-columns:repeat(2,1fr);

    gap:25px;

    margin-top:50px;

    width:100%;

    max-width:620px;

}

.feature{

    background:rgba(255,255,255,.12);

    backdrop-filter:blur(10px);

    border-radius:18px;

    padding:25px;

    transition:.35s;

    border:1px solid rgba(255,255,255,.18);

}

.feature:hover{

    transform:translateY(-8px);

    background:rgba(255,255,255,.18);

}

.feature i{

    font-size:38px;

    color:#ffd54f;

    margin-bottom:15px;

}

.feature h5{

    margin-bottom:8px;

    font-weight:600;

}

.feature small{

    color:#f1f1f1;

}

/* ==========================
RIGHT PANEL
========================== */

.right-side{

    height:100vh;

    background:#f5f8fc;

    display:flex;

    justify-content:center;

    align-items:center;

    padding:40px;

}

.login-card{

    width:100%;

    max-width:470px;

    background:rgba(255,255,255,.95);

    border-radius:25px;

    padding:45px;

    box-shadow:0 20px 50px rgba(0,0,0,.18);

    animation:fadeIn .8s ease;

}

@keyframes fadeIn{

    from{

        opacity:0;

        transform:translateY(40px);

    }

    to{

        opacity:1;

        transform:translateY(0);

    }

}

.login-logo{

    width:95px;

    height:95px;

    background:#fff;

    border-radius:50%;

    padding:6px;

    box-shadow:0 10px 25px rgba(0,0,0,.15);

}
.login-title{

    font-size:30px;

    font-weight:700;

    color:#003366;

    margin-top:20px;

}

.login-subtitle{

    color:#6c757d;

    margin-bottom:30px;

}

.form-label{

    font-weight:600;

    color:#333;

    margin-bottom:8px;

}

.input-group{

    margin-bottom:20px;

}

.input-group-text{

    background:#eef4fb;

    border:1px solid #ced4da;

    color:#0056b3;

    width:50px;

    justify-content:center;

}

.form-control{

    height:50px;

    border:1px solid #ced4da;

    border-left:none;

    font-size:15px;

}

.form-control:focus{

    box-shadow:none;

    border-color:#0d6efd;

}

.btn-login{

    width:100%;

    height:52px;

    border:none;

    border-radius:10px;

    background:linear-gradient(135deg,#0056b3,#0d6efd);

    color:#fff;

    font-size:17px;

    font-weight:600;

    transition:.3s;

}

.btn-login:hover{

    transform:translateY(-3px);

    box-shadow:0 12px 25px rgba(13,110,253,.35);

}

.form-check-label{

    font-size:14px;

}

.forgot-link{

    text-decoration:none;

    font-size:14px;

    font-weight:500;

}

.forgot-link:hover{

    text-decoration:underline;

}

.footer-text{

    margin-top:25px;

    text-align:center;

    color:#777;

    font-size:13px;

}

@media(max-width:991px){

.left-side{

display:none;

}

.right-side{

width:100%;

padding:20px;

}

.login-card{

max-width:450px;

padding:35px;

}

}

@media(max-width:576px){

.login-card{

padding:25px;

}

.login-title{

font-size:24px;

}

}

</style>

</head>
<body>

<div class="container-fluid">

<div class="row g-0">

<!-- ===========================
LEFT PANEL
=========================== -->

<div class="col-lg-7 left-side">

<div class="left-content">

<img
src="../assets/images/logo-circle.png"
class="left-logo"
alt="APDCL Logo">

<h1>

APDCL Admin Portal

</h1>

<h4>

Assam Power Distribution Company Limited

</h4>

<p>

⚡ Powering Assam • Empowering People

</p>

<p>

Manage Consumers, Meter Readings, Electricity Bills,
Payments, Complaints, Outages and Reports
through one secure administration portal.

</p>

<div class="feature-box">

<div class="feature">

<i class="bi bi-shield-lock-fill"></i>

<h5>

Secure Login

</h5>

<small>

Encrypted Authentication

</small>

</div>

<div class="feature">

<i class="bi bi-speedometer2"></i>

<h5>

Smart Dashboard

</h5>

<small>

Real-time Monitoring

</small>

</div>

<div class="feature">

<i class="bi bi-lightning-charge-fill"></i>

<h5>

Billing System

</h5>

<small>

Electricity Billing

</small>

</div>

<div class="feature">

<i class="bi bi-bar-chart-fill"></i>

<h5>

Analytics

</h5>

<small>

Reports & Statistics

</small>

</div>

</div>

</div>

</div>

<!-- ===========================
RIGHT PANEL
=========================== -->

<div class="col-lg-5 right-side">

<div class="login-card">

<div class="text-center">

<img
src="../assets/images/logo-circle.png"
class="login-logo"
alt="Logo">

<h2 class="login-title">

Admin Login

</h2>

<p class="login-subtitle">

Super Admin & SDE Portal

</p>

</div>

<?= $message ?>

<form method="POST">

<div class="mb-3">

<label class="form-label">

Username

</label>

<div class="input-group">

<span class="input-group-text">

<i class="bi bi-person-fill"></i>

</span>

<input
type="text"
name="username"
class="form-control"
placeholder="Enter Username"
required>

</div>

</div>

<div class="mb-3">

<label class="form-label">

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
required>

<button
type="button"
class="btn btn-outline-secondary"
onclick="togglePassword()">

<i
id="eye"
class="bi bi-eye"></i>

</button>

</div>

</div>
<div class="d-flex justify-content-between align-items-center mb-4">

    <div class="form-check">

        <input
            class="form-check-input"
            type="checkbox"
            id="remember">

        <label
            class="form-check-label"
            for="remember">

            Remember Me

        </label>

    </div>

    <a
        href="forgot_password.php"
        class="forgot-link">

        Forgot Password?

    </a>

</div>

<button type="submit" name="login" class="btn-login">
    <i class="bi bi-box-arrow-in-right me-2"></i>
    Login
</button>

<div class="text-center mt-3">
    <a href="../index.php" class="text-primary fw-semibold text-decoration-none">
        <i class="bi bi-house-door-fill"></i> Back to Home
    </a>
</div>

</form>

<div class="footer-text">

    © <?= date("Y") ?>

    APDCL Electricity Billing Management System

</div>

</div>

</div>

</div>

</div>