<?php

session_start();

require_once("../db.php");


if(!isset($_SESSION['consumer'])){

    header("Location: login.php");
    exit();

}


$consumer_no=$_SESSION['consumer'];


// Consumer Details

$userQuery=mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");


$user=mysqli_fetch_assoc($userQuery);



// Mark as read
mysqli_query($conn,"
UPDATE notifications
SET status='Read'
WHERE consumer_no='$consumer_no'
AND status='Unread'
");



// Fetch notifications

$query=mysqli_prepare($conn,"
SELECT *
FROM notifications
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

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">


<title>Notifications | APDCL Consumer Portal</title>


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
width:260px;
height:100vh;

background:linear-gradient(180deg,#003366,#0066cc);

padding:20px;

color:white;

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

font-weight:700;

margin-top:10px;

}



.sidebar a{

display:block;

color:white;

text-decoration:none;

padding:12px 15px;

margin-top:8px;

border-radius:10px;

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

display:flex;

justify-content:space-between;

align-items:center;

box-shadow:0 5px 15px rgba(0,0,0,.1);

}



.content{

padding:30px;

}



/* NOTIFICATION CARD */


.notification-card{

background:white;

border-radius:18px;

padding:20px;

margin-bottom:20px;

box-shadow:0 8px 20px rgba(0,0,0,.08);

border-left:5px solid #0d6efd;

transition:.3s;

}


.notification-card:hover{

transform:translateY(-5px);

}



.notification-title{

font-size:20px;

font-weight:700;

color:#005BAC;

}



.message{

color:#555;

}



.time{

font-size:13px;

color:#777;

}



.badge-read{

background:#198754;

}


.badge-unread{

background:#dc3545;

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

<h4>APDCL</h4>

<small>Consumer Portal</small>

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



<a href="notifications.php" class="active">

<i class="bi bi-bell-fill"></i>

Notifications

</a>



<a href="complaint.php">

<i class="bi bi-pencil-square"></i>

Register Complaint

</a>



<a href="complaint_history.php">

<i class="bi bi-list-check"></i>

Complaint History

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



<div class="topbar">


<div>

<h3 class="text-primary fw-bold">

<i class="bi bi-bell-fill"></i>

Notifications

</h3>

<small>

Consumer Notification Center

</small>

</div>



<div class="text-end">

<strong>

<?= htmlspecialchars($user['name'] ?? 'Consumer') ?>

</strong>

<br>

<small>

<?= htmlspecialchars($consumer_no) ?>

</small>

</div>


</div>





<div class="content">



<?php if(mysqli_num_rows($result)>0){ ?>


<?php while($row=mysqli_fetch_assoc($result)){ ?>



<div class="notification-card">


<div class="d-flex justify-content-between">


<div class="notification-title">

<i class="bi bi-info-circle-fill"></i>

<?= htmlspecialchars($row['title']) ?>

</div>



<span class="badge badge-read">

<?= htmlspecialchars($row['status']) ?>

</span>


</div>



<hr>



<p class="message">

<?= htmlspecialchars($row['message']) ?>

</p>



<p class="time mb-0">

<i class="bi bi-clock"></i>

<?= date(
"d M Y h:i A",
strtotime($row['created_at'])
) ?>

</p>



</div>



<?php } ?>



<?php }else{ ?>


<div class="alert alert-info text-center">

<h5>No Notifications Found</h5>

<p>You don't have any notifications.</p>

</div>


<?php } ?>





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



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>