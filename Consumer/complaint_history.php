<?php

session_start();

require_once("../db.php");


if(!isset($_SESSION['consumer'])){

    header("Location: login.php");
    exit();

}


$consumer_no=$_SESSION['consumer'];



$query=mysqli_prepare($conn,"
SELECT *
FROM complaint
WHERE consumer_no=?
ORDER BY id DESC
");


mysqli_stmt_bind_param(
    $query,
    "s",
    $consumer_no
);


mysqli_stmt_execute($query);


$result=mysqli_stmt_get_result($query);



/* COUNT STATUS */

$total=mysqli_num_rows($result);


mysqli_data_seek($result,0);


$pending=0;
$resolved=0;


while($c=mysqli_fetch_assoc($result)){

    if($c['status']=="Pending"){
        $pending++;
    }

    if($c['status']=="Resolved"){
        $resolved++;
    }

}


mysqli_data_seek($result,0);


?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">


<title>Complaint History | APDCL Consumer Portal</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">



<style>


body{

background:#f1f6fc;
font-family:'Segoe UI',sans-serif;

}



/* SIDEBAR */


.sidebar{

position:fixed;
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



.sidebar a{

display:block;

padding:12px;

margin-top:8px;

color:white;

text-decoration:none;

border-radius:10px;

}


.sidebar a:hover,
.sidebar .active{

background:rgba(255,255,255,.2);

}



/* MAIN */


.main{

margin-left:260px;

}



.topbar{

background:white;

padding:20px 30px;

box-shadow:0 5px 15px rgba(0,0,0,.1);

display:flex;

justify-content:space-between;

}



/* CONTENT */


.content{

padding:30px;

}



/* CARDS */


.stat-card{

border:none;

border-radius:18px;

box-shadow:0 5px 15px rgba(0,0,0,.1);

}



.table{

background:white;

border-radius:15px;

overflow:hidden;

}



.table th{

background:#005BAC;

color:white;

text-align:center;

}



.table td{

text-align:center;

vertical-align:middle;

}



.badge{

padding:8px 15px;

border-radius:20px;

}



.btn{

border-radius:10px;

}



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


<h4 class="mt-2">

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

Bills

</a>



<a href="payment_history.php">

<i class="bi bi-credit-card"></i>

Payments

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



<a href="profile.php">

<i class="bi bi-person"></i>

Profile

</a>



<a href="logout.php">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>



</div>





<!-- MAIN -->


<div class="main">



<div class="topbar">


<div>

<h3 class="text-primary fw-bold">

<i class="bi bi-list-check"></i>

Complaint History

</h3>


<small>
Track all your complaints
</small>


</div>



<div>

<strong>

<?= htmlspecialchars($consumer_no) ?>

</strong>


</div>


</div>





<div class="content">





<!-- STATISTICS -->


<div class="row mb-4">


<div class="col-md-4">

<div class="card stat-card p-4">


<h6>Total Complaints</h6>

<h2 class="text-primary">

<?= $total ?>

</h2>


</div>

</div>



<div class="col-md-4">

<div class="card stat-card p-4">


<h6>Pending</h6>

<h2 class="text-warning">

<?= $pending ?>

</h2>


</div>

</div>



<div class="col-md-4">

<div class="card stat-card p-4">


<h6>Resolved</h6>

<h2 class="text-success">

<?= $resolved ?>

</h2>


</div>

</div>



</div>





<div class="card shadow border-0">


<div class="card-header bg-primary text-white">


<h5 class="mb-0">

Complaint Records

</h5>


</div>



<div class="card-body">



<div class="table-responsive">


<table class="table table-bordered">


<thead>

<tr>

<th>#</th>

<th>Complaint ID</th>

<th>Category</th>

<th>Status</th>

<th>Officer</th>

<th>Date</th>

<th>Action</th>


</tr>

</thead>



<tbody>


<?php

$sl=1;


while($complaint=mysqli_fetch_assoc($result)){


?>


<tr>


<td>

<?= $sl++; ?>

</td>



<td class="fw-bold">

<?= htmlspecialchars($complaint['complaint_id']); ?>

</td>



<td>

<?= htmlspecialchars($complaint['category']); ?>

</td>



<td>


<?php


$status=$complaint['status'];


$color="secondary";


if($status=="Pending")
$color="warning";

elseif($status=="Assigned")
$color="info";

elseif($status=="In Progress")
$color="primary";

elseif($status=="Resolved")
$color="success";

elseif($status=="Rejected")
$color="danger";


?>


<span class="badge bg-<?= $color ?>">

<?= $status ?>

</span>



</td>



<td>


<?= !empty($complaint['assigned_to'])

? htmlspecialchars($complaint['assigned_to'])

: "Not Assigned"; ?>


</td>




<td>


<?= date(
"d M Y",
strtotime($complaint['created_at'])
); ?>


</td>




<td>


<a href="view_complaint.php?id=<?= $complaint['id']; ?>"
class="btn btn-primary btn-sm">

    <i class="bi bi-eye"></i>
    View

</a>

<a href="track_complaint.php?id=<?= $complaint['id']; ?>"
class="btn btn-info btn-sm text-white">

    <i class="bi bi-geo-alt"></i>
    Track

</a>



</td>



</tr>


<?php } ?>


</tbody>


</table>


</div>


</div>


</div>





<footer class="text-center mt-5 text-muted">


<hr>


<h6 class="fw-bold">

Assam Power Distribution Company Limited

</h6>


<p>

APDCL Consumer Portal | Internship Demo Project

</p>


</footer>



</div>


</div>



</body>

</html>
