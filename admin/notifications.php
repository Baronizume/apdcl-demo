<?php

session_start();

require_once("../db.php");


// Admin login check (change according to your admin session)

if(!isset($_SESSION['admin'])){

    header("Location: login.php");
    exit();

}


/*=====================================
    MARK NOTIFICATIONS AS READ
=====================================*/

mysqli_query($conn,"
UPDATE admin_notifications
SET status='Read'
WHERE status='Unread'
");



/*=====================================
    FETCH ADMIN NOTIFICATIONS
=====================================*/

$query=mysqli_prepare($conn,"
SELECT *
FROM admin_notifications
ORDER BY id DESC
");


mysqli_stmt_execute($query);


$result=mysqli_stmt_get_result($query);

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width,initial-scale=1">


<title>Admin Notifications | APDCL</title>


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



/* NOTIFICATION */


.notification-card{

    background:white;

    padding:20px;

    border-radius:18px;

    margin-bottom:20px;

    box-shadow:0 8px 20px rgba(0,0,0,.08);

    border-left:6px solid #005BAC;

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

    margin-top:10px;

}



.time{

    font-size:13px;

    color:#777;

}



.unread{

    background:#dc3545;

}



.read{

    background:#198754;

}



/* FOOTER */

footer{

    text-align:center;

    color:#777;

    margin-top:40px;

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


<small>Admin Portal</small>


</div>


<hr>


<a href="dashboard.php">

<i class="bi bi-speedometer2"></i>

Dashboard

</a>


<a href="manage_consumers.php">

<i class="bi bi-people"></i>

Consumers

</a>


<a href="manage_bills.php">

<i class="bi bi-receipt"></i>

Bills

</a>


<a href="manage_payments.php">

<i class="bi bi-credit-card"></i>

Payments

</a>


<a href="manage_complaints.php">

<i class="bi bi-chat-left-text"></i>

Complaints

</a>


<a href="notifications.php"
class="active">

<i class="bi bi-bell-fill"></i>

Notifications

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

<i class="bi bi-bell-fill"></i>

Admin Notifications

</h3>


<small>

Notification Center

</small>


</div>



<div class="text-end">


<strong>

Administrator

</strong>


<br>


<small>

APDCL Admin Panel

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

<?= htmlspecialchars($row['title']); ?>


</div>



<?php if($row['status']=="Unread"){ ?>


<span class="badge unread">

Unread

</span>


<?php }else{ ?>


<span class="badge read">

Read

</span>


<?php } ?>


</div>




<hr>



<p class="message">

<?= htmlspecialchars($row['message']); ?>

</p>




<p class="time">

<i class="bi bi-clock"></i>

<?= date(
"d M Y h:i A",
strtotime($row['created_at'])
); ?>


</p>



</div>


<?php } ?>



<?php }else{ ?>


<div class="alert alert-info text-center">


<h5>No Notifications Found</h5>


<p>

Admin notifications will appear here.

</p>


</div>



<?php } ?>




<footer>


<hr>


<h6 class="fw-bold">

Assam Power Distribution Company Limited

</h6>


<p>

APDCL Admin Portal | Internship Demo Project

</p>


<p>

© 2026 APDCL Demo Portal. All Rights Reserved.

</p>


</footer>



</div>


</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>