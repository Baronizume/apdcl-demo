<?php

session_start();

include("../db.php");


/* ==========================
   CHECK LOGIN
========================== */

if(!isset($_SESSION['consumer'])){

    header("Location: login.php");
    exit();

}


$consumer_no = $_SESSION['consumer'];



/* ==========================
   GET CONSUMER DETAILS
========================== */


$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");


$user = mysqli_fetch_assoc($userQuery);



if(!$user){

    die("Consumer not found");

}



/* ==========================
   PROFILE IMAGE
========================== */


$profilePhoto = "../assets/images/default-user.png";


if(!empty($user['photo']) && file_exists("../uploads/".$user['photo'])){


    $profilePhoto="../uploads/".$user['photo'];

}


?>


<!DOCTYPE html>

<html lang="en">


<head>


<meta charset="UTF-8">


<meta name="viewport" content="width=device-width, initial-scale=1.0">



<title>
APDCL Consumer Profile
</title>



<!-- Bootstrap -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<!-- Icons -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

html,body{
    width:100%;
    background:#eef5fc;
    overflow-x:hidden;
}


/* ================= HEADER ================= */

.top-header{

    width:100vw;
    margin-left:calc(50% - 50vw);

    height:110px;

    background:linear-gradient(
        90deg,
        #002b5c,
        #005bac
    );

    display:flex;
    align-items:center;
    justify-content:space-between;

    padding:15px 45px;

    color:white;
}


.brand{
    display:flex;
    align-items:center;
}


.brand img{

    width:75px;
    height:75px;

    border-radius:50%;
    background:white;

    padding:5px;
    margin-right:15px;
}


.brand h2{

    font-size:30px;
    font-weight:800;
}


.brand small{
    font-size:13px;
}


/* ELECTRICITY */

.electricity-brand{

    display:flex;
    align-items:center;
    gap:15px;

}


.power-circle{

    width:65px;
    height:65px;

    border-radius:50%;
    border:4px solid #ffc107;

    display:flex;
    align-items:center;
    justify-content:center;

}


.power-circle i{

    color:#ffc107;
    font-size:38px;

}



/* USER */

.user-area{

    display:flex;
    align-items:center;

}


.user-area img{

    width:60px;
    height:60px;

    border-radius:50%;

    border:3px solid white;

    object-fit:cover;

}


.logout-btn{

    margin-left:20px;

    background:#dc3545;

    color:white;

    padding:10px 20px;

    border-radius:8px;

    text-decoration:none;

}



/* ================= NAVBAR ================= */


.profile-nav{

    width:100vw;

    margin-left:calc(50% - 50vw);

    height:60px;

    background:white;

    display:flex;

    justify-content:center;

    align-items:center;

    gap:40px;

    box-shadow:0 4px 12px rgba(0,0,0,.1);

}


.profile-nav a{

    text-decoration:none;

    color:#003366;

    font-weight:600;

}



/* ================= BANNER ================= */


.profile-banner{

    width:100vw;

    margin-left:calc(50% - 50vw);

    height:150px;


    background:

    linear-gradient(
    90deg,
    rgba(235,245,255,.95),
    rgba(255,255,255,.5)
    ),

    url("https://images.unsplash.com/photo-1473341304170-971dccb5ac1e");


    background-size:cover;

    background-position:center;

    display:flex;

    align-items:center;

}


.banner-content{

    display:flex;

    align-items:center;

    gap:25px;

    padding-left:50px;

}



.profile-icon{

    width:75px;
    height:75px;

    border-radius:50%;

    background:#315dcc;

    display:flex;

    align-items:center;
    justify-content:center;

}


.profile-icon i{

    color:white;
    font-size:40px;

}



/* ================= CONTENT ================= */


.page-wrapper{

    width:100vw;

    margin-left:calc(50% - 50vw);

    padding:30px 45px;

}


.container-fluid{

    width:100%;

    padding:0!important;

}



.profile-card{

    background:white;

    width:100%;

    padding:25px;

    border-radius:20px;

    box-shadow:0 8px 25px rgba(0,0,0,.12);

}



/* PROFILE */


.profile-image{

    width:140px;

    height:140px;

    border-radius:50%;

    object-fit:cover;

    border:5px solid #dcecff;

}


.profile-info{

    display:flex;

    align-items:center;

    gap:15px;

    margin:20px 0;

}



.profile-info i{

    width:55px;

    height:55px;

    display:flex;

    align-items:center;

    justify-content:center;

    background:#eaf2ff;

    color:#315dcc;

    border-radius:12px;

    font-size:25px;

}



.card-heading{

    color:#005bac;

    font-size:24px;

    font-weight:800;

    border-bottom:2px solid #d5e5ff;

    padding-bottom:12px;

}



.form-control{

    border-radius:10px;

    padding:12px;

}



/* PASSWORD */

.password-btn{

    background:#003366;

    color:white;

    padding:10px 25px;

    border-radius:8px;

}



.password-note{

    background:#e7f1ff;

    padding:12px;

    border-radius:8px;

}



/* ==========================
   APDCL DASHBOARD FOOTER
========================== */


.apdcl-footer{

    width:100%;

    background:linear-gradient(
        90deg,
        #0B2C74,
        #1565d8
    );

    color:white;

    margin-top:50px;

    padding:50px 40px 20px;

}



/* Footer container */

.footer-container{

    width:100%;

    display:flex;

    justify-content:space-around;

    align-items:flex-start;

    gap:40px;

}



/* Footer sections */

.footer-section{

    flex:1;

    text-align:center;

    color:white;

}



/* Logo */

.footer-section img{

    width:70px;

    height:70px;

    background:white;

    padding:5px;

    border-radius:50%;

    margin-bottom:10px;

}



.footer-section h4{

    font-size:24px;

    font-weight:800;

    margin-bottom:8px;

}



.footer-section h5{

    font-size:18px;

    font-weight:700;

    margin-bottom:15px;

}



.footer-section p{

    margin:6px 0;

    font-size:14px;

    color:#e5efff;

}



/* Quick links */

.footer-section a{

    display:block;

    color:#e5efff;

    text-decoration:none;

    margin:8px 0;

    font-size:14px;

}



.footer-section a:hover{

    color:#ffc107;

}



/* Icons */

.footer-section i{

    color:#ffc107;

    margin-right:6px;

}



/* Social icons */

.social-icons{

    display:flex;

    justify-content:center;

    gap:15px;

}



.social-icons a{

    width:40px;

    height:40px;

    border-radius:50%;

    background:rgba(255,255,255,.15);

    display:flex;

    align-items:center;

    justify-content:center;

}



.social-icons i{

    color:white;

    margin:0;

    font-size:20px;

}



/* Bottom */

.footer-bottom{

    border-top:1px solid rgba(255,255,255,.3);

    margin-top:30px;

    padding-top:15px;

    text-align:center;

    font-size:14px;

    color:#e5efff;

}



/* Mobile */

@media(max-width:992px){


.footer-container{

    flex-direction:column;

    align-items:center;

}


.footer-section{

    width:100%;

}

}


/* MOBILE */

@media(max-width:992px){

.top-header{

    flex-direction:column;

    height:auto;

    gap:20px;

}


.profile-nav{

    overflow-x:auto;

}


.apdcl-footer{

    flex-direction:column;

}


}

.profile-container{

    width:100vw;

    margin-left:calc(50% - 50vw);

    padding:30px 45px;

}


.profile-container .row{

    width:100%;

    margin:0;

}


.profile-container .profile-card{

    width:100%;

}



.apdcl-footer{

    width:120%;

    margin-left:calc(50% - 50vw);

    display:flex;

    justify-content:center;

    align-items:center;

}

</style>

</head>

<body>

<!-- ==========================
     HEADER
========================== -->


<div class="top-header">



<!-- APDCL BRAND -->


<div class="brand">


<img src="../assets/images/logo-circle.png">


<div>


<h2>
APDCL
</h2>


<small>
Assam Power Distribution Company Limited
</small>


</div>


</div>




<!-- ELECTRICITY BRAND -->


<div class="electricity-brand">


<div class="power-circle">


<i class="bi bi-lightning-charge-fill"></i>


</div>



<div>


<h4>
ELECTRICITY
</h4>


<small>
Safe • Reliable • Sustainable
</small>


</div>



</div>




<!-- USER -->


<div class="user-area">


<img src="<?= $profilePhoto ?>">



<div>


<b>
<?= htmlspecialchars($user['name']); ?>
</b>


<br>


<small>

Consumer No:
<?= $consumer_no ?>

</small>


</div>



<a href="logout.php"
class="logout-btn">


<i class="bi bi-box-arrow-right"></i>

Logout


</a>


</div>



</div>

<!-- ==========================
NAVIGATION BAR
========================== -->

<div class="profile-nav">


<a href="dashboard.php">
<i class="bi bi-speedometer2"></i>
Dashboard
</a>


<a href="bill.php">
<i class="bi bi-receipt"></i>
Bills
</a>


<a href="payment.php">
<i class="bi bi-credit-card"></i>
Payment
</a>


<a href="complaint.php">
<i class="bi bi-tools"></i>
Complaint
</a>


<a href="outage_map.php">
<i class="bi bi-lightning-charge"></i>
Power Outage
</a>


<a href="notifications.php">
<i class="bi bi-bell-fill"></i>
Notifications
</a>


</div>

<!-- ==========================
PROFILE TOWER BANNER
========================== -->


<div class="profile-banner">


<div class="banner-content">


<div class="profile-icon">


<i class="bi bi-person-fill"></i>


</div>



<div>


<h1>
My Profile
</h1>


<p>
Manage your personal information and electricity account
</p>


</div>


</div>


</div>

<!-- ==========================
PROFILE CONTENT
========================== -->


<div class="page-wrapper">

<div class="container-fluid profile-container">


<div class="row g-4 align-items-start">



<!-- LEFT PROFILE CARD -->

<div class="col-lg-3">


<div class="profile-card text-center">


<img src="<?= $profilePhoto ?>"
class="profile-image">


<h3 class="mt-3 fw-bold">

<?= htmlspecialchars($user['name']); ?>

</h3>



<span class="badge bg-success">

Active Consumer

</span>



<hr>



<div class="profile-info">

<i class="bi bi-person-badge-fill"></i>

<div>

<b>Consumer Number</b>

<br>

<?= $user['consumer_no']; ?>

</div>


</div>




<div class="profile-info">

<i class="bi bi-telephone-fill"></i>

<div>

<b>Mobile</b>

<br>

<?= $user['mobile']; ?>

</div>


</div>




<div class="profile-info">

<i class="bi bi-envelope-fill"></i>

<div>

<b>Email</b>

<br>

<?= $user['email']; ?>

</div>


</div>



</div>


</div>





<!-- RIGHT SIDE -->

<div class="col-lg-9">



<div class="profile-card">


<h3 class="card-heading">

<i class="bi bi-person-lines-fill"></i>

Personal Information

</h3>




<div class="row">



<div class="col-md-6 mb-3">


<label>
Full Name
</label>


<input type="text"
class="form-control"
name="name"
value="<?= htmlspecialchars($user['name']); ?>">


</div>





<div class="col-md-6 mb-3">


<label>
Consumer Number
</label>


<input type="text"
class="form-control"
value="<?= $user['consumer_no']; ?>"
readonly>


</div>





<div class="col-md-6 mb-3">


<label>
Email Address
</label>


<input type="email"
class="form-control"
name="email"
value="<?= htmlspecialchars($user['email']); ?>">


</div>





<div class="col-md-6 mb-3">


<label>
Mobile Number
</label>


<input type="text"
class="form-control"
name="mobile"
value="<?= htmlspecialchars($user['mobile']); ?>">


</div>





<div class="col-12 mb-3">


<label>
Address
</label>


<textarea class="form-control"
name="address"
rows="3"><?= htmlspecialchars($user['address']); ?></textarea>


</div>



</div>




<button class="btn btn-primary float-end">

<i class="bi bi-save"></i>

Update Profile

</button>



</div>





<!-- ELECTRICITY DETAILS -->


<div class="profile-card mt-4">



<h3 class="card-heading">

<i class="bi bi-lightning-charge-fill"></i>

Electricity Connection Details

</h3>




<div class="row">



<div class="col-md-6 mb-3">

<label>
Meter Number
</label>


<input class="form-control"
value="<?= $user['meter_no'] ?? 'N/A'; ?>">


</div>





<div class="col-md-6 mb-3">

<label>
Category
</label>


<input class="form-control"
value="<?= $user['category'] ?? 'Domestic'; ?>">


</div>





<div class="col-md-6 mb-3">

<label>
Zone
</label>


<input class="form-control"
value="<?= $user['zone'] ?? 'N/A'; ?>">


</div>





<div class="col-md-6 mb-3">

<label>
Circle
</label>


<input class="form-control"
value="<?= $user['circle'] ?? 'N/A'; ?>">


</div>





<div class="col-md-6 mb-3">

<label>
Sub Division
</label>


<input class="form-control"
value="<?= $user['sub_division'] ?? 'N/A'; ?>">


</div>





<div class="col-md-6 mb-3">

<label>
DTR Number
</label>


<input class="form-control"
value="<?= $user['dtr_no'] ?? 'N/A'; ?>">


</div>



</div>


</div>

<!-- ==========================
CHANGE PASSWORD
========================== -->

<div class="container-fluid px-5">

<div class="profile-card mt-4">


<h3 class="card-heading">

<i class="bi bi-shield-lock-fill"></i>

Change Password

</h3>



<div class="row">



<div class="col-md-4 mb-3">


<label>
Current Password
</label>


<div class="input-group">


<span class="input-group-text">

<i class="bi bi-lock-fill"></i>

</span>


<input type="password"
class="form-control"
placeholder="Enter current password">


</div>


</div>





<div class="col-md-4 mb-3">


<label>
New Password
</label>


<div class="input-group">


<span class="input-group-text">

<i class="bi bi-key-fill"></i>

</span>


<input type="password"
class="form-control"
placeholder="Enter new password">


</div>


</div>





<div class="col-md-4 mb-3">


<label>
Confirm Password
</label>


<div class="input-group">


<span class="input-group-text">

<i class="bi bi-check-circle-fill"></i>

</span>


<input type="password"
class="form-control"
placeholder="Confirm password">


</div>


</div>



</div>




<div class="text-end">


<button class="btn password-btn">


<i class="bi bi-shield-check"></i>

Change Password


</button>


</div>



<div class="password-note mt-3">


<i class="bi bi-info-circle-fill"></i>

For security reasons, use a strong password with letters, numbers and symbols.


</div>



</div>

<!-- ==========================
FOOTER
========================== -->

<footer class="apdcl-footer">


<div class="footer-container">


<!-- APDCL BRAND -->

<div class="footer-section">


<img src="../assets/images/logo-circle.png">


<h4>
APDCL
</h4>


<p>
Assam Power Distribution Company Limited
</p>


<small>
© <?= date('Y'); ?> All Rights Reserved
</small>


</div>





<!-- QUICK LINKS -->

<div class="footer-section">


<h5>
Quick Links
</h5>


<a href="dashboard.php">
Dashboard
</a>


<a href="bill.php">
My Bills
</a>


<a href="payment_history.php">
Payment History
</a>


<a href="complaint.php">
Complaints
</a>


</div>






<!-- CUSTOMER SERVICE -->

<div class="footer-section">


<h5>
Customer Service
</h5>


<p>
<i class="bi bi-telephone-fill"></i>
1912
</p>


<p>
<i class="bi bi-envelope-fill"></i>
customercare@apdcl.org
</p>


<p>
<i class="bi bi-globe"></i>
www.apdcl.org
</p>


</div>






<!-- SOCIAL -->

<div class="footer-section">


<h5>
Follow Us
</h5>


<div class="social-icons">


<a href="#">
<i class="bi bi-facebook"></i>
</a>


<a href="#">
<i class="bi bi-twitter"></i>
</a>


<a href="#">
<i class="bi bi-youtube"></i>
</a>


<a href="#">
<i class="bi bi-globe"></i>
</a>


</div>


</div>



</div>


<div class="footer-bottom">


APDCL Consumer Portal | Internship Demo Project


</div>


</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>