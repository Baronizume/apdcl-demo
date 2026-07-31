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

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
      rel="stylesheet">

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
      rel="stylesheet">

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
    --orange:#ffc107;
    --dark:#0b3d91;
    --white:#ffffff;

}

body{

    background:#eef4fb;
    overflow:hidden;

}

.main-wrapper{

    display:flex;
    width:100%;
    height:100vh;

}

/*==================================
LEFT PANEL
==================================*/

.left-panel{

    width:58%;
    position:relative;

    background:
    linear-gradient(rgba(0,40,95,.45),
    rgba(0,40,95,.55)),
    url("../assets/images/consumer-bg.jpg");

    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;

    display:flex;
    justify-content:center;
    align-items:center;

    overflow:hidden;

}

.left-content{

    width:82%;
    color:#fff;
    position:relative;
    z-index:10;

}

.logo-row{

    display:flex;
    align-items:center;
    margin-bottom:40px;

}

.logo-row img{

    width:95px;
    height:95px;
    background:#fff;
    padding:8px;
    border-radius:50%;
    margin-right:20px;
    box-shadow:0 10px 30px rgba(0,0,0,.25);

}

.logo-row h1{

    font-size:58px;
    font-weight:700;
    margin:0;

}

.logo-row h6{

    margin-top:6px;
    font-weight:400;
    letter-spacing:1px;
    opacity:.95;

}

.hero-title{

    font-size:56px;
    font-weight:700;
    line-height:1.2;
    margin-bottom:25px;

}

.hero-title span{

    color:#ffd54f;

}

.hero-text{

    font-size:20px;
    line-height:34px;
    max-width:700px;
    margin-bottom:50px;

}
/*==================================
FEATURE GRID
==================================*/

.feature-grid{

    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:25px;

}

.feature-card{

    background:rgba(255,255,255,.14);
    backdrop-filter:blur(12px);
    border:1px solid rgba(255,255,255,.18);

    border-radius:18px;
    padding:28px;

    transition:.35s;
    cursor:pointer;

}

.feature-card:hover{

    transform:translateY(-8px);
    background:rgba(255,255,255,.22);

}

.feature-card i{

    font-size:40px;
    color:#ffd54f;
    display:block;
    margin-bottom:15px;

}

.feature-card h5{

    font-weight:600;
    margin-bottom:10px;

}

.feature-card p{

    margin:0;
    font-size:14px;
    opacity:.9;

}

/*==================================
RIGHT PANEL
==================================*/

.right-panel{

    width:42%;

    display:flex;
    justify-content:center;
    align-items:center;

    padding:40px;

    background:linear-gradient(
    135deg,
    #003c8f,
    #1565c0,
    #42a5f5);

}

.login-card{

    width:100%;
    max-width:470px;

    background:rgba(255,255,255,.95);
    backdrop-filter:blur(18px);

    border-radius:24px;
    overflow:hidden;

    box-shadow:0 25px 60px rgba(0,0,0,.25);

    animation:fadeUp .8s ease;

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

.login-header{

    padding:35px;
    text-align:center;

}

.login-header img{

    width:95px;
    height:95px;

    background:#fff;
    padding:8px;

    border-radius:50%;

    box-shadow:0 10px 25px rgba(0,0,0,.15);

}

.login-header h3{

    margin-top:20px;
    font-weight:700;
    color:#003c8f;

}

.login-header p{

    margin-top:8px;
    color:#666;

}

.form-control{

    height:55px;
    border-radius:12px;

}

.form-control:focus{

    border-color:#1976d2;
    box-shadow:0 0 12px rgba(25,118,210,.2);

}

.input-group-text{

    background:#0d47a1;
    color:#fff;
    border:none;

}

.btn-primary{

    height:55px;

    border:none;
    border-radius:12px;

    font-size:18px;
    font-weight:600;

    background:linear-gradient(
    135deg,
    #0056b3,
    #1976d2);

    transition:.3s;

}

.btn-primary:hover{

    transform:translateY(-3px);

    box-shadow:0 15px 35px rgba(25,118,210,.35);

}

.footer{

    text-align:center;
    font-size:13px;
    margin-top:25px;
    color:#666;

}

/*==================================
RESPONSIVE
==================================*/

@media(max-width:991px){

.left-panel{

display:none;

}

.right-panel{

width:100%;

}

.login-card{

max-width:520px;

}

}

@media(max-width:576px){

.right-panel{

padding:20px;

}

.login-card{

border-radius:18px;

}

}

</style>

</head>

<body>

<div class="main-wrapper">
<!-- =====================================
LEFT PANEL
===================================== -->

<div class="left-panel">

    <div class="left-content">

        <!-- Logo -->

        <div class="logo-row">

            <img src="../assets/images/logo-circle.png"
                 alt="APDCL Logo">

            <div>

                <h1>APDCL</h1>

                <h6>
                    ASSAM POWER DISTRIBUTION<br>
                    COMPANY LIMITED
                </h6>

            </div>

        </div>

        <!-- Hero Text -->

        <h2 class="hero-title">

            Powering Assam,<br>

            <span>Empowering Consumers</span>

        </h2>

        <p class="hero-text">

            Welcome to the APDCL Consumer Portal.

            Access your electricity account securely,
            pay bills online, submit complaints,
            monitor consumption and receive important
            service updates anytime.

        </p>

        <!-- Features -->

        <div class="feature-grid">

            <div class="feature-card">

                <i class="bi bi-lightning-charge-fill"></i>

                <h5>Online Bill Payment</h5>

                <p>

                    Pay electricity bills securely
                    from anywhere.

                </p>

            </div>

            <div class="feature-card">

                <i class="bi bi-speedometer2"></i>

                <h5>Meter Reading</h5>

                <p>

                    View monthly consumption
                    and meter history.

                </p>

            </div>

            <div class="feature-card">

                <i class="bi bi-chat-left-text-fill"></i>

                <h5>Complaint Service</h5>

                <p>

                    Register complaints and
                    track their live status.

                </p>

            </div>

            <div class="feature-card">

                <i class="bi bi-bell-fill"></i>

                <h5>Latest Notices</h5>

                <p>

                    Stay informed with outage
                    alerts and announcements.

                </p>

            </div>

        </div>

    </div>

</div>
<!-- =====================================
RIGHT PANEL
===================================== -->

<div class="right-panel">

    <div class="login-card">

        <div class="login-header">

            <img src="../assets/images/logo-circle.png"
                 alt="APDCL Logo">

            <h3>Consumer Login</h3>

            <p>
                Sign in to access your APDCL Consumer Account
            </p>

        </div>

        <div class="card-body p-4">

            <?= $success ?>

            <?= $error ?>

            <form method="POST">

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
                            required>

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            onclick="togglePassword()">

                            <i id="eyeIcon"
                               class="bi bi-eye-fill"></i>

                        </button>

                    </div>

                </div>

                <!-- Remember -->

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

                    <a href="forgot_password.php"
                       class="text-decoration-none">

                        Forgot Password?

                    </a>

                </div>

                <!-- Login -->

                <div class="d-grid mb-3">

                    <button
                        type="submit"
                        name="login"
                        class="btn btn-primary">

                        <i class="bi bi-box-arrow-in-right me-2"></i>

                        Login

                    </button>

                </div>

            </form>

            <hr class="my-4">

            <div class="d-grid">

                <a href="../index.php"
                   class="btn btn-outline-primary btn-lg">

                    <i class="bi bi-house-door-fill me-2"></i>

                    Back to Home

                </a>

            </div>

            <div class="footer">

                © <?= date("Y") ?>

                APDCL Consumer Portal

            </div>

        </div>

    </div>

</div>

</div>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/*=========================================
SHOW / HIDE PASSWORD
=========================================*/

function togglePassword(){

    const password = document.getElementById("password");
    const eye = document.getElementById("eyeIcon");

    if(password.type === "password"){

        password.type = "text";
        eye.classList.remove("bi-eye-fill");
        eye.classList.add("bi-eye-slash-fill");

    }else{

        password.type = "password";
        eye.classList.remove("bi-eye-slash-fill");
        eye.classList.add("bi-eye-fill");

    }

}

/*=========================================
SMOOTH LOGIN CARD ANIMATION
=========================================*/

window.addEventListener("load",function(){

    const card=document.querySelector(".login-card");

    card.style.opacity="0";
    card.style.transform="translateY(35px)";

    setTimeout(function(){

        card.style.transition="all .8s ease";
        card.style.opacity="1";
        card.style.transform="translateY(0)";

    },150);

});

/*=========================================
FEATURE CARD HOVER EFFECT
=========================================*/

document.querySelectorAll(".feature-card").forEach(function(card){

    card.addEventListener("mouseenter",function(){

        card.style.transform="translateY(-8px) scale(1.02)";

    });

    card.addEventListener("mouseleave",function(){

        card.style.transform="translateY(0) scale(1)";

    });

});

/*=========================================
LOGIN BUTTON EFFECT
=========================================*/

const loginBtn=document.querySelector(".btn-primary");

loginBtn.addEventListener("mouseenter",function(){

    loginBtn.style.transform="translateY(-3px)";

});

loginBtn.addEventListener("mouseleave",function(){

    loginBtn.style.transform="translateY(0)";

});

</script>

</body>
</html>