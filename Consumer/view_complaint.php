<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================
    LOGGED IN CONSUMER
=========================================*/

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

$user = mysqli_fetch_assoc($userQuery);


/*=========================================
    GET COMPLAINT ID
=========================================*/

if(isset($_GET['id']) && !empty($_GET['id'])){

    $complaint_id = trim($_GET['id']);

}else{

    // Load latest complaint automatically

$latest = mysqli_query($conn,"
SELECT id
FROM complaint
WHERE consumer_no='$consumer_no'
ORDER BY id DESC
LIMIT 1
");

if(mysqli_num_rows($latest)==0){

    die("No complaint found.");

}

$latestData = mysqli_fetch_assoc($latest);

$complaint_id = $latestData['id'];

}


/*=========================================
    FETCH COMPLAINT
=========================================*/

$complaint_id = (int)$complaint_id;

$stmt = mysqli_prepare($conn,"
SELECT *
FROM complaint
WHERE id=?
LIMIT 1
");

if(!$stmt){
    die("SQL Error : ".mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt,"i",$complaint_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

/* Check if complaint exists */

if(mysqli_num_rows($result)==0){
    die("Complaint not found.");
}

/* Fetch complaint data */

$complaint = mysqli_fetch_assoc($result);

/* Security Check */

if($complaint['consumer_no'] != $consumer_no){
    die("Access denied.");
}

/*=========================================
    STATUS BADGE
=========================================*/

switch($complaint['status']){

    case "Pending":
        $badge="warning";
        break;

    case "Assigned":
        $badge="info";
        break;

    case "In Progress":
        $badge="primary";
        break;

    case "Resolved":
        $badge="success";
        break;

    case "Rejected":
        $badge="danger";
        break;

    default:
        $badge="secondary";
}

/*=========================================
    PHOTO
=========================================*/

$photo="";

if(
    !empty($complaint['photo']) &&
    file_exists("../uploads/complaint/".$complaint['photo'])
){
    $photo="../uploads/complaint/".$complaint['photo'];
}

/*=========================================
    GOOGLE MAP LINK
=========================================*/

$mapLink="";

if(
    !empty($complaint['latitude']) &&
    !empty($complaint['longitude'])
){
    $mapLink="https://www.google.com/maps?q="
        .$complaint['latitude'].","
        .$complaint['longitude'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>View Complaint | APDCL Consumer Portal</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Segoe UI',sans-serif;
}


body{

background:#eef3fb;

}


/* SIDEBAR */

.sidebar{

position:fixed;
left:0;
top:0;
width:260px;
height:100vh;

background:linear-gradient(180deg,#003366,#0066cc);

color:white;

padding:20px;

}


.logo{

text-align:center;

}


.logo img{

width:90px;
height:90px;

background:white;

border-radius:50%;

padding:8px;

}


.logo h4{

margin-top:10px;

font-weight:700;

}



.sidebar a{

display:block;

color:white;

text-decoration:none;

padding:13px 15px;

border-radius:10px;

margin-top:8px;

transition:.3s;

}


.sidebar a:hover,
.sidebar a.active{

background:rgba(255,255,255,.2);

}



/* MAIN */

.main{

margin-left:260px;

}



/* HEADER */

.topbar{

background:white;

padding:20px 30px;

box-shadow:0 5px 15px rgba(0,0,0,.08);

display:flex;

justify-content:space-between;

align-items:center;

}



.content{

padding:30px;

}



/* CARD */

.card{

border:none;

border-radius:18px;

box-shadow:0 8px 20px rgba(0,0,0,.08);

margin-bottom:25px;

}


.card-header{

font-weight:700;

border-radius:18px 18px 0 0 !important;

}



.form-control{

border-radius:10px;

background:#f8f9fa;

}



/* PHOTO */

.photo{

width:250px;

border-radius:15px;

border:3px solid #ddd;

}



/* MOBILE */

@media(max-width:992px){

.sidebar{

position:relative;

width:100%;

height:auto;

}


.main{

margin-left:0;

}

}


</style>


</head>


<body>



<!-- SIDEBAR -->

<div class="sidebar">


<div class="logo">


<img src="../assets/images/logo-circle.png">


<h4>

APDCL

</h4>


<small>

Consumer Portal

</small>


</div>


<hr>


<a href="dashboard.php">

<i class="bi bi-speedometer2"></i>

Dashboard

</a>


<a href="bill.php">

<i class="bi bi-receipt"></i>

View Bills

</a>


<a href="payment.php">

<i class="bi bi-credit-card"></i>

Pay Bill

</a>


<a href="payment_history.php">

<i class="bi bi-clock-history"></i>

Payment History

</a>


<a href="complaint.php">

<i class="bi bi-pencil-square"></i>

Register Complaint

</a>


<a href="complaint_history.php"
class="active">

<i class="bi bi-list-check"></i>

Complaint History

</a>

<a href="complaint_history.php">

<i class="bi bi-geo-alt"></i>

Track Complaint

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





<!-- MAIN -->


<div class="main">



<!-- HEADER -->


<div class="topbar">


<div>

<h3 class="text-primary fw-bold">

<i class="bi bi-eye-fill"></i>

View Complaint

</h3>


<small>

Complaint Details

</small>


</div>



<div class="text-end">


<strong>

<?= htmlspecialchars($user['name']) ?>

</strong>

<br>


<small>

<?= htmlspecialchars($consumer_no) ?>

</small>


</div>


</div>




<div class="content">



<!-- COMPLAINT SUMMARY -->


<div class="card">


<div class="card-header bg-primary text-white">

<i class="bi bi-info-circle"></i>

Complaint Summary

</div>


<div class="card-body">


<div class="row">


<div class="col-md-4">

<label>Complaint ID</label>

<input class="form-control"
readonly
value="<?= $complaint['complaint_id'] ?>">


</div>



<div class="col-md-4">

<label class="form-label">Status</label>

<div class="mt-2">

<span class="badge bg-<?= $badge ?> px-3 py-2">

<?= $complaint['status'] ?>

</span>

</div>

</div>

<div class="col-md-4">



<div class="col-md-4">

<label>Date</label>


<input class="form-control"
readonly
value="<?=date('d M Y h:i A',strtotime($complaint['created_at']))?>">


</div>


</div>


</div>

</div>

<!-- ===========================
     FOOTER
=========================== -->

<footer class="text-center mt-5 mb-3 text-muted">

<hr>

<h6 class="fw-bold">

Assam Power Distribution Company Limited

</h6>


<p class="mb-1">

APDCL Consumer Portal | Internship Demo Project

</p>


<p>

© 2026 APDCL Demo Portal. All Rights Reserved.

</p>


</footer>



</div> 
<!-- content -->


</div>
<!-- main -->




<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>




<script>

// Image click preview

document.addEventListener("DOMContentLoaded",function(){


let image=document.querySelector(".photo");


if(image){


image.style.cursor="pointer";


image.onclick=function(){


window.open(this.src,"_blank");


};


}


});



</script>




</body>

</html>
