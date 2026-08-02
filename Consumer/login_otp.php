<?php
session_start();

include("../db.php");

/*=========================================
    CHECK LOGIN OTP SESSION
=========================================*/

if(
    !isset($_SESSION['login_otp']) ||
    !isset($_SESSION['pending_consumer']) ||
    !isset($_SESSION['otp_expiry'])
){

    header("Location: login.php");
    exit();

}

$error="";
$success="";

/*=========================================
    DEMO OTP
=========================================*/

$demo_otp=$_SESSION['demo_otp'];

/*=========================================
    OTP EXPIRED
=========================================*/

if(time()>$_SESSION['otp_expiry']){

    session_unset();
    session_destroy();

    header("Location: login.php?expired=1");
    exit();

}

/*=========================================
    VERIFY LOGIN OTP
=========================================*/

if(isset($_POST['verify'])){

    $otp = trim($_POST['otp']);

    if(empty($otp)){

        $error='
        <div class="alert alert-danger">
            Please enter OTP.
        </div>';

    }elseif($otp != $_SESSION['login_otp']){

        $error='
        <div class="alert alert-danger">
            Invalid OTP.
        </div>';

    }else{

        // Get Consumer Number
        $consumer_no = $_SESSION['pending_consumer'];

        // Fetch Consumer Details
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

            session_regenerate_id(true);

            $_SESSION['consumer']        = $user['consumer_no'];
            $_SESSION['consumer_name']   = $user['name'];
            $_SESSION['consumer_email']  = $user['email'];
            $_SESSION['consumer_mobile'] = $user['mobile'];
            $_SESSION['meter_no']        = $user['meter_no'];
            $_SESSION['category']        = $user['category'];

            // Clear OTP Sessions
            unset($_SESSION['login_otp']);
            unset($_SESSION['otp_expiry']);
            unset($_SESSION['pending_consumer']);
            unset($_SESSION['demo_otp']);

            header("Location: dashboard.php");
            exit();

        }

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1.0">

<title>Login OTP | APDCL</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

*{

margin:0;
padding:0;
box-sizing:border-box;
font-family:Poppins,sans-serif;

}

body{

height:100vh;

display:flex;

justify-content:center;

align-items:center;

background:linear-gradient(135deg,#0B2C74,#1565d8,#42a5f5);

}

.otp-card{

width:100%;
max-width:480px;

background:#fff;

border-radius:25px;

overflow:hidden;

box-shadow:0 25px 50px rgba(0,0,0,.25);

}

.otp-header{

padding:35px;

text-align:center;

background:#0B2C74;

color:#fff;

}

.otp-header img{

width:90px;

background:#fff;

padding:8px;

border-radius:50%;

margin-bottom:15px;

}

.otp-body{

padding:35px;

}

.otp-icon{

font-size:65px;

color:#1565d8;

margin-bottom:20px;

}

.form-control{

height:60px;

font-size:24px;

text-align:center;

letter-spacing:8px;

border-radius:15px;

}

.btn-primary{

height:55px;

border-radius:15px;

font-weight:600;

}

.footer{

text-align:center;

margin-top:20px;

font-size:13px;

color:#777;

}

</style>

</head>

<body>

<div class="otp-card">

    <div class="otp-header">

        <img src="../assets/images/logo-circle.png">

        <h3>OTP Verification</h3>

        <p>Secure Consumer Authentication</p>

    </div>

    <div class="otp-body">

        <?php if(!empty($error)){ ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-triangle-fill me-2"></i>

            <?= $error ?>

        </div>

        <?php } ?>

        <div class="text-center mb-4">

            <i class="bi bi-shield-lock-fill text-primary"
               style="font-size:75px;"></i>

            <h2 class="fw-bold mt-3">

                Verify OTP

            </h2>

            <p class="text-muted">

                Enter the 6-digit verification code to continue.

            </p>

        </div>

        <!-- Demo OTP -->

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-body text-center">

                <h6 class="text-secondary">

                    Demo OTP

                </h6>

                <span class="badge bg-danger fs-2 px-4 py-3">

                    <?= $_SESSION['demo_otp']; ?>

                </span>

            </div>

        </div>

        <!-- Countdown -->

        <div class="alert alert-warning text-center">

            <i class="bi bi-clock-history"></i>

            OTP expires in

            <strong id="countdown">

                05:00

            </strong>

        </div>

        <form method="POST" autocomplete="off">

            <div class="mb-3">

                <label class="form-label fw-semibold">

                    Enter OTP

                </label>

                <input

                type="text"

                name="otp"

                id="otp"

                class="form-control text-center"

                maxlength="6"

                placeholder="000000"

                inputmode="numeric"

                autocomplete="one-time-code"

                required>

                <div class="form-text">

                    Please enter the 6-digit OTP.

                </div>

            </div>

            <div class="d-grid">

                <button

                id="verifyBtn"

                type="submit"

                name="verify"

                class="btn btn-primary btn-lg">

                    <i class="bi bi-shield-check me-2"></i>

                    Verify & Continue

                </button>

            </div>

        </form>

        <div class="text-center mt-4">

            <p class="text-muted">

                Didn't receive the OTP?

            </p>

            <a href="login.php"

               class="btn btn-outline-primary">

                <i class="bi bi-arrow-repeat me-2"></i>

                Resend OTP

            </a>

        </div>

        <div class="text-center mt-3">

            <a href="login.php"

               class="text-decoration-none">

                <i class="bi bi-arrow-left-circle-fill me-2"></i>

                Back to Consumer Login

            </a>

        </div>

        <div class="footer mt-4">

            <hr>

            <p class="mb-1">

                © <?= date("Y"); ?>

                <strong>APDCL Consumer Portal</strong>

            </p>

            <small class="text-muted">

                Internship Demo Project

            </small>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

const otp=document.getElementById("otp");

otp.focus();

otp.addEventListener("input",function(){

    this.value=this.value.replace(/\D/g,'');

});

// Countdown Timer

let timeLeft=300;

const timer=document.getElementById("countdown");

const verifyBtn=document.getElementById("verifyBtn");

const countdown=setInterval(function(){

    let minutes=Math.floor(timeLeft/60);

    let seconds=timeLeft%60;

    timer.innerHTML=

        String(minutes).padStart(2,'0')

        +":"

        +

        String(seconds).padStart(2,'0');

    if(timeLeft<=0){

        clearInterval(countdown);

        timer.innerHTML="Expired";

        verifyBtn.disabled=true;

        verifyBtn.innerHTML="OTP Expired";

        setTimeout(function(){

            alert("OTP has expired. Please login again.");

            window.location.href="login.php";

        },1000);

    }

    timeLeft--;

},1000);

</script>

</body>

</html>
