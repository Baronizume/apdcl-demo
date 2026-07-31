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

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>APDCL Consumer Dashboard</title>

<link rel="icon" href="../assets/images/logo-circle.png">

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
/* ===============================
   PREMIUM APDCL HEADER
=================================*/

body{
    background:#eef3f9;
    font-family:'Poppins',sans-serif;
}

.topbar{
    background:linear-gradient(90deg,#0B2C74,#0D5BD7);
    height:90px;
    border-radius:0 0 22px 22px;
    padding:0 30px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    box-shadow:0 10px 30px rgba(0,0,0,.15);

    position:fixed;
    top:0;
    left:0;
    width:100%;
    z-index:1100;
}

body{
    padding-top:90px;
}

.main-content{
    margin-left:250px;
    width:calc(100% - 250px);
    padding-top:90px;
}

.brand{
    display:flex;
    align-items:center;
    gap:15px;
}

.brand img{
    width:58px;
    height:58px;
    border-radius:50%;
    background:#fff;
    padding:5px;
}

.brand h3{
    color:#fff;
    margin:0;
    font-size:22px;
    font-weight:700;
}

.brand small{
    color:#d8e6ff;
    display:block;
}

.portal-name{
    color:#fff;
    font-size:24px;
    font-weight:700;
}

.top-right{
    display:flex;
    align-items:center;
    gap:20px;
}

.notification{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    text-decoration:none;
}

.notification span{

    position:absolute;
    top:-8px;
    right:-8px;

    background:red;
    color:white;

    width:22px;
    height:22px;

    border-radius:50%;

    font-size:12px;
    font-weight:bold;

    display:flex;
    align-items:center;
    justify-content:center;

}

.user-box{
    display:flex;
    align-items:center;
    gap:12px;
}

.user-box img{
    width:48px;
    height:48px;
    border-radius:50%;
    border:2px solid #fff;
}

.user-box h6{
    color:#fff;
    margin:0;
}

.user-box small{
    color:#d8e6ff;
}

/* Page */

.page-content{
    padding:30px;
}

/* Welcome */

.welcome-box{
    margin-bottom:25px;
}

.welcome-box h2{
    color:#163E8F;
    font-weight:700;
}

.welcome-box p{
    color:#6b7280;
}

/* ===============================
   PREMIUM DASHBOARD CARDS
=================================*/

.stats-card{
    background:#fff;
    border-radius:18px;
    padding:22px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    transition:.3s;
    height:170px;
    position:relative;
    overflow:hidden;
}

.stats-card:hover{
    transform:translateY(-8px);
    box-shadow:0 18px 35px rgba(0,0,0,.12);
}

.stats-card::before{
    content:"";
    position:absolute;
    left:0;
    top:0;
    width:100%;
    height:5px;
    background:linear-gradient(90deg,#0B5ED7,#42A5F5);
}

.stats-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
}

.stats-left{
    display:flex;
    gap:15px;
}

.stats-icon{
    width:62px;
    height:62px;
    border-radius:16px;
    display:flex;
    justify-content:center;
    align-items:center;
    color:#fff;
    font-size:26px;
}

.bg-blue{background:#1565d8;}
.bg-green{background:#28a745;}
.bg-orange{background:#ff9800;}
.bg-purple{background:#7b4dff;}

.stats-title{
    font-size:15px;
    color:#666;
    margin-bottom:6px;
}

.stats-value{
    font-size:38px;
    font-weight:700;
    color:#163E8F;
    line-height:1;
}

.stats-link{
    display:inline-block;
    margin-top:12px;
    text-decoration:none;
    color:#1565d8;
    font-weight:600;
}

.stats-link:hover{
    color:#0B2C74;
}

.stats-arrow{
    font-size:24px;
    color:#c5c5c5;
}

/* ===============================
   PREMIUM PANELS
=================================*/

.dashboard-panel{
    background:#fff;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    overflow:hidden;
    height:100%;
}

.dashboard-panel .panel-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 25px;
    border-bottom:1px solid #edf2f7;
}

.panel-title{
    font-size:20px;
    font-weight:700;
    color:#163E8F;
}

.panel-title i{
    color:#1565d8;
    margin-right:8px;
}

.year-select{
    border:1px solid #dbe4f3;
    border-radius:10px;
    padding:6px 12px;
    outline:none;
    background:#fff;
}

.chart-area{
    padding:25px;
    height:420px;
}

/* Notice */

.notice-list{
    padding:20px;
}

.notice-item{
    display:flex;
    gap:15px;
    margin-bottom:25px;
}

.notice-dot{
    width:14px;
    height:14px;
    border-radius:50%;
    margin-top:6px;
}

.notice-title{
    font-weight:600;
    color:#163E8F;
}

.notice-date{
    color:#999;
    font-size:13px;
}

.notice-text{
    color:#555;
    margin-top:5px;
}

.red{background:#ef4444;}
.green{background:#22c55e;}
.blue{background:#3b82f6;}

.view-all{
    text-decoration:none;
    color:#1565d8;
    font-weight:600;
}

/* ======================================
   TABLE CARDS
====================================== */

.table-card{
    background:#fff;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    margin-top:25px;
}

.table-card .card-header{
    background:#fff;
    border-bottom:1px solid #edf2f7;
    padding:18px 25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.table-card h5{
    margin:0;
    font-weight:700;
    color:#163E8F;
}

.table-card table{
    margin:0;
}

.table-card thead{
    background:#f5f8ff;
}

.table-card thead th{
    color:#163E8F;
    font-weight:600;
    border:none;
}

.table-card tbody td{
    vertical-align:middle;
    border-color:#f1f5f9;
}

.table-card tbody tr:hover{
    background:#f8fbff;
}

.badge-paid{
    background:#22c55e;
    color:#fff;
    padding:6px 12px;
    border-radius:20px;
}

.badge-pending{
    background:#f59e0b;
    color:#fff;
    padding:6px 12px;
    border-radius:20px;
}

.action-btn{
    width:34px;
    height:34px;
    border-radius:8px;
    background:#1565d8;
    color:#fff;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
}

.action-btn:hover{
    background:#0b2c74;
    color:#fff;
}

.apdcl-footer{

margin-top:60px;

background:linear-gradient(90deg,#0B2C74,#1565d8);

color:#fff;

padding:60px 0 20px;

}

.apdcl-footer h5{

font-weight:700;

margin-bottom:20px;

}

.apdcl-footer ul{

padding:0;

list-style:none;

}

.apdcl-footer li{

margin-bottom:12px;

}

.apdcl-footer a{

color:#dbeafe;

text-decoration:none;

}

.apdcl-footer a:hover{

color:#fff;

}

.social-icons{

display:flex;

gap:15px;

margin-top:20px;

}

.social-icons a{

width:42px;

height:42px;

border-radius:50%;

background:rgba(255,255,255,.15);

display:flex;

justify-content:center;

align-items:center;

font-size:18px;

}

/* ==============================
RESPONSIVE
==============================*/

@media(max-width:992px){

.portal-name{

display:none;

}

.topbar{

height:auto;

padding:18px;

flex-wrap:wrap;

gap:15px;

}

.page-content{

padding:20px;

}

.stats-card{

height:auto;

}

.chart-area{

height:300px;

}

}

@media(max-width:768px){

.brand small{

display:none;

}

.user-box{

display:none;

}

.notification{

margin-left:auto;

}

.col-xl-3{

width:50%;

}

.table-responsive{

overflow:auto;

}

.apdcl-footer{

text-align:center;

}

.social-icons{

justify-content:center;

}

}

@media(max-width:576px){

.col-xl-3{

width:100%;

}

.brand img{

width:45px;

height:45px;

}

.brand h3{

font-size:18px;

}

.panel-title{

font-size:18px;

}

.stats-value{

font-size:30px;

}

.chart-area{

height:250px;

}

}

/* ==========================
   DARK MODE
========================== */

.dark-btn{
    width:42px;
    height:42px;
    border-radius:50%;
    border:none;
    background:white;
    color:#0b4ea2;
    cursor:pointer;
    font-size:18px;
}


/* Dark Theme */

body.dark-mode{

    background:#111827;
    color:#e5e7eb;

}


/* Header */

body.dark-mode .header{

    background:linear-gradient(90deg,#020617,#1e3a8a);

}


/* Cards */

body.dark-mode .stats-card,
body.dark-mode .dashboard-card,
body.dark-mode .dashboard-panel,
body.dark-mode .table-card{

    background:#1f2937;
    color:white;

}


/* Text */

body.dark-mode h1,
body.dark-mode h2,
body.dark-mode h3,
body.dark-mode h4,
body.dark-mode h5,
body.dark-mode h6{

    color:#f8fafc;

}


body.dark-mode p,
body.dark-mode td,
body.dark-mode th{

    color:#d1d5db;

}


/* Tables */

body.dark-mode table{

    background:#1f2937;

}


body.dark-mode thead{

    background:#111827;

}


/* Notice */

body.dark-mode .notice-item{

    border-color:#374151;

}


/* Footer */

body.dark-mode .apdcl-footer{

    background:#020617;

}

.notification{
    position:relative;
    color:white;
    text-decoration:none;
    font-size:22px;
}

.notification span{

    position:absolute;
    top:-8px;
    right:-10px;

    background:red;
    color:white;

    width:20px;
    height:20px;

    border-radius:50%;

    font-size:12px;
    display:flex;
    justify-content:center;
    align-items:center;
    font-weight:bold;

}
.quick-btn{

    height:170px;

    border-radius:15px;

    display:flex;

    flex-direction:column;

    justify-content:center;

    align-items:center;

    text-decoration:none;

    color:white;

    font-size:20px;

    font-weight:600;

}


.quick-btn i{

    font-size:40px;

    margin-bottom:15px;

}

.quick-action-col{
    padding:8px;
}

/* Sidebar */
.sidebar{
    width:250px;
    height:calc(100vh - 90px);
    background:#0B2C74;
    position:fixed;
    left:0;
    top:90px;
    padding:25px 15px;
    overflow-y:auto;
    z-index:1000;
}

.main-content{
    margin-left:250px;
}

.sidebar a{
    display:block;
    color:white;
    padding:14px 18px;
    margin-bottom:10px;
    text-decoration:none;
    border-radius:10px;
}

.sidebar a:hover{
    background:#1565d8;
}

.sidebar i{
    margin-right:10px;
}

.main-content{
    margin-left:250px;
    width:calc(100% - 250px);
}


/* Mobile */
@media(max-width:768px){

.sidebar{
    position:relative;
    width:100%;
    top:0;
}

.main-content{
    margin-left:0;
}

}

@media(max-width:768px){

.sidebar{
    position:relative;
    width:100%;
    min-height:auto;
}

.main-content{
    margin-left:0;
    width:100%;
}

}

</style>

</head>

<body>

<!-- ==========================
TOP HEADER
========================== -->
<header class="topbar">

<div class="brand">

<img src="../assets/images/logo-circle.png">

<div>

<h3>APDCL</h3>

<small>Assam Power Distribution Company Limited</small>

</div>

</div>

<div class="portal-name">

Consumer Dashboard

</div>

<button id="darkToggle" class="dark-btn">
    <i class="bi bi-moon-fill"></i>
</button>

<div class="top-right">

<a href="notifications.php" class="notification">

<i class="bi bi-bell-fill"></i>

<?php if($notificationCount > 0){ ?>

<span>
<?= $notificationCount ?>
</span>

<?php } ?>

</a>

<div class="user-box">

<img src="../assets/images/user.jpg">

<div>

<h6><?= htmlspecialchars($consumer['name']) ?></h6>

<small><?= htmlspecialchars($consumer['consumer_no']) ?></small>

</div>

</div>

</div>

</header>

<div class="sidebar">

<a href="dashboard.php">
<i class="bi bi-house"></i> Dashboard
</a>

<a href="bill.php">
<i class="bi bi-receipt"></i> Bills
</a>

<a href="payment.php">
<i class="bi bi-credit-card"></i> Payment
</a>

<a href="complaint.php">
<i class="bi bi-tools"></i> Complaints
</a>

<a href="notifications.php">
<i class="bi bi-bell"></i> Notifications
</a>

<a href="profile.php">
<i class="bi bi-person"></i> Profile
</a>

<a href="logout.php">
<i class="bi bi-box-arrow-right"></i> Logout
</a>

</div>

<div class="main-content">
<div class="page-content">

<div class="welcome-box">

<h2>Welcome, <?= htmlspecialchars($consumer['name']) ?></h2>

<p>Manage your electricity services from one modern dashboard.</p>

</div>

<div class="row g-4">

<!-- Total Bills -->
<div class="col-xl-3 col-md-6">
    <div class="stats-card">

        <div class="stats-header">

            <div class="stats-left">

                <div class="stats-icon bg-blue">
                    <i class="bi bi-receipt"></i>
                </div>

                <div>
                    <div class="stats-title">Total Bills</div>
                    <div class="stats-value"><?= $totalBills ?></div>

                    <a href="bill.php" class="stats-link">
                        View Bills →
                    </a>
                </div>

            </div>

            <i class="bi bi-chevron-right stats-arrow"></i>

        </div>

    </div>
</div>

<!-- Paid Bills -->
<div class="col-xl-3 col-md-6">
    <div class="stats-card">

        <div class="stats-header">

            <div class="stats-left">

                <div class="stats-icon bg-green">
                    <i class="bi bi-check-circle-fill"></i>
                </div>

                <div>
                    <div class="stats-title">Paid Bills</div>
                    <div class="stats-value"><?= $paidBills ?></div>

                    <a href="payment_history.php" class="stats-link">
                        View Payments →
                    </a>
                </div>

            </div>

            <i class="bi bi-chevron-right stats-arrow"></i>

        </div>

    </div>
</div>

<!-- Pending Bills -->
<div class="col-xl-3 col-md-6">
    <div class="stats-card">

        <div class="stats-header">

            <div class="stats-left">

                <div class="stats-icon bg-orange">
                    <i class="bi bi-clock-history"></i>
                </div>

                <div>
                    <div class="stats-title">Pending Bills</div>
                    <div class="stats-value"><?= $pendingBills ?></div>

                    <a href="payment.php" class="stats-link">
                        Pay Now →
                    </a>
                </div>

            </div>

            <i class="bi bi-chevron-right stats-arrow"></i>

        </div>

    </div>
</div>

<!-- Complaints -->
<div class="col-xl-3 col-md-6">
    <div class="stats-card">

        <div class="stats-header">

            <div class="stats-left">

                <div class="stats-icon bg-purple">
                    <i class="bi bi-chat-dots-fill"></i>
                </div>

                <div>
                    <div class="stats-title">Complaints</div>
                    <div class="stats-value"><?= $totalComplaints ?></div>

                    <a href="complaint.php" class="stats-link">
                        Track →
                    </a>
                </div>

            </div>

            <i class="bi bi-chevron-right stats-arrow"></i>

        </div>

    </div>
</div>

</div>

<!-- Power Outage -->

<div class="col-xl-3 col-md-6">

<div class="stats-card">

<div class="stats-header">

<div class="stats-left">

<div class="stats-icon bg-danger">

<i class="bi bi-lightning-charge-fill"></i>

</div>

<div>

<div class="stats-title">
Power Outage
</div>

<div class="stats-value">
<?= $totalOutages ?? 0 ?>
</div>

<a href="outage_map.php" class="stats-link">
View Map →
</a>

</div>

</div>

<i class="bi bi-chevron-right stats-arrow"></i>

</div>

</div>

</div>

<div class="row mt-4">

<!-- Chart -->

<div class="col-lg-8">

<div class="dashboard-panel">

<div class="panel-header">

<div class="panel-title">

<i class="bi bi-graph-up-arrow"></i>

Monthly Electricity Usage

</div>

<select class="year-select">

<option>2026</option>
<option>2025</option>

</select>

</div>

<div class="chart-area">

<canvas id="usageChart"></canvas>

</div>

</div>

</div>

<!-- Notice -->

<div class="col-lg-4">

<div class="dashboard-panel">

<div class="panel-header">

<div class="panel-title">

<i class="bi bi-megaphone-fill"></i>

Notice Board

</div>

<a href="#" class="view-all">

View all

</a>

</div>

<div class="notice-list">

<?php

while($notice=mysqli_fetch_assoc($noticeQuery)){

?>

<div class="notice-item">

<div class="notice-dot blue"></div>

<div>

<div class="notice-title">

<?= htmlspecialchars($notice['title']) ?>

</div>

<div class="notice-date">

<?= date("d M Y",strtotime($notice['notice_date'])) ?>

</div>

<div class="notice-text">

<?php

if(isset($notice['description'])){

echo htmlspecialchars($notice['description']);

}elseif(isset($notice['message'])){

echo htmlspecialchars($notice['message']);

}elseif(isset($notice['notice'])){

echo htmlspecialchars($notice['notice']);

}else{

echo "No description available.";

}

?>

</div>

</div>

</div>

<?php } ?>

</div>

</div>

</div>

</div>

<!-- ==========================================
CHART SCRIPT
========================================== -->

<script>
const ctx=document.getElementById("usageChart");

new Chart(ctx,{

type:"line",

data:{

labels:[<?= "'".implode("','",$months)."'" ?>],

datasets:[{

label:"Units",

data:[<?= implode(",",$units) ?>],

fill:true,

borderColor:"#1565d8",

backgroundColor:"rgba(21,101,216,.12)",

pointBackgroundColor:"#1565d8",

pointRadius:5,

borderWidth:3,

tension:.4

}]

},

options:{

responsive:true,

maintainAspectRatio:false,

plugins:{

legend:{display:false}

},

scales:{

x:{

grid:{display:false}

},

y:{

beginAtZero:true,

grid:{color:"#edf2f7"}

}

}

}

});
</script>

<!-- ==========================================
RECENT BILLS & PAYMENT HISTORY
========================================== -->
<div class="table-card">

<div class="card-header">

<h5><i class="bi bi-receipt me-2"></i>Recent Bills</h5>

<a href="bill.php">View All</a>

</div>

<div class="table-responsive">

<table class="table">

<thead>

<tr>

<th>Month</th>
<th>Units</th>
<th>Amount</th>
<th>Status</th>
<th></th>

</tr>

</thead>

<tbody>

<?php while($bill=mysqli_fetch_assoc($recentBills)){ ?>

<tr>

<td><?= htmlspecialchars($bill['month']) ?></td>

<td><?= $bill['units'] ?></td>

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

<div class="table-card">

<div class="card-header">

<h5><i class="bi bi-credit-card me-2"></i>Payment History</h5>

<a href="payment_history.php">View All</a>

</div>

<div class="table-responsive">

<table class="table">

<thead>

<tr>

<th>Date</th>
<th>Amount</th>
<th>Mode</th>
<th>Transaction</th>
<th></th>

</tr>

</thead>

<tbody>

<?php while($pay=mysqli_fetch_assoc($payments)){ ?>

<tr>

<td><?= date("d M Y",strtotime($pay['payment_date'])) ?></td>

<td>₹<?= number_format($pay['amount'],2) ?></td>

<td><?= htmlspecialchars($pay['payment_method']) ?></td>

<td><?= htmlspecialchars($pay['transaction_id']) ?></td>

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

<!-- ==========================================
QUICK ACTIONS
========================================== -->

<div class="card border-0 shadow-lg rounded-4 mb-4 mt-4">

    <div class="card-header bg-white border-0">

        <h4 class="fw-bold text-primary">

            <i class="bi bi-lightning-charge-fill me-2"></i>

            Quick Actions

        </h4>

    </div>

    <div class="card-body">

        <div class="row text-center g-4">

            <div class="col">

                <a href="bill.php" class="btn btn-primary w-100 py-3">

                    <i class="bi bi-file-earmark-text fs-3 d-block mb-2"></i>

                    View Bills

                </a>

            </div>

            <div class="col-md-3">

                <a href="payment.php" class="btn btn-success w-100 py-3">

                    <i class="bi bi-wallet2 fs-3 d-block mb-2"></i>

                    Pay Bill

                </a>

            </div>

            <div class="col-md-3">

                <a href="complaint.php" class="btn btn-warning w-100 py-3">

                    <i class="bi bi-tools fs-3 d-block mb-2"></i>

                    Complaint

                </a>

            </div>

            <div class="col-md-3">

                <a href="profile.php" class="btn btn-info w-100 py-3 text-white">

                    <i class="bi bi-person-circle fs-3 d-block mb-2"></i>

                    My Profile

                </a>

            </div>


                <div class="col-md-3">

                    <a href="logout.php" class="btn btn-danger w-100 py-3 text-white">

                        <i class="bi bi-box-arrow-right fs-3 d-block mb-2"></i>

                        Logout

                    </a>

                </div>

        </div>

    </div>

</div>

</div>

<!-- ==========================================
FOOTER
========================================== -->

<footer class="apdcl-footer">

<div class="container">

<div class="row gy-4">

<div class="col-lg-4">

<img src="../assets/images/logo-circle.png" width="65">

<h4 class="mt-3">APDCL</h4>

<p>
Assam Power Distribution Company Limited
</p>

<p>
Consumer Portal | Internship Demo Project
</p>

</div>

<div class="col-lg-2">

<h5>Quick Links</h5>

<ul>

<li><a href="dashboard.php">Dashboard</a></li>

<li><a href="bill.php">Bills</a></li>

<li><a href="payment.php">Payments</a></li>

<li><a href="complaint.php">Complaints</a></li>

</ul>

</div>

<div class="col-lg-3">

<h5>Customer Care</h5>

<p>

1912 (24×7)

</p>

<p>

support@apdcl.org

</p>

<p>

www.apdcl.org

</p>

</div>

<div class="col-lg-3">

<h5>Follow Us</h5>

<div class="social-icons">

<a href="#"><i class="bi bi-facebook"></i></a>

<a href="#"><i class="bi bi-twitter-x"></i></a>

<a href="#"><i class="bi bi-instagram"></i></a>

<a href="#"><i class="bi bi-linkedin"></i></a>

</div>

</div>

</div>

<hr>

<div class="text-center">

© <?= date("Y") ?>

APDCL Consumer Portal.

All Rights Reserved.

</div>

</div>

</footer>

<script>

const toggle = document.getElementById("darkToggle");


if(localStorage.getItem("theme") === "dark"){

    document.body.classList.add("dark-mode");

}


toggle.onclick = function(){

    document.body.classList.toggle("dark-mode");


    if(document.body.classList.contains("dark-mode")){

        localStorage.setItem("theme","dark");

        toggle.innerHTML =
        '<i class="bi bi-sun-fill"></i>';

    }
    else{

        localStorage.setItem("theme","light");

        toggle.innerHTML =
        '<i class="bi bi-moon-fill"></i>';

    }

};


</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>