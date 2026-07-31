<?php
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

include("../db.php");

/* Logged In Admin */

$admin_username = $_SESSION['admin'];

$adminQuery = mysqli_query($conn,"
SELECT *
FROM admin
WHERE username='$admin_username'
");

$admin = mysqli_fetch_assoc($adminQuery);

/* Notices */

$query = mysqli_query($conn,"
SELECT *
FROM notices
ORDER BY id DESC
");

if(!$query){
    die("Database Error : ".mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>APDCL Notices</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{
background:#f4f7fc;
font-family:'Segoe UI',sans-serif;
}

/* ===========================
   NAVBAR
=========================== */

.navbar{

background:linear-gradient(90deg,#0d47a1,#1565c0,#1976d2);

height:75px;

padding:0 25px;

box-shadow:0 4px 18px rgba(0,0,0,.2);

}

.navbar-brand{

display:flex;

align-items:center;

text-decoration:none;

color:#fff!important;

}

.navbar-brand img{

width:58px;

height:58px;

border-radius:50%;

background:#fff;

padding:4px;

margin-right:15px;

}

.navbar-brand h5{

margin:0;

font-weight:700;

}

.navbar-brand small{

color:#d9ecff;

font-size:12px;

}

.admin-profile{

display:flex;

align-items:center;

color:#fff;

text-decoration:none;

}

.admin-profile img{

width:45px;

height:45px;

border-radius:50%;

margin-right:12px;

background:#fff;

padding:2px;

}

.dropdown-menu{

border:none;

border-radius:12px;

box-shadow:0 8px 25px rgba(0,0,0,.15);

}

.dropdown-item{

padding:10px 18px;

}

.dropdown-item:hover{

background:#eef6ff;

}

/* ===========================
SIDEBAR
=========================== */

.sidebar{

position:fixed;

top:75px;

left:0;

width:260px;

height:calc(100vh - 75px);

background:#0b3b86;

overflow-y:auto;

box-shadow:4px 0 15px rgba(0,0,0,.15);

}

.sidebar-header{

text-align:center;

padding:20px;

border-bottom:1px solid rgba(255,255,255,.15);

color:#fff;

}

.sidebar-logo{

width:65px;

height:65px;

border-radius:50%;

background:#fff;

padding:5px;

margin-bottom:10px;

}

.sidebar-header h5{

margin:0;

font-weight:bold;

}

.sidebar-header small{

color:#d0d0d0;

}

.sidebar a{

display:flex;

align-items:center;

padding:15px 22px;

color:#fff;

text-decoration:none;

transition:.3s;

border-left:4px solid transparent;

}

.sidebar a i{

width:30px;

font-size:20px;

}

.sidebar a span{

font-size:15px;

font-weight:500;

}

.sidebar a:hover{

background:#1565c0;

padding-left:28px;

border-left:4px solid #ffc107;

}

.sidebar a.active{

background:#1976d2;

border-left:4px solid #ffc107;

}

.sidebar-divider{

height:1px;

background:rgba(255,255,255,.15);

margin:15px;

}

.logout{

margin:15px;

border-radius:8px;

background:#c62828;

}

.logout:hover{

background:#b71c1c!important;

}

/* ===========================
CONTENT
=========================== */

.content{

margin-left:280px;

padding:35px;

}

.page-title{

font-size:32px;

font-weight:bold;

color:#0d47a1;

margin-bottom:8px;

}

.page-subtitle{

color:#666;

margin-bottom:30px;

}

/* Notice Cards */

.notice-card{

border:none;

border-radius:15px;

box-shadow:0 6px 18px rgba(0,0,0,.12);

margin-bottom:25px;

overflow:hidden;

transition:.3s;

}

.notice-card:hover{

transform:translateY(-5px);

}

.notice-header{

background:linear-gradient(90deg,#0d47a1,#1565c0);

padding:18px;

color:#fff;

font-size:20px;

font-weight:bold;

}

.notice-body{

padding:25px;

background:#fff;

}

.notice-date{

margin-top:20px;

font-size:14px;

color:#777;

font-style:italic;

}

.search-box{

background:#fff;

padding:20px;

border-radius:15px;

box-shadow:0 5px 15px rgba(0,0,0,.1);

margin-bottom:25px;

}

footer{

margin-left:280px;

padding:20px;

text-align:center;

color:#666;

}

@media(max-width:992px){

.sidebar{

width:70px;

}

.sidebar-header{

display:none;

}

.sidebar span{

display:none;

}

.sidebar a{

justify-content:center;

padding:15px;

}

.sidebar a i{

margin:0;

}

.content{

margin-left:90px;

}

footer{

margin-left:90px;

}

}

</style>

</head>

<body>

<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg">

<div class="container-fluid">

<a class="navbar-brand" href="dashboard.php">

<img src="/apdcl-demo/assets/images/logo-circle.png">

<div>

<h5>APDCL</h5>

<small>Assam Power Distribution Company Ltd.</small>

</div>

</a>

<div class="ms-auto d-flex align-items-center">

<div class="text-end text-white me-4">

<div><?= date("d M Y"); ?></div>

<small id="clock"></small>

</div>

<div class="dropdown">

<a href="#"

class="admin-profile dropdown-toggle"

data-bs-toggle="dropdown">

<img src="https://ui-avatars.com/api/?name=<?= urlencode($admin['name']); ?>&background=ffffff&color=0d47a1">

<div>

<b><?= htmlspecialchars($admin['name']); ?></b><br>

<small>Administrator</small>

</div>

</a>

<ul class="dropdown-menu dropdown-menu-end">

<li>

<a class="dropdown-item"

href="dashboard.php">

<i class="bi bi-speedometer2"></i>

Dashboard

</a>

</li>

<li>

<a class="dropdown-item"

href="profile.php">

<i class="bi bi-person-circle"></i>

Profile

</a>

</li>

<li><hr></li>

<li>

<a class="dropdown-item text-danger"

href="../logout.php">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>

</li>

</ul>

</div>

</div>

</div>

</nav>

<!-- ================= SIDEBAR ================= -->

<div class="sidebar">

    <div class="sidebar-header">

        <img src="/apdcl-demo/assets/images/logo-circle.png"
             class="sidebar-logo">

        <h5>APDCL</h5>

        <small>Admin Panel</small>

    </div>

    <a href="dashboard.php">
        <i class="bi bi-speedometer2"></i>
        <span>Dashboard</span>
    </a>

    <a href="manage_consumer.php">
        <i class="bi bi-people-fill"></i>
        <span>Manage Consumers</span>
    </a>

    <a href="generate_bill.php">
        <i class="bi bi-lightning-charge-fill"></i>
        <span>Generate Bill</span>
    </a>

    <a href="manage_bills.php">
        <i class="bi bi-receipt-cutoff"></i>
        <span>Manage Bills</span>
    </a>

    <a href="complaint.php">
        <i class="bi bi-chat-left-text-fill"></i>
        <span>Complaint</span>
    </a>

    <a href="notices.php" class="active">
        <i class="bi bi-megaphone-fill"></i>
        <span>Notices</span>
    </a>

    <a href="reports.php">
        <i class="bi bi-bar-chart-fill"></i>
        <span>Reports</span>
    </a>

    <div class="sidebar-divider"></div>

    <a href="../logout.php" class="logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
    </a>

</div>

<!-- ================= CONTENT ================= -->

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="page-title">

                <i class="bi bi-megaphone-fill text-primary"></i>

                APDCL Notices

            </h2>

            <p class="page-subtitle">

                Latest announcements and important information for consumers.

            </p>

        </div>

        <div>

            <button onclick="window.print()"
                    class="btn btn-primary">

                <i class="bi bi-printer-fill"></i>

                Print

            </button>

        </div>

    </div>

    <!-- Search -->

    <div class="search-box">

        <form method="GET">

            <div class="row">

                <div class="col-md-10">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search Notice Title..."
                        value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

                </div>

                <div class="col-md-2">

                    <button class="btn btn-primary w-100">

                        <i class="bi bi-search"></i>

                        Search

                    </button>

                </div>

            </div>

        </form>

    </div>

    <!-- NOTICE CONTENT START -->

<?php

$search = "";

if(isset($_GET['search'])){

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $query = mysqli_query($conn,"
    SELECT *
    FROM notices
    WHERE title LIKE '%$search%'
       OR message LIKE '%$search%'
    ORDER BY id DESC
    ");

}else{

    $query = mysqli_query($conn,"
    SELECT *
    FROM notices
    ORDER BY id DESC
    ");

}

if(mysqli_num_rows($query)>0){

while($row=mysqli_fetch_assoc($query)){

?>

<div class="notice-card">

    <div class="notice-header d-flex justify-content-between align-items-center">

        <div>

            <i class="bi bi-megaphone-fill me-2"></i>

            <?= htmlspecialchars($row['title']); ?>

        </div>

        <span class="badge bg-warning text-dark">

            Notice

        </span>

    </div>

    <div class="notice-body">

        <p style="font-size:16px;line-height:30px;color:#555;">

            <?= nl2br(htmlspecialchars($row['message'])); ?>

        </p>

        <hr>

        <div class="d-flex justify-content-between align-items-center">

            <small class="notice-date">

                <i class="bi bi-calendar-event"></i>

                Posted on

                <?= date("d M Y",strtotime($row['created_at'])); ?>

            </small>

            <small class="text-muted">

                <i class="bi bi-clock-history"></i>

                <?= date("h:i A",strtotime($row['created_at'])); ?>

            </small>

        </div>

    </div>

</div>

<?php

}

}else{

?>

<div class="card shadow border-0">

    <div class="card-body text-center p-5">

        <i class="bi bi-info-circle-fill text-primary"
           style="font-size:70px;"></i>

        <h3 class="mt-3">

            No Notices Available

        </h3>

        <p class="text-muted">

            There are currently no announcements from APDCL.

        </p>

    </div>

</div>

<?php } ?>

<div class="text-center mt-4">

    <a href="dashboard.php"
       class="btn btn-secondary btn-lg">

        <i class="bi bi-arrow-left-circle-fill"></i>

        Back to Dashboard

    </a>

</div>

</div>

<!-- ================= FOOTER ================= -->

<footer>

<hr>

<p>

© <?= date("Y"); ?> APDCL - Assam Power Distribution Company Limited

</p>

<p>

Electricity Billing Management System | Admin Panel

</p>

</footer>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/* ==========================
   Live Clock
========================== */

function updateClock(){

    const now = new Date();

    document.getElementById("clock").innerHTML =
        now.toLocaleTimeString();

}

setInterval(updateClock,1000);

updateClock();

/* ==========================
   Sidebar Active Animation
========================== */

const links=document.querySelectorAll(".sidebar a");

links.forEach(link=>{

link.addEventListener("mouseenter",function(){

this.style.transition=".25s";

});

});

/* ==========================
   Smooth Card Hover
========================== */

const cards=document.querySelectorAll(".notice-card");

cards.forEach(card=>{

card.addEventListener("mouseenter",()=>{

card.style.transform="translateY(-6px)";
card.style.boxShadow="0 12px 30px rgba(0,0,0,.18)";

});

card.addEventListener("mouseleave",()=>{

card.style.transform="translateY(0)";
card.style.boxShadow="0 6px 18px rgba(0,0,0,.12)";

});

});

</script>

</body>

</html>