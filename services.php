<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Services | APDCL Consumer Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>

html{
    scroll-behavior:smooth;
}

body{
    font-family:'Segoe UI',sans-serif;
    background:#f5f8fc;
}

/*================ TOP BAR ================*/

.topbar{

background:#004a99;
color:#fff;
font-size:14px;
padding:8px 0;

}

/*================ NAVBAR ================*/

.navbar{

background:#fff;
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
color:#0056b3!important;

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
margin-left:25px;
color:#333!important;
transition:.3s;

}

.nav-link:hover,
.nav-link.active{

color:#0056b3!important;

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

/*================ HERO ================*/

.services-banner{

background:
linear-gradient(rgba(0,45,90,.65),rgba(0,45,90,.65)),
url("assets/images/our-services.jpg");

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

.services-banner h1{

font-size:68px;
font-weight:700;
text-shadow:0 5px 12px rgba(0,0,0,.4);

}

.services-banner p{

font-size:24px;
max-width:850px;
margin:auto;
line-height:1.7;
text-shadow:0 2px 8px rgba(0,0,0,.4);

}

/*================ INTRO ================*/

.intro{

padding:80px 0;

}

.intro h2{

font-weight:700;
color:#0056b3;

}

.intro p{

font-size:18px;
line-height:1.8;
color:#555;

}

/*================ MOBILE ================*/

@media(max-width:768px){

.services-banner{

height:350px;

}

.services-banner h1{

font-size:42px;

}

.services-banner p{

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

.service-card{

background:#fff;

padding:35px;

border-radius:20px;

box-shadow:0 10px 25px rgba(0,0,0,.08);

text-align:center;

transition:.35s;

height:100%;

}

.service-card:hover{

transform:translateY(-10px);

box-shadow:0 20px 35px rgba(0,0,0,.15);

}

.service-card i{

font-size:55px;

margin-bottom:20px;

}

.process-box{

padding:30px;

}

.process-number{

width:70px;

height:70px;

background:#0056b3;

color:#fff;

font-size:28px;

font-weight:bold;

border-radius:50%;

display:flex;

align-items:center;

justify-content:center;

margin:auto auto 20px;

}

</style>

</head>

<body>

<!--================ TOP BAR ================-->

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

<!--================ NAVBAR ================-->

<nav class="navbar navbar-expand-lg">

<div class="container">

<a class="navbar-brand" href="index.php">

<img src="assets/images/logo-circle.png" alt="Logo">

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
<a class="nav-link" href="index.php">Home</a>
</li>

<li class="nav-item">
<a class="nav-link" href="about.php">About</a>
</li>

<li class="nav-item">
<a class="nav-link active" href="services.php">Services</a>
</li>

<li class="nav-item">
<a class="nav-link" href="contact.php">Contact</a>
</li>

</ul>

</div>

</div>

</nav>

<!--================ HERO ================-->

<section class="services-banner">

<div class="container">

<h1>Our Services</h1>

<p>

Explore APDCL's digital consumer services designed to make electricity management simple, secure, and convenient.

</p>

</div>

</section>

<!--================ INTRO ================-->

<section class="intro">

<div class="container">

<div class="row align-items-center">

<div class="col-lg-6">

<h2>Consumer Services</h2>

<p>

APDCL provides a wide range of online services that allow consumers to manage their electricity accounts efficiently. From viewing and paying bills to registering complaints and checking outage information, our digital platform ensures quick, transparent, and reliable service.

</p>

<p>

This demo portal showcases the essential services available to consumers through a modern and user-friendly interface.

</p>

</div>

<div class="col-lg-6 text-center">

<img src="assets/images/services-intro.jpg"

class="img-fluid rounded-4 shadow-lg"

alt="Consumer Services">

</div>

</div>

</div>

</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
<!-- ================= OUR SERVICES ================= -->

<section class="py-5 bg-light">

<div class="container">

<div class="text-center mb-5">

<h2 class="fw-bold text-primary">
Our Digital Services
</h2>

<p class="text-muted">
Fast, secure and convenient services for every consumer.
</p>

</div>

<div class="row g-4">

<div class="col-lg-4 col-md-6">

<div class="service-card">

<i class="bi bi-receipt-cutoff text-primary"></i>

<h4>View Bills</h4>

<p>
View current and previous electricity bills online anytime.
</p>

</div>

</div>

<div class="col-lg-4 col-md-6">

<div class="service-card">

<i class="bi bi-credit-card-fill text-success"></i>

<h4>Pay Bills</h4>

<p>
Secure online payment using multiple payment methods.
</p>

</div>

</div>

<div class="col-lg-4 col-md-6">

<div class="service-card">

<i class="bi bi-download text-info"></i>

<h4>Download Bills</h4>

<p>
Download electricity bills instantly in PDF format.
</p>

</div>

</div>

<div class="col-lg-4 col-md-6">

<div class="service-card">

<i class="bi bi-exclamation-triangle-fill text-danger"></i>

<h4>Register Complaint</h4>

<p>
Submit complaints and track their status online.
</p>

</div>

</div>

<div class="col-lg-4 col-md-6">

<div class="service-card">

<i class="bi bi-lightning-fill text-warning"></i>

<h4>Power Outages</h4>

<p>
Stay informed about planned and emergency outages.
</p>

</div>

</div>

<div class="col-lg-4 col-md-6">

<div class="service-card">

<i class="bi bi-person-circle text-secondary"></i>

<h4>Consumer Profile</h4>

<p>
Manage your personal information securely.
</p>

</div>

</div>

</div>

</div>

</section>

<!-- ================= SERVICE PROCESS ================= -->

<section class="py-5">

<div class="container">

<div class="text-center mb-5">

<h2 class="fw-bold text-primary">
How Our Services Work
</h2>

</div>

<div class="row text-center">

<div class="col-md-3">

<div class="process-box">

<div class="process-number">1</div>

<h5>Login</h5>

<p>Access your consumer account securely.</p>

</div>

</div>

<div class="col-md-3">

<div class="process-box">

<div class="process-number">2</div>

<h5>Select Service</h5>

<p>Choose bill payment, complaints or any service.</p>

</div>

</div>

<div class="col-md-3">

<div class="process-box">

<div class="process-number">3</div>

<h5>Complete Request</h5>

<p>Submit information quickly and securely.</p>

</div>

</div>

<div class="col-md-3">

<div class="process-box">

<div class="process-number">4</div>

<h5>Track Status</h5>

<p>Monitor your request in real time.</p>

</div>

</div>

</div>

</div>

</section>

<!-- ================= WHY CHOOSE APDCL ================= -->

<section class="py-5 bg-light">

<div class="container">

<div class="row align-items-center">

<div class="col-lg-6">

<img src="assets/images/services-benefits.jpg"

class="img-fluid rounded-4 shadow-lg"

alt="Benefits">

</div>

<div class="col-lg-6">

<h2 class="fw-bold text-primary mb-4">

Why Choose APDCL Services?

</h2>

<ul class="list-group list-group-flush">

<li class="list-group-item">✔ Quick & Secure Online Access</li>

<li class="list-group-item">✔ Transparent Billing</li>

<li class="list-group-item">✔ Easy Complaint Registration</li>

<li class="list-group-item">✔ 24×7 Consumer Support</li>

<li class="list-group-item">✔ Reliable Electricity Distribution</li>

<li class="list-group-item">✔ Modern Digital Services</li>

</ul>

</div>

</div>

</div>

</section>

<!-- ================= FAQ ================= -->

<section class="py-5">

<div class="container">

<div class="text-center mb-5">

<h2 class="fw-bold text-primary">

Frequently Asked Questions

</h2>

<p class="text-muted">

Find answers to common consumer queries.

</p>

</div>

<div class="accordion" id="faqAccordion">

<div class="accordion-item">

<h2 class="accordion-header">

<button class="accordion-button" type="button"
data-bs-toggle="collapse"
data-bs-target="#faq1">

How can I view my electricity bill?

</button>

</h2>

<div id="faq1"
class="accordion-collapse collapse show"
data-bs-parent="#faqAccordion">

<div class="accordion-body">

Log in to your consumer account and open the <strong>View Bills</strong> section to see your current and previous bills.

</div>

</div>

</div>

<div class="accordion-item">

<h2 class="accordion-header">

<button class="accordion-button collapsed"
type="button"
data-bs-toggle="collapse"
data-bs-target="#faq2">

How do I pay my electricity bill online?

</button>

</h2>

<div id="faq2"
class="accordion-collapse collapse"
data-bs-parent="#faqAccordion">

<div class="accordion-body">

Go to the <strong>Pay Bill</strong> page, choose your preferred payment method, and complete the transaction securely.

</div>

</div>

</div>

<div class="accordion-item">

<h2 class="accordion-header">

<button class="accordion-button collapsed"
type="button"
data-bs-toggle="collapse"
data-bs-target="#faq3">

How can I register a complaint?

</button>

</h2>

<div id="faq3"
class="accordion-collapse collapse"
data-bs-parent="#faqAccordion">

<div class="accordion-body">

Use the <strong>Complaint</strong> section to submit your issue and track its status.

</div>

</div>

</div>

<div class="accordion-item">

<h2 class="accordion-header">

<button class="accordion-button collapsed"
type="button"
data-bs-toggle="collapse"
data-bs-target="#faq4">

Can I download my electricity bill?

</button>

</h2>

<div id="faq4"
class="accordion-collapse collapse"
data-bs-parent="#faqAccordion">

<div class="accordion-body">

Yes. Bills can be downloaded as PDF files from the <strong>Download Bill</strong> section.

</div>

</div>

</div>

</div>

</div>

</section>

<!-- ================= CONTACT ================= -->

<section class="py-5 bg-light">

<div class="container">

<div class="row">

<div class="col-lg-6">

<h3 class="text-primary fw-bold">

Contact Information

</h3>

<p class="mt-4">

<i class="bi bi-geo-alt-fill text-primary"></i>

Bijulee Bhawan, Paltan Bazar, Guwahati, Assam - 781001

</p>

<p>

<i class="bi bi-telephone-fill text-success"></i>

Customer Care : <strong>1912</strong>

</p>

<p>

<i class="bi bi-envelope-fill text-danger"></i>

support@apdcl.org

</p>

<p>

<i class="bi bi-globe text-info"></i>

www.apdcl.org

</p>

</div>

<div class="col-lg-6 text-center">

<img src="assets/images/contact-supports.jpg"

class="img-fluid"

style="max-height:320px;"

alt="Customer Support">

</div>

</div>

</div>

</section>

<!-- ================= FOOTER ================= -->

<footer class="bg-dark text-white pt-5 pb-3">

<div class="container">

<div class="row">

<div class="col-md-4">

<h4 class="text-warning">

APDCL

</h4>

<p>

Assam Power Distribution Company Limited

</p>

<p>

Providing reliable electricity services across Assam.

</p>

</div>

<div class="col-md-4">

<h5 class="text-warning">

Quick Links

</h5>

<ul class="list-unstyled">

<li><a href="index.php" class="text-white text-decoration-none">Home</a></li>

<li><a href="about.php" class="text-white text-decoration-none">About</a></li>

<li><a href="services.php" class="text-white text-decoration-none">Services</a></li>

<li><a href="contact.php" class="text-white text-decoration-none">Contact</a></li>

</ul>

</div>

<div class="col-md-4">

<h5 class="text-warning">

Customer Care

</h5>

<p>☎ 1912</p>

<p>✉ support@apdcl.org</p>

<p>📍 Guwahati, Assam</p>

</div>

</div>

<hr class="border-secondary">

<p class="text-center mb-0">

© <?php echo date("Y"); ?> APDCL Consumer Portal | Internship Demo Project

</p>

</div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>