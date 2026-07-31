<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

/* ===============================
   Consumer Information
================================= */
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

/* ===============================
   Bills
================================= */
$bills = mysqli_query($conn,"
SELECT *
FROM bills
WHERE consumer_no='$consumer_no'
ORDER BY id DESC
");

if(!$bills){
    die(mysqli_error($conn));
}

/* ===============================
   Dashboard Statistics
================================= */

$totalBills = mysqli_num_rows($bills);

$paidBills = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM bills
WHERE consumer_no='$consumer_no'
AND status='Paid'
"))['total'];

$pendingBills = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM bills
WHERE consumer_no='$consumer_no'
AND status='Pending'
"))['total'];

$totalAmount = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT SUM(total_bill) total
FROM bills
WHERE consumer_no='$consumer_no'
"))['total'];

if(empty($totalAmount)){
    $totalAmount = 0;
}

$paymentRate = ($totalBills>0)
? round(($paidBills/$totalBills)*100)
:0;

// Monthly Usage Chart Data
$months = [];
$units = [];

$chartQuery = mysqli_query($conn,"
SELECT month, units
FROM bills
WHERE consumer_no='$consumer_no'
ORDER BY id ASC
");

while($row = mysqli_fetch_assoc($chartQuery)){
    $months[] = $row['month'];
    $units[] = $row['units'];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>My Bills | APDCL Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<style>
body{
    margin:0;
    background:#f4f7fc;
    font-family:'Segoe UI',sans-serif;
}

/* Wrapper */

.wrapper{
    display:flex;
}

/* Sidebar */

.sidebar{
    width:260px;
    height:100vh;
    position:fixed;
    left:0;
    top:0;
    background:#0D47A1;
    color:#fff;
    overflow-y:auto;
    box-shadow:3px 0 15px rgba(0,0,0,.15);
}

.sidebar-header{
    padding:30px 20px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,.15);
}

.sidebar-header img{
    width:75px;
    background:#fff;
    border-radius:50%;
    padding:5px;
}

.sidebar-header h4{
    margin-top:12px;
    font-weight:700;
}

.sidebar-header small{
    color:#ddd;
}

.consumer-card{
    text-align:center;
    padding:25px;
}

.consumer-card img{
    width:85px;
    height:85px;
    border-radius:50%;
    background:#fff;
    padding:5px;
}

.consumer-card h6{
    margin-top:12px;
    font-weight:600;
}

.consumer-card small{
    color:#ddd;
}

/* Menu */

.sidebar-menu{
    list-style:none;
    margin:0;
    padding:0;
}

.sidebar-menu li{
    margin:8px 15px;
}

.sidebar-menu li a{
    display:flex;
    align-items:center;
    gap:12px;
    color:white;
    text-decoration:none;
    padding:14px 18px;
    border-radius:12px;
    transition:.3s;
}

.sidebar-menu li a:hover,
.sidebar-menu li.active a{
    background:#1565C0;
}

.sidebar-menu i{
    font-size:18px;
}

/* Main */

.main{
    margin-left:260px;
    width:calc(100% - 260px);
    padding:30px;
}

/* Topbar */

.topbar{
    background:white;
    border-radius:15px;
    padding:18px 25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
    margin-bottom:30px;
}

.topbar h4{
    margin:0;
    color:#0D47A1;
    font-weight:700;
}

.top-user{
    font-weight:600;
}

/* Hero */

.hero{
    background:linear-gradient(135deg,#1565C0,#0D47A1);
    border-radius:20px;
    color:white;
    padding:40px;
    margin-bottom:30px;
}

.hero h2{
    font-weight:700;
}

.profile-box{
    background:rgba(255,255,255,.15);
    padding:20px;
    border-radius:15px;
}

/* Cards */

.dashboard-card{
    border:none;
    border-radius:18px;
    transition:.3s;
}

.dashboard-card:hover{
    transform:translateY(-6px);
    box-shadow:0 15px 30px rgba(0,0,0,.15);
}

.card-icon{
    font-size:45px;
    opacity:.25;
}

/* Tables */

.table{
    border-radius:15px;
    overflow:hidden;
}

.table thead{
    background:#1565C0;
    color:white;
}

.table tbody tr:hover{
    background:#eef5ff;
}

/* Footer */

.footer{
    background:#0D47A1;
    color:white;
    padding:35px;
    margin-top:50px;
}
.table thead{

background:#1565C0;

color:white;

}

.table tbody tr{

transition:.3s;

}

.table tbody tr:hover{

background:#edf4ff;

}

.btn-group .btn{

margin-right:5px;

}

.badge{

border-radius:30px;

font-size:13px;

}

@media(max-width:991px){

.sidebar{

left:-260px;

transition:.4s;

}

.main{

margin-left:0;

width:100%;

padding:20px;

}

.topbar{

flex-direction:column;

gap:15px;

text-align:center;

}

.hero{

padding:25px;

}

.dashboard-card{

margin-bottom:20px;

}

.footer{

text-align:center;

}

}
.card{

animation:fadeUp .6s ease;

}

@keyframes fadeUp{

from{

opacity:0;

transform:translateY(30px);

}

to{

opacity:1;

transform:translateY(0);

}

}

.dashboard-card:hover{

transform:translateY(-8px);

transition:.3s;

}

.btn{

transition:.3s;

}

.btn:hover{

transform:scale(1.05);

}

</style>

</head>

<body>

<body>

<div class="wrapper">

    <!-- ================= SIDEBAR ================= -->

    <aside class="sidebar">

        <div class="sidebar-header">

            <img src="../assets/images/logo-circle.png" alt="APDCL">

            <h4>APDCL</h4>

            <small>Consumer Portal</small>

        </div>

        <div class="consumer-card">

            <img src="../assets/images/user.jpg" alt="Consumer">

            <h6><?= htmlspecialchars($consumer['name']) ?></h6>

            <small>Consumer No.</small><br>

            <strong><?= $consumer_no ?></strong>

        </div>

        <ul class="sidebar-menu">

            <li>

                <a href="dashboard.php">

                    <i class="bi bi-speedometer2"></i>

                    Dashboard

                </a>

            </li>

            <li class="active">

                <a href="bill.php">

                    <i class="bi bi-receipt-cutoff"></i>

                    My Bills

                </a>

            </li>

            <li>

                <a href="payment_history.php">

                    <i class="bi bi-credit-card"></i>

                    Payment History

                </a>

            </li>

            <li>

                <a href="track_complaint.php">

                    <i class="bi bi-tools"></i>

                    Complaints

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

    <!-- ================= MAIN ================= -->

    <main class="main">

        <!-- ================= TOPBAR ================= -->

        <div class="topbar">

            <div>

                <h4>Electricity Bill Management</h4>

            </div>

            <div class="top-user">

                <i class="bi bi-person-circle"></i>

                <?= htmlspecialchars($consumer['name']) ?>

            </div>

        </div>

        <!-- ================= HERO ================= -->

        <div class="hero">

            <div class="row align-items-center">

                <div class="col-lg-8">

                    <h2>

                        ⚡ Welcome,

                        <?= htmlspecialchars($consumer['name']) ?>

                    </h2>

                    <p class="mt-3">

                        View and download electricity bills, pay online, print invoices,
                        monitor payment history, and manage your APDCL consumer account.

                    </p>

                </div>

                <div class="col-lg-4">

                    <div class="profile-box">

                        <h5 class="mb-3">

                            Consumer Information

                        </h5>

                        <table class="table table-borderless text-white mb-0">

                            <tr>

                                <td><strong>Consumer No.</strong></td>

                                <td><?= $consumer_no ?></td>

                            </tr>

                            <tr>

                                <td><strong>Name</strong></td>

                                <td><?= htmlspecialchars($consumer['name']) ?></td>

                            </tr>

                            <tr>

                                <td><strong>Email</strong></td>

                                <td><?= htmlspecialchars($consumer['email']) ?></td>

                            </tr>

                        </table>

                    </div>

                </div>

            </div>

        </div>

<!-- Dashboard Cards Start Here -->

        <div class="row g-4 mb-4">

    <div class="col-lg-3">

        <div class="card dashboard-card bg-primary text-white">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <small>Total Bills</small>

                        <h2><?= $totalBills ?></h2>

                    </div>

                    <i class="bi bi-receipt card-icon"></i>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3">

        <div class="card dashboard-card bg-success text-white">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <small>Paid Bills</small>

                        <h2><?= $paidBills ?></h2>

                    </div>

                    <i class="bi bi-check-circle-fill card-icon"></i>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3">

        <div class="card dashboard-card bg-warning">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <small>Pending Bills</small>

                        <h2><?= $pendingBills ?></h2>

                    </div>

                    <i class="bi bi-hourglass-split card-icon"></i>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3">

        <div class="card dashboard-card bg-danger text-white">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <small>Total Amount</small>

                        <h3>

                            ₹<?= number_format($totalAmount,2) ?>

                        </h3>

                    </div>

                    <i class="bi bi-cash-stack card-icon"></i>

                </div>

            </div>

        </div>

    </div>

</div>
<!-- ================= CURRENT BILL SUMMARY ================= -->

<?php
$currentBill = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT *
FROM bills
WHERE consumer_no='$consumer_no'
ORDER BY id DESC
LIMIT 1
"));
?>

<div class="card border-0 shadow-lg rounded-4 mb-4">

    <div class="card-header bg-primary text-white">

        <h4 class="mb-0">

            <i class="bi bi-lightning-charge-fill"></i>

            Current Bill Summary

        </h4>

    </div>

    <div class="card-body">

        <?php if($currentBill){ ?>

        <div class="row text-center">

            <div class="col-md-3">

                <h6 class="text-muted">Bill Month</h6>

                <h4><?= htmlspecialchars($currentBill['month']) ?></h4>

            </div>

            <div class="col-md-3">

                <h6 class="text-muted">Amount</h6>

                <h3 class="text-success">

                    ₹<?= number_format($currentBill['total_bill'],2) ?>

                </h3>

            </div>

            <div class="col-md-3">

                <h6 class="text-muted">Status</h6>

                <?php if($currentBill['status']=="Paid"){ ?>

                <span class="badge bg-success px-4 py-2">

                    Paid

                </span>

                <?php }else{ ?>

                <span class="badge bg-danger px-4 py-2">

                    Pending

                </span>

                <?php } ?>

            </div>

            <div class="col-md-3">

                <a href="download_bill.php?id=<?= $currentBill['id'] ?>"

                   class="btn btn-primary">

                    <i class="bi bi-download"></i>

                    Download

                </a>

            </div>

        </div>

        <?php } ?>

    </div>

</div>
<!-- ================= BILL HISTORY ================= -->

<div class="card shadow border-0 rounded-4 mb-4">

    <div class="card-header bg-primary text-white p-4">

        <div class="row align-items-center">

            <div class="col-lg-6">

                <h4 class="mb-1">

                    <i class="bi bi-receipt-cutoff-fill"></i>

                    Electricity Bill History

                </h4>

                <small>

                    View, pay, print and download your electricity bills.

                </small>

            </div>

            <div class="col-lg-6">

                <div class="input-group">

                    <span class="input-group-text">

                        <i class="bi bi-search"></i>

                    </span>

                    <input
                        type="text"
                        id="searchBill"
                        class="form-control"
                        placeholder="Search Bill Month">

                </div>

            </div>

        </div>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover align-middle" id="billTable">

                <thead>

                <tr>

                    <th>#</th>

                    <th>Month</th>

                    <th>Units</th>

                    <th>Total Bill</th>

                    <th>Status</th>

                    <th class="text-center">Action</th>

                </tr>

                </thead>

                <tbody>

<?php

if(mysqli_num_rows($bills)>0){

while($bill=mysqli_fetch_assoc($bills)){

?>

<tr>

<td>

<?= $bill['id'] ?>

</td>

<td>

<strong>

<?= htmlspecialchars($bill['month']) ?>

</strong>

</td>

<td>

<?= number_format($bill['units']) ?> Units

</td>

<td>

<span class="fw-bold text-success">

₹<?= number_format($bill['total_bill'],2) ?>

</span>

</td>

<td>

<?php if($bill['status']=="Paid"){ ?>

<span class="badge bg-success px-3 py-2">

Paid

</span>

<?php }else{ ?>

<span class="badge bg-warning text-dark px-3 py-2">

Pending

</span>

<?php } ?>

</td>

<td class="text-center">

<div class="btn-group">

<?php if($bill['status']=="Pending"){ ?>

<a href="pay_bill.php?id=<?= $bill['id'] ?>"

class="btn btn-success btn-sm">

<i class="bi bi-credit-card"></i>

</a>

<?php } ?>

<a href="print_bill.php?id=<?= $bill['id'] ?>"

target="_blank"

class="btn btn-primary btn-sm">

<i class="bi bi-printer"></i>

</a>

<a href="download_bill.php?id=<?= $bill['id'] ?>"

class="btn btn-danger btn-sm">

<i class="bi bi-file-earmark-pdf"></i>

</a>

</div>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="6" class="text-center py-5">

<h5>No Bills Found</h5>

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

</div>
<div class="row mb-4">

    <!-- Monthly Usage -->

    <div class="col-lg-8 mb-4">

        <div class="card shadow border-0 rounded-4">

            <div class="card-header bg-primary text-white">

                <h5 class="mb-0">

                    <i class="bi bi-bar-chart-fill"></i>

                    Monthly Electricity Usage

                </h5>

            </div>

            <div class="card-body">

                <canvas id="usageChart" height="110"></canvas>

            </div>

        </div>

    </div>

    <!-- Bill Status -->

    <div class="col-lg-4 mb-4">

        <div class="card shadow border-0 rounded-4">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">

                    <i class="bi bi-pie-chart-fill"></i>

                    Bill Status

                </h5>

            </div>

            <div class="card-body">

                <canvas id="statusChart"></canvas>

            </div>

        </div>

    </div>

</div>
<div class="row">

    <!-- Recent Payments -->

    <div class="col-lg-7">

        <div class="card shadow border-0 rounded-4 mb-4">

            <div class="card-header bg-success text-white">

                <h5>

                    <i class="bi bi-credit-card-fill"></i>

                    Recent Payments

                </h5>

            </div>

            <div class="card-body">

                <table class="table table-hover">

                    <thead>

                    <tr>

                        <th>Month</th>

                        <th>Amount</th>

                        <th>Status</th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php

                    $paymentQuery=mysqli_query($conn,"
                    SELECT month,total_bill,status
                    FROM bills
                    WHERE consumer_no='$consumer_no'
                    AND status='Paid'
                    ORDER BY id DESC
                    LIMIT 5
                    ");

                    while($pay=mysqli_fetch_assoc($paymentQuery)){

                    ?>

                    <tr>

                        <td><?= htmlspecialchars($pay['month']) ?></td>

                        <td>

                            ₹<?= number_format($pay['total_bill'],2) ?>

                        </td>

                        <td>

                            <span class="badge bg-success">

                                Paid

                            </span>

                        </td>

                    </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- Notice Board -->

    <div class="col-lg-5">

        <div class="card shadow border-0 rounded-4">

            <div class="card-header bg-warning">

                <h5>

                    <i class="bi bi-megaphone-fill"></i>

                    Notice Board

                </h5>

            </div>

            <div class="card-body">

                <div class="alert alert-primary">

                    Pay electricity bills before the due date to avoid penalties.

                </div>

                <div class="alert alert-success">

                    Online payment service is available 24×7.

                </div>

                <div class="alert alert-warning">

                    Download your latest bill anytime.

                </div>

                <div class="alert alert-danger">

                    Register complaints online for faster resolution.

                </div>

            </div>

        </div>

    </div>

</div>
<!-- ================= FOOTER ================= -->

<footer class="footer mt-5">

    <div class="container-fluid">

        <div class="row">

            <div class="col-lg-4 mb-4">

                <h4>

                    <i class="bi bi-lightning-charge-fill"></i>

                    APDCL Consumer Portal

                </h4>

                <p class="mt-3">

                    Assam Power Distribution Company Limited

                    <br>

                    Consumer Electricity Billing & Payment System

                </p>

            </div>

            <div class="col-lg-4 mb-4">

                <h5>Quick Links</h5>

                <ul class="list-unstyled">

                    <li><a href="dashboard.php" class="text-white text-decoration-none">Dashboard</a></li>

                    <li><a href="bill.php" class="text-white text-decoration-none">My Bills</a></li>

                    <li><a href="payment_history.php" class="text-white text-decoration-none">Payments</a></li>

                    <li><a href="track_complaint.php" class="text-white text-decoration-none">Complaints</a></li>

                </ul>

            </div>

            <div class="col-lg-4">

                <h5>Customer Care</h5>

                <p>

                    📞 1912

                    <br>

                    ✉ support@apdcl.org

                    <br>

                    🌐 www.apdcl.org

                </p>

            </div>

        </div>

        <hr class="bg-light">

        <div class="text-center">

            © <?= date('Y') ?>

            APDCL Consumer Portal

            <br>

            Internship Demo Project

        </div>

    </div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

new Chart(document.getElementById('usageChart'),{

type:'bar',

data:{

labels:['Jan','Feb','Mar','Apr','May','Jun'],

datasets:[{

label:'Units',

data:[210,240,195,270,310,290],

backgroundColor:'#1565C0'

}]

}

});

new Chart(document.getElementById('statusChart'),{

type:'doughnut',

data:{

labels:['Paid','Pending'],

datasets:[{

data:[<?= $paidBills ?>,<?= $pendingBills ?>],

backgroundColor:['#28a745','#ffc107']

}]

}

});

</script>
</body>
</html>