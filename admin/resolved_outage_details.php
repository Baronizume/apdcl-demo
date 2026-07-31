<?php
session_start();

/*====================================================
    ADMIN LOGIN CHECK
====================================================*/

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*====================================================
    VALIDATE OUTAGE ID
====================================================*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Outage ID.");
}

$id = (int)$_GET['id'];

/*====================================================
    GET ADMIN DETAILS
====================================================*/

$admin_username = $_SESSION['admin'];

$adminQuery = mysqli_query($conn,"
SELECT *
FROM admin
WHERE username='$admin_username'
LIMIT 1
");

$admin = mysqli_fetch_assoc($adminQuery);

/*====================================================
    FETCH ONLY RESTORED OUTAGE
====================================================*/

$query = mysqli_query($conn,"
SELECT *
FROM outages
WHERE id='$id'
AND status='Restored'
LIMIT 1
");

if(mysqli_num_rows($query)==0){

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Resolved Outage</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

body{

    background:#eef3f9;
    font-family:'Segoe UI',sans-serif;

}

.box{

    width:650px;
    margin:120px auto;

}

</style>

</head>

<body>

<div class="box">

<div class="card shadow">

<div class="card-body text-center p-5">

<h2 class="text-warning">

This outage is still Active.

</h2>

<p class="mt-3">

Only restored outages can be viewed here.

</p>

<a href="manage_outages.php"
class="btn btn-primary mt-3">

Back to Pending Outages

</a>

<a href="resolved_outages.php"
class="btn btn-success mt-3">

Resolved Outages

</a>

</div>

</div>

</div>

</body>

</html>

<?php
exit();
}

$row = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1">

<title>

Resolved Outage Details

</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
rel="stylesheet">

<style>

body{

background:#eef3f9;

font-family:'Segoe UI',sans-serif;

}

.container{

max-width:1200px;

}

.card{

border:none;

border-radius:18px;

box-shadow:0 8px 20px rgba(0,0,0,.08);

}

.card-header{

background:#198754;

color:#fff;

font-size:24px;

font-weight:bold;

}

.table th{

width:30%;

background:#f5f5f5;

}

.page-title{

font-size:32px;

font-weight:700;

color:#1d3557;

}

.badge{

font-size:15px;

padding:8px 16px;

}

.section-title{

font-size:22px;

font-weight:bold;

color:#198754;

margin:30px 0 20px;

}

.btn{

border-radius:10px;

}

</style>

</head>

<body>

<div class="container py-5">

<!-- ==========================================
PAGE HEADER
========================================== -->

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="page-title">

<i class="fa-solid fa-circle-check text-success"></i>

Resolved Outage Details

</h2>

<p class="text-muted">

Complete information about the restored power outage.

</p>

</div>

</div>

<!-- ==========================================
OUTAGE INFORMATION
========================================== -->

<div class="card">

<div class="card-header">

<i class="fa-solid fa-bolt"></i>

Resolved Outage Information

</div>

<div class="card-body">

<table class="table table-bordered align-middle">

<tr>

<th>Outage ID</th>

<td>

<strong>#<?= $row['id']; ?></strong>

</td>

</tr>

<tr>

<th>Status</th>

<td>

<span class="badge bg-success">

Restored

</span>

</td>

</tr>

<tr>

<th>District</th>

<td>

<?= htmlspecialchars($row['district']); ?>

</td>

</tr>

<tr>

<th>Zone</th>

<td>

<?= htmlspecialchars($row['zone']); ?>

</td>

</tr>

<tr>

<th>Circle</th>

<td>

<?= htmlspecialchars($row['circle']); ?>

</td>

</tr>

<tr>

<th>Sub Division</th>

<td>

<?= htmlspecialchars($row['sub_division']); ?>

</td>

</tr>

<tr>

<th>Feeder Name</th>

<td>

<?= htmlspecialchars($row['feeder_name']); ?>

</td>

</tr>

<tr>

<th>Transformer</th>

<td>

<?= !empty($row['transformer']) ? htmlspecialchars($row['transformer']) : "N/A"; ?>

</td>

</tr>

<tr>

<th>Consumers Affected</th>

<td>

<?= number_format($row['consumers_affected']); ?>

</td>

</tr>

<tr>

<th>Outage Reason</th>

<td>

<?= nl2br(htmlspecialchars($row['outage_reason'])); ?>

</td>

</tr>

<tr>

<th>Start Time</th>

<td>

<?= date("d M Y h:i A",strtotime($row['start_time'])); ?>

</td>

</tr>

<tr>

<th>Estimated Restore</th>

<td>

<?php

if(!empty($row['estimated_restore'])){

echo date("d M Y h:i A",strtotime($row['estimated_restore']));

}else{

echo "Not Available";

}

?>

</td>

</tr>

<tr>

<th>Created At</th>

<td>

<?= date("d M Y h:i A",strtotime($row['created_at'])); ?>

</td>

</tr>

</table>

<!-- ==========================================
LOCATION
========================================== -->

<hr>

<h4 class="section-title">

<i class="fa-solid fa-location-dot"></i>

Location Information

</h4>

<table class="table table-bordered">

<tr>

<th>Latitude</th>

<td>

<?= $row['latitude']; ?>

</td>

</tr>

<tr>

<th>Longitude</th>

<td>

<?= $row['longitude']; ?>

</td>

</tr>

</table>

<?php if(!empty($row['latitude']) && !empty($row['longitude'])){ ?>

<div class="text-center mb-4">

<a href="https://www.google.com/maps?q=<?= $row['latitude']; ?>,<?= $row['longitude']; ?>"
target="_blank"
class="btn btn-danger btn-lg">

<i class="fa-solid fa-location-dot"></i>

Open in Google Maps

</a>

</div>

<?php } ?>

<!-- ==========================================
RESOLUTION DETAILS
========================================== -->

<hr>

<h4 class="section-title">

<i class="fa-solid fa-screwdriver-wrench"></i>

Resolution Details

</h4>

<table class="table table-bordered">

<tr>

<th>Resolved By</th>

<td>

<?= htmlspecialchars($row['resolved_by']); ?>

</td>

</tr>

<tr>

<th>Resolved At</th>

<td>

<?= date("d M Y h:i A",strtotime($row['resolved_at'])); ?>

</td>

</tr>

<tr>

<th>Resolution Note</th>

<td>

<?= !empty($row['resolution_note'])
? nl2br(htmlspecialchars($row['resolution_note']))
: "No resolution note."; ?>

</td>

</tr>

<?php

$seconds = strtotime($row['resolved_at']) - strtotime($row['start_time']);

$hours = floor($seconds / 3600);

$minutes = floor(($seconds % 3600)/60);

?>

<tr>

<th>Outage Duration</th>

<td>

<span class="badge bg-primary">

<?= $hours ?> Hours <?= $minutes ?> Minutes

</span>

</td>

</tr>

</table>

<!-- ==========================================
SYSTEM INFORMATION
========================================== -->

<hr>

<div class="alert alert-success">

<h5>

<i class="fa-solid fa-circle-info"></i>

System Information

</h5>

<ul class="mb-0 mt-3">

<li>

<strong>Outage Record ID :</strong>

#<?= $row['id']; ?>

</li>

<li>

<strong>Status :</strong>

<?= htmlspecialchars($row['status']); ?>

</li>

<li>

<strong>Created :</strong>

<?= date("d M Y h:i A",strtotime($row['created_at'])); ?>

</li>

<li>

<strong>Resolved :</strong>

<?= date("d M Y h:i A",strtotime($row['resolved_at'])); ?>

</li>

<li>

<strong>Resolved By :</strong>

<?= htmlspecialchars($row['resolved_by']); ?>

</li>

</ul>

</div>

<!-- ==========================================
ACTION BUTTONS
========================================== -->

<div class="d-flex justify-content-between flex-wrap mt-4">

<a
href="resolved_outages.php"
class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Back to Resolved Outages

</a>

<div>

<button
onclick="window.print();"
class="btn btn-primary me-2">

<i class="fa fa-print"></i>

Print

</button>

<a
href="manage_outages.php"
class="btn btn-success">

<i class="fa fa-list"></i>

Manage Outages

</a>

</div>

</div>

</div>

</div>

<!-- ==========================================
FOOTER
========================================== -->

<footer class="mt-5">

<div class="card shadow-sm border-0">

<div class="card-body text-center">

<h5 class="fw-bold text-primary">

⚡ Assam Power Distribution Company Limited (APDCL)

</h5>

<p class="mb-1 text-muted">

Resolved Outage Management System

</p>

<small class="text-secondary">

© <?= date("Y"); ?> APDCL. All Rights Reserved.

</small>

</div>

</div>

</footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

document.querySelectorAll("table tr").forEach(function(row){

    row.addEventListener("mouseenter",function(){

        this.style.transition=".2s";
        this.style.background="#eef8ff";

    });

    row.addEventListener("mouseleave",function(){

        this.style.background="";

    });

});

</script>

</body>

</html>