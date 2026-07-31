<?php
session_start();

if (!isset($_SESSION['admin'])) {
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

/* Complaint Statistics */

$totalComplaint = mysqli_num_rows(mysqli_query($conn,"
SELECT * FROM complaint
"));

$totalPending = mysqli_num_rows(mysqli_query($conn,"
SELECT * FROM complaint
WHERE status='Pending'
"));

$totalResolved = mysqli_num_rows(mysqli_query($conn,"
SELECT * FROM complaint
WHERE status='Resolved'
"));

/* Search */

$where = "1";

if(isset($_GET['search']) && $_GET['search']!=""){

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $where .= " AND consumer_no LIKE '%$search%'";

}

$result = mysqli_query($conn,"
SELECT *
FROM complaint
WHERE $where
ORDER BY created_at DESC
");
if(!$result){

    die("Complaint Query Error: ".mysqli_error($conn));

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width,initial-scale=1">

<title>Manage Complaints</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{

background:#f4f7fc;

font-family:'Segoe UI',sans-serif;

}

/* NAVBAR */

.navbar{

background:linear-gradient(90deg,#0d47a1,#1565c0,#1976d2);

height:75px;

padding:0 25px;

box-shadow:0 4px 18px rgba(0,0,0,.2);

}

.navbar-brand{

display:flex;

align-items:center;

color:#fff!important;

font-weight:bold;

text-decoration:none;

}

.navbar-brand img{

width:58px;

height:58px;

background:#fff;

border-radius:50%;

padding:4px;

margin-right:15px;

}

.admin-box{

display:flex;

align-items:center;

color:#fff;

text-decoration:none;

}

.admin-box img{

width:45px;

height:45px;

border-radius:50%;

margin-right:12px;

background:#fff;

padding:2px;

}

/* SIDEBAR */

.sidebar{

position:fixed;

top:75px;

left:0;

width:260px;

height:calc(100vh - 75px);

background:#0b3b86;

overflow-y:auto;

}

.sidebar-header{

text-align:center;

padding:20px;

color:#fff;

border-bottom:1px solid rgba(255,255,255,.15);

}

.sidebar-logo{

width:65px;

background:#fff;

border-radius:50%;

padding:5px;

margin-bottom:10px;

}

.sidebar a{

display:flex;

align-items:center;

padding:15px 20px;

color:#fff;

text-decoration:none;

border-left:4px solid transparent;

transition:.3s;

}

.sidebar a i{

width:28px;

font-size:20px;

}

.sidebar a span{

font-size:15px;

font-weight:500;

}

.sidebar a:hover{

background:#1565c0;

padding-left:25px;

border-left:4px solid #ffc107;

}

.sidebar a.active{

background:#1976d2;

border-left:4px solid #ffc107;

}

.content{

margin-left:280px;

padding:30px;

}

.card{

border:none;

border-radius:15px;

box-shadow:0 5px 15px rgba(0,0,0,.12);

}

.table th{

background:#0d47a1;

color:#fff;

}

.stat-card{

border-radius:15px;

color:#fff;

padding:20px;

}

.bg-blue{

background:#1565c0;

}

.bg-red{

background:#d32f2f;

}

.bg-green{

background:#2e7d32;

}

</style>

</head>

<body>

<nav class="navbar">

<div class="container-fluid">

<a class="navbar-brand"
href="dashboard.php">

<img src="/apdcl-demo/assets/images/logo-circle.png">

<div>

<h5 class="mb-0">

APDCL

</h5>

<small>

Complaint Management

</small>

</div>

</a>

<div class="ms-auto">

<div class="dropdown">

<a href="#"

class="admin-box dropdown-toggle"

data-bs-toggle="dropdown">

<img src="https://ui-avatars.com/api/?name=<?= urlencode($admin['name']); ?>&background=ffffff&color=0d47a1">

<div>

<b><?= htmlspecialchars($admin['name']); ?></b>

<br>

<small>Administrator</small>

</div>

</a>

<ul class="dropdown-menu dropdown-menu-end">

<li>

<a class="dropdown-item"

href="dashboard.php">

Dashboard

</a>

</li>

<li>

<a class="dropdown-item"

href="../logout.php">

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

    <a href="generate_bill.php">
        <i class="bi bi-lightning-charge-fill"></i>
        <span>Generate Bill</span>
    </a>

    <a href="manage_bills.php">
        <i class="bi bi-receipt-cutoff"></i>
        <span>Manage Bills</span>
    </a>

    <a href="complaint.php" class="active">
        <i class="bi bi-chat-left-text-fill"></i>
        <span>Complaint</span>
    </a>

    <a href="notices.php">
        <i class="bi bi-megaphone-fill"></i>
        <span>Notices</span>
    </a>

    <a href="reports.php">
        <i class="bi bi-bar-chart-fill"></i>
        <span>Reports</span>
    </a>

    <hr style="border-color:rgba(255,255,255,.2);">

    <a href="../logout.php" style="background:#c62828;">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
    </a>

</div>

<!-- ================= CONTENT ================= -->

<div class="content">

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold text-primary">

            <i class="bi bi-chat-left-text-fill"></i>

            Consumer Complaints

        </h2>

        <p class="text-muted">

            View and monitor complaints submitted by electricity consumers.

        </p>

    </div>

    <div>

        <button
            class="btn btn-primary"
            onclick="window.print()">

            <i class="bi bi-printer-fill"></i>

            Print Report

        </button>

    </div>

</div>

<!-- ================= STATISTICS ================= -->

<div class="row mb-4">

    <div class="col-md-4">

        <div class="stat-card bg-blue">

            <h6>Total Complaints</h6>

            <h2><?= $totalComplaint; ?></h2>

        </div>

    </div>

    <div class="col-md-4">

        <div class="stat-card bg-red">

            <h6>Pending Complaints</h6>

            <h2><?= $totalPending; ?></h2>

        </div>

    </div>

    <div class="col-md-4">

        <div class="stat-card bg-green">

            <h6>Resolved Complaints</h6>

            <h2><?= $totalResolved; ?></h2>

        </div>

    </div>

</div>

<!-- ================= SEARCH ================= -->

<div class="card mb-4">

    <div class="card-body">

        <form method="GET">

            <div class="row">

                <div class="col-md-10">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search by Consumer Number..."
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

</div>

<!-- ================= COMPLAINT TABLE START ================= -->

<div class="card">

<div class="card-header">

    <i class="bi bi-table"></i>

    Complaint Records

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-hover table-bordered align-middle">

<thead>

<tr>

<th>ID</th>

<th>Consumer No</th>

<th>Subject</th>

<th>Description</th>

<th>Status</th>

<th>Date</th>

<th width="160">Action</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($result) > 0){

while($row = mysqli_fetch_assoc($result)){

?>

<tr>

<td>

<?= $row['id']; ?>

</td>

<td>

<b><?= htmlspecialchars($row['consumer_no']); ?></b>

</td>

<td>

<?= htmlspecialchars($row['subject']); ?>

</td>

<td style="max-width:300px;">

<?= nl2br(htmlspecialchars($row['description'])); ?>

</td>

<td>

<?php

$status = strtolower(trim($row['status']));

if($status == "resolved"){

?>

<span class="badge bg-success">

<i class="bi bi-check-circle-fill"></i>

Resolved

</span>

<?php

}elseif($status == "pending"){

?>

<span class="badge bg-danger">

<i class="bi bi-clock-fill"></i>

Pending

</span>

<?php

}else{

?>

<span class="badge bg-warning text-dark">

<i class="bi bi-tools"></i>

<?= htmlspecialchars($row['status']); ?>

</span>

<?php } ?>

</td>

<td>

<?= date("d M Y",strtotime($row['created_at'])); ?>

<br>

<small class="text-muted">

<?= date("h:i A",strtotime($row['created_at'])); ?>

</small>

</td>

<td>

<div class="btn-group">

<a
href="view_complaint.php?id=<?= $row['id']; ?>"
class="btn btn-info btn-sm">

<i class="bi bi-eye-fill"></i>

</a>

<a
href="edit_complaint.php?id=<?= $row['id']; ?>"
class="btn btn-warning btn-sm">

<i class="bi bi-pencil-square"></i>

</a>

<a
href="delete_complaint.php?id=<?= $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this complaint?');">

<i class="bi bi-trash-fill"></i>

</a>

</div>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="7" class="text-center py-5">

<i class="bi bi-inbox display-4 text-secondary"></i>

<h5 class="mt-3">

No Complaints Found

</h5>

<p class="text-muted">

There are currently no complaint records available.

</p>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<!-- ================= FOOTER ================= -->

<footer class="text-center mt-5 mb-4 text-muted">

<hr>

<p class="mb-1">

© <?= date("Y"); ?> APDCL - Assam Power Distribution Company Limited

</p>

<small>

Electricity Billing Management System | Complaint Management Module

</small>

</footer>

</div>

<!-- ================= VIEW COMPLAINT MODAL ================= -->

<div
class="modal fade"
id="viewModal"
tabindex="-1">

<div class="modal-dialog modal-lg">

<div class="modal-content">

<div class="modal-header bg-primary text-white">

<h5 class="modal-title">

<i class="bi bi-chat-left-text-fill"></i>

Complaint Details

</h5>

<button
type="button"
class="btn-close btn-close-white"
data-bs-dismiss="modal">

</button>

</div>

<div class="modal-body">

<div id="complaintContent">

Loading...

</div>

</div>

</div>

</div>

</div>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/* ================= Live Clock ================= */

function updateClock(){

const now = new Date();

const options = {

weekday:'short',

day:'2-digit',

month:'short',

year:'numeric'

};

let date = now.toLocaleDateString('en-IN',options);

let time = now.toLocaleTimeString();

let clock = document.getElementById("clock");

if(clock){

clock.innerHTML = date + " | " + time;

}

}

setInterval(updateClock,1000);

updateClock();

/* ================= Table Hover ================= */

document.querySelectorAll("tbody tr").forEach(function(row){

row.addEventListener("mouseenter",function(){

this.style.transition=".2s";

this.style.transform="scale(1.003)";

});

row.addEventListener("mouseleave",function(){

this.style.transform="scale(1)";

});

});

/* ================= Search Box Focus ================= */

const searchBox = document.querySelector("input[name='search']");

if(searchBox){

searchBox.addEventListener("focus",function(){

this.style.boxShadow="0 0 10px rgba(13,110,253,.35)";

});

searchBox.addEventListener("blur",function(){

this.style.boxShadow="none";

});

}

</script>

</body>

</html>