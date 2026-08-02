<?php
session_start();

if(!isset($_SESSION['consumer'])){
    header("Location: login.php");
    exit();
}

include("../db.php");

$query=mysqli_query($conn,"
SELECT *
FROM notices
ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Notice Board | APDCL Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:#eef3fb;
    overflow-x:hidden;
}

/*=========================================
            LAYOUT
=========================================*/

.wrapper{
    display:flex;
    min-height:100vh;
}

/*=========================================
            SIDEBAR
=========================================*/

.sidebar{

    width:270px;

    position:fixed;

    top:0;
    left:0;
    bottom:0;

    background:linear-gradient(180deg,#0B2C74,#1565d8);

    color:#fff;

    overflow-y:auto;

    box-shadow:8px 0 25px rgba(0,0,0,.15);

    z-index:1000;

}

.logo-area{

    text-align:center;

    padding:30px 20px;

    border-bottom:1px solid rgba(255,255,255,.15);

}

.logo{

    width:90px;

    height:90px;

    background:#fff;

    border-radius:50%;

    padding:6px;

    margin-bottom:15px;

}

.logo-area h4{

    font-weight:700;

    margin-bottom:3px;

}

.logo-area p{

    font-size:14px;

    opacity:.9;

}

/*=========================================
            MENU
=========================================*/

.menu{

    list-style:none;

    padding:20px 15px;

}

.menu li{

    margin-bottom:8px;

}

.menu a{

    display:flex;

    align-items:center;

    padding:14px 18px;

    color:#fff;

    text-decoration:none;

    border-radius:14px;

    transition:.35s;

    font-weight:500;

}

.menu a i{

    width:28px;

    font-size:20px;

    margin-right:12px;

}

.menu a:hover{

    background:rgba(255,255,255,.15);

    transform:translateX(6px);

}

.menu .active a{

    background:#fff;

    color:#1565d8;

    font-weight:700;

    box-shadow:0 8px 20px rgba(255,255,255,.25);

}

/*=========================================
            CONTENT
=========================================*/

.content{

    margin-left:270px;

    width:calc(100% - 270px);

    padding:35px;

}

/*=========================================
            CARDS
=========================================*/

.card{

    border:none;

    border-radius:22px;

    box-shadow:0 12px 30px rgba(0,0,0,.08);

}

/*=========================================
            BUTTONS
=========================================*/

.btn{

    border-radius:12px;

    font-weight:600;

}

/*=========================================
            BADGES
=========================================*/

.badge{

    border-radius:30px;

}

/*=========================================
            SCROLLBAR
=========================================*/

::-webkit-scrollbar{

    width:8px;

}

::-webkit-scrollbar-thumb{

    background:#1565d8;

    border-radius:20px;

}

::-webkit-scrollbar-track{

    background:#f1f5fb;

}

/*=========================================
            RESPONSIVE
=========================================*/

@media(max-width:992px){

.sidebar{

    left:-270px;

    transition:.4s;

}

.content{

    margin-left:0;

    width:100%;

    padding:20px;

}

}

/*=========================================
            HERO SECTION
=========================================*/

.hero-section{

    background:linear-gradient(135deg,#0B2C74,#1565d8,#42a5f5);

    color:#fff;

    border-radius:25px;

    padding:40px;

    margin-bottom:35px;

    box-shadow:0 15px 35px rgba(0,0,0,.15);

}

.hero-badge{

    display:inline-block;

    background:rgba(255,255,255,.18);

    padding:10px 22px;

    border-radius:40px;

    font-weight:600;

}

.hero-section h1{

    font-size:42px;

    font-weight:700;

}

.hero-section p{

    font-size:17px;

    line-height:1.8;

    opacity:.95;

}

.hero-section .btn{

    border-radius:40px;

    padding:12px 28px;

    font-weight:600;

}

.hero-card{

    background:#fff;

    color:#333;

    border-radius:22px;

    padding:30px;

    box-shadow:0 10px 25px rgba(0,0,0,.12);

}

.hero-card h2{

    font-size:45px;

    font-weight:700;

}

.hero-card h5{

    font-weight:600;

}

.hero-card hr{

    margin:20px 0;

}

/*=========================================
            NOTICE CARDS
=========================================*/

.notice-card{

border:none;

border-radius:22px;

overflow:hidden;

box-shadow:0 12px 30px rgba(0,0,0,.08);

transition:.35s;

}

.notice-card:hover{

transform:translateY(-8px);

box-shadow:0 20px 45px rgba(0,0,0,.15);

}

.notice-top{

background:linear-gradient(135deg,#1565d8,#1976d2);

padding:25px 30px;

display:flex;

justify-content:space-between;

align-items:center;

color:#fff;

flex-wrap:wrap;

}

.notice-number{

background:rgba(255,255,255,.20);

padding:8px 18px;

border-radius:30px;

font-size:14px;

font-weight:600;

}

.notice-date{

background:#fff;

color:#1565d8;

padding:10px 18px;

border-radius:30px;

font-weight:600;

display:inline-block;

}

.notice-content{

padding:30px;

background:#fff;

}

.notice-content p{

font-size:16px;

line-height:1.9;

color:#555;

}

.notice-content hr{

margin:25px 0;

}

.notice-content .btn{

border-radius:30px;

padding:8px 20px;

}

/*=========================================
            FOOTER
=========================================*/

footer{

margin-top:50px;

}

footer .card{

border-radius:22px;

box-shadow:0 10px 25px rgba(0,0,0,.08);

}

footer img{

background:#fff;

padding:5px;

border-radius:50%;

}

footer h5{

font-weight:700;

}

footer p{

margin-bottom:6px;

}

</style>

</head>

<body>

<div class="wrapper">

<!--=========================================
        SIDEBAR
==========================================-->

<aside class="sidebar">

<div class="logo-area">

<img
src="../assets/images/logo-circle.png"
class="logo">

<h4>

APDCL

</h4>

<p>

Consumer Portal

</p>

</div>

<ul class="menu">

<li>

<a href="dashboard.php">

<i class="bi bi-speedometer2"></i>

Dashboard

</a>

</li>

<li>

<a href="bill.php">

<i class="bi bi-receipt"></i>

My Bills

</a>

</li>

<li>

<a href="payment_history.php">

<i class="bi bi-credit-card"></i>

Payment History

</a>

</li>

<li class="active">

<a href="notice.php">

<i class="bi bi-megaphone-fill"></i>

Notice Board

</a>

</li>

<li>

<a href="complaint.php">

<i class="bi bi-chat-left-text-fill"></i>

Complaints

</a>

</li>

<li>

<a href="track_complaint.php">

<i class="bi bi-geo-alt-fill"></i>

Track Complaint

</a>

</li>

<li>

<a href="profile.php">

<i class="bi bi-person-circle"></i>

My Profile

</a>

</li>

<li>

<a href="logout.php">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>

</li>

</ul>

</aside>

<!--=========================================
        MAIN CONTENT
==========================================-->

<main class="content">
<!--=========================================
        HERO SECTION
==========================================-->

<div class="hero-section">

<div class="row align-items-center">

<div class="col-lg-8">

<span class="hero-badge">

<i class="bi bi-megaphone-fill me-2"></i>

APDCL Consumer Notice Board

</span>

<h1 class="mt-3">

Latest Notices & Announcements

</h1>

<p class="mt-3">

Stay informed with the latest APDCL announcements, maintenance schedules,
power shutdown notifications, consumer advisories and important updates.

</p>

<div class="mt-4">

<a href="dashboard.php"
class="btn btn-light btn-lg me-2">

<i class="bi bi-speedometer2 me-2"></i>

Dashboard

</a>

<a href="#noticeList"
class="btn btn-outline-light btn-lg">

<i class="bi bi-arrow-down-circle me-2"></i>

View Notices

</a>

</div>

</div>

<div class="col-lg-4 mt-4 mt-lg-0">

<div class="hero-card">

<div class="text-center">

<i class="bi bi-calendar-event display-3 text-primary"></i>

<h4 class="mt-3">

<?= date("d M Y") ?>

</h4>

<h5 id="clock">

00:00:00

</h5>

<hr>

<h6>

Total Notices

</h6>

<h2 class="text-primary">

<?= mysqli_num_rows($query) ?>

</h2>

</div>

</div>

</div>

</div>

</div>

<br>

<div id="noticeList"></div>
<?php

if(mysqli_num_rows($query)>0){

$sl=1;

while($row=mysqli_fetch_assoc($query)){

?>

<div class="card notice-card mb-4">

<div class="card-body p-0">

<div class="notice-top">

<div>

<span class="notice-number">

Notice #<?= $sl++ ?>

</span>

<h4 class="mt-3 mb-1">

<i class="bi bi-megaphone-fill text-warning me-2"></i>

<?= htmlspecialchars($row['title']) ?>

</h4>

<p class="text-light mb-0">

Official APDCL Consumer Notification

</p>

</div>

<div class="text-end">

<span class="notice-date">

<i class="bi bi-calendar-event me-2"></i>

<?= date("d M Y",strtotime($row['created_at'])) ?>

</span>

</div>

</div>

<div class="notice-content">

<p>

<?= nl2br(htmlspecialchars($row['message'])) ?>

</p>

<hr>

<div class="d-flex justify-content-between align-items-center flex-wrap">

<div>

<span class="badge bg-success">

<i class="bi bi-check-circle-fill me-1"></i>

Published

</span>

</div>

<div>

<a href="dashboard.php" class="btn btn-outline-primary btn-sm">

<i class="bi bi-house-door-fill me-2"></i>

Dashboard

</a>

</div>

</div>

</div>

</div>

</div>

<?php

}

}else{

?>

<div class="card shadow border-0 rounded-4">

<div class="card-body text-center py-5">

<i class="bi bi-megaphone display-1 text-secondary"></i>

<h3 class="mt-4">

No Notices Available

</h3>

<p class="text-muted">

There are currently no notices published by APDCL.

</p>

<a href="dashboard.php" class="btn btn-primary rounded-pill px-4">

<i class="bi bi-arrow-left-circle me-2"></i>

Back to Dashboard

</a>

</div>

</div>

<?php } ?>

<!--=========================================
        PROFESSIONAL FOOTER
==========================================-->

<footer class="mt-5">

<div class="card border-0 shadow-lg rounded-4">

<div class="card-body py-4">

<div class="row align-items-center">

<div class="col-lg-6">

<div class="d-flex align-items-center">

<img
src="../assets/images/logo-circle.png"
width="70"
class="me-3">

<div>

<h5 class="fw-bold text-primary mb-1">

Assam Power Distribution Company Limited

</h5>

<p class="text-muted mb-0">

Consumer Notice Management Portal

</p>

</div>

</div>

</div>

<div class="col-lg-6 text-lg-end mt-3 mt-lg-0">

<p class="mb-1">

<i class="bi bi-calendar-event-fill text-primary me-2"></i>

<?= date("d F Y") ?>

</p>

<p class="mb-1">

<i class="bi bi-clock-fill text-primary me-2"></i>

<span id="clock"></span>

</p>

<p class="text-secondary mb-0">

© <?= date("Y") ?>

APDCL | Internship Demo Project

</p>

</div>

</div>

<hr>

<div class="text-center text-muted">

Consumer Support :
<strong>1912</strong>

&nbsp; | &nbsp;

Email :
<strong>support@apdcl.org</strong>

</div>

</div>

</div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/*=========================================
        LIVE CLOCK
=========================================*/

function updateClock(){

const now=new Date();

const options={

hour:'2-digit',

minute:'2-digit',

second:'2-digit',

hour12:true

};

document.getElementById("clock").innerHTML=

now.toLocaleTimeString("en-IN",options);

}

updateClock();

setInterval(updateClock,1000);

/*=========================================
        CARD ANIMATION
=========================================*/

window.addEventListener("load",function(){

document.querySelectorAll(".notice-card,.hero-section,.card").forEach(function(card,index){

card.style.opacity="0";

card.style.transform="translateY(25px)";

setTimeout(function(){

card.style.transition=".5s";

card.style.opacity="1";

card.style.transform="translateY(0)";

},index*120);

});

});

</script>

</body>
</html>
