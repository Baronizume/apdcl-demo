<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

// Notification Count

$notificationCount = 0;

$notificationQuery = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM notifications
WHERE consumer_no='$consumer_no'
AND status='Unread'
");

if($notificationQuery){

    $row = mysqli_fetch_assoc($notificationQuery);

    $notificationCount = $row['total'];

}
/* ============================================
   Consumer Information
============================================ */

$consumerQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

$consumer = mysqli_fetch_assoc($consumerQuery);

if(!$consumer){
    die("Consumer not found.");
}

/* ============================================
   Dashboard Statistics
============================================ */

// Total Bills
$totalBills = 0;
$result = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM bills
WHERE consumer_no='$consumer_no'
");

if($result){
    $row = mysqli_fetch_assoc($result);
    $totalBills = $row['total'];
}

// Paid Bills
$paidBills = 0;
$result = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM bills
WHERE consumer_no='$consumer_no'
AND status='Paid'
");

if($result){
    $row = mysqli_fetch_assoc($result);
    $paidBills = $row['total'];
}

// Pending Bills
$pendingBills = 0;
$result = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM bills
WHERE consumer_no='$consumer_no'
AND status='Pending'
");

if($result){
    $row = mysqli_fetch_assoc($result);
    $pendingBills = $row['total'];
}

// Complaints
$totalComplaints = 0;
$result = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM complaint
WHERE consumer_no='$consumer_no'
");

if($result){
    $row = mysqli_fetch_assoc($result);
    $totalComplaints = $row['total'];
}

// Pending Outages

$totalOutages = 0;

$outageResult = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM outages
WHERE sub_division='".$consumer['sub_division']."'
AND status='Pending'
");

if($outageResult){

    $row = mysqli_fetch_assoc($outageResult);

    $totalOutages = $row['total'];

}
/* ============================================
   Monthly Electricity Usage
============================================ */

$months = [];
$units = [];

$usageQuery = mysqli_query($conn,"
SELECT month,units
FROM bills
WHERE consumer_no='$consumer_no'
ORDER BY id ASC
LIMIT 6
");

while($row=mysqli_fetch_assoc($usageQuery)){
    $months[] = $row['month'];
    $units[]  = $row['units'];
}

/* ============================================
   Notice Board
============================================ */

$noticeQuery = mysqli_query($conn,"
SELECT *
FROM notices
ORDER BY notice_date DESC
LIMIT 5
");

/* ============================================
   Recent Bills
============================================ */

$recentBills = mysqli_query($conn,"
SELECT *
FROM bills
WHERE consumer_no='$consumer_no'
ORDER BY id DESC
LIMIT 5
");

/* ============================================
   Payment History
============================================ */

$payments = mysqli_query($conn,"
SELECT *
FROM payments
WHERE consumer_no='$consumer_no'
ORDER BY payment_date DESC
LIMIT 5
");

/* ============================================
   Today's Date
============================================ */

$currentDate = date("d M Y");

// Pending outages

$outageNotify = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM outages
WHERE sub_division='".$consumer['sub_division']."'
AND status='Pending'
");


if($outageNotify){

$row=mysqli_fetch_assoc($outageNotify);

$notificationCount += $row['total'];

}


// Latest notices

$noticeNotify = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM notices
WHERE notice_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");


if($noticeNotify){

$row=mysqli_fetch_assoc($noticeNotify);

$notificationCount += $row['total'];

}
/* ===============================
   NOTIFICATION COUNT
================================ */

$notificationCount = 0;

$notifyQuery = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM notifications
WHERE consumer_no='$consumer_no'
AND status='Unread'
");

if($notifyQuery){

    $row=mysqli_fetch_assoc($notifyQuery);

    $notificationCount=$row['total'];

}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">

<title>APDCL Consumer Dashboard</title>

<link rel="icon" href="../assets/images/logo-circle.png">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{

background:#edf3fb;

overflow-x:hidden;

}

/* =====================
SIDEBAR
===================== */

.sidebar{

position:fixed;

left:0;

top:0;

width:260px;

height:100vh;

background:linear-gradient(180deg,#07245C,#0A3D91);

color:white;

overflow:auto;

z-index:1000;

box-shadow:5px 0 25px rgba(0,0,0,.2);

}

.sidebar-header{

padding:30px 20px;

text-align:center;

border-bottom:1px solid rgba(255,255,255,.1);

}

.sidebar-header img{

width:75px;

background:white;

padding:6px;

border-radius:50%;

}

.sidebar-header h3{

margin-top:15px;

font-weight:700;

}

.sidebar-header p{

font-size:13px;

opacity:.8;

}

.menu{

padding:20px;

}

.menu a{

display:flex;

align-items:center;

gap:15px;

padding:15px;

margin-bottom:8px;

border-radius:12px;

text-decoration:none;

color:white;

transition:.3s;

font-size:15px;

}

.menu a:hover{

background:#1565d8;

transform:translateX(5px);

}

.menu a.active{

background:#1565d8;

}

.menu i{

font-size:20px;

}

/* Customer Care */

.sidebar{
    position:fixed;
    left:0;
    top:0;
    width:260px;
    height:100vh;
    background:linear-gradient(180deg,#07245C,#0A3D91);
    display:flex;
    flex-direction:column;
    overflow:hidden;
}

.menu{
    flex:1;
    overflow-y:auto;
    padding:20px;
}

.sidebar-footer{
    padding:20px;
    border-top:1px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.03);
}
/* =====================
HEADER
===================== */

.header{

position:fixed;

left:260px;

right:0;

top:0;

height:80px;

background:white;

display:flex;

align-items:center;

justify-content:space-between;

padding:0 30px;

box-shadow:0 3px 15px rgba(0,0,0,.08);

z-index:999;

}

.header-left h3{

font-weight:700;

color:#0B4EA2;

margin:0;

}

.header-left small{

color:#888;

}

.header-right{

display:flex;

align-items:center;

gap:25px;

}

.notification{

position:relative;

font-size:24px;

color:#0B4EA2;

text-decoration:none;

}

.notification span{

position:absolute;

top:-7px;

right:-8px;

background:red;

color:white;

width:20px;

height:20px;

display:flex;

align-items:center;

justify-content:center;

border-radius:50%;

font-size:11px;

}

.profile{

display:flex;

align-items:center;

gap:12px;

}

.profile img{

width:45px;

height:45px;

border-radius:50%;

border:2px solid #1565d8;

}

/* =====================
CONTENT
===================== */

.main{

margin-left:260px;

margin-top:80px;

padding:30px;

}

.welcome{

background:white;

padding:25px;

border-radius:20px;

box-shadow:0 5px 20px rgba(0,0,0,.08);

margin-bottom:25px;

}

.welcome h2{

font-weight:700;

color:#0B4EA2;

}

/* ==========================
   PREMIUM DASHBOARD CARDS
========================== */

.stats-card{

background:#fff;

border-radius:20px;

padding:25px;

height:170px;

position:relative;

overflow:hidden;

box-shadow:0 10px 25px rgba(0,0,0,.08);

transition:.35s;

cursor:pointer;

}

.stats-card:hover{

transform:translateY(-8px);

box-shadow:0 20px 35px rgba(0,0,0,.12);

}

.stats-card::before{

content:"";

position:absolute;

left:0;

top:0;

width:100%;

height:5px;

background:linear-gradient(90deg,#1565d8,#4fa3ff);

}

.stats-top{

display:flex;

justify-content:space-between;

align-items:center;

}

.stats-left{

display:flex;

gap:15px;

align-items:center;

}

.stats-icon{

width:65px;

height:65px;

border-radius:18px;

display:flex;

justify-content:center;

align-items:center;

font-size:28px;

color:white;

}

.icon-blue{
background:#1565d8;
}

.icon-green{
background:#22c55e;
}

.icon-orange{
background:#f59e0b;
}

.icon-red{
background:#ef4444;
}

.icon-purple{
background:#7c3aed;
}

.stats-title{

font-size:15px;

color:#777;

margin-bottom:8px;

}

.stats-value{

font-size:38px;

font-weight:700;

color:#0B4EA2;

line-height:1;

}

.stats-link{

display:inline-block;

margin-top:14px;

text-decoration:none;

font-weight:600;

color:#1565d8;

}

.stats-link:hover{

color:#0B2C74;

}

.arrow{

font-size:24px;

color:#d0d0d0;

}

.dashboard-box{

background:#fff;

border-radius:18px;

box-shadow:0 10px 25px rgba(0,0,0,.08);

overflow:hidden;

height:100%;

}

.box-header{

padding:18px 22px;

display:flex;

justify-content:space-between;

align-items:center;

border-bottom:1px solid #eee;

}

.box-header h5{

margin:0;

font-weight:700;

color:#0B4EA2;

}

.box-header a{

text-decoration:none;

font-weight:600;

}

.box-body{

padding:20px;

}

.notice-list{

padding:20px;

max-height:420px;

overflow:auto;

}

.notice-item{

padding-bottom:18px;

margin-bottom:18px;

border-bottom:1px solid #eee;

}

.notice-date{

font-size:12px;

color:#999;

margin-bottom:6px;

}

.notice-title{

font-weight:600;

color:#0B4EA2;

}

.notice-text{

font-size:14px;

color:#666;

margin-top:5px;

}

.map-box{

height:420px;

}

.map-box iframe{

width:100%;

height:100%;

border:none;

}

/* ==========================
   TABLE SECTION
========================== */

.table-card{

background:#fff;

border-radius:18px;

box-shadow:0 10px 25px rgba(0,0,0,.08);

overflow:hidden;

height:100%;

}

.table-header{

padding:18px 22px;

display:flex;

justify-content:space-between;

align-items:center;

border-bottom:1px solid #eee;

}

.table-header h5{

margin:0;

font-weight:700;

color:#0B4EA2;

}

.table-header a{

text-decoration:none;

font-weight:600;

}

.table{

margin:0;

}

.table thead{

background:#f5f8ff;

}

.table thead th{

color:#0B4EA2;

font-weight:600;

}

.table tbody tr:hover{

background:#f8fbff;

}

.badge-paid{

background:#22c55e;

padding:6px 14px;

border-radius:20px;

color:white;

}

.badge-pending{

background:#f59e0b;

padding:6px 14px;

border-radius:20px;

color:white;

}

.action-btn{

width:36px;

height:36px;

border-radius:8px;

display:flex;

justify-content:center;

align-items:center;

background:#1565d8;

color:white;

text-decoration:none;

}

.action-btn:hover{

background:#0B2C74;

color:white;

}

/* Upcoming Outages */

.outage-item{

padding:18px;

border-bottom:1px solid #eee;

}

.outage-item:last-child{

border-bottom:none;

}

.outage-status{

display:inline-block;

padding:5px 12px;

border-radius:20px;

font-size:12px;

font-weight:600;

margin-bottom:8px;

}

.major{

background:#fee2e2;

color:#dc2626;

}

.partial{

background:#fef3c7;

color:#d97706;

}

.planned{

background:#dcfce7;

color:#16a34a;

}

/* ==========================
   APDCL FOOTER
========================== */

.footer{
    margin-top:40px;
    position:relative;
    overflow:hidden;
    border-radius:20px 20px 0 0;
    color:#fff;
}

/* Background Image */
.footer::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:url('../assets/images/footer-bg.png') center center/cover no-repeat;
    z-index:0;
}

/* Blue Overlay */
.footer::after{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(5,35,82,.88);
    z-index:1;
}

/* Keep footer content above image */
.footer-top,
.footer-bottom{
    position:relative;
    z-index:2;
}

.footer-top{
    padding:45px 35px;
}

.footer h5{
    color:#fff;
    font-weight:700;
    margin-bottom:20px;
}

.footer p{
    color:#d8e6ff;
    line-height:1.8;
}

.footer ul{
    list-style:none;
    padding:0;
    margin:0;
}

.footer ul li{
    margin-bottom:12px;
}

.footer ul li a{
    color:#d8e6ff;
    text-decoration:none;
    transition:.3s;
}

.footer ul li a:hover{
    color:#fff;
    padding-left:8px;
}

.footer .social a{
    width:42px;
    height:42px;
    display:inline-flex;
    justify-content:center;
    align-items:center;
    border-radius:50%;
    background:rgba(255,255,255,.15);
    color:#fff;
    margin-right:10px;
    transition:.3s;
    text-decoration:none;
}

.footer .social a:hover{
    background:#2196f3;
    transform:translateY(-3px);
}

.footer-bottom{
    text-align:center;
    padding:18px;
    border-top:1px solid rgba(255,255,255,.15);
    color:#d8e6ff;
}

/* ==========================
   DARK MODE
========================== */

body.dark-mode{
    background:#121212;
    color:#ffffff;
}

/* Header */
body.dark-mode .header{
    background:#1f1f1f;
    color:#fff;
}

/* Sidebar */
body.dark-mode .sidebar{
    background:#0f172a;
}

/* Cards */
body.dark-mode .stat-card,
body.dark-mode .dashboard-box,
body.dark-mode .table-card{
    background:#1e1e1e;
    color:#fff;
    box-shadow:none;
}

/* Tables */
body.dark-mode .table{
    color:#fff;
}

body.dark-mode .table thead{
    background:#2b2b2b;
}

body.dark-mode .table td,
body.dark-mode .table th{
    border-color:#444;
}

/* Footer */
body.dark-mode .footer{
    background:#0b1120;
}

/* Inputs */
body.dark-mode input,
body.dark-mode select{
    background:#2b2b2b;
    color:#fff;
    border:1px solid #555;
}

/* Links */
body.dark-mode a{
    color:#9ecbff;
}

/* Text */
body.dark-mode h1,
body.dark-mode h2,
body.dark-mode h3,
body.dark-mode h4,
body.dark-mode h5,
body.dark-mode h6,
body.dark-mode p,
body.dark-mode span{
    color:#fff;
}

.quick-card{

    background:#fff;
    border-radius:18px;
    padding:25px;
    text-align:center;
    text-decoration:none;
    display:block;

    box-shadow:0 10px 25px rgba(0,0,0,.08);

    transition:.35s;

    color:#163c8f;

    height:100%;
}

.quick-card:hover{

    transform:translateY(-8px);

    box-shadow:0 20px 35px rgba(0,0,0,.18);

    background:linear-gradient(135deg,#2958d6,#1f75ff);

    color:#fff;

}

.quick-icon{

    width:70px;
    height:70px;

    margin:auto;

    border-radius:50%;

    display:flex;

    justify-content:center;

    align-items:center;

    color:#fff;

    font-size:30px;

    margin-bottom:18px;

}

.quick-card h6{

    font-weight:700;

    margin-bottom:8px;

}

.quick-card p{

    font-size:13px;

    margin:0;

    opacity:.8;

}

.section-header{

    display:flex;

    justify-content:space-between;

    align-items:center;

    margin-bottom:20px;

}

.section-header h5{

    margin:0;

    font-weight:700;

    color:#2d4fa8;

}

body.dark-mode .quick-card{

    background:#1e1e1e;

    color:#fff;

}

body.dark-mode .quick-card:hover{

    background:linear-gradient(135deg,#2958d6,#1f75ff);

}

.welcome{
    background:#fff;
    padding:30px;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    margin-bottom:30px;
}

.welcome h2{
    color:#0B4EA2;
    font-weight:700;
    margin-bottom:10px;
}

.welcome .card{
    border-left:5px solid #1565d8;
}

body.dark-mode .welcome{
    background:#1f1f1f;
}

body.dark-mode .welcome .card{
    background:#2b2b2b;
    color:#fff;
}

</style>

</head>

<body>

<div class="sidebar">

<div class="sidebar-header">

<img src="../assets/images/logo-circle.png">

<h3>APDCL</h3>

<p>Assam Power Distribution Company Limited</p>

</div>

<div class="menu">

<a href="dashboard.php" class="active">
<i class="bi bi-grid-fill"></i>
Dashboard
</a>

<a href="bill.php">
<i class="bi bi-receipt"></i>
My Bills
</a>

<a href="payment_history.php">
<i class="bi bi-credit-card"></i>
Payment History
</a>

<a href="complaint.php">
<i class="bi bi-tools"></i>
Complaints
</a>

<a href="track_complaint.php">
<i class="bi bi-search"></i>
Track Complaint
</a>

<a href="outage_map.php">
<i class="bi bi-geo-alt-fill"></i>
Outage Map
</a>

<a href="notice.php">
<i class="bi bi-megaphone-fill"></i>
Notice Board
</a>

<a href="profile.php">
<i class="bi bi-person-circle"></i>
Profile
</a>

<a href="logout.php">
<i class="bi bi-box-arrow-right"></i>
Logout
</a>

</div>

<div class="sidebar-footer">

<h6>Customer Care</h6>

<p>☎ 1912 (24×7)</p>

<p>✉ support@apdcl.org</p>

</div>

</div>

<div class="header">

<div class="header-left">

<h3>Consumer Dashboard</h3>

<small><?= date("l, d F Y") ?></small>

</div>

<div class="header-right">

<a href="notifications.php" class="notification">

<i class="bi bi-bell-fill"></i>

<?php if($notificationCount>0){ ?>

<span><?= $notificationCount ?></span>

<?php } ?>

</a>

<button id="darkModeToggle" class="btn btn-primary rounded-circle ms-2"
        style="width:42px;height:42px;">
    <i class="bi bi-moon-fill"></i>
</button>
<div class="profile">

<img src="../assets/images/user.jpg">

<div>

<strong><?= htmlspecialchars($consumer['name']) ?></strong><br>

<small><?= htmlspecialchars($consumer['consumer_no']) ?></small>

</div>

</div>

</div>

</div>

<?php
// Greeting
$hour = date("H");

if($hour < 12){
    $greeting = "Good Morning";
}elseif($hour < 17){
    $greeting = "Good Afternoon";
}else{
    $greeting = "Good Evening";
}

// Latest Pending Bill
$currentBill = mysqli_query($conn,"
SELECT *
FROM bills
WHERE consumer_no='$consumer_no'
AND status='Pending'
ORDER BY id DESC
LIMIT 1
");

$bill = mysqli_fetch_assoc($currentBill);
?>

<div class="main">

<div class="welcome">

<div class="row align-items-center">

    <!-- Left -->
    <div class="col-lg-8">

        <h2><?= $greeting ?>, <?= htmlspecialchars($consumer['name']) ?> 👋</h2>

        <p class="mb-1">
            <strong>Consumer No:</strong>
            <?= htmlspecialchars($consumer['consumer_no']) ?>
        </p>

        <p class="text-muted">
            Manage your electricity services quickly and securely through the APDCL Consumer Portal.
        </p>

        <div class="mt-3">

            <a href="bill.php" class="btn btn-primary rounded-pill px-4">
                <i class="bi bi-receipt"></i> My Bills
            </a>

            <a href="payment.php" class="btn btn-success rounded-pill px-4 ms-2">
                <i class="bi bi-credit-card"></i> Pay Bill
            </a>

        </div>

    </div>

    <!-- Right -->
    <div class="col-lg-4">

        <div class="card shadow-sm border-0 rounded-4">

            <div class="card-body">

                <small class="text-muted">Current Bill</small>

                <h2 class="text-primary mt-2">
                    ₹<?= isset($bill['total_bill']) ? number_format($bill['total_bill'],2) : "0.00"; ?>
                </h2>

                <?php if(isset($bill['status'])){ ?>

                    <span class="badge bg-warning">
                        <?= $bill['status']; ?>
                    </span>

                <?php } ?>

                <div class="mt-2">

                    <?php if(isset($bill['bill_date'])){ ?>

                        <small class="text-muted">
                            Bill Date:
                            <?= date("d M Y",strtotime($bill['bill_date'])) ?>
                        </small>

                    <?php } ?>

                </div>

            </div>

        </div>

    </div>

<div class="row mt-3">

    <div class="col-md-4 mb-2">
        <i class="bi bi-geo-alt-fill text-danger"></i>
        <strong>Sub Division:</strong><br>
        <span><?= htmlspecialchars($consumer['sub_division']) ?></span>
    </div>

    <div class="col-md-4 mb-2">
        <i class="bi bi-calendar-event-fill text-primary"></i>
        <strong>Date:</strong><br>
        <span id="currentDate"></span>
    </div>

    <div class="col-md-4 mb-2">
        <i class="bi bi-clock-fill text-success"></i>
        <strong>Time:</strong><br>
        <span id="currentTime"></span>
    </div>

</div>

</div>

</div>

<div class="row mt-4">

    <!-- Monthly Chart -->
    <div class="col-lg-6">

        <div class="dashboard-box">

            <div class="box-header">
                <h5>Monthly Electricity Usage (Units)</h5>

                <select class="form-select w-auto">
                    <option>2026</option>
                    <option>2025</option>
                </select>
            </div>

            <div class="box-body">
                <canvas id="usageChart" height="260"></canvas>
            </div>

        </div>

    </div>

    <!-- Notice Board -->
    <div class="col-lg-3">

        <div class="dashboard-box">

            <div class="box-header">

                <h5>Notice Board</h5>

                <a href="notice.php">View All</a>

            </div>

            <div class="notice-list">
                <?php while($notice=mysqli_fetch_assoc($noticeQuery)){ ?>

                <div class="notice-item">

                <div class="notice-date">
                <?php

                    if(!empty($notice['notice_date']) && $notice['notice_date']!="0000-00-00"){
                        echo date("d M Y",strtotime($notice['notice_date']));
                    }
                    else{
                        echo "-";
                    }

                ?>
                </div>

                <div class="notice-title">
                <?= htmlspecialchars($notice['title']) ?>
                </div>

                <div class="notice-text">
                <?php

                    if(isset($notice['description']) && $notice['description']!=""){
                        echo htmlspecialchars($notice['description']);
                    }
                    elseif(isset($notice['message']) && $notice['message']!=""){
                        echo htmlspecialchars($notice['message']);
                    }
                    elseif(isset($notice['notice']) && $notice['notice']!=""){
                        echo htmlspecialchars($notice['notice']);
                    }
                    else{
                        echo "No notice available.";
                    }

                ?>
                </div>

                </div>

                <?php } ?>
            </div>

        </div>

    </div>

    <!-- Outage Map -->
    <div class="col-lg-3">

        <div class="dashboard-box">

            <div class="box-header">

                <h5>Power Outage Map</h5>

                <a href="outage_map.php">View All</a>

            </div>

           <div class="map-box">
                <iframe
                src="https://www.google.com/maps?q=Guwahati,Assam&output=embed">
                </iframe>

            </div>

        </div>

    </div>

</div>

<div class="dashboard-box mt-4">

    <div class="section-header">
        <h5><i class="bi bi-lightning-charge-fill"></i> Quick Access</h5>
    </div>

    <div class="row g-4">

        <div class="col-lg-3 col-md-6">
            <a href="bill.php" class="quick-card">
                <div class="quick-icon bg-primary">
                    <i class="bi bi-receipt"></i>
                </div>

                <h6>My Bills</h6>
                <p>View all electricity bills</p>
            </a>
        </div>

        <div class="col-lg-3 col-md-6">
            <a href="payment.php" class="quick-card">
                <div class="quick-icon bg-success">
                    <i class="bi bi-credit-card"></i>
                </div>

                <h6>Pay Bill</h6>
                <p>Pay your bill online</p>
            </a>
        </div>

        <div class="col-lg-3 col-md-6">
            <a href="complaint.php" class="quick-card">
                <div class="quick-icon bg-warning">
                    <i class="bi bi-tools"></i>
                </div>

                <h6>Complaint</h6>
                <p>Register a complaint</p>
            </a>
        </div>

        <div class="col-lg-3 col-md-6">
            <a href="download_bill.php" class="quick-card">
                <div class="quick-icon bg-danger">
                    <i class="bi bi-download"></i>
                </div>

                <h6>Download</h6>
                <p>Download PDF bill</p>
            </a>
        </div>

    </div>

</div>

<div class="row mt-4">

<!-- Recent Bills -->

<div class="col-lg-5">

<div class="table-card">

<div class="table-header">

<h5>Recent Bills</h5>

<a href="bill.php">View All</a>

</div>

<div class="table-responsive">

<table class="table align-middle">

<thead>

<tr>

<th>Month</th>

<th>Amount</th>

<th>Status</th>

<th></th>

</tr>

</thead>

<tbody>

<?php while($bill=mysqli_fetch_assoc($recentBills)){ ?>

<tr>

<td><?= htmlspecialchars($bill['month']) ?></td>

<td>₹<?= number_format($bill['total_bill'],2) ?></td>

<td>

<?php if($bill['status']=="Paid"){ ?>

<span class="badge-paid">Paid</span>

<?php }else{ ?>

<span class="badge-pending">Pending</span>

<?php } ?>

</td>

<td>

<a href="view_bill.php?id=<?= $bill['id'] ?>" class="action-btn">

<i class="bi bi-eye"></i>

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<!-- Payment History -->

<div class="col-lg-4">

<div class="table-card">

<div class="table-header">

<h5>Payment History</h5>

<a href="payment_history.php">View All</a>

</div>

<div class="table-responsive">

<table class="table align-middle">

<thead>

<tr>

<th>Date</th>

<th>Amount</th>

<th></th>

</tr>

</thead>

<tbody>

<?php while($pay=mysqli_fetch_assoc($payments)){ ?>

<tr>

<td><?= date("d M Y",strtotime($pay['payment_date'])) ?></td>

<td>₹<?= number_format($pay['amount'],2) ?></td>

<td>

<a href="payment_receipt.php?id=<?= $pay['id'] ?>" class="action-btn">

<i class="bi bi-download"></i>

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<!-- Upcoming Outages -->

<div class="col-lg-3">

<div class="table-card">

<div class="table-header">

<h5>Upcoming Outages</h5>

<a href="outage_map.php">View All</a>

</div>

<?php

$outages=mysqli_query($conn,"
SELECT *
FROM outages
WHERE sub_division='".$consumer['sub_division']."'
ORDER BY outage_date ASC
LIMIT 3
");

while($out=mysqli_fetch_assoc($outages)){

$status=strtolower($out['status']);

$class="planned";

if($status=="major") $class="major";

elseif($status=="partial") $class="partial";

?>

<div class="outage-item">

<div class="outage-status <?= $class ?>">

<?= htmlspecialchars($out['status']) ?>

</div>

<h6><?= htmlspecialchars($out['area']) ?></h6>

<p class="mb-1">

<?= date("d M Y",strtotime($out['outage_date'])) ?>

</p>

<small>

<?= htmlspecialchars($out['start_time']) ?>

-

<?= htmlspecialchars($out['end_time']) ?>

</small>

</div>

<?php } ?>

</div>

</div>

</div>

<footer class="footer">

    <div class="footer-top">

        <div class="row">

            <!-- APDCL -->
            <div class="col-lg-4">

                <h5>
                    <i class="bi bi-lightning-charge-fill text-warning"></i>
                    Assam Power Distribution Company Ltd.
                </h5>

                <p>
                    Providing reliable, affordable and sustainable electricity
                    services across Assam.
                    This portal allows consumers to manage bills, payments,
                    complaints and outages online.
                </p>

                <div class="social">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-twitter-x"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-youtube"></i></a>
                </div>

            </div>

            <!-- Quick Links -->
            <div class="col-lg-4">

                <h5>Quick Links</h5>

                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="bill.php">My Bills</a></li>
                    <li><a href="payment_history.php">Payment History</a></li>
                    <li><a href="complaint_history.php">Complaints</a></li>
                    <li><a href="outage_map.php">Outage Map</a></li>
                    <li><a href="profile.php">Profile</a></li>
                </ul>

            </div>

            <!-- Customer Care -->
            <div class="col-lg-4">

                <h5>Customer Care</h5>

                <p>
                    <i class="bi bi-telephone-fill"></i>
                    1912 (24×7)
                </p>

                <p>
                    <i class="bi bi-envelope-fill"></i>
                    support@apdcl.org
                </p>

                <p>
                    <i class="bi bi-geo-alt-fill"></i>
                    Bijulee Bhawan, Paltan Bazar,<br>
                    Guwahati, Assam – 781001
                </p>

            </div>

        </div>

    </div>

    <div class="footer-bottom">
        © <?= date('Y') ?> Assam Power Distribution Company Limited (APDCL) |
        Consumer Portal | Internship Demo Project
    </div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('usageChart');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun'],
        datasets: [{
            label: 'Units',
            data: [210,240,195,275,310,290],
            backgroundColor: '#1565d8',
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});
</script>

<script>
const toggleBtn = document.getElementById('darkModeToggle');

// Restore saved theme
if(localStorage.getItem("theme") === "dark"){
    document.body.classList.add("dark-mode");
    toggleBtn.innerHTML = '<i class="bi bi-sun-fill"></i>';
}

// Toggle theme
toggleBtn.addEventListener("click", function(){

    document.body.classList.toggle("dark-mode");

    if(document.body.classList.contains("dark-mode")){
        localStorage.setItem("theme","dark");
        toggleBtn.innerHTML = '<i class="bi bi-sun-fill"></i>';
    }else{
        localStorage.setItem("theme","light");
        toggleBtn.innerHTML = '<i class="bi bi-moon-fill"></i>';
    }

});
</script>

<script>
function updateDateTime() {

    const now = new Date();

    const dateOptions = {
        weekday: "long",
        day: "2-digit",
        month: "long",
        year: "numeric"
    };

    const timeOptions = {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: true
    };

    document.getElementById("currentDate").innerHTML =
        now.toLocaleDateString("en-IN", dateOptions);

    document.getElementById("currentTime").innerHTML =
        now.toLocaleTimeString("en-IN", timeOptions);
}

updateDateTime();
setInterval(updateDateTime, 1000);
</script>

</<body>

</<html>
