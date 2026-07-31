<?php
session_start();
include("../db.php");

if (!isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

$adminName = $_SESSION['name'];
$role      = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Settings | APDCL Admin Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#eef3f9;
    font-family:'Segoe UI',sans-serif;
}

.header{
    background:linear-gradient(135deg,#0d47a1,#1565c0,#1e88e5);
    color:white;
    padding:35px;
    border-radius:20px;
    margin-bottom:30px;
    box-shadow:0 10px 25px rgba(0,0,0,.15);
}

.profile-box{
    background:white;
    border-radius:20px;
    padding:30px;
    text-align:center;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
    margin-bottom:30px;
}

.avatar{
    width:100px;
    height:100px;
    border-radius:50%;
    background:#0d6efd;
    color:white;
    font-size:40px;
    display:flex;
    justify-content:center;
    align-items:center;
    margin:auto;
    margin-bottom:15px;
}

.setting-card{
    background:white;
    border-radius:18px;
    padding:25px;
    text-align:center;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
    transition:.3s;
    text-decoration:none;
    color:#333;
    display:block;
    height:100%;
}

.setting-card:hover{
    transform:translateY(-8px);
    text-decoration:none;
    color:#0d6efd;
}

.setting-card i{
    font-size:45px;
    color:#1565c0;
    margin-bottom:15px;
}

.setting-card h5{
    font-weight:600;
}

.setting-card p{
    font-size:14px;
    color:#666;
}

.footer{
    text-align:center;
    margin-top:50px;
    color:#666;
}

</style>

</head>

<body>

<div class="container py-4">

<div class="header">

<div class="d-flex justify-content-between align-items-center">

<div>

<h2>
<i class="bi bi-gear-fill"></i>
Admin Settings
</h2>

<p class="mb-0">
Configure your APDCL Administration Portal
</p>

</div>

<a href="dashboard.php" class="btn btn-light">
<i class="bi bi-speedometer2"></i>
Dashboard
</a>

</div>

</div>

<div class="profile-box">

<div class="avatar">

<i class="bi bi-person-fill"></i>

</div>

<h3><?= htmlspecialchars($adminName) ?></h3>

<span class="badge bg-primary">
<?= htmlspecialchars($role) ?>
</span>

<p class="mt-3 text-muted">
Welcome to the APDCL Administration Settings Panel.
Manage your profile, password and account settings from here.
</p>

</div>

<div class="row g-4">

<div class="col-md-3">

<a href="profile.php" class="setting-card">

<i class="bi bi-person-circle"></i>

<h5>My Profile</h5>

<p>View and update your profile information.</p>

</a>

</div>

<div class="col-md-3">

<a href="change_admin_password.php" class="setting-card">

<i class="bi bi-key-fill"></i>

<h5>Change Password</h5>

<p>Update your account password securely.</p>

</a>

</div>

<div class="col-md-3">

<a href="dashboard.php" class="setting-card">

<i class="bi bi-speedometer2"></i>

<h5>Dashboard</h5>

<p>Return to the Admin Dashboard.</p>

</a>

</div>

<div class="col-md-3">

<a href="logout.php" class="setting-card">

<i class="bi bi-box-arrow-right text-danger"></i>

<h5 class="text-danger">Logout</h5>

<p>Sign out from the Admin Portal.</p>

</a>

</div>

</div>

<div class="footer">

<hr>

<p>
<strong>APDCL Admin Portal</strong><br>
Assam Power Distribution Company Limited
</p>

<p>
© <?= date("Y"); ?> Internship Demo Project
</p>

</div>

</div>

</body>
</html>