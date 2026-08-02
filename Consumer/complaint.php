<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

$success = "";
$error = "";

/*=========================================================
    LOAD CONSUMER DETAILS
=========================================================*/

$stmt = mysqli_prepare($conn,"
SELECT *
FROM users
WHERE consumer_no=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$consumer_no);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$consumer = mysqli_fetch_assoc($result);

if(!$consumer){
    die("Consumer not found.");
}

/*=========================================================
    SUCCESS MESSAGE
=========================================================*/

if(isset($_SESSION['success'])){
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

/*=========================================================
    TOTAL COMPLAINTS
=========================================================*/

function getCount($conn,$consumer_no,$status=null){

    if($status==""){

        $stmt=mysqli_prepare($conn,"
        SELECT COUNT(*) total
        FROM complaint
        WHERE consumer_no=?
        ");

        mysqli_stmt_bind_param($stmt,"s",$consumer_no);

    }else{

        $stmt=mysqli_prepare($conn,"
        SELECT COUNT(*) total
        FROM complaint
        WHERE consumer_no=?
        AND status=?
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $consumer_no,
            $status
        );

    }

    mysqli_stmt_execute($stmt);

    $result=mysqli_stmt_get_result($stmt);

    $row=mysqli_fetch_assoc($result);

    return $row['total'];

}

$totalComplaints    = getCount($conn,$consumer_no);
$pendingComplaints  = getCount($conn,$consumer_no,"Pending");
$assignedComplaints = getCount($conn,$consumer_no,"Assigned");
$progressComplaints = getCount($conn,$consumer_no,"In Progress");
$resolvedComplaints = getCount($conn,$consumer_no,"Resolved");

/*=========================================================
    TODAY'S COMPLAINTS
=========================================================*/

$stmt=mysqli_prepare($conn,"
SELECT COUNT(*) total
FROM complaint
WHERE consumer_no=?
AND DATE(created_at)=CURDATE()
");

mysqli_stmt_bind_param($stmt,"s",$consumer_no);

mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

$row=mysqli_fetch_assoc($result);

$todayComplaints=$row['total'];

/*=========================================================
    SEARCH
=========================================================*/

$search = trim($_GET['search'] ?? "");

if($search==""){

    $stmt=mysqli_prepare($conn,"
    SELECT *
    FROM complaint
    WHERE consumer_no=?
    ORDER BY id DESC
    ");

    mysqli_stmt_bind_param($stmt,"s",$consumer_no);

}
else{

    $like="%".$search."%";

    $stmt=mysqli_prepare($conn,"
    SELECT *
    FROM complaint
    WHERE consumer_no=?
    AND
    (
        complaint_id LIKE ?
        OR category LIKE ?
        OR subject LIKE ?
        OR status LIKE ?
    )
    ORDER BY id DESC
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $consumer_no,
        $like,
        $like,
        $like,
        $like
    );

}

mysqli_stmt_execute($stmt);

$complaints=mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Complaint Management | APDCL Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:"Segoe UI",sans-serif;
}

body{
    background:#f4f7fc;
    overflow-x:hidden;
}

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

    padding:30px 20px;

    text-align:center;

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

    opacity:.85;

    font-size:14px;

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

    color:#fff;

    text-decoration:none;

    padding:14px 18px;

    border-radius:14px;

    transition:.35s;

    font-weight:500;

}

.menu a i{

    font-size:20px;

    width:30px;

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

    box-shadow:0 8px 18px rgba(255,255,255,.25);

}

/*=========================================
            MAIN CONTENT
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

}

/*=========================================
            BADGES
=========================================*/

.badge{

    border-radius:30px;

    padding:8px 14px;

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

    background:#eef2f7;

}

/*=========================================
            RESPONSIVE
=========================================*/

@media(max-width:992px){

.sidebar{

    left:-270px;

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

    border-radius:24px;

    color:#fff;

    padding:45px;

    margin-bottom:35px;

    box-shadow:0 18px 35px rgba(0,0,0,.12);

}

.hero-section h1{

    font-weight:700;

}

.hero-section p{

    opacity:.95;

    line-height:1.8;

}

.hero-section .btn{

    border-radius:50px;

    font-weight:600;

    padding:13px 28px;

}

.consumer-card{

    background:#fff;

    color:#333;

    border-radius:20px;

    padding:25px;

    text-align:center;

    box-shadow:0 12px 25px rgba(0,0,0,.12);

}

.consumer-avatar{

    width:110px;

    height:110px;

    border-radius:50%;

    object-fit:cover;

    border:5px solid #1565d8;

    margin-bottom:15px;

}

/*=========================================
        PREMIUM STATISTICS
=========================================*/

.stats-box{

    background:#fff;

    border-radius:20px;

    padding:25px;

    display:flex;

    align-items:center;

    gap:18px;

    box-shadow:0 15px 30px rgba(0,0,0,.08);

    transition:.35s;

    height:100%;

    overflow:hidden;

}

.stats-box:hover{

    transform:translateY(-8px);

    box-shadow:0 25px 40px rgba(0,0,0,.15);

}

.stats-icon{

    width:70px;

    height:70px;

    border-radius:18px;

    display:flex;

    justify-content:center;

    align-items:center;

    color:#fff;

    font-size:28px;

    flex-shrink:0;

}

.stats-content h2{

    font-size:34px;

    font-weight:700;

    margin:0;

}

.stats-content p{

    margin:4px 0 0;

    color:#666;

    font-weight:600;

}

/* Colors */

.total .stats-icon{

    background:#1565d8;

}

.pending .stats-icon{

    background:#ff9800;

}

.assigned .stats-icon{

    background:#8e24aa;

}

.progress .stats-icon{

    background:#00acc1;

}

.resolved .stats-icon{

    background:#2e7d32;

}

.today .stats-icon{

    background:#ef6c00;

}

.total{

    border-left:6px solid #1565d8;

}

.pending{

    border-left:6px solid #ff9800;

}

.assigned{

    border-left:6px solid #8e24aa;

}

.progress{

    border-left:6px solid #00acc1;

}

.resolved{

    border-left:6px solid #2e7d32;

}

.today{

    border-left:6px solid #ef6c00;

}

@media(max-width:768px){

.stats-box{

    flex-direction:column;

    text-align:center;

}

}

/*=========================================
        SEARCH CARD
=========================================*/

.search-card{

    border:none;

    border-radius:22px;

    overflow:hidden;

    box-shadow:0 15px 35px rgba(0,0,0,.08);

}

.search-card .card-body{

    background:#fff;

}

.input-group{

    border-radius:15px;

    overflow:hidden;

}

.input-group-text{

    background:#fff;

    border:none;

    padding-left:20px;

}

.input-group .form-control{

    border:none;

    box-shadow:none;

    height:58px;

    font-size:15px;

}

.input-group .form-control:focus{

    box-shadow:none;

}

.input-group .btn{

    border-radius:0;

    font-weight:600;

}

.input-group .btn:last-child{

    border-radius:0;

}

/*=========================================
        COMPLAINT TABLE
=========================================*/

.complaint-card{

    border:none;

    border-radius:24px;

    overflow:hidden;

    box-shadow:0 15px 35px rgba(0,0,0,.08);

}

.modern-table thead{

    background:linear-gradient(135deg,#1565d8,#1976d2);

    color:#fff;

}

.modern-table thead th{

    border:none;

    padding:18px;

    font-size:15px;

    font-weight:600;

    white-space:nowrap;

}

.modern-table tbody td{

    padding:18px;

    vertical-align:middle;

}

.modern-table tbody tr{

    transition:.3s;

}

.modern-table tbody tr:hover{

    background:#f5f9ff;

}

.modern-table .badge{

    border-radius:25px;

    padding:8px 14px;

    font-size:13px;

}

.modern-table .btn{

    width:40px;

    height:40px;

    display:inline-flex;

    align-items:center;

    justify-content:center;

}

.modern-table .btn:hover{

    transform:translateY(-3px);

}

/*=========================================
            FOOTER
=========================================*/

.footer{

margin-top:60px;

}

.footer .card{

border-radius:25px;

}

.footer a{

text-decoration:none;

color:#555;

transition:.3s;

}

.footer a:hover{

color:#1565d8;

padding-left:6px;

}

.footer h5{

margin-bottom:18px;

}

.footer p{

color:#666;

}

</style>

</head>

<body>

<div class="wrapper">

<!-- ===========================
        SIDEBAR
=========================== -->

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

<li class="active">

<a href="complaint.php">

<i class="bi bi-chat-left-text-fill"></i>

Complaint Management

</a>

</li>

<li>

<a href="new_complaint.php">

<i class="bi bi-plus-circle-fill"></i>

Register Complaint

</a>

</li>

<li>

<a href="track_complaint.php">

<i class="bi bi-geo-alt-fill"></i>

Track Complaint

</a>

</li>

<li>

<a href="complaint_history.php">

<i class="bi bi-clock-history"></i>

Complaint History

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

<!-- ===========================
        MAIN CONTENT
=========================== -->

<main class="content">
<!--=========================================
        TOP HEADER
==========================================-->

<div class="d-flex justify-content-between align-items-center flex-wrap mb-4">

    <div>

        <h2 class="fw-bold text-primary mb-1">

            Complaint Management

        </h2>

        <p class="text-muted mb-0">

            Manage, track and monitor all your complaints.

        </p>

    </div>

    <div class="text-end">

        <span class="badge bg-success px-3 py-2">

            <i class="bi bi-circle-fill me-1"></i>

            Online

        </span>

        <h6 class="mt-2 text-secondary">

            <?= date("d M Y") ?>

        </h6>

        <h5 id="clock" class="fw-bold text-primary"></h5>

    </div>

</div>

<!--=========================================
        HERO SECTION
==========================================-->

<div class="hero-section">

<div class="row align-items-center">

<!-- Left -->

<div class="col-lg-8">

<span class="badge bg-light text-primary px-3 py-2 mb-3">

<i class="bi bi-lightning-charge-fill"></i>

APDCL Consumer Services

</span>

<h1 class="display-5 fw-bold mb-3">

Welcome,

<?= htmlspecialchars($consumer['name']) ?>

</h1>

<p class="lead mb-4">

Manage electricity complaints, monitor complaint status, register new service requests and track progress through the APDCL Consumer Complaint Management System.

</p>

<div class="d-flex flex-wrap gap-3">

<a
href="new_complaint.php"
class="btn btn-light btn-lg px-4">

<i class="bi bi-plus-circle-fill me-2"></i>

Register Complaint

</a>

<a
href="dashboard.php"
class="btn btn-outline-light btn-lg px-4">

<i class="bi bi-speedometer2 me-2"></i>

Dashboard

</a>

</div>

</div>

<!-- Right -->

<div class="col-lg-4 mt-4 mt-lg-0">

<div class="consumer-card">

<img
src="../assets/images/user.jpg"
class="consumer-avatar">

<h4>

<?= htmlspecialchars($consumer['name']) ?>

</h4>

<p class="text-muted">

Registered Consumer

</p>

<hr>

<div class="row text-start">

<div class="col-6">

<strong>Consumer</strong>

</div>

<div class="col-6 text-end">

<?= htmlspecialchars($consumer_no) ?>

</div>

<div class="col-6 mt-3">

<strong>Status</strong>

</div>

<div class="col-6 mt-3 text-end">

<span class="badge bg-success">

Active

</span>

</div>

<div class="col-6 mt-3">

<strong>Today's Date</strong>

</div>

<div class="col-6 mt-3 text-end">

<?= date("d M Y") ?>

</div>

</div>

</div>

</div>

</div>

</div>

<!--=========================================
        DASHBOARD OVERVIEW
==========================================-->

<div class="row g-4 mb-5">

<div class="col-xl-2 col-lg-4 col-md-4 col-6">

<div class="stats-box total">

<div class="stats-icon">

<i class="bi bi-chat-square-text-fill"></i>

</div>

<div class="stats-content">

<h2><?= $totalComplaints ?></h2>

<p>Total Complaints</p>

</div>

</div>

</div>


<div class="col-xl-2 col-lg-4 col-md-4 col-6">

<div class="stats-box pending">

<div class="stats-icon">

<i class="bi bi-hourglass-split"></i>

</div>

<div class="stats-content">

<h2><?= $pendingComplaints ?></h2>

<p>Pending</p>

</div>

</div>

</div>


<div class="col-xl-2 col-lg-4 col-md-4 col-6">

<div class="stats-box assigned">

<div class="stats-icon">

<i class="bi bi-person-check-fill"></i>

</div>

<div class="stats-content">

<h2><?= $assignedComplaints ?></h2>

<p>Assigned</p>

</div>

</div>

</div>


<div class="col-xl-2 col-lg-4 col-md-4 col-6">

<div class="stats-box progress">

<div class="stats-icon">

<i class="bi bi-tools"></i>

</div>

<div class="stats-content">

<h2><?= $progressComplaints ?></h2>

<p>In Progress</p>

</div>

</div>

</div>


<div class="col-xl-2 col-lg-4 col-md-4 col-6">

<div class="stats-box resolved">

<div class="stats-icon">

<i class="bi bi-patch-check-fill"></i>

</div>

<div class="stats-content">

<h2><?= $resolvedComplaints ?></h2>

<p>Resolved</p>

</div>

</div>

</div>


<div class="col-xl-2 col-lg-4 col-md-4 col-6">

<div class="stats-box today">

<div class="stats-icon">

<i class="bi bi-calendar-check-fill"></i>

</div>

<div class="stats-content">

<h2><?= $todayComplaints ?></h2>

<p>Today</p>

</div>

</div>

</div>

</div>

<!--=========================================
        SEARCH & QUICK ACTION
==========================================-->

<div class="card search-card mb-5">

<div class="card-body p-4">

<div class="row align-items-center">

<!-- Left -->

<div class="col-lg-8">

<h4 class="fw-bold mb-3">

<i class="bi bi-search text-primary me-2"></i>

Search Complaints

</h4>

<form method="GET">

<div class="input-group input-group-lg shadow-sm">

<span class="input-group-text bg-white border-0">

<i class="bi bi-search text-primary"></i>

</span>

<input
type="text"
name="search"
class="form-control border-0"
placeholder="Search by Complaint ID, Subject, Category or Status..."
value="<?= htmlspecialchars($search) ?>">

<button
class="btn btn-primary px-4"
type="submit">

<i class="bi bi-search me-2"></i>

Search

</button>

<?php if($search!=""){ ?>

<a
href="complaint.php"
class="btn btn-outline-secondary">

<i class="bi bi-arrow-clockwise me-2"></i>

Reset

</a>

<?php } ?>

</div>

</form>

</div>

<!-- Right -->

<div class="col-lg-4 text-lg-end mt-4 mt-lg-0">

<a
href="new_complaint.php"
class="btn btn-success btn-lg rounded-pill px-4">

<i class="bi bi-plus-circle-fill me-2"></i>

Register Complaint

</a>

</div>

</div>

</div>

</div>

<!--=========================================
        MY COMPLAINTS
==========================================-->

<div class="card complaint-card mb-5">

<div class="card-header bg-white border-0 py-4 px-4">

<div class="d-flex justify-content-between align-items-center flex-wrap">

<div>

<h3 class="fw-bold text-primary mb-1">

<i class="bi bi-chat-left-text-fill me-2"></i>

My Complaints

</h3>

<p class="text-muted mb-0">

Track, view and manage your registered complaints.

</p>

</div>

<div>

<span class="badge bg-primary fs-6 px-4 py-2">

<?= mysqli_num_rows($complaints) ?>

Records

</span>

</div>

</div>

</div>

<div class="table-responsive">

<table class="table table-hover align-middle modern-table mb-0">

<thead>

<tr>

<th>#</th>

<th>Complaint ID</th>

<th>Category</th>

<th>Subject</th>

<th>Priority</th>

<th>Status</th>

<th>Date</th>

<th class="text-center">Actions</th>

</tr>

</thead>

<tbody>

<?php

$sl=1;

while($row=mysqli_fetch_assoc($complaints)){

switch($row['priority']){

case "Low":
$priority="success";
break;

case "Medium":
$priority="warning";
break;

case "High":
$priority="danger";
break;

default:
$priority="secondary";
}

switch($row['status']){

case "Pending":
$status="warning";
break;

case "Assigned":
$status="info";
break;

case "In Progress":
$status="primary";
break;

case "Resolved":
$status="success";
break;

case "Rejected":
$status="danger";
break;

default:
$status="secondary";
}

?>

<tr>

<td><?= $sl++ ?></td>

<td>

<strong class="text-primary">

<?= htmlspecialchars($row['complaint_id']) ?>

</strong>

</td>

<td><?= htmlspecialchars($row['category']) ?></td>

<td><?= htmlspecialchars($row['subject']) ?></td>

<td>

<span class="badge bg-<?= $priority ?>">

<?= htmlspecialchars($row['priority']) ?>

</span>

</td>

<td>

<span class="badge bg-<?= $status ?>">

<?= htmlspecialchars($row['status']) ?>

</span>

</td>

<td>

<?= date("d M Y",strtotime($row['created_at'])) ?>

</td>

<td class="text-center">

<a
href="track_complaint.php?id=<?= $row['id'] ?>"
class="btn btn-primary btn-sm rounded-circle me-1"
title="Track">

<i class="bi bi-geo-alt-fill"></i>

</a>

<a
href="view_complaint.php?id=<?= $row['id'] ?>"
class="btn btn-success btn-sm rounded-circle me-1"
title="View">

<i class="bi bi-eye-fill"></i>

</a>

<?php if($row['status']=="Pending"){ ?>

<a
href="edit_complaint.php?id=<?= $row['id'] ?>"
class="btn btn-warning btn-sm rounded-circle"
title="Edit">

<i class="bi bi-pencil-fill"></i>

</a>

<?php } ?>

</td>

</tr>

<?php } ?>

<?php if(mysqli_num_rows($complaints)==0){ ?>

<tr>

<td colspan="8" class="text-center py-5">

<i class="bi bi-inbox display-1 text-secondary"></i>

<h3 class="mt-3">

No Complaints Found

</h3>

<p class="text-muted">

You haven't registered any complaints yet.

</p>

<a
href="new_complaint.php"
class="btn btn-primary rounded-pill px-4">

<i class="bi bi-plus-circle-fill me-2"></i>

Register Complaint

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<!--=========================================
        PROFESSIONAL FOOTER
==========================================-->

<footer class="footer mt-5">

<div class="container-fluid">

<div class="card border-0 shadow-lg">

<div class="card-body py-5">

<div class="row">

<!-- APDCL -->

<div class="col-lg-4 mb-4">

<img
src="../assets/images/logo-circle.png"
width="75"
class="mb-3">

<h4 class="fw-bold text-primary">

Assam Power Distribution Company Limited

</h4>

<p class="text-muted">

Consumer Complaint Management Portal

Internship Demonstration Project

</p>

</div>

<!-- Quick Links -->

<div class="col-lg-4 mb-4">

<h5 class="fw-bold">

Quick Links

</h5>

<ul class="list-unstyled">

<li class="mb-2">

<a href="dashboard.php">

<i class="bi bi-speedometer2 me-2"></i>

Dashboard

</a>

</li>

<li class="mb-2">

<a href="new_complaint.php">

<i class="bi bi-plus-circle me-2"></i>

Register Complaint

</a>

</li>

<li class="mb-2">

<a href="track_complaint.php">

<i class="bi bi-geo-alt me-2"></i>

Track Complaint

</a>

</li>

<li>

<a href="profile.php">

<i class="bi bi-person-circle me-2"></i>

Profile

</a>

</li>

</ul>

</div>

<!-- Contact -->

<div class="col-lg-4">

<h5 class="fw-bold">

Customer Support

</h5>

<p>

<i class="bi bi-telephone-fill text-primary me-2"></i>

1912

</p>

<p>

<i class="bi bi-envelope-fill text-primary me-2"></i>

support@apdcl.org

</p>

<p>

<i class="bi bi-geo-alt-fill text-primary me-2"></i>

Assam, India

</p>

</div>

</div>

<hr>

<div class="text-center">

© <?= date("Y") ?>

<strong>APDCL Consumer Portal</strong>

| Internship Project

</div>

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

    const now = new Date();

    const options = {

        hour:'2-digit',
        minute:'2-digit',
        second:'2-digit',
        hour12:true

    };

    const clock=document.getElementById("clock");

    if(clock){

        clock.innerHTML=now.toLocaleTimeString('en-IN',options);

    }

}

updateClock();

setInterval(updateClock,1000);

/*=========================================
        CARD ANIMATION
=========================================*/

window.addEventListener("load",()=>{

document.querySelectorAll(".stats-box,.card").forEach((card,index)=>{

card.style.opacity="0";
card.style.transform="translateY(30px)";

setTimeout(()=>{

card.style.transition=".5s ease";

card.style.opacity="1";

card.style.transform="translateY(0)";

},index*100);

});

});

/*=========================================
        AUTO HIDE ALERT
=========================================*/

setTimeout(()=>{

document.querySelectorAll(".alert").forEach(alert=>{

bootstrap.Alert.getOrCreateInstance(alert).close();

});

},5000);

/*=========================================
        SMOOTH SCROLL
=========================================*/

document.documentElement.style.scrollBehavior="smooth";

</script>

</body>

</html>
