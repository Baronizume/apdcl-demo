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
    FETCH ONLY ACTIVE OUTAGE
====================================================*/

$query = mysqli_query($conn,"
SELECT *
FROM outages
WHERE id='$id'
AND status='Pending'
LIMIT 1
");

if(mysqli_num_rows($query)==0){

    die("
    <div style='
        width:600px;
        margin:100px auto;
        font-family:Segoe UI;
        text-align:center;
    '>

        <h2 style='color:#dc3545'>
            This outage has already been restored.
        </h2>

        <p>
            Only Pending outages can be viewed here.
        </p>

        <a href='manage_outages.php'
        style='
            display:inline-block;
            padding:12px 25px;
            background:#1565c0;
            color:#fff;
            border-radius:8px;
            text-decoration:none;
        '>

            Back to Manage Outages

        </a>

    </div>
    ");

}

$row = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>

Pending Outage Details | APDCL

</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
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

    border-radius:20px;

    box-shadow:0 10px 25px rgba(0,0,0,.08);

}

.card-header{

    background:#1565c0;

    color:#fff;

    font-size:24px;

    font-weight:bold;

    border-radius:20px 20px 0 0 !important;

}

.table th{

    width:30%;

    background:#f7f7f7;

}

.page-title{

    font-size:32px;

    font-weight:bold;

    color:#1d3557;

}

.badge{

    font-size:15px;

    padding:8px 16px;

}

.section-title{

    color:#1565c0;

    font-size:22px;

    font-weight:bold;

    margin-top:30px;

    margin-bottom:20px;

}

.btn{

    border-radius:10px;

}

.map-btn{

    padding:12px 30px;

    font-size:17px;

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

            <i class="fa-solid fa-bolt text-warning"></i>

            Pending Outage Details

        </h2>

        <p class="text-muted">

            View complete information about this pending power outage.

        </p>

    </div>

</div>

<!-- ==========================================
OUTAGE INFORMATION
========================================== -->

<div class="card">

<div class="card-header">

<i class="fa-solid fa-circle-info"></i>

Outage Information

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
<?php if($row['status']=="Pending"){ ?>

<span class="badge bg-warning text-dark">
Pending
</span>

<?php }else{ ?>

<span class="badge bg-success">
Restored
</span>

<?php } ?>

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

<?= !empty($row['transformer'])
? htmlspecialchars($row['transformer'])
: "N/A"; ?>

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

<th>Outage Start</th>

<td>

<?= date("d M Y",strtotime($row['start_time'])); ?>

&nbsp;&nbsp;

<?= date("h:i A",strtotime($row['start_time'])); ?>

</td>

</tr>

<tr>

<th>Estimated Restore</th>

<td>

<?php

if(!empty($row['estimated_restore'])){

    echo date(
        "d M Y h:i A",
        strtotime($row['estimated_restore'])
    );

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
LOCATION DETAILS
========================================== -->

<hr>

<h4 class="section-title">

<i class="fa-solid fa-location-dot text-danger"></i>

Location Information

</h4>

<table class="table table-bordered align-middle">

<tr>

<th>Latitude</th>

<td>

<?= !empty($row['latitude']) ? $row['latitude'] : "Not Available"; ?>

</td>

</tr>

<tr>

<th>Longitude</th>

<td>

<?= !empty($row['longitude']) ? $row['longitude'] : "Not Available"; ?>

</td>

</tr>

</table>

<?php if(!empty($row['latitude']) && !empty($row['longitude'])){ ?>

<div class="text-center mt-4 mb-4">

<a
href="https://www.google.com/maps?q=<?= $row['latitude']; ?>,<?= $row['longitude']; ?>"
target="_blank"
class="btn btn-danger map-btn">

<i class="fa-solid fa-location-dot"></i>

Open Location in Google Maps

</a>

</div>

<?php } ?>

<!-- ==========================================
SYSTEM INFORMATION
========================================== -->

<hr>

<h4 class="section-title">

<i class="fa-solid fa-circle-info text-primary"></i>

System Information

</h4>

<div class="alert alert-info">

<ul class="mb-0">

<li>

<strong>Outage ID :</strong>

#<?= $row['id']; ?>

</li>

<li>

<strong>Status :</strong>

<span class="badge bg-danger">

Pending

</span>

</li>

<li>

<strong>Created At :</strong>

<?= date("d M Y h:i A",strtotime($row['created_at'])); ?>

</li>

</ul>

</div>

<!-- ==========================================
ACTION BUTTONS
========================================== -->

<hr>

<div class="d-flex justify-content-between flex-wrap">

<a
href="manage_outages.php"
class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Back

</a>

<div>

<a
href="edit_outage.php?id=<?= $row['id']; ?>"
class="btn btn-warning me-2">

<i class="fa-solid fa-pen-to-square"></i>

Edit Outage

</a>

<button
onclick="window.print();"
class="btn btn-primary">

<i class="fa-solid fa-print"></i>

Print

</button>

</div>

</div>

</div>

</div>

<!-- ==========================================
APDCL FOOTER
========================================== -->

<footer class="mt-5">

    <div class="card shadow-sm border-0">

        <div class="card-body text-center">

            <h5 class="fw-bold text-primary mb-2">

                ⚡ Assam Power Distribution Company Limited (APDCL)

            </h5>

            <p class="text-muted mb-1">

                Pending Outage Management System

            </p>

            <small class="text-secondary">

                © <?= date("Y"); ?> APDCL. All Rights Reserved.

            </small>

        </div>

    </div>

</footer>

</div>

<!-- ==========================================
BOOTSTRAP JS
========================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- ==========================================
TABLE HOVER EFFECT
========================================== -->

<script>

document.querySelectorAll(".table tbody tr").forEach(function(row){

    row.addEventListener("mouseenter",function(){

        this.style.transition="0.25s";
        this.style.background="#f8fbff";

    });

    row.addEventListener("mouseleave",function(){

        this.style.background="";

    });

});

</script>

</body>

</html>