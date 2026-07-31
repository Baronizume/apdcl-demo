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

if(isset($_POST['verify'])){

    $otp = trim($_POST['otp']);

    if(empty($otp)){

        $message="<div class='alert alert-danger'>
        Please enter OTP.
        </div>";

    }else{

        $admin_id = $_SESSION['reset_admin_id'];

        $stmt = mysqli_prepare($conn,"
            SELECT *
            FROM otp_verification
            WHERE admin_id=?
            AND otp=?
            AND verified=0
            ORDER BY id DESC
            LIMIT 1
        ");

        mysqli_stmt_bind_param($stmt,"is",$admin_id,$otp);

        mysqli_stmt_execute($stmt);

        $result=mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result)==1){

            $row=mysqli_fetch_assoc($result);

            if(strtotime($row['expires_at']) < time()){

                $message="<div class='alert alert-danger'>
                OTP has expired.
                </div>";

            }else{

                mysqli_query($conn,"
                    UPDATE otp_verification
                    SET verified=1
                    WHERE id=".$row['id']."
                ");

                $_SESSION['otp_verified']=true;

                header("Location: reset_password.php");
                exit();

            }

        }else{

            $message="<div class='alert alert-danger'>
            Invalid OTP.
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

<title>Verify OTP</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{

background:linear-gradient(135deg,#0d47a1,#1565c0,#42a5f5);

height:100vh;

display:flex;

justify-content:center;

align-items:center;

font-family:Segoe UI,sans-serif;

}

.card{

width:420px;

border:none;

border-radius:20px;

overflow:hidden;

box-shadow:0 20px 40px rgba(0,0,0,.30);

}

.card-header{

background:#0d47a1;

color:#fff;

padding:25px;

text-align:center;

}

.card-body{

padding:30px;

}

.form-control{

height:55px;

font-size:22px;

text-align:center;

letter-spacing:8px;

font-weight:bold;

}

.btn-primary{

height:50px;

}

</style>

</head>

<body>

<div class="card">

<div class="card-header">

<h3>

<i class="bi bi-shield-check"></i>

Verify OTP

</h3>

<p class="mb-0">

<?= htmlspecialchars($_SESSION['reset_username']) ?>

</p>

<small>

<?= htmlspecialchars($_SESSION['reset_mobile']) ?>

</small>

</div>

<div class="card-body">

<?= $message ?>

<?php
if(isset($_SESSION['demo_otp'])){
?>

<div class="alert alert-info">

<b>Demo OTP :</b>

<?= $_SESSION['demo_otp']; ?>

</div>

<?php
}
?>

<form method="post">

<div class="mb-4">

<label class="form-label">

Enter 6 Digit OTP

</label>

<input
type="text"
name="otp"
maxlength="6"
class="form-control"
placeholder="000000"
required>

</div>

<button
type="submit"
name="verify"
class="btn btn-primary w-100"
id="verifyBtn">

<i class="bi bi-check-circle-fill"></i>

Verify OTP

</button>

</form>

<hr>

<div class="text-center">

<a href="forgot_password.php">

Back

</a>

</div>

</div>

</div>

<script>

document.querySelector("form").addEventListener("submit",function(){

document.getElementById("verifyBtn").innerHTML=
"<span class='spinner-border spinner-border-sm'></span> Verifying...";

document.getElementById("verifyBtn").disabled=true;

});

</script>

</body>

</html>