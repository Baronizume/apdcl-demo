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

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>APDCL Consumer Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<link rel="preconnect" href="https://fonts.googleapis.com">

<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{
background:#eef4fb;
overflow-x:hidden;
}

.layout{
display:flex;
min-height:100vh;
}

/* ================= Sidebar ================= */

.sidebar{

width:270px;

background:linear-gradient(180deg,#0B2C74,#1565d8);

color:#fff;

position:fixed;

left:0;

top:0;

bottom:0;

padding:25px 20px;

display:flex;

flex-direction:column;

box-shadow:5px 0 20px rgba(0,0,0,.15);

z-index:1000;

}

.logo{

text-align:center;

margin-bottom:35px;

}

.logo img{

width:80px;

height:80px;

background:#fff;

border-radius:50%;

padding:6px;

margin-bottom:10px;

}

.logo h3{

font-weight:700;

margin-bottom:3px;

}

.logo small{

color:#dbe8ff;

}

.menu{

list-style:none;

padding:0;

margin:0;

flex:1;

}

.menu li{

margin-bottom:8px;

}

.menu a{

display:flex;

align-items:center;

gap:15px;

padding:14px 18px;

border-radius:12px;

color:#fff;

text-decoration:none;

transition:.3s;

font-weight:500;

}

.menu a:hover{

background:rgba(255,255,255,.15);

transform:translateX(5px);

}

.menu .active a{

background:#fff;

color:#0B2C74;

font-weight:600;

}

.menu i{

font-size:20px;

}

/* Customer Care */

.support{

background:rgba(255,255,255,.15);

border-radius:18px;

padding:20px;

text-align:center;

margin-top:25px;

}

.support i{

font-size:38px;

margin-bottom:10px;

}

.support h2{

font-size:30px;

font-weight:700;

margin:10px 0;

}

/* ================= Content ================= */

.content{

margin-left:270px;

width:calc(100% - 270px);

padding:30px;

}

/* ================= Header ================= */

.topbar{

background:#fff;

padding:18px 30px;

border-radius:20px;

display:flex;

justify-content:space-between;

align-items:center;

box-shadow:0 8px 25px rgba(0,0,0,.08);

margin-bottom:30px;

}

.topbar h3{

font-weight:700;

color:#0B2C74;

}

.top-right{

display:flex;

align-items:center;

gap:20px;

}

.top-right i{

font-size:22px;

cursor:pointer;

color:#0B2C74;

}

.top-right img{

width:48px;

height:48px;

border-radius:50%;

object-fit:cover;

border:3px solid #1565d8;

}

.top-right b{

display:block;

font-size:15px;

}

.top-right small{

color:#777;

}

/* ================= Cards ================= */

.card-box{

background:#fff;

border-radius:22px;

padding:30px;

box-shadow:0 8px 25px rgba(0,0,0,.08);

margin-bottom:25px;

}

.section-title{

font-size:22px;

font-weight:700;

color:#0B2C74;

margin-bottom:25px;

padding-bottom:10px;

border-bottom:2px solid #eef3ff;

}

.form-control{

border-radius:12px;

padding:12px;

}

.btn-primary{

border-radius:12px;

padding:12px 30px;

background:#1565d8;

border:none;

}

.btn-primary:hover{

background:#0B2C74;

}

/* ================= Responsive ================= */

@media(max-width:992px){

.sidebar{

left:-270px;

}

.content{

margin-left:0;

width:100%;

padding:20px;

}

.topbar{

flex-direction:column;

gap:15px;

}

}

/* ================= Profile Hero ================= */

.profile-photo{

width:180px;

height:180px;

border-radius:50%;

object-fit:cover;

border:6px solid #dcecff;

box-shadow:0 8px 25px rgba(0,0,0,.15);

}

.info-box{

display:flex;

align-items:center;

gap:15px;

padding:15px;

background:#f8fbff;

border-radius:15px;

transition:.3s;

}

.info-box:hover{

transform:translateY(-5px);

box-shadow:0 8px 20px rgba(0,0,0,.08);

}

.info-box i{

font-size:28px;

color:#1565d8;

width:55px;

height:55px;

display:flex;

align-items:center;

justify-content:center;

background:#e8f2ff;

border-radius:50%;

}

.info-box h6{

margin:0;

font-weight:600;

}

.info-box small{

color:#777;

}

.form-label{

font-weight:600;

color:#0B2C74;

margin-bottom:8px;

}

.form-control{

height:50px;

border:1px solid #d8e5ff;

transition:.3s;

}

textarea.form-control{

height:auto;

resize:none;

}

.form-control:focus{

border-color:#1565d8;

box-shadow:0 0 0 .2rem rgba(21,101,216,.15);

}

.btn-lg{

padding:12px 35px;

border-radius:12px;

font-weight:600;

}

/* ================= Electricity Cards ================= */

.detail-card{

background:linear-gradient(135deg,#ffffff,#f4f8ff);

border-radius:18px;

padding:25px;

text-align:center;

transition:.35s;

border:1px solid #dce8ff;

height:100%;

}

.detail-card:hover{

transform:translateY(-8px);

box-shadow:0 15px 30px rgba(0,0,0,.12);

border-color:#1565d8;

}

.detail-card i{

font-size:42px;

color:#1565d8;

margin-bottom:15px;

display:block;

}

.detail-card small{

display:block;

color:#777;

font-size:14px;

margin-bottom:8px;

}

.detail-card h5{

font-weight:700;

color:#0B2C74;

margin:0;

word-break:break-word;

}

.dark{

background:#111827;

}

.dark .card-box{

background:#1f2937;

color:white;

}

.dark .topbar{

background:#1f2937;

color:white;

}

.dark .form-control{

background:#374151;

border-color:#4b5563;

color:white;

}

.dark .detail-card{

background:#2d3748;

color:white;

}

.dark .info-box{

background:#2d3748;

color:white;

}

</style>

</head>

<body>

<div class="layout">

    <!-- Sidebar -->

    <aside class="sidebar">

        <!-- Logo -->

        <div class="logo">

            <img src="../assets/images/logo-circle.png">

            <h3>APDCL</h3>

            <small>Consumer Portal</small>

        </div>

        <!-- Menu -->

        <ul class="menu">

            <li>
                <a href="dashboard.php">
                    <i class="bi bi-grid"></i>
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
                <a href="payment_history.php">
                    <i class="bi bi-credit-card"></i>
                    Payment History
                </a>
            </li>

            <li>
                <a href="complaint.php">
                    <i class="bi bi-tools"></i>
                    Complaints
                </a>
            </li>

            <li>
                <a href="track_complaint.php">
                    <i class="bi bi-geo-alt"></i>
                    Track Complaint
                </a>
            </li>

            <li>
                <a href="outage_map.php">
                    <i class="bi bi-lightning-charge"></i>
                    Outage Map
                </a>
            </li>

            <li>
                <a href="notice_board.php">
                    <i class="bi bi-megaphone"></i>
                    Notice Board
                </a>
            </li>

            <li class="active">

                <a href="profile.php">

                    <i class="bi bi-person-circle"></i>

                    Profile

                </a>

            </li>

            <li>

                <a href="logout.php">

                    <i class="bi bi-box-arrow-right"></i>

                    Logout

                </a>

            </li>

        </ul>

        <!-- Customer Care -->

        <div class="support">

            <i class="bi bi-headset"></i>

            <h5>Customer Care</h5>

            <h2>1912</h2>

            <small>24×7 Power Support</small>

        </div>

    </aside>

    <!-- Main -->

    <main class="content">

        <!-- Top Header -->

        <header class="topbar">

            <div>

                <h3>My Profile</h3>

            </div>

            <div class="top-right">

                <i class="bi bi-bell"></i>

                <i class="bi bi-moon"></i>

                <img src="<?= $profilePhoto ?>">

                <div>

                    <b><?= htmlspecialchars($user['name']); ?></b>

                    <small>Consumer</small>

                </div>

            </div>

        </header>
<!-- ================= PROFILE HERO ================= -->

<div class="card-box">

    <div class="row align-items-center">

        <!-- Profile Photo -->

        <div class="col-lg-3 text-center">

            <img src="<?= $profilePhoto ?>"
                 class="profile-photo">

        </div>

        <!-- Consumer Details -->

        <div class="col-lg-6">

            <span class="badge bg-success mb-3 px-3 py-2">
                Active Consumer
            </span>

            <h2 class="fw-bold text-primary">
                <?= htmlspecialchars($user['name']); ?>
            </h2>

            <p class="text-muted mb-4">
                Consumer No :
                <strong><?= $consumer_no ?></strong>
            </p>

            <div class="row">

                <div class="col-md-6 mb-3">

                    <div class="info-box">

                        <i class="bi bi-envelope-fill"></i>

                        <div>

                            <small>Email</small>

                            <h6><?= htmlspecialchars($user['email']); ?></h6>

                        </div>

                    </div>

                </div>

                <div class="col-md-6 mb-3">

                    <div class="info-box">

                        <i class="bi bi-telephone-fill"></i>

                        <div>

                            <small>Mobile</small>

                            <h6><?= htmlspecialchars($user['mobile']); ?></h6>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- Right Side -->

        <div class="col-lg-3 text-end">

            <button class="btn btn-primary">

                <i class="bi bi-pencil-square"></i>

                Edit Profile

            </button>

        </div>

    </div>

</div>
<!-- ================= PERSONAL INFORMATION ================= -->

<div class="card-box">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h4 class="section-title mb-0">
            <i class="bi bi-person-lines-fill"></i>
            Personal Information
        </h4>

        <span class="badge bg-primary px-3 py-2">
            Consumer Account
        </span>

    </div>

    <form action="" method="POST">

        <div class="row">

            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">
                    Full Name
                </label>

                <input
                    type="text"
                    class="form-control"
                    name="name"
                    value="<?= htmlspecialchars($user['name']); ?>">

            </div>

            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">
                    Consumer Number
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= $user['consumer_no']; ?>"
                    readonly>

            </div>

            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">
                    Email Address
                </label>

                <input
                    type="email"
                    class="form-control"
                    name="email"
                    value="<?= htmlspecialchars($user['email']); ?>">

            </div>

            <div class="col-md-6 mb-4">

                <label class="form-label fw-semibold">
                    Mobile Number
                </label>

                <input
                    type="text"
                    class="form-control"
                    name="mobile"
                    value="<?= htmlspecialchars($user['mobile']); ?>">

            </div>

            <div class="col-12">

                <label class="form-label fw-semibold">
                    Address
                </label>

                <textarea
                    class="form-control"
                    rows="4"
                    name="address"><?= htmlspecialchars($user['address']); ?></textarea>

            </div>

        </div>

        <div class="text-end mt-4">

            <button class="btn btn-primary btn-lg">

                <i class="bi bi-save"></i>

                Update Profile

            </button>

        </div>

    </form>

</div>
<!-- ================= ELECTRICITY DETAILS ================= -->

<div class="card-box">

    <h4 class="section-title">
        <i class="bi bi-lightning-charge-fill"></i>
        Electricity Connection Details
    </h4>

    <div class="row g-4">

        <div class="col-md-4">
            <div class="detail-card">
                <i class="bi bi-speedometer2"></i>
                <small>Meter Number</small>
                <h5><?= $user['meter_no'] ?? 'N/A'; ?></h5>
            </div>
        </div>

        <div class="col-md-4">
            <div class="detail-card">
                <i class="bi bi-house-fill"></i>
                <small>Category</small>
                <h5><?= $user['category'] ?? 'Domestic'; ?></h5>
            </div>
        </div>

        <div class="col-md-4">
            <div class="detail-card">
                <i class="bi bi-geo-fill"></i>
                <small>Zone</small>
                <h5><?= $user['zone'] ?? 'N/A'; ?></h5>
            </div>
        </div>

        <div class="col-md-4">
            <div class="detail-card">
                <i class="bi bi-diagram-3-fill"></i>
                <small>Circle</small>
                <h5><?= $user['circle'] ?? 'N/A'; ?></h5>
            </div>
        </div>

        <div class="col-md-4">
            <div class="detail-card">
                <i class="bi bi-building-fill"></i>
                <small>Sub Division</small>
                <h5><?= $user['sub_division'] ?? 'N/A'; ?></h5>
            </div>
        </div>

        <div class="col-md-4">
            <div class="detail-card">
                <i class="bi bi-lightning-fill"></i>
                <small>DTR Number</small>
                <h5><?= $user['dtr_no'] ?? 'N/A'; ?></h5>
            </div>
        </div>

    </div>

</div>
<!-- ================= CHANGE PASSWORD ================= -->

<div class="card-box">

    <h4 class="section-title">
        <i class="bi bi-shield-lock-fill"></i>
        Change Password
    </h4>

    <form>

        <div class="row">

            <div class="col-md-4 mb-3">

                <label class="form-label">Current Password</label>

                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bi bi-lock-fill"></i>
                    </span>

                    <input
                        type="password"
                        class="form-control"
                        placeholder="Current Password">

                </div>

            </div>

            <div class="col-md-4 mb-3">

                <label class="form-label">New Password</label>

                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bi bi-key-fill"></i>
                    </span>

                    <input
                        type="password"
                        class="form-control"
                        placeholder="New Password">

                </div>

            </div>

            <div class="col-md-4 mb-3">

                <label class="form-label">Confirm Password</label>

                <div class="input-group">

                    <span class="input-group-text">
                        <i class="bi bi-check-circle-fill"></i>
                    </span>

                    <input
                        type="password"
                        class="form-control"
                        placeholder="Confirm Password">

                </div>

            </div>

        </div>

        <div class="alert alert-info mt-3">

            <i class="bi bi-info-circle-fill"></i>

            Use at least 8 characters including uppercase, lowercase,
            numbers and symbols.

        </div>

        <div class="text-end">

            <button class="btn btn-primary">

                <i class="bi bi-shield-check"></i>

                Change Password

            </button>

        </div>

    </form>

</div>

<footer class="mt-5">

<div class="card-box">

<div class="row">

<div class="col-lg-4">

<img src="../assets/images/logo-circle.png"
style="width:70px">

<h4 class="mt-3">APDCL</h4>

<p>
Assam Power Distribution Company Limited
</p>

</div>

<div class="col-lg-4">

<h5>Quick Links</h5>

<p><a href="dashboard.php">Dashboard</a></p>

<p><a href="bill.php">My Bills</a></p>

<p><a href="payment_history.php">Payment History</a></p>

<p><a href="complaint.php">Complaints</a></p>

</div>

<div class="col-lg-4">

<h5>Customer Care</h5>

<p>📞 1912</p>

<p>✉ customercare@apdcl.org</p>

<p>🌐 www.apdcl.org</p>

</div>

</div>

<hr>

<div class="text-center">

    &copy; <?php echo date('Y'); ?>

    APDCL Consumer Portal | Internship Demo Project

</div>

</div>

</footer>

<script>

const moon = document.querySelector(".bi-moon");

if (moon) {
    moon.addEventListener("click", function () {
        document.body.classList.toggle("dark");
    });
}

</script>

</body>

</html>
