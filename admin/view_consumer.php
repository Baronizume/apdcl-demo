<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================
ADMIN DETAILS
=========================================*/

$admin_username = $_SESSION['admin'];

$stmt = mysqli_prepare($conn,"
SELECT *
FROM admin
WHERE username=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$admin_username);
mysqli_stmt_execute($stmt);

$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

mysqli_stmt_close($stmt);

if(!$admin){
    session_destroy();
    header("Location: login.php");
    exit();
}

/*=========================================
CHECK CONSUMER ID
=========================================*/

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){
    die("Invalid Consumer ID.");
}

$id=(int)$_GET['id'];

/*=========================================
FETCH CONSUMER
=========================================*/

$stmt=mysqli_prepare($conn,"
SELECT *
FROM users
WHERE id=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"i",$id);
mysqli_stmt_execute($stmt);

$user=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

mysqli_stmt_close($stmt);

if(!$user){
    die("Consumer not found.");
}

$consumer_no=$user['consumer_no'];

/*=========================================
PROFILE PHOTO
=========================================*/

$photo="../assets/images/user.png";

if(!empty($user['photo'])){

    if(file_exists("../assets/profile/".$user['photo'])){

        $photo="../assets/profile/".$user['photo'];

    }
    elseif(file_exists("../uploads/profile/".$user['photo'])){

        $photo="../uploads/profile/".$user['photo'];

    }

}

/*=========================================
LATEST BILL
=========================================*/

$stmt=mysqli_prepare($conn,"
SELECT *
FROM bills
WHERE consumer_no=?
ORDER BY id DESC
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$consumer_no);
mysqli_stmt_execute($stmt);

$latestBill=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

mysqli_stmt_close($stmt);

/*=========================================
STATISTICS
=========================================*/

$stmt=mysqli_prepare($conn,"
SELECT
COUNT(*) totalBills,
IFNULL(SUM(total_bill),0) totalAmount,
IFNULL(SUM(units),0) totalUnits,
IFNULL(SUM(
CASE
WHEN status='Pending'
THEN total_bill
ELSE 0
END
),0) pendingAmount
FROM bills
WHERE consumer_no=?
");

mysqli_stmt_bind_param($stmt,"s",$consumer_no);
mysqli_stmt_execute($stmt);

$stats=mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

mysqli_stmt_close($stmt);

$totalBills=$stats['totalBills'];
$totalAmount=$stats['totalAmount'];
$totalUnits=$stats['totalUnits'];
$pendingAmount=$stats['pendingAmount'];

/*=========================================
STATUS COLOR
=========================================*/

$statusColor="secondary";

if($latestBill){

    switch($latestBill['status']){

        case "Paid":
            $statusColor="success";
            break;

        case "Pending":
            $statusColor="warning";
            break;

        case "Overdue":
            $statusColor="danger";
            break;

        case "Cancelled":
            $statusColor="dark";
            break;

        default:
            $statusColor="primary";
    }

}

/*=========================================
BILL HISTORY
=========================================*/

$history=mysqli_query($conn,"
SELECT *
FROM bills
WHERE consumer_no='$consumer_no'
ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>

View Consumer | APDCL Admin

</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{

    background:#eef3f8;
    font-family:'Segoe UI',sans-serif;

}

/* NAVBAR */

.navbar{

    background:linear-gradient(90deg,#0d47a1,#1565c0,#1e88e5);
    height:75px;
    box-shadow:0 4px 15px rgba(0,0,0,.2);

}

.logo{

    width:55px;
    height:55px;
    border-radius:50%;
    background:#fff;
    padding:4px;
    margin-right:15px;

}

.brand-title{

    color:#fff;
    font-size:24px;
    font-weight:700;
    margin:0;

}

.brand-sub{

    color:#dbeeff;
    font-size:13px;

}

/* CONTENT */

.content{

    padding:35px;

}

/* CARDS */

.card{

    border:none;
    border-radius:18px;
    box-shadow:0 8px 25px rgba(0,0,0,.08);

}

/* PROFILE */

.profile-card{

    text-align:center;

}

.profile-photo{

    width:140px;
    height:140px;
    border-radius:50%;
    object-fit:cover;
    border:6px solid #fff;
    box-shadow:0 10px 25px rgba(0,0,0,.15);
    margin-top:-70px;

}

.profile-header{

    height:120px;
    background:linear-gradient(135deg,#0d47a1,#42a5f5);
    border-radius:18px 18px 0 0;

}

.consumer-name{

    font-size:32px;
    font-weight:bold;
    color:#0d47a1;

}

.consumer-number{

    color:#666;
    font-size:18px;

}

.badge-large{

    font-size:15px;
    padding:8px 15px;

}

/* STAT CARDS */

.stat-card{

    color:#fff;
    border-radius:18px;
    padding:28px;
    transition:.35s;

}

.stat-card:hover{

    transform:translateY(-6px);

}

.stat-card i{

    font-size:42px;

}

.stat-card h2{

    margin-top:18px;
    font-size:34px;
    font-weight:bold;

}

.bg-blue{

    background:linear-gradient(135deg,#1565c0,#64b5f6);

}

.bg-green{

    background:linear-gradient(135deg,#2e7d32,#81c784);

}

.bg-orange{

    background:linear-gradient(135deg,#ef6c00,#ffb74d);

}

.bg-red{

    background:linear-gradient(135deg,#c62828,#ef5350);

}

/* TABLE */

.table thead{

    background:#1565c0;
    color:#fff;

}

.table{

    margin-bottom:0;

}

.table td,
.table th{

    vertical-align:middle;

}

/* SECTION TITLE */

.section-title{

    font-size:22px;
    font-weight:bold;
    color:#0d47a1;

}

</style>

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar">

<div class="container-fluid">

<div class="d-flex align-items-center">

<img
src="../assets/images/logo-circle.png"
class="logo">

<div>

<h4 class="brand-title">

APDCL Electricity Billing System

</h4>

<div class="brand-sub">

Assam Power Distribution Company Limited

</div>

</div>

</div>

<div class="text-end text-white">

<b>

<?= htmlspecialchars($admin['name']) ?>

</b>

<br>

<?= htmlspecialchars($admin['role']) ?>

</div>

</div>

</nav>

<!-- PAGE HEADER -->

<div class="container-fluid mt-4">

<div class="card border-0 shadow-sm">

<div class="card-body">

<div class="row align-items-center">

<div class="col-md-8">

<h2 class="fw-bold text-primary mb-1">

<i class="bi bi-person-vcard-fill"></i>

Consumer Dashboard

</h2>

<p class="text-muted mb-0">

View complete consumer profile, billing history, electricity consumption, payment status and account information.

</p>

</div>

<div class="col-md-4 text-md-end">

<a href="dashboard.php" class="btn btn-primary">

<i class="bi bi-house-fill"></i>

Dashboard

</a>

<a href="manage_consumers.php" class="btn btn-outline-primary">

<i class="bi bi-people-fill"></i>

Consumers

</a>

</div>

</div>

<hr>

<div class="row">

<!-- LEFT PANEL -->

<div class="col-lg-4">

<div class="card profile-card">

<div class="card-body">

<h3 class="consumer-name">

<?= htmlspecialchars($user['name']) ?>

</h3>

<div class="consumer-number mb-3">

<?= htmlspecialchars($user['consumer_no']) ?>

</div>

<span class="badge bg-success badge-large">

ACTIVE CONSUMER

</span>

<hr>
<div class="row text-start">

<div class="col-6 mb-3">
    <small class="text-muted">Father Name</small>
    <h6><?= htmlspecialchars($user['father_name']) ?></h6>
</div>

<div class="col-6 mb-3">
    <small class="text-muted">Mobile</small>
    <h6><?= htmlspecialchars($user['mobile']) ?></h6>
</div>

<div class="col-12 mb-3">
    <small class="text-muted">Email</small>
    <h6><?= htmlspecialchars($user['email']) ?></h6>
</div>

<div class="col-12 mb-3">
    <small class="text-muted">Address</small>
    <h6><?= htmlspecialchars($user['address']) ?></h6>
</div>

<div class="col-6 mb-3">
    <small class="text-muted">District</small>
    <h6><?= htmlspecialchars($user['district']) ?></h6>
</div>

<div class="col-6 mb-3">
    <small class="text-muted">Meter No</small>
    <h6><?= htmlspecialchars($user['meter_no']) ?></h6>
</div>

<div class="col-6 mb-3">
    <small class="text-muted">Meter Type</small>
    <h6><?= htmlspecialchars($user['meter_type']) ?></h6>
</div>

<div class="col-6 mb-3">
    <small class="text-muted">Category</small>
    <h6><?= htmlspecialchars($user['category']) ?></h6>
</div>

<div class="col-4 mb-3">
    <small class="text-muted">Zone</small>
    <h6><?= htmlspecialchars($user['zone']) ?></h6>
</div>

<div class="col-4 mb-3">
    <small class="text-muted">Circle</small>
    <h6><?= htmlspecialchars($user['circle']) ?></h6>
</div>

<div class="col-4 mb-3">
    <small class="text-muted">Sub Division</small>
    <h6><?= htmlspecialchars($user['sub_division']) ?></h6>
</div>

<div class="col-6">
    <small class="text-muted">DTR No</small>
    <h6><?= htmlspecialchars($user['dtr_no']) ?></h6>
</div>

<div class="col-6">
    <small class="text-muted">Pole No</small>
    <h6><?= htmlspecialchars($user['pole_no']) ?></h6>
</div>

</div>

</div>

</div>

</div>

<!-- RIGHT SIDE -->

<div class="col-lg-8">

<div class="row g-4">

<div class="col-md-6">

<div class="stat-card bg-blue">

<i class="bi bi-receipt-cutoff"></i>

<h2><?= $totalBills ?></h2>

<p>Total Bills Generated</p>

</div>

</div>

<div class="col-md-6">

<div class="stat-card bg-green">

<i class="bi bi-cash-stack"></i>

<h2>₹ <?= number_format($totalAmount,2) ?></h2>

<p>Total Billing Amount</p>

</div>

</div>

<div class="col-md-6">

<div class="stat-card bg-orange">

<i class="bi bi-lightning-charge-fill"></i>

<h2><?= number_format($totalUnits) ?></h2>

<p>Total Units Consumed</p>

</div>

</div>

<div class="col-md-6">

<div class="stat-card bg-red">

<i class="bi bi-exclamation-triangle-fill"></i>

<h2>₹ <?= number_format($pendingAmount,2) ?></h2>

<p>Pending Amount</p>

</div>

</div>

</div>

<br>

<div class="card">

<div class="card-header bg-primary text-white">

<h5 class="mb-0">

<i class="bi bi-receipt"></i>

Latest Bill

</h5>

</div>

<div class="card-body">

<?php if($latestBill){ ?>

<div class="row">

<div class="col-md-6 mb-3">

<small class="text-muted">Bill Number</small>

<h5><?= htmlspecialchars($latestBill['bill_no']) ?></h5>

</div>

<div class="col-md-6 mb-3">

<small class="text-muted">Billing Month</small>

<h5><?= htmlspecialchars($latestBill['month']) ?></h5>

</div>

<div class="col-md-6 mb-3">

<small class="text-muted">Units Consumed</small>

<h5><?= number_format($latestBill['units']) ?> Units</h5>

</div>

<div class="col-md-6 mb-3">

<small class="text-muted">Total Bill</small>

<h4 class="text-success">

₹ <?= number_format($latestBill['total_bill'],2) ?>

</h4>

</div>

<div class="col-md-6 mb-3">

<small class="text-muted">Due Date</small>

<h5>

<?= date("d M Y",strtotime($latestBill['due_date'])) ?>

</h5>

</div>

<div class="col-md-6 mb-3">

<small class="text-muted">Status</small>

<br>

<span class="badge bg-<?= $statusColor ?> fs-6">

<?= htmlspecialchars($latestBill['status']) ?>

</span>

</div>

</div>

<?php }else{ ?>

<div class="alert alert-warning mb-0">

<i class="bi bi-exclamation-circle-fill"></i>

No bill has been generated for this consumer yet.

</div>

<?php } ?>

</div>

</div>

<!-- BILL HISTORY -->

<div class="card mt-4">

<div class="card-header bg-dark text-white">

<h5 class="mb-0">

<i class="bi bi-clock-history"></i>

Bill History

</h5>

</div>

<div class="card-body p-0">

<div class="table-responsive">

<table class="table table-hover table-bordered align-middle mb-0">

<thead>

<tr>

<th>#</th>

<th>Bill No</th>

<th>Month</th>

<th>Units</th>

<th>Total</th>

<th>Status</th>

<th>Bill Date</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php

$sl=1;

if(mysqli_num_rows($history)>0){

while($bill=mysqli_fetch_assoc($history)){

?>

<tr>

<td><?= $sl++ ?></td>

<td><?= htmlspecialchars($bill['bill_no']) ?></td>

<td><?= htmlspecialchars($bill['month']) ?></td>

<td><?= number_format($bill['units']) ?></td>

<td>

₹ <?= number_format($bill['total_bill'],2) ?>

</td>

<td>

<span class="badge bg-<?=
($bill['status']=="Paid")?"success":
(($bill['status']=="Pending")?"warning":
(($bill['status']=="Overdue")?"danger":"secondary"))
?>">

<?= htmlspecialchars($bill['status']) ?>

</span>

</td>

<td>

<?= date("d-m-Y",strtotime($bill['bill_date'])) ?>

</td>

<td>

<a href="view_bill.php?id=<?= $bill['id'] ?>" class="btn btn-primary btn-sm">

<i class="bi bi-eye-fill"></i>

View

</a>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="8" class="text-center py-5">

No Bill History Available

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<!-- ACTION BUTTONS -->

<div class="text-center mt-4 mb-5">

<a href="generate_bill.php?consumer_no=<?= urlencode($consumer_no) ?>"

class="btn btn-success btn-lg px-4 me-2">

<i class="bi bi-lightning-charge-fill"></i>

Generate New Bill

</a>

<a href="manage_consumers.php"

class="btn btn-primary btn-lg px-4 me-2">

<i class="bi bi-people-fill"></i>

Manage Consumers

</a>

<a href="dashboard.php"

class="btn btn-secondary btn-lg px-4">

<i class="bi bi-house-fill"></i>

Dashboard

</a>

</div>

</div>

</div>

</div>

<footer class="text-center py-4 text-muted">

<hr>

© <?= date("Y") ?>

APDCL Electricity Billing Management System

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>