<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>About APDCL | Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>

html{
    scroll-behavior:smooth;
}

body{
    font-family:'Segoe UI',sans-serif;
    background:#f5f8fc;
    margin:0;
    padding:0;
}

/* ================= TOP BAR ================= */

.topbar{

    background:#004a99;
    color:#fff;
    font-size:14px;
    padding:8px 0;

}

/* ================= NAVBAR ================= */

.navbar{

    background:#ffffff;

    padding:15px 0;

    box-shadow:0 5px 20px rgba(0,0,0,.08);

    position:sticky;

    top:0;

    z-index:1000;

}

.navbar-brand{

    display:flex;

    align-items:center;

    font-size:34px;

    font-weight:700;

    color:#0056b3 !important;

}

.navbar-brand img{

    width:55px;

    height:55px;

    margin-right:12px;

}

.nav-link{

    position:relative;

    font-size:17px;

    font-weight:600;

    color:#333 !important;

    margin-left:28px;

    transition:.3s;

}

.nav-link:hover{

    color:#0056b3 !important;

}

.nav-link.active{

    color:#0056b3 !important;

}

.nav-link::after{

    content:"";

    position:absolute;

    left:0;

    bottom:-5px;

    width:0;

    height:3px;

    background:#0056b3;

    transition:.3s;

}

.nav-link:hover::after,
.nav-link.active::after{

    width:100%;

}

/* ================= ABOUT BANNER ================= */

.about-banner{

    background:
    linear-gradient(rgba(0,45,90,.60),rgba(0,45,90,.60)),
    url("assets/images/about-apdcl.jpg");

    background-size:cover;

    background-position:center;

    background-repeat:no-repeat;

    height:500px;

    display:flex;

    align-items:center;

    justify-content:center;

    text-align:center;

    color:#fff;

}

.about-banner h1{

    font-size:70px;

    font-weight:700;

    text-shadow:0 5px 12px rgba(0,0,0,.45);

}

.about-banner p{

    font-size:24px;

    max-width:850px;

    margin:auto;

    line-height:1.6;

    text-shadow:0 2px 8px rgba(0,0,0,.45);

}

/* ================= MOBILE ================= */

@media(max-width:768px){

.about-banner{

height:350px;

}

.about-banner h1{

font-size:42px;

}

.about-banner p{

font-size:18px;

padding:0 20px;

}

.navbar-brand{

font-size:28px;

}

.navbar-brand img{

width:45px;

height:45px;

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

<img src="assets/images/logo-circle.png" alt="APDCL Logo">

APDCL

</a>

<button class="navbar-toggler"

type="button"

data-bs-toggle="collapse"

data-bs-target="#menu">

<span class="navbar-toggler-icon"></span>

</button>

<div class="collapse navbar-collapse" id="menu">

<ul class="navbar-nav ms-auto">

<li class="nav-item">

<a class="nav-link" href="index.php">

Home

</a>

</li>

<li class="nav-item">

<a class="nav-link active" href="about.php">

About

</a>

</li>

<li class="nav-item">

<a class="nav-link" href="services.php">

Services

</a>

</li>

<li class="nav-item">

<a class="nav-link" href="contact.php">

Contact

</a>

</li>

</ul>

</div>

</div>

</nav>

<!-- ================= ABOUT BANNER ================= -->

<section class="about-banner">

<div class="container">

<h1>

About APDCL

</h1>

<p>

Powering Assam with Reliable, Efficient & Consumer-Friendly Electricity Services

</p>

</div>

</section>
<!-- ================= WHO WE ARE ================= -->

<section class="py-5 bg-white">

<div class="container">

<div class="row align-items-center g-5">

<div class="col-lg-6">

<img src="assets/images/about-office.jpeg"
class="img-fluid rounded-4 shadow-lg"
alt="APDCL Office">

</div>

<div class="col-lg-6">

<h2 class="fw-bold text-primary mb-4">

Who We Are

</h2>

<p class="text-muted fs-5">

Assam Power Distribution Company Limited (APDCL) is the state-owned electricity distribution utility responsible for delivering reliable and affordable electricity across Assam. APDCL continuously modernizes its network and digital services to provide better customer experiences.

</p>

<p class="text-muted">

This Consumer Portal demonstrates services such as electricity bill viewing, online bill payment, complaint registration, outage information, and consumer profile management.

</p>

<div class="row mt-4">

<div class="col-6 mb-3">

<div class="feature-box">

<i class="bi bi-lightning-charge-fill text-warning"></i>

<h6>Reliable Supply</h6>

<p>Continuous power distribution.</p>

</div>

</div>

<div class="col-6 mb-3">

<div class="feature-box">

<i class="bi bi-shield-check text-success"></i>

<h6>Safe Network</h6>

<p>Consumer safety comes first.</p>

</div>

</div>

<div class="col-6">

<div class="feature-box">

<i class="bi bi-cpu-fill text-primary"></i>

<h6>Digital Services</h6>

<p>Modern online consumer portal.</p>

</div>

</div>

<div class="col-6">

<div class="feature-box">

<i class="bi bi-headset text-danger"></i>

<h6>24×7 Support</h6>

<p>Dedicated customer care.</p>

</div>

</div>

</div>

</div>

</div>

</div>

</section>

<!-- ================= VISION & MISSION ================= -->

<section class="py-5 bg-light">

<div class="container">

<div class="text-center mb-5">

<h2 class="fw-bold text-primary">

Vision & Mission

</h2>

<p class="text-muted">

Building a brighter and energy-efficient Assam.

</p>

</div>

<div class="row g-4">

<div class="col-lg-6">

<div class="card border-0 shadow-lg h-100">

<div class="card-body p-5 text-center">

<div class="icon-circle bg-primary text-white">

<i class="bi bi-eye-fill"></i>

</div>

<h3 class="mt-4">

Our Vision

</h3>

<p class="text-muted mt-3">

To become one of India's leading electricity distribution companies by delivering reliable, sustainable, and consumer-centric power services.

</p>

</div>

</div>

</div>

<div class="col-lg-6">

<div class="card border-0 shadow-lg h-100">

<div class="card-body p-5 text-center">

<div class="icon-circle bg-success text-white">

<i class="bi bi-bullseye"></i>

</div>

<h3 class="mt-4">

Our Mission

</h3>

<p class="text-muted mt-3">

To provide safe, quality, and uninterrupted electricity while embracing innovation, transparency, and consumer satisfaction.

</p>

</div>

</div>

</div>

</div>

</div>

</section>

<!-- ================= CORE VALUES ================= -->

<section class="py-5">

<div class="container">

<div class="text-center mb-5">

<h2 class="fw-bold text-primary">

Our Core Values

</h2>

<p class="text-muted">

The values that drive APDCL's commitment to consumers.

</p>

</div>

<div class="row g-4">

<div class="col-md-3">

<div class="value-card">

<i class="bi bi-lightning-charge-fill text-warning"></i>

<h5>Reliability</h5>

<p>Ensuring uninterrupted power supply.</p>

</div>

</div>

<div class="col-md-3">

<div class="value-card">

<i class="bi bi-tree-fill text-success"></i>

<h5>Sustainability</h5>

<p>Supporting efficient energy usage.</p>

</div>

</div>

<div class="col-md-3">

<div class="value-card">

<i class="bi bi-people-fill text-primary"></i>

<h5>Transparency</h5>

<p>Building trust through openness.</p>

</div>

</div>

<div class="col-md-3">

<div class="value-card">

<i class="bi bi-cpu-fill text-danger"></i>

<h5>Innovation</h5>

<p>Using technology to improve services.</p>

</div>

</div>

</div>

</div>

</section>