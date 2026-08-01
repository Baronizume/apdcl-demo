<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

/*=========================================
    FETCH CONSUMER DETAILS
=========================================*/

$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

if(mysqli_num_rows($userQuery)==0){
    die("Consumer not found.");
}

$user = mysqli_fetch_assoc($userQuery);

/*=========================================
    PAYMENT STATISTICS
=========================================*/

// Total Paid Bills
$totalPaidQuery = mysqli_query($conn,"
SELECT COUNT(id) AS total_paid
FROM payments
WHERE consumer_no='$consumer_no'
AND status='Success'
");

if (!$totalPaidQuery) {
    die("SQL Error: " . mysqli_error($conn));
}

$totalPaid = mysqli_fetch_assoc($totalPaidQuery)['total_paid'];

// Total Paid Amount
$totalAmountQuery = mysqli_query($conn,"
SELECT SUM(amount) AS total_amount
FROM payments
WHERE consumer_no='$consumer_no'
AND status='Success'
");

$totalAmount = mysqli_fetch_assoc($totalAmountQuery)['total_amount'] ?? 0;

// Last Payment
$lastPaymentQuery = mysqli_query($conn,"
SELECT *
FROM payments
WHERE consumer_no='$consumer_no'
ORDER BY payment_date DESC
LIMIT 1
");

$lastPayment = mysqli_fetch_assoc($lastPaymentQuery);

/*=========================================
    SEARCH
=========================================*/

$search = "";

$where = "WHERE p.consumer_no='$consumer_no'";

if(isset($_GET['search']) && $_GET['search']!=""){

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $where .= " AND (

        p.transaction_id LIKE '%$search%'

        OR

        b.bill_no LIKE '%$search%'

        OR

        p.payment_method LIKE '%$search%'

    )";

}

/*=========================================
    PAYMENT HISTORY
=========================================*/

$paymentQuery = mysqli_query($conn,"
SELECT
    p.*,
    b.bill_no,
    b.month,
    b.due_date,
    b.total_bill
FROM payments p
LEFT JOIN bills b
ON p.bill_no = b.bill_no
$where
ORDER BY p.payment_date DESC
");

if(!$paymentQuery){
    die("SQL Error: ".mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>APDCL | Payment History</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{
background:#eef4fb;
overflow-x:hidden;
}

/*========== Layout ==========*/

.wrapper{
display:flex;
min-height:100vh;
}

/*========== Sidebar ==========*/

.sidebar{

width:270px;

background:linear-gradient(180deg,#0B2C74,#1565d8);

position:fixed;

left:0;

top:0;

bottom:0;

padding:25px;

display:flex;

flex-direction:column;

color:#fff;

box-shadow:5px 0 20px rgba(0,0,0,.15);

z-index:1000;

}

.logo{

text-align:center;

margin-bottom:35px;

}

.logo img{

width:85px;

height:85px;

border-radius:50%;

background:#fff;

padding:6px;

margin-bottom:10px;

}

.logo h4{

font-weight:700;

margin-bottom:3px;

}

.logo small{

opacity:.8;

}

.menu{

list-style:none;

padding:0;

margin-top:20px;

flex:1;

}

.menu li{

margin-bottom:8px;

}

.menu a{

display:flex;

align-items:center;

gap:15px;

padding:14px 18px;

border-radius:12px;

color:#fff;

text-decoration:none;

transition:.3s;

}

.menu a:hover{

background:rgba(255,255,255,.15);

transform:translateX(5px);

}

.menu .active a{

background:#fff;

color:#0B2C74;

font-weight:600;

}

.menu i{

font-size:20px;

}

/*========== Customer Care ==========*/

.support{

margin-top:auto;

background:rgba(255,255,255,.15);

padding:20px;

border-radius:18px;

text-align:center;

}

.support i{

font-size:40px;

}

.support h2{

font-size:34px;

font-weight:700;

margin:8px 0;

}

/*========== Main ==========*/

.main{

margin-left:270px;

width:calc(100% - 270px);

padding:25px;

}

/*========== Header ==========*/

.header{

background:#fff;

border-radius:20px;

padding:18px 28px;

display:flex;

justify-content:space-between;

align-items:center;

box-shadow:0 10px 25px rgba(0,0,0,.08);

margin-bottom:30px;

}

.header h3{

margin:0;

font-weight:700;

color:#0B2C74;

}

.header p{

margin:0;

color:#777;

font-size:14px;

}

.header-right{

display:flex;

align-items:center;

gap:18px;

}

.header-right i{

font-size:22px;

cursor:pointer;

color:#0B2C74;

}

.user{

display:flex;

align-items:center;

gap:12px;

}

.user img{

width:45px;

height:45px;

border-radius:50%;

object-fit:cover;

border:3px solid #1565d8;

}

.notification{

position:relative;

}

.notification span{

position:absolute;

top:-6px;

right:-8px;

background:red;

color:#fff;

width:18px;

height:18px;

border-radius:50%;

font-size:11px;

display:flex;

align-items:center;

justify-content:center;

}

.profile-img{

width:150px;

height:150px;

border-radius:50%;

border:5px solid #1565d8;

object-fit:cover;

}

.info-box{

display:flex;

align-items:center;

gap:15px;

padding:18px;

background:#f8fbff;

border-radius:15px;

transition:.3s;

}

.info-box:hover{

transform:translateY(-5px);

box-shadow:0 10px 20px rgba(0,0,0,.08);

}

.info-box i{

font-size:28px;

color:#1565d8;

width:55px;

height:55px;

display:flex;

align-items:center;

justify-content:center;

background:#e8f2ff;

border-radius:50%;

}

.summary-card{

position:relative;

padding:30px;

border-radius:22px;

overflow:hidden;

color:#fff;

transition:.35s;

box-shadow:0 15px 25px rgba(0,0,0,.12);

}

.summary-card:hover{

transform:translateY(-8px);

}

.paid{

background:linear-gradient(135deg,#1abc9c,#16a085);

}

.amount{

background:linear-gradient(135deg,#1565d8,#0B2C74);

}

.last{

background:linear-gradient(135deg,#f39c12,#e67e22);

}

.summary-card h2{

font-size:34px;

font-weight:700;

margin-top:10px;

}

.icon-bg{

position:absolute;

right:20px;

bottom:20px;

font-size:60px;

opacity:.18;

}

.table{

border-collapse:separate;

border-spacing:0 12px;

}

.table thead th{

background:#0B2C74;

color:#fff;

border:none;

padding:15px;

}

.table tbody tr{

background:#fff;

box-shadow:0 8px 18px rgba(0,0,0,.06);

transition:.3s;

}

.table tbody tr:hover{

transform:scale(1.01);

}

.table td{

padding:18px;

vertical-align:middle;

border:none;

}

.input-group-text{

border:none;

}

.form-control{

border:none;

box-shadow:none;

}

.form-control:focus{

box-shadow:none;

}

/*================ Footer ================*/

.footer{

margin-top:50px;

background:linear-gradient(rgba(5,35,82,.92),rgba(5,35,82,.92)),

url('../assets/images/footer-bg.png') center/cover;

padding:45px;

color:#fff;

border-radius:20px;

}

.footer-logo{

width:70px;

margin-bottom:15px;

}

.footer h4,

.footer h5{

font-weight:700;

margin-bottom:20px;

}

.footer p{

color:#d7e5ff;

}

.footer ul{

list-style:none;

padding:0;

}

.footer li{

margin-bottom:12px;

}

.footer a{

color:#d7e5ff;

text-decoration:none;

transition:.3s;

}

.footer a:hover{

padding-left:8px;

color:#fff;

}

.social a{

display:inline-flex;

width:42px;

height:42px;

justify-content:center;

align-items:center;

background:rgba(255,255,255,.15);

border-radius:50%;

margin-right:8px;

font-size:18px;

}

.social a:hover{

background:#2196f3;

}

.dark{

background:#111827;

}

.dark .header,

.dark .card,

.dark .table tbody tr{

background:#1f2937 !important;

color:#fff;

}

.dark .form-control{

background:#374151;

color:#fff;

border:none;

}

.dark .table thead th{

background:#000;

}

.dark .footer{

background:#08172e;

}

#topBtn{

display:none;

position:fixed;

right:25px;

bottom:25px;

width:48px;

height:48px;

border:none;

border-radius:50%;

background:#1565d8;

color:#fff;

font-size:20px;

box-shadow:0 8px 20px rgba(0,0,0,.3);

z-index:999;

}

#topBtn:hover{

background:#0B2C74;

}

.profile-card{
    border-radius:25px;
    overflow:hidden;
    transition:.3s;
}

.profile-card:hover{
    transform:translateY(-5px);
}

.profile-photo{
    width:160px;
    height:160px;
    object-fit:cover;
    border-radius:50%;
    border:6px solid #1565d8;
    box-shadow:0 12px 25px rgba(0,0,0,.15);
    background:#fff;
}

.info-box{
    display:flex;
    align-items:center;
    gap:15px;
    background:#f8fbff;
    border-radius:16px;
    padding:18px;
    transition:.3s;
    height:100%;
    border:1px solid #e6eefc;
}

.info-box:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.info-box i{
    width:55px;
    height:55px;
    border-radius:50%;
    background:#eaf3ff;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:24px;
    color:#1565d8;
}

.info-box h6{
    margin:0;
    font-weight:600;
}

.info-box small{
    color:#777;
}

.profile-card .btn{
    padding:12px 28px;
    font-weight:600;
}

</style>

</head>

<body>

<div class="wrapper">

<!-- Sidebar -->

<div class="sidebar">

<div class="logo">

<img src="../assets/images/logo-circle.png">

<h4>APDCL</h4>

<small>Consumer Portal</small>

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

<li class="active">

<a href="payment_history.php">

<i class="bi bi-credit-card"></i>

Payment History

</a>

</li>

<li>

<a href="complaint.php">

<i class="bi bi-tools"></i>

Complaints

</a>

</li>

<li>

<a href="track_complaint.php">

<i class="bi bi-geo-alt"></i>

Track Complaint

</a>

</li>

<li>

<a href="outage_map.php">

<i class="bi bi-lightning-charge"></i>

Outage Map

</a>

</li>

<li>

<a href="notice_board.php">

<i class="bi bi-megaphone"></i>

Notice Board

</a>

</li>

<li>

<a href="profile.php">

<i class="bi bi-person-circle"></i>

Profile

</a>

</li>

<li>

<a href="logout.php">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>

</li>

</ul>

<div class="support">

<i class="bi bi-headset"></i>

<h5>Customer Care</h5>

<h2>1912</h2>

<small>24×7 Support</small>

</div>

</div>

<!-- Main -->

<div class="main">

<div class="header">

<div>

<h3>Payment History</h3>

<p>Manage all electricity bill payments</p>

</div>

<div class="header-right">

<a href="notifications.php" class="notification text-decoration-none">

<i class="bi bi-bell-fill"></i>

<span>3</span>

</a>

<button id="darkModeToggle" class="btn btn-outline-primary btn-sm">

<i class="bi bi-moon-fill"></i>

</button>

<div class="user">

<img src="<?= !empty($photo) ? $photo : '../assets/images/user.jpg'; ?>" alt="Profile">

<div>

<strong><?= htmlspecialchars($user['name']) ?></strong>

<br>

<small>Consumer</small>

</div>

</div>

</div>

</div>

<!--==============================
    CONSUMER PROFILE
===============================-->

<div class="card border-0 shadow-lg rounded-4 mb-4">

<div class="card-body p-4">

<div class="row align-items-center">

<div class="col-lg-2 text-center">

<img src="<?= !empty($photo) ? $photo : '../assets/images/user.jpg'; ?>"
     class="profile-photo"
     alt="Consumer Profile">

</div>

<div class="col-lg-7">

<span class="badge bg-success px-3 py-2 mb-3">

<i class="bi bi-patch-check-fill"></i>

Active Consumer

</span>

<h2 class="fw-bold text-primary">

<?= htmlspecialchars($user['name']) ?>

</h2>

<p class="text-muted">

Consumer No :

<strong>

<?= htmlspecialchars($user['consumer_no']) ?>

</strong>

</p>

<div class="row mt-4">

<div class="col-md-6 mb-3">

<div class="info-box">

<i class="bi bi-envelope-fill"></i>

<div>

<small>Email</small>

<h6><?= htmlspecialchars($user['email']) ?></h6>

</div>

</div>

</div>

<div class="col-md-6 mb-3">

<div class="info-box">

<i class="bi bi-phone-fill"></i>

<div>

<small>Mobile</small>

<h6><?= htmlspecialchars($user['mobile']) ?></h6>

</div>

</div>

</div>

</div>

</div>

<div class="col-md-6 mb-3">

    <div class="info-box">

        <i class="bi bi-geo-alt-fill"></i>

        <div>

            <small>Sub Division</small>

            <h6><?= htmlspecialchars($user['sub_division']) ?></h6>

        </div>

    </div>

</div>

<div class="col-lg-3 text-end">

<a href="profile.php"

class="btn btn-primary rounded-pill px-4">

<i class="bi bi-pencil-square"></i>

Edit Profile

</a>

</div>

</div>

</div>

</div>

<div class="row g-4 mb-4">

<div class="col-lg-4">

<div class="summary-card paid">

<i class="bi bi-check-circle-fill icon-bg"></i>

<h6>Total Payments</h6>

<h2><?= $totalPaid ?></h2>

<p>Successful Transactions</p>

</div>

</div>

<div class="col-lg-4">

<div class="summary-card amount">

<i class="bi bi-cash-stack icon-bg"></i>

<h6>Total Amount Paid</h6>

<h2>

₹ <?= number_format($totalAmount,2) ?>

</h2>

<p>Lifetime Payments</p>

</div>

</div>

<div class="col-lg-4">

<div class="summary-card last">

<i class="bi bi-calendar-check-fill icon-bg"></i>

<h6>Last Payment</h6>

<h2>

<?php

if($lastPayment){

echo date("d M Y",strtotime($lastPayment['payment_date']));

}else{

echo "--";

}

?>

</h2>

<p>Latest Transaction</p>

</div>

</div>

</div>

<!--==============================
SEARCH PAYMENT
===============================-->

<div class="card border-0 shadow-lg rounded-4 mb-4">

<div class="card-body p-4">

<div class="row align-items-center">

<div class="col-lg-8">

<h4 class="fw-bold text-primary mb-1">

<i class="bi bi-search"></i>

Search Payment

</h4>

<small class="text-muted">

Search using Bill Number, Transaction ID or Payment Method

</small>

</div>

<div class="col-lg-4 text-end">

<span class="badge bg-primary fs-6">

<?= mysqli_num_rows($paymentQuery) ?>

Records

</span>

</div>

</div>

<form method="GET" class="mt-4">

<div class="input-group input-group-lg">

<span class="input-group-text bg-primary text-white">

<i class="bi bi-search"></i>

</span>

<input

type="text"

name="search"

class="form-control"

placeholder="Search here..."

value="<?= htmlspecialchars($search) ?>">

<button class="btn btn-primary">

<i class="bi bi-search"></i>

Search

</button>

</div>

</form>

</div>

</div>

<!--==============================
PAYMENT HISTORY
===============================-->

<div class="card border-0 shadow-lg rounded-4">

<div class="card-header bg-primary text-white py-3">

<h4 class="mb-0">

<i class="bi bi-credit-card-fill"></i>

Payment History

</h4>

</div>

<div class="card-body">

<?php if(mysqli_num_rows($paymentQuery)>0){ ?>

<div class="table-responsive">

<table class="table align-middle table-hover">

<thead>

<tr>

<th>#</th>

<th>Bill No</th>

<th>Month</th>

<th>Amount</th>

<th>Method</th>

<th>Transaction</th>

<th>Date</th>

<th>Status</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php

$sl=1;

while($row=mysqli_fetch_assoc($paymentQuery)){

?>

<tr>

<td><?= $sl++ ?></td>

<td><strong><?= $row['bill_no'] ?></strong></td>

<td><?= $row['month'] ?></td>

<td>

<span class="text-success fw-bold">

₹ <?= number_format($row['amount'],2) ?>

</span>

</td>

<td>

<span class="badge bg-info">

<?= $row['payment_method'] ?>

</span>

</td>

<td>

<small><?= $row['transaction_id'] ?></small>

</td>

<td>

<?= date("d M Y",strtotime($row['payment_date'])) ?>

</td>

<td>

<?php

if($row['status']=="Success"){

echo '<span class="badge bg-success">Paid</span>';

}elseif($row['status']=="Pending"){

echo '<span class="badge bg-warning text-dark">Pending</span>';

}else{

echo '<span class="badge bg-danger">Failed</span>';

}

?>

</td>

<td>

<a href="view_receipt.php?id=<?= $row['id'] ?>"

class="btn btn-sm btn-primary">

<i class="bi bi-eye-fill"></i>

</a>

<a href="download_receipt.php?id=<?= $row['id'] ?>"

class="btn btn-sm btn-success">

<i class="bi bi-download"></i>

</a>

<button

onclick="window.open('print_receipt.php?id=<?= $row['id'] ?>')"

class="btn btn-sm btn-dark">

<i class="bi bi-printer-fill"></i>

</button>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<?php } else { ?>

<div class="text-center py-5">

<i class="bi bi-credit-card display-1 text-secondary"></i>

<h3>No Payment Records</h3>

<p class="text-muted">

No payment history found.

</p>

<a href="bill.php" class="btn btn-primary">

Pay Electricity Bill

</a>

</div>

<?php } ?>

</div>

</div>

<!-- ================= FOOTER ================= -->

<footer class="footer mt-5">

<div class="container-fluid">

<div class="row">

<div class="col-lg-4">

<img src="../assets/images/logo-circle.png" class="footer-logo">

<h4>APDCL</h4>

<p>
Assam Power Distribution Company Limited
</p>

<div class="social">

<a href="#"><i class="bi bi-facebook"></i></a>

<a href="#"><i class="bi bi-twitter-x"></i></a>

<a href="#"><i class="bi bi-instagram"></i></a>

<a href="#"><i class="bi bi-youtube"></i></a>

</div>

</div>

<div class="col-lg-4">

<h5>Quick Links</h5>

<ul>

<li><a href="dashboard.php">Dashboard</a></li>

<li><a href="bill.php">My Bills</a></li>

<li><a href="payment_history.php">Payment History</a></li>

<li><a href="complaint.php">Complaints</a></li>

<li><a href="profile.php">Profile</a></li>

</ul>

</div>

<div class="col-lg-4">

<h5>Customer Care</h5>

<p><i class="bi bi-telephone-fill"></i> 1912</p>

<p><i class="bi bi-envelope-fill"></i> support@apdcl.org</p>

<p>
<i class="bi bi-geo-alt-fill"></i>
Bijulee Bhawan, Paltan Bazar,
Guwahati, Assam
</p>

</div>

</div>

<hr>

<div class="text-center">

© <?= date('Y') ?>

APDCL Consumer Portal

|

Internship Demo Project

</div>

</div>

</footer>

<button id="topBtn">

<i class="bi bi-arrow-up"></i>

</button>

<script>

const darkBtn=document.getElementById("darkModeToggle");

if(darkBtn){

darkBtn.onclick=function(){

document.body.classList.toggle("dark");

localStorage.setItem(

"theme",

document.body.classList.contains("dark")

?

"dark"

:

"light"

);

};

}

if(localStorage.getItem("theme")=="dark"){

document.body.classList.add("dark");

}

const topBtn=document.getElementById("topBtn");

window.onscroll=function(){

topBtn.style.display=

window.scrollY>300

?

"block"

:

"none";

};

topBtn.onclick=function(){

window.scrollTo({

top:0,

behavior:"smooth"

});

};

</script>

</body>

</html>
