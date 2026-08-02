<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

/*=========================================
    LOAD CONSUMER DETAILS
=========================================*/

$stmt = mysqli_prepare($conn,"
SELECT *
FROM users
WHERE consumer_no=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$consumer_no);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$consumer = mysqli_fetch_assoc($result);

if(!$consumer){
    die("Consumer not found.");
}

/*=========================================
    LOAD NOTICES
=========================================*/

$noticeQuery = mysqli_query($conn,"
SELECT *
FROM notices
ORDER BY id DESC
");
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Notice Board | APDCL Consumer Portal</title>

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
overflow-x:hidden;
}

/*==============================
WRAPPER
==============================*/

.wrapper{
display:flex;
min-height:100vh;
}

/*==============================
SIDEBAR
==============================*/

.sidebar{

width:270px;

background:linear-gradient(180deg,#0B2C74,#1565d8);

color:#fff;

position:fixed;

left:0;

top:0;

bottom:0;

padding:25px;

overflow-y:auto;

box-shadow:5px 0 20px rgba(0,0,0,.15);

}

.sidebar-header{

text-align:center;

margin-bottom:35px;

}

.sidebar-header img{

width:85px;

height:85px;

background:#fff;

padding:8px;

border-radius:50%;

margin-bottom:12px;

}

.sidebar-header h4{

font-weight:700;

margin-bottom:3px;

}

.sidebar-header small{

opacity:.9;

}

.sidebar-menu{

list-style:none;

padding:0;

}

.sidebar-menu li{

margin-bottom:8px;

}

.sidebar-menu a{

display:flex;

align-items:center;

padding:14px 18px;

color:#fff;

text-decoration:none;

border-radius:12px;

transition:.3s;

font-weight:500;

}

.sidebar-menu a:hover{

background:rgba(255,255,255,.15);

padding-left:24px;

}

.sidebar-menu a i{

font-size:20px;

margin-right:14px;

width:24px;

}

.sidebar-menu .active a{

background:#fff;

color:#1565d8;

font-weight:700;

}

/*==============================
MAIN
==============================*/

.main{

margin-left:270px;

width:calc(100% - 270px);

padding:30px;

}

/*==============================
HERO
==============================*/

.hero{

background:linear-gradient(135deg,#0B2C74,#1565d8,#42a5f5);

color:#fff;

padding:35px;

border-radius:25px;

margin-bottom:35px;

box-shadow:0 15px 35px rgba(0,0,0,.15);

}

.hero h2{

font-size:36px;

font-weight:700;

margin-bottom:10px;

}

.hero p{

font-size:16px;

line-height:1.8;

opacity:.95;

}

.hero .date{

display:inline-block;

margin-top:15px;

background:rgba(255,255,255,.2);

padding:10px 18px;

border-radius:30px;

font-weight:600;

}

/*==============================
NOTICE CARD
==============================*/

.notice-card{

background:#fff;

border:none;

border-radius:20px;

overflow:hidden;

box-shadow:0 10px 25px rgba(0,0,0,.08);

margin-bottom:25px;

transition:.3s;

}

.notice-card:hover{

transform:translateY(-6px);

box-shadow:0 20px 35px rgba(0,0,0,.15);

}

.notice-header{

background:linear-gradient(135deg,#1565d8,#1976d2);

color:#fff;

padding:18px 25px;

font-size:20px;

font-weight:700;

display:flex;

justify-content:space-between;

align-items:center;

}

.notice-body{

padding:25px;

}

.notice-body p{

color:#555;

line-height:1.8;

font-size:15px;

}

.notice-date{

background:#eef5ff;

padding:8px 15px;

border-radius:30px;

font-size:13px;

font-weight:600;

color:#1565d8;

}

.empty-card{

background:#fff;

padding:60px;

border-radius:20px;

text-align:center;

box-shadow:0 10px 25px rgba(0,0,0,.08);

}

.empty-card i{

font-size:70px;

color:#1565d8;

}

.footer{

margin-top:50px;

background:#fff;

border-radius:20px;

padding:30px;

text-align:center;

box-shadow:0 10px 25px rgba(0,0,0,.08);

}

@media(max-width:992px){

.sidebar{

left:-270px;

}

.main{

margin-left:0;

width:100%;

}

.hero{

text-align:center;

}

}

</style>

</head>

<body>

<div class="wrapper">
<!-- SIDEBAR -->

<div class="sidebar">

    <div class="sidebar-header">

        <img src="../assets/images/logo-circle.png" alt="APDCL">

        <h4>APDCL</h4>

        <small>Consumer Portal</small>

    </div>

    <ul class="sidebar-menu">

        <li>
            <a href="dashboard.php">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
        </li>

        <li>
            <a href="bill.php">
                <i class="bi bi-receipt"></i>
                My Bills
            </a>
        </li>

        <li>
            <a href="payment.php">
                <i class="bi bi-credit-card"></i>
                Pay Bill
            </a>
        </li>

        <li>
            <a href="payment_history.php">
                <i class="bi bi-clock-history"></i>
                Payment History
            </a>
        </li>

        <li>
            <a href="complaint.php">
                <i class="bi bi-chat-left-text"></i>
                Complaints
            </a>
        </li>

        <li class="active">
            <a href="notice_board.php">
                <i class="bi bi-megaphone-fill"></i>
                Notice Board
            </a>
        </li>

        <li>
            <a href="profile.php">
                <i class="bi bi-person-circle"></i>
                My Profile
            </a>
        </li>

        <li>
            <a href="logout.php">
                <i class="bi bi-box-arrow-right"></i>
                Logout
            </a>
        </li>

    </ul>

</div>

<!-- MAIN CONTENT -->

<div class="main">

    <!-- HERO -->

    <div class="hero">

        <div class="row align-items-center">

            <div class="col-lg-8">

                <h2>

                    <i class="bi bi-megaphone-fill me-2"></i>

                    APDCL Notice Board

                </h2>

                <p>

                    Welcome

                    <strong><?= htmlspecialchars($consumer['name']) ?></strong>,

                    stay updated with the latest APDCL announcements,
                    scheduled maintenance, power interruptions,
                    consumer services and important notifications.

                </p>

                <div class="date">

                    <i class="bi bi-calendar-event me-2"></i>

                    <?= date("d F Y") ?>

                </div>

            </div>

            <div class="col-lg-4 text-end">

                <img
                    src="../assets/images/logo-circle.png"
                    width="120"
                    class="img-fluid">

            </div>

        </div>

    </div>

    <!-- NOTICE LIST -->
     <?php if(mysqli_num_rows($noticeQuery) > 0){ ?>

    <?php while($notice = mysqli_fetch_assoc($noticeQuery)){ ?>

        <div class="notice-card">

            <div class="notice-header">

                <div>

                    <i class="bi bi-pin-angle-fill me-2"></i>

                    <?= htmlspecialchars($notice['title']) ?>

                </div>

                <span class="notice-date">

                    <i class="bi bi-calendar-event me-1"></i>

                    <?= date("d M Y", strtotime($notice['created_at'])) ?>

                </span>

            </div>

            <div class="notice-body">

                <p>

                    <?= nl2br(htmlspecialchars($notice['message'])) ?>

                </p>

            </div>

        </div>

    <?php } ?>

<?php } else { ?>

    <div class="empty-card">

        <i class="bi bi-megaphone-fill"></i>

        <h3 class="mt-4">

            No Notices Available

        </h3>

        <p class="text-muted mt-2">

            There are currently no notices published by APDCL.

        </p>

    </div>

<?php } ?>

<!-- FOOTER -->

<div class="footer">

    <img
        src="../assets/images/logo-circle.png"
        width="70"
        class="mb-3">

    <h5 class="text-primary fw-bold">

        Assam Power Distribution Company Limited

    </h5>

    <p class="text-muted">

        APDCL Consumer Portal | Internship Demo Project

    </p>

    <hr>

    <div class="row justify-content-center">

        <div class="col-auto">

            <i class="bi bi-telephone-fill text-primary"></i>

            1912

        </div>

        <div class="col-auto">

            <i class="bi bi-envelope-fill text-primary"></i>

            support@apdcl.org

        </div>

        <div class="col-auto">

            <i class="bi bi-geo-alt-fill text-primary"></i>

            Assam, India

        </div>

    </div>

    <p class="mt-3 mb-0 text-secondary">

        © <?= date("Y") ?> <strong>APDCL Consumer Portal</strong> | All Rights Reserved.

    </p>

</div>

</div>
<!-- End Main -->

</div>
<!-- End Wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

// Smooth card animation

window.addEventListener("load", function(){

    document.querySelectorAll(".notice-card").forEach(function(card,index){

        card.style.opacity = "0";
        card.style.transform = "translateY(30px)";

        setTimeout(function(){

            card.style.transition = "all .5s ease";
            card.style.opacity = "1";
            card.style.transform = "translateY(0)";

        }, index * 120);

    });

});

</script>

</body>

</html>
