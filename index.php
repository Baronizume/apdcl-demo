<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>APDCL Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>

body{
    font-family:'Segoe UI',sans-serif;
    background:#f5f7fb;
}

/* ================= TOP BAR ================= */

.topbar{
    background:#004a99;
    color:#fff;
    padding:8px 0;
    font-size:14px;
}

/* ================= NAVBAR ================= */

.navbar{
    background:#fff;
    box-shadow:0 2px 15px rgba(0,0,0,.08);
}

.navbar-brand{
    font-size:30px;
    font-weight:700;
    color:#0056b3 !important;
}

.navbar-brand img{
    height:55px;
    margin-right:8px;
}

.nav-link{
    color:#333 !important;
    font-weight:600;
    margin-left:20px;
}

.nav-link:hover{
    color:#0056b3 !important;
}

/* ================= HERO ================= */

.hero{

    background:
    linear-gradient(rgba(0,35,70,.45),rgba(0,35,70,.45)),
    url("assets/images/hero.png");

    background-size:cover;
    background-position:center;
    background-repeat:no-repeat;

    min-height:90vh;

    display:flex;
    align-items:center;
    justify-content:center;

    text-align:center;

    color:#fff;

}

.hero h1{

    font-size:60px;
    font-weight:700;
    text-shadow:2px 2px 10px rgba(0,0,0,.6);

}

.hero .lead{

    font-size:26px;
    font-weight:500;
    text-shadow:2px 2px 8px rgba(0,0,0,.5);

}

.hero p{

    font-size:18px;
    text-shadow:1px 1px 6px rgba(0,0,0,.5);

}

.hero-buttons{

    margin-top:35px;

}

.hero-buttons .btn{

    border-radius:50px;
    padding:14px 35px;
    font-size:18px;
    font-weight:600;
    margin:10px;

}

.hero-buttons .btn-primary{

    background:#0056b3;
    border:none;

}

.hero-buttons .btn-warning{

    color:#fff;

}

.hero-buttons .btn:hover{

    transform:translateY(-4px);
    transition:.3s;
    box-shadow:0 10px 20px rgba(0,0,0,.35);

}

/* ================= MOBILE ================= */

@media(max-width:768px){

.hero h1{

font-size:38px;

}

.hero .lead{

font-size:20px;

}

.hero{

padding:80px 15px;

}

}

</style>

</head>

<body>

<!-- ================= TOP BAR ================= -->

<div class="topbar">

<div class="container d-flex justify-content-between">

<div>

<i class="bi bi-geo-alt-fill"></i>

Bijulee Bhawan, Paltan Bazar, Guwahati

</div>

<div>

<i class="bi bi-telephone-fill"></i>

Customer Care : 1912

</div>

</div>

</div>

<!-- ================= NAVBAR ================= -->

<nav class="navbar navbar-expand-lg">

<div class="container">

<a class="navbar-brand" href="index.php">

<img src="assets/images/logo-circle.png">

APDCL

</a>

<button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">

<span class="navbar-toggler-icon"></span>

</button>

<div class="collapse navbar-collapse" id="menu">

<ul class="navbar-nav ms-auto">

<li class="nav-item">

<a class="nav-link active" href="#">Home</a>

</li>

<li class="nav-item">

<a class="nav-link" href="about.php">About</a>

</li>

<li class="nav-item">

<a class="nav-link" href="services.php">Services</a>

</li>

<li class="nav-item">

<a class="nav-link" href="#contact">Contact</a>

</li>

</ul>

</div>

</div>

</nav>

<!-- ================= HERO ================= -->

<section class="hero">

<div class="container">

<h1>

Assam Power Distribution Company Limited

</h1>

<p class="lead">

Powering Assam with Reliable Electricity

</p>

<p>

Consumer Electricity Billing & Management System

</p>

<div class="hero-buttons">

<a href="consumer/login.php" class="btn btn-primary btn-lg">

<i class="bi bi-person-circle"></i>

Consumer Login

</a>

<a href="admin/login.php" class="btn btn-warning btn-lg">

<i class="bi bi-person-lock"></i>

Admin Login

</a>

</div>

</div>

</section>
<footer class="bg-dark text-white pt-5 pb-3">

<div class="container">

<div class="row">

<div class="col-md-4">

<h4 class="fw-bold">APDCL</h4>

<p>
Assam Power Distribution Company Limited
</p>

<p>
Powering Assam with Reliable Electricity.
</p>

</div>

<div class="col-md-4">

<h5>Quick Links</h5>

<ul class="list-unstyled">

<li><a href="#" class="text-white text-decoration-none">Home</a></li>

<li><a href="about.php" class="text-white text-decoration-none">About</a></li>

<li><a href="services.php" class="text-white text-decoration-none">Services</a></li>

<li><a href="#contact" class="text-white text-decoration-none">Contact</a></li>

</ul>

</div>

<div class="col-md-4">

<h5>Contact</h5>

<p><i class="bi bi-telephone"></i> 1912</p>

<p><i class="bi bi-envelope"></i> support@apdcl.org</p>

<p><i class="bi bi-geo-alt"></i> Guwahati, Assam</p>

</div>

</div>

<hr class="border-light">

<p class="text-center mb-0">

© <?php echo date('Y'); ?> APDCL Consumer Portal | Internship Demo Project

</p>

</div>

</footer>