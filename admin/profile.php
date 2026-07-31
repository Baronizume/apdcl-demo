<?php
session_start();
include("../db.php");

/*====================================================
    LOGIN CHECK
====================================================*/

if (
    !isset($_SESSION['logged_in']) ||
    !isset($_SESSION['admin_id'])
) {
    header("Location: login.php");
    exit();
}

/*====================================================
    LOGGED IN USER
====================================================*/

$adminId = (int)$_SESSION['admin_id'];

$success = "";
$error = "";

/*====================================================
    LOAD PROFILE
====================================================*/

$query = mysqli_query(
    $conn,
    "SELECT * FROM admin WHERE id='$adminId' LIMIT 1"
);

if (!$query || mysqli_num_rows($query) == 0) {

    session_destroy();

    header("Location: login.php");

    exit();

}

$admin = mysqli_fetch_assoc($query);

/*====================================================
    UPDATE PROFILE
====================================================*/

if(isset($_POST['update_profile']))
{

$name = mysqli_real_escape_string(
$conn,
trim($_POST['name'])
);

$email = mysqli_real_escape_string(
$conn,
trim($_POST['email'])
);

$mobile = mysqli_real_escape_string(
$conn,
trim($_POST['mobile'])
);

$photo = $admin['photo'] ?? "";

/*==========================
UPLOAD PHOTO
==========================*/

if(
isset($_FILES['photo']) &&
$_FILES['photo']['name']!="")
{

$targetDir="../assets/profile/";

if(!is_dir($targetDir))
{

mkdir($targetDir,0777,true);

}

$fileName=time()."_".basename($_FILES['photo']['name']);

$targetFile=$targetDir.$fileName;

$extension=strtolower(pathinfo($targetFile,PATHINFO_EXTENSION));

$allowed=['jpg','jpeg','png','webp'];

if(in_array($extension,$allowed))
{

if(move_uploaded_file($_FILES['photo']['tmp_name'],$targetFile))
{

$photo=$fileName;

}

}

}

$sql="UPDATE admin SET

name='$name',

email='$email',

mobile='$mobile',

photo='$photo'

WHERE id='$adminId'";

if(mysqli_query($conn,$sql))
{

$_SESSION['name']=$name;

$success="Profile updated successfully.";

$query=mysqli_query($conn,"SELECT * FROM admin WHERE id='$adminId'");

$admin=mysqli_fetch_assoc($query);

}
else
{

$error=mysqli_error($conn);

}

}

/*====================================================
    CHANGE PASSWORD
====================================================*/

if(isset($_POST['change_password']))
{

$current=$_POST['current_password'];

$new=$_POST['new_password'];

$confirm=$_POST['confirm_password'];

if(
!password_verify(
$current,
$admin['password']
)
)
{

$error="Current password is incorrect.";

}
elseif($new!=$confirm)
{

$error="New passwords do not match.";

}
else
{

$newHash=password_hash(
$new,
PASSWORD_DEFAULT
);

mysqli_query(
$conn,
"UPDATE admin
SET password='$newHash'
WHERE id='$adminId'"
);

$success="Password changed successfully.";

}

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>

My Profile

</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{

background:#eef3f9;

font-family:'Segoe UI',sans-serif;

overflow-x:hidden;

}

/*==========================
NAVBAR
==========================*/

.navbar{

position:fixed;

top:0;

left:0;

right:0;

height:75px;

background:linear-gradient(90deg,#0d47a1,#1565c0,#1976d2);

box-shadow:0 5px 18px rgba(0,0,0,.18);

z-index:999;

}

.logo{

width:58px;

height:58px;

margin-right:15px;

object-fit:contain;

}

.brand-title{

font-size:24px;

font-weight:700;

color:#fff;

margin:0;

}

.brand-sub{

font-size:13px;

color:#dbeafe;

}

.profile-user{

text-align:right;

color:#fff;

}

/*==========================
SIDEBAR
==========================*/

.sidebar{

position:fixed;

top:75px;

left:0;

width:250px;

height:100%;

background:#083b8a;

overflow-y:auto;

padding-top:18px;

}

.sidebar a{

display:flex;

align-items:center;

padding:15px 22px;

text-decoration:none;

color:#fff;

font-size:15px;

transition:.25s;

border-left:4px solid transparent;

}

.sidebar a i{

font-size:20px;

margin-right:12px;

}

.sidebar a:hover{

background:#1565c0;

border-left:4px solid #ffc107;

padding-left:28px;

}

.sidebar a.active{

background:#1976d2;

border-left:4px solid #ffc107;

}

/*==========================
CONTENT
==========================*/

.main-content{

margin-left:250px;

margin-top:75px;

padding:30px;

}

/*==========================
PAGE HEADER
==========================*/

.page-header{

background:#fff;

padding:25px;

border-radius:18px;

box-shadow:0 8px 20px rgba(0,0,0,.08);

margin-bottom:25px;

}

.page-header h2{

font-weight:700;

color:#0d47a1;

margin-bottom:8px;

}

/*==========================
PROFILE CARD
==========================*/

.profile-card{

background:#fff;

border:none;

border-radius:18px;

padding:25px;

box-shadow:0 8px 20px rgba(0,0,0,.08);

margin-bottom:25px;

}

.form-label{

font-weight:600;

}

.form-control{

border-radius:10px;

}

.btn{

border-radius:10px;

}

/*==========================
FOOTER
==========================*/

footer{

margin-top:40px;

padding:20px;

background:#fff;

border-radius:15px;

box-shadow:0 8px 20px rgba(0,0,0,.08);

}

</style>

</head>

<body>

<!--==========================
NAVBAR
===========================-->

<nav class="navbar">

<div class="container-fluid">

<div class="d-flex align-items-center">

<img
src="../assets/images/logo-circle.png"
class="logo"
alt="APDCL Logo">

<div>

<h4 class="brand-title">

APDCL Super Admin Portal

</h4>

<div class="brand-sub">

Assam Power Distribution Company Limited

</div>

</div>

</div>

<div class="profile-user">

<strong>

<?= htmlspecialchars($admin['name']) ?>

</strong>

<br>

<small>

<?= htmlspecialchars($_SESSION['role']) ?>

</small>

</div>

</div>

</nav>

<!--==========================
SIDEBAR
===========================-->

<div class="sidebar">

<a href="dashboard.php">

<i class="bi bi-speedometer2"></i>

Dashboard

</a>

<a href="manage_zones.php">

<i class="bi bi-globe-central-south-asia"></i>

Manage Zones

</a>

<a href="manage_circles.php">

<i class="bi bi-diagram-3-fill"></i>

Manage Circles

</a>

<a href="manage_subdivisions.php">

<i class="bi bi-building"></i>

Manage Sub-Divisions

</a>

<a href="manage_admins.php">

<i class="bi bi-person-badge"></i>

Manage Admins

</a>

<a href="settings.php">

<i class="bi bi-gear-fill"></i>

Settings

</a>

<a href="profile.php" class="active">

<i class="bi bi-person-circle"></i>

My Profile

</a>

<hr>

<a href="logout.php">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>

</div>

<!--==========================
MAIN CONTENT
===========================-->

<div class="main-content">

<div class="page-header">

<h2>

<i class="bi bi-person-circle"></i>

My Profile

</h2>

<p class="text-muted mb-0">

Manage your account information, profile photo and password.

</p>

</div>

<?php if($success!=""){ ?>

<div class="alert alert-success">

<?= $success ?>

</div>

<?php } ?>

<?php if($error!=""){ ?>

<div class="alert alert-danger">

<?= $error ?>

</div>

<?php } ?>

<!--=========================================
    PROFILE INFORMATION
==========================================-->

<form
method="POST"
enctype="multipart/form-data">

<div class="profile-card">

<h4 class="mb-4">

<i class="bi bi-person-fill text-primary"></i>

Profile Information

</h4>

<div class="row">

<!-- Profile Photo -->

<div class="col-md-3 text-center mb-4">

<?php

$photoPath="../assets/profile/";

if(
!empty($admin['photo']) &&
file_exists($photoPath.$admin['photo'])
)
{
?>

<img
src="<?= $photoPath.$admin['photo'] ?>"
class="rounded-circle shadow border"
style="width:170px;height:170px;object-fit:cover;">

<?php
}
else
{
?>

<div
class="rounded-circle border shadow d-flex align-items-center justify-content-center"
style="width:170px;height:170px;background:#f5f5f5;margin:auto;">

<i class="bi bi-person display-1 text-secondary"></i>

</div>

<?php
}
?>

<div class="mt-3">

<input
type="file"
name="photo"
class="form-control"
accept=".jpg,.jpeg,.png,.webp">

<small class="text-muted">

JPG, PNG or WEBP

</small>

</div>

</div>

<!-- Profile Details -->

<div class="col-md-9">

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Full Name

</label>

<input
type="text"
name="name"
class="form-control"
required
value="<?= htmlspecialchars($admin['name']) ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Email Address

</label>

<input
type="email"
name="email"
class="form-control"
value="<?= htmlspecialchars($admin['email']) ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Mobile Number

</label>

<input
type="text"
name="mobile"
class="form-control"
value="<?= htmlspecialchars($admin['mobile']) ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Role

</label>

<input
type="text"
class="form-control"
readonly
value="<?= htmlspecialchars($_SESSION['role']) ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Admin ID

</label>

<input
type="text"
class="form-control"
readonly
value="<?= $admin['id'] ?>">

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Username

</label>

<input
type="text"
class="form-control"
readonly
value="<?= htmlspecialchars($admin['username']) ?>">

</div>

</div>

<div class="text-end mt-3">

<button
type="submit"
name="update_profile"
class="btn btn-primary btn-lg">

<i class="bi bi-save"></i>

Update Profile

</button>

</div>

</div>

</div>

</div>

</form>

<!--=========================================
    CHANGE PASSWORD
==========================================-->

<form method="POST">

<div class="profile-card">

<h4 class="mb-4">

<i class="bi bi-shield-lock-fill text-danger"></i>

Change Password

</h4>

<div class="row">

<!-- Current Password -->

<div class="col-md-4 mb-3">

<label class="form-label">

Current Password

</label>

<input
type="password"
name="current_password"
class="form-control"
required>

</div>

<!-- New Password -->

<div class="col-md-4 mb-3">

<label class="form-label">

New Password

</label>

<input
type="password"
name="new_password"
class="form-control"
required>

</div>

<!-- Confirm Password -->

<div class="col-md-4 mb-3">

<label class="form-label">

Confirm New Password

</label>

<input
type="password"
name="confirm_password"
class="form-control"
required>

</div>

</div>

<div class="alert alert-info mt-3">

<i class="bi bi-info-circle-fill"></i>

<strong>Password Tips</strong>

<ul class="mb-0 mt-2">

<li>Use at least 8 characters.</li>

<li>Include uppercase and lowercase letters.</li>

<li>Include at least one number.</li>

<li>Use at least one special character.</li>

</ul>

</div>

<div class="text-end">

<button
type="submit"
name="change_password"
class="btn btn-danger btn-lg">

<i class="bi bi-key-fill"></i>

Change Password

</button>

</div>

</div>

</form>

<!--=========================================
    ACCOUNT INFORMATION
==========================================-->

<div class="profile-card">

<h4 class="mb-4">

<i class="bi bi-person-vcard-fill text-success"></i>

Account Information

</h4>

<div class="row">

<div class="col-md-6">

<table class="table table-bordered">

<tr>

<th width="35%">

Admin ID

</th>

<td>

<?= $admin['id'] ?>

</td>

</tr>

<tr>

<th>

Username

</th>

<td>

<?= htmlspecialchars($admin['username']) ?>

</td>

</tr>

<tr>

<th>

Role

</th>

<td>

<span class="badge bg-primary">

<?= htmlspecialchars($_SESSION['role']) ?>

</span>

</td>

</tr>

<tr>

<th>

Email

</th>

<td>

<?= htmlspecialchars($admin['email']) ?>

</td>

</tr>

<tr>

<th>

Mobile

</th>

<td>

<?= htmlspecialchars($admin['mobile']) ?>

</td>

</tr>

</table>

</div>

<div class="col-md-6">

<table class="table table-bordered">

<tr>

<th width="40%">

Account Status

</th>

<td>

<span class="badge bg-success">

Active

</span>

</td>

</tr>

<tr>

<th>

Created On

</th>

<td>

<?php
if(isset($admin['created_at']) && !empty($admin['created_at']))
{
echo date("d M Y",strtotime($admin['created_at']));
}
else
{
echo "Not Available";
}
?>

</td>

</tr>

<tr>

<th>

Last Login

</th>

<td>

<?php
if(isset($admin['last_login']) && !empty($admin['last_login']))
{
echo date("d M Y h:i A",strtotime($admin['last_login']));
}
else
{
echo "Not Available";
}
?>

</td>

</tr>

<tr>

<th>

Portal

</th>

<td>

APDCL Super Admin Portal

</td>

</tr>

<tr>

<th>

Company

</th>

<td>

Assam Power Distribution Company Limited

</td>

</tr>

</table>

</div>

</div>

<hr>

<div class="d-flex justify-content-between">

<a
href="dashboard.php"
class="btn btn-secondary btn-lg">

<i class="bi bi-arrow-left"></i>

Back to Dashboard

</a>

<a
href="settings.php"
class="btn btn-success btn-lg">

<i class="bi bi-gear-fill"></i>

System Settings

</a>

</div>

</div>

<!--=========================================
    FOOTER
==========================================-->

<footer>

<div class="row align-items-center">

<div class="col-md-6">

<strong>

APDCL Super Admin Portal

</strong>

<br>

<small>

Assam Power Distribution Company Limited

</small>

</div>

<div class="col-md-6 text-end">

<strong>

Logged in as:

</strong>

<?= htmlspecialchars($admin['name']) ?>

<br>

<small>

<?= htmlspecialchars($_SESSION['role']) ?>

</small>

<br>

<span id="liveClock"
class="fw-bold text-primary">

</span>

</div>

</div>

<hr>

<div class="text-center">

© <?= date("Y") ?>

APDCL Electricity Billing Management System

All Rights Reserved.

</div>

</footer>

</div>

<!--=========================================
    BOOTSTRAP
==========================================-->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/*=========================================
    LIVE CLOCK
=========================================*/

function updateClock(){

const now = new Date();

const options = {

weekday:'short',

day:'2-digit',

month:'short',

year:'numeric'

};

const date = now.toLocaleDateString('en-IN',options);

const time = now.toLocaleTimeString();

document.getElementById("liveClock").innerHTML =

date + " | " + time;

}

updateClock();

setInterval(updateClock,1000);

/*=========================================
    AUTO HIDE ALERTS
=========================================*/

setTimeout(function(){

document.querySelectorAll(".alert").forEach(function(alert){

alert.style.transition="0.5s";

alert.style.opacity="0";

setTimeout(function(){

alert.remove();

},500);

});

},4000);

/*=========================================
    CARD HOVER EFFECT
=========================================*/

document.querySelectorAll(".profile-card").forEach(function(card){

card.addEventListener("mouseenter",function(){

this.style.transform="translateY(-5px)";

this.style.transition=".3s";

this.style.boxShadow="0 12px 25px rgba(0,0,0,.15)";

});

card.addEventListener("mouseleave",function(){

this.style.transform="translateY(0px)";

this.style.boxShadow="0 8px 20px rgba(0,0,0,.08)";

});

});

/*=========================================
    PASSWORD MATCH CHECK
=========================================*/

const newPassword = document.querySelector('input[name="new_password"]');
const confirmPassword = document.querySelector('input[name="confirm_password"]');

if(newPassword && confirmPassword){

function validatePassword(){

if(confirmPassword.value===""){

confirmPassword.setCustomValidity("");

return;

}

if(newPassword.value!==confirmPassword.value){

confirmPassword.setCustomValidity("Passwords do not match.");

}else{

confirmPassword.setCustomValidity("");

}

}

newPassword.addEventListener("keyup",validatePassword);
confirmPassword.addEventListener("keyup",validatePassword);

}

/*=========================================
    PHOTO SELECTION MESSAGE
=========================================*/

const photoInput = document.querySelector('input[name="photo"]');

if(photoInput){

photoInput.addEventListener("change",function(){

if(this.files.length){

alert("Profile picture selected. Click 'Update Profile' to save your changes.");

}

});

}

</script>

</body>
</html>