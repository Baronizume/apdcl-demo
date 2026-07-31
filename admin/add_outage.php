<?php

session_start();

/*=========================================================
ADMIN LOGIN
=========================================================*/

if (!isset($_SESSION['admin'])) {

    header("Location:login.php");
    exit();

}

include("../db.php");

/*=========================================================
SUCCESS MESSAGE
=========================================================*/

$success = "";
$error = "";

/*=========================================================
ADD OUTAGE
=========================================================*/

if(isset($_POST['add_outage'])){

    $district = mysqli_real_escape_string($conn,$_POST['district']);

    $zone = mysqli_real_escape_string($conn,$_POST['zone']);

    $circle = mysqli_real_escape_string($conn,$_POST['circle']);

    $sub_division = mysqli_real_escape_string($conn,$_POST['sub_division']);

    $feeder_name = mysqli_real_escape_string($conn,$_POST['feeder_name']);

    $transformer = mysqli_real_escape_string($conn,$_POST['transformer']);

    $latitude = mysqli_real_escape_string($conn,$_POST['latitude']);

    $longitude = mysqli_real_escape_string($conn,$_POST['longitude']);

    $reason = mysqli_real_escape_string($conn,$_POST['outage_reason']);

    $consumers = (int)$_POST['consumers_affected'];

    $start_time = $_POST['start_time'];

    $estimated_restore = $_POST['estimated_restore'];

    $status = $_POST['status'];

    $sql = "

    INSERT INTO outages(

        district,

        zone,

        circle,

        sub_division,

        feeder_name,

        transformer,

        latitude,

        longitude,

        outage_reason,

        consumers_affected,

        start_time,

        estimated_restore,

        status,

        outage_reports

    )

    VALUES(

        '$district',

        '$zone',

        '$circle',

        '$sub_division',

        '$feeder_name',

        '$transformer',

        '$latitude',

        '$longitude',

        '$reason',

        '$consumers',

        '$start_time',

        '$estimated_restore',

        '$status',

        0

    )

    ";

    if(mysqli_query($conn,$sql)){

        $success = "Power outage added successfully.";

    }

    else{

        $error = mysqli_error($conn);

    }

}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Add Outage | APDCL Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

<style>

body{

background:#eef3f9;

font-family:'Segoe UI',sans-serif;

}

.container-fluid{

padding:30px;

}

.page-title{

font-size:30px;

font-weight:700;

color:#d32f2f;

}

.page-subtitle{

color:#6c757d;

}

.card{

border:none;

border-radius:20px;

box-shadow:0 8px 25px rgba(0,0,0,.08);

}

.form-control,

.form-select{

height:48px;

border-radius:10px;

}

textarea.form-control{

height:auto;

}

label{

font-weight:600;

margin-bottom:8px;

}

.btn{

border-radius:10px;

padding:10px 18px;

}

.logo{

width:80px;

height:80px;

object-fit:contain;

}

.section-title{

font-size:18px;

font-weight:700;

color:#0d47a1;

margin-bottom:20px;

padding-bottom:10px;

border-bottom:2px solid #e3eaf5;

}

</style>

</head>

<body>

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">

<div class="d-flex align-items-center">

<img src="../assets/images/logo-circle.png" class="logo me-3">

<div>

<h2 class="page-title">

<i class="fa-solid fa-bolt text-warning"></i>

Add Power Outage

</h2>

<p class="page-subtitle">

Create and publish new outage notifications for consumers.

</p>

</div>

</div>

<div>

<a href="dashboard.php" class="btn btn-secondary me-2">

<i class="fa fa-arrow-left"></i>

Back to Dashboard

</a>

<a href="manage_outages.php" class="btn btn-primary">

<i class="fa fa-list"></i>

Manage Outages

</a>

</div>

</div>

<?php

if($success!=""){

?>

<div class="alert alert-success alert-dismissible fade show">

<i class="fa fa-check-circle"></i>

<?= $success; ?>

<button class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php } ?>

<?php

if($error!=""){

?>

<div class="alert alert-danger alert-dismissible fade show">

<i class="fa fa-triangle-exclamation"></i>

<?= $error; ?>

<button class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php } ?>

<div class="card">

<div class="card-body">

<h4 class="section-title">

<i class="fa-solid fa-map-location-dot text-danger"></i>

Outage Information

</h4>

<form method="POST">

<div class="row">

<!-- ==============================
OUTAGE FORM
============================== -->

<div class="col-md-4 mb-3">

<label>District</label>

<input
type="text"
name="district"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label>Zone</label>

<input
type="text"
name="zone"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label>Circle</label>

<input
type="text"
name="circle"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label>Sub Division</label>

<input
type="text"
name="sub_division"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label>Feeder Name</label>

<input
type="text"
name="feeder_name"
class="form-control"
required>

</div>

<div class="col-md-4 mb-3">

<label>Transformer</label>

<input
type="text"
name="transformer"
class="form-control">

</div>

<div class="col-md-3 mb-3">

<label>Latitude</label>

<input
type="number"
step="0.00000001"
name="latitude"
class="form-control"
required>

</div>

<div class="col-md-3 mb-3">

<label>Longitude</label>

<input
type="number"
step="0.00000001"
name="longitude"
class="form-control"
required>

</div>

<div class="col-md-3 mb-3">

<label>Consumers Affected</label>

<input
type="number"
name="consumers_affected"
class="form-control"
value="0"
required>

</div>

<div class="col-md-3 mb-3">

<label>Status</label>

<select
name="status"
class="form-select"
required>

<option value="Issued">

Issued

</option>

<option value="Resolved">

Resolved

</option>

</select>

</div>

<div class="col-md-6 mb-3">

<label>Outage Start</label>

<input
type="datetime-local"
name="start_time"
class="form-control"
required>

</div>

<div class="col-md-6 mb-3">

<label>Estimated Restore</label>

<input
type="datetime-local"
name="estimated_restore"
class="form-control">

</div>

<div class="col-md-12 mb-4">

<label>Outage Reason</label>

<textarea

name="outage_reason"

rows="5"

class="form-control"

placeholder="Enter outage reason..."

required>

</textarea>

</div>

<div class="col-md-12 text-center">

<button

type="submit"

name="add_outage"

class="btn btn-danger px-5">

<i class="fa fa-plus-circle"></i>

Add Outage

</button>

<a

href="manage_outages.php"

class="btn btn-primary px-5 ms-2">

<i class="fa fa-list"></i>

Manage Outages

</a>

</div>

</div>

<!-- ==========================================
END FORM
========================================== -->

</form>

</div>

</div>

<!-- ==========================================
FOOTER
========================================== -->

<footer class="mt-5">

<div class="card border-0 shadow-sm">

<div class="card-body text-center">

<h5 class="fw-bold text-danger">

⚡ Assam Power Distribution Company Limited (APDCL)

</h5>

<p class="mb-1 text-muted">

Power Outage Management System

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
AUTO HIDE ALERTS
========================================== -->

<script>

setTimeout(function(){

let success=document.querySelector(".alert-success");

if(success){

success.style.transition=".5s";

success.style.opacity="0";

setTimeout(function(){

success.remove();

},500);

}

},4000);

setTimeout(function(){

let error=document.querySelector(".alert-danger");

if(error){

error.style.transition=".5s";

error.style.opacity="0";

setTimeout(function(){

error.remove();

},500);

}

},5000);

</script>

<!-- ==========================================
CONFIRM BEFORE SUBMIT
========================================== -->

<script>

document.querySelector("form").addEventListener("submit",function(e){

if(!confirm("Do you want to publish this outage?")){

e.preventDefault();

}

});

</script>

</body>

</html>