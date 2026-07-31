<?php
session_start();
include("../db.php");

/*=============================
 ADMIN NOTIFICATIONS
=============================*/

$adminNotificationQuery=mysqli_query($conn,"
SELECT *
FROM admin_notifications
ORDER BY id DESC
LIMIT 5
");


if(!$adminNotificationQuery){
    die("Notification Error: ".mysqli_error($conn));
}



$adminCountQuery=mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM admin_notifications
WHERE status='Unread'
");


if(!$adminCountQuery){
    die("Count Error: ".mysqli_error($conn));
}


$adminUnread=mysqli_fetch_assoc($adminCountQuery)['total'];

/*=========================================
LOGIN CHECK
=========================================*/

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/*=========================================
GET ADMIN DETAILS
=========================================*/

$username = $_SESSION['admin'];

$stmt = mysqli_prepare($conn,"
SELECT *
FROM admin
WHERE username=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

/*=========================================
GET ADMIN SUB DIVISION
=========================================*/

$subDivision = $admin['sub_division'] ?? '';

mysqli_stmt_close($stmt);

if(!$admin){
    session_destroy();
    header("Location: login.php");
    exit();
}

/*=========================================
LOAD SETTINGS
=========================================*/

$settings = [];

$q = mysqli_query($conn,"
SELECT *
FROM settings
LIMIT 1
");

if($q && mysqli_num_rows($q)>0){
    $settings = mysqli_fetch_assoc($q);
}

/*=========================================
COMPANY DETAILS
=========================================*/

$companyName = $settings['company_name'] ?? "APDCL";
$logo = "../assets/images/".$settings['logo'];

/*=========================================
SUB DIVISION DASHBOARD COUNTS
=========================================*/

// Consumers
$q = mysqli_query($conn,"
SELECT COUNT(*) total
FROM users
WHERE sub_division='".mysqli_real_escape_string($conn,$subDivision)."'
");
$totalConsumers = mysqli_fetch_assoc($q)['total'];

// Bills
$q = mysqli_query($conn,"
SELECT COUNT(*) total
FROM bills b
INNER JOIN users u
ON b.consumer_no=u.consumer_no
WHERE u.sub_division='".mysqli_real_escape_string($conn,$subDivision)."'
");
$totalBills = mysqli_fetch_assoc($q)['total'];

// Payments
$q = mysqli_query($conn,"
SELECT COUNT(*) total
FROM payments p
INNER JOIN users u
ON p.consumer_no=u.consumer_no
WHERE u.sub_division='".mysqli_real_escape_string($conn,$subDivision)."'
");
$totalPayments = mysqli_fetch_assoc($q)['total'];

// Complaints
$q = mysqli_query($conn,"
SELECT COUNT(*) total
FROM complaint c
INNER JOIN users u
ON c.consumer_no=u.consumer_no
WHERE u.sub_division='".mysqli_real_escape_string($conn,$subDivision)."'
");
$totalComplaints = mysqli_fetch_assoc($q)['total'];

// Outages
$q = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM outages
WHERE sub_division='".mysqli_real_escape_string($conn,$subDivision)."'
AND status='Pending'
");
$totalOutages = mysqli_fetch_assoc($q)['total'];

/*=========================================
TOTAL REVENUE
=========================================*/

$totalRevenue = 0;

$q = mysqli_query($conn,"
SELECT SUM(b.total_bill) total
FROM bills b
INNER JOIN users u
ON b.consumer_no=u.consumer_no
WHERE b.status='Paid'
AND u.sub_division='".mysqli_real_escape_string($conn,$subDivision)."'
");

if($q){
    $r = mysqli_fetch_assoc($q);
    $totalRevenue = $r['total'] ?? 0;
}

/*=========================================
COMPLAINT STATUS COUNTS
=========================================*/

$q = mysqli_query($conn,"
SELECT c.status,
COUNT(*) total
FROM complaint c
INNER JOIN users u
ON c.consumer_no=u.consumer_no
WHERE u.sub_division='".mysqli_real_escape_string($conn,$subDivision)."'
GROUP BY c.status
");

$pending = 0;
$assigned = 0;
$inprogress = 0;
$resolved = 0;
$rejected = 0;

while($r = mysqli_fetch_assoc($q)){

    switch($r['status']){

        case "Pending":
            $pending = $r['total'];
            break;

        case "Assigned":
            $assigned = $r['total'];
            break;

        case "In Progress":
            $inprogress = $r['total'];
            break;

        case "Resolved":
            $resolved = $r['total'];
            break;

        case "Rejected":
            $rejected = $r['total'];
            break;
    }

}

$pageTitle = "APDCL Dashboard";
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1">

<title><?= $pageTitle ?></title>

<!-- Bootstrap -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<!-- Bootstrap Icons -->

<link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Google Font -->

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
      rel="stylesheet">

<!-- ChartJS -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Leaflet -->

<link rel="stylesheet"
      href="https://unpkg.com/leaflet/dist/leaflet.css">

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<style>

:root{

    --primary:#0056b3;
    --secondary:#1976d2;
    --yellow:#ffc107;

    --bg:#eef4fb;
    --card:#ffffff;
    --text:#222;
    --sidebar:#003c8f;
    --navbar:#0056b3;

    --shadow:0 10px 25px rgba(0,0,0,.12);

}

/*==============================
DARK MODE
==============================*/

body.dark{

    --bg:#0f172a;
    --card:#1e293b;
    --text:#f5f5f5;
    --sidebar:#111827;
    --navbar:#0f172a;

}

*{

    margin:0;
    padding:0;
    box-sizing:border-box;

}

body{

    background:var(--bg);
    color:var(--text);
    font-family:'Poppins',sans-serif;
    transition:.3s;
    overflow-x:hidden;

}

/*==============================
NAVBAR
==============================*/

.navbar{

    height:75px;
    background:linear-gradient(90deg,#004aad,#1976d2);
    box-shadow:0 8px 20px rgba(0,0,0,.18);

    position:fixed;
    top:0;
    left:0;
    right:0;

    z-index:1000;

}

.logo{

    width:55px;
    height:55px;

    border-radius:50%;
    background:#fff;
    padding:5px;

    box-shadow:0 5px 15px rgba(0,0,0,.25);

}

.brand{

    color:#fff;
    font-size:23px;
    font-weight:700;
    letter-spacing:.5px;

}

.subtitle{

    color:#dbe9ff;
    font-size:12px;

}

/*==============================
SIDEBAR
==============================*/

.sidebar{

    position:fixed;

    top:75px;
    left:0;

    width:260px;
    height:calc(100vh - 75px);

    background:var(--sidebar);

    overflow-y:auto;

    transition:.3s;

}

.sidebar::-webkit-scrollbar{

    width:6px;

}

.sidebar::-webkit-scrollbar-thumb{

    background:#ffc107;
    border-radius:20px;

}

.sidebar a{

    display:flex;
    align-items:center;
    gap:14px;

    padding:15px 22px;

    color:#fff;
    text-decoration:none;

    border-bottom:1px solid rgba(255,255,255,.08);

    transition:.3s;

}

.sidebar a:hover{

    background:rgba(255,255,255,.08);
    padding-left:30px;

}

.sidebar a.active{

    background:#fff;
    color:#003c8f;
    font-weight:600;

    border-left:5px solid #ffc107;

}

.sidebar i{

    width:25px;
    font-size:20px;
    color:#ffc107;

}

/*==============================
CONTENT
==============================*/

.content{

    margin-left:260px;
    margin-top:90px;

    padding:30px;

    transition:.3s;

}

/*==============================
CARDS
==============================*/

.dashboard-card{

    background:var(--card);

    border:none;
    border-radius:18px;

    box-shadow:var(--shadow);

    transition:.35s;

}

.dashboard-card:hover{

    transform:translateY(-5px);

}

.icon-box{

    width:65px;
    height:65px;

    border-radius:18px;

    display:flex;
    justify-content:center;
    align-items:center;

    color:#fff;
    font-size:28px;

}

/*==============================
GRADIENT ICON BACKGROUNDS
==============================*/

.bg-primary2{
background:linear-gradient(135deg,#1976d2,#003c8f);
}

.bg-success2{
background:linear-gradient(135deg,#28a745,#20c997);
}

.bg-warning2{
background:linear-gradient(135deg,#ff9800,#ffc107);
}

.bg-danger2{
background:linear-gradient(135deg,#e53935,#ff5252);
}

.bg-info2{
background:linear-gradient(135deg,#00bcd4,#2196f3);
}

.bg-dark2{
background:linear-gradient(135deg,#424242,#000);
}

/*==============================
RESPONSIVE
==============================*/

@media(max-width:992px){

.sidebar{

width:80px;

}

.sidebar span{

display:none;

}

.content{

margin-left:80px;

}

}

@media(max-width:768px){

.sidebar{

display:none;

}

.content{

margin-left:0;
padding:15px;

}

}

</style>

</head>

<body>

<!-- ===========================
TOP NAVBAR
=========================== -->

<nav class="navbar navbar-expand-lg">

<div class="container-fluid px-4">

    <!-- Logo -->

    <div class="d-flex align-items-center">

        <img src="<?= $logo ?>"
             class="logo me-3"
             alt="Logo">

        <div>

            <h4 class="brand mb-0">
                <?= htmlspecialchars($companyName) ?>
            </h4>

            <small class="subtitle">
                Electricity Billing Management System
            </small>

        </div>

    </div>

    <!-- Right -->

    <div class="d-flex align-items-center gap-3 ms-auto">

        <div class="text-end me-2">

            <div id="liveDate"
                 class="fw-semibold text-white"></div>

            <small id="liveTime"
                   style="color:#d9e8ff;"></small>

        </div>

        <!-- Dark Mode -->

        <button id="themeToggle"
                class="btn btn-light rounded-circle"
                style="width:45px;height:45px;">

            <i class="bi bi-moon-stars-fill"></i>

        </button>

        <!-- ADMIN NOTIFICATION -->


            <div class="dropdown">


            <a href="#"
            class="btn btn-light rounded-circle position-relative me-3"
            data-bs-toggle="dropdown">


            <i class="bi bi-bell-fill fs-5"></i>



            <?php if($adminUnread>0){ ?>

            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

            <?= $adminUnread ?>

            </span>

            <?php } ?>


            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow"
            style="width:320px">


            <li class="dropdown-header fw-bold">

            Admin Notifications

            </li>

            <?php while($note=mysqli_fetch_assoc($adminNotificationQuery)){ ?>


            <li>

            <a class="dropdown-item">


            <strong>

            <?= htmlspecialchars($note['title']); ?>

            </strong>


            <br>


            <small>

            <?= htmlspecialchars($note['message']); ?>

            </small>


            <br>


            <small class="text-muted">

            <?= date(
            "d M Y h:i A",
            strtotime($note['created_at'])
            ); ?>

            </small>


            </a>

            </li>


            <?php } ?>


            </ul>


            </div>

        <!-- Profile -->

        <div class="dropdown">

            <a href="#"
               class="dropdown-toggle text-white text-decoration-none fw-semibold d-flex align-items-center"
               data-bs-toggle="dropdown">

                <div class="rounded-circle bg-white text-primary d-flex justify-content-center align-items-center me-2"
                     style="width:42px;height:42px;">

                    <i class="bi bi-person-fill fs-5"></i>

                </div>

                <?= htmlspecialchars($admin['name']) ?>

            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow">

                <li>
                    <h6 class="dropdown-header">
                        <?= htmlspecialchars($admin['role']) ?>
                    </h6>
                </li>

                <li>
                    <a class="dropdown-item" href="profile.php">
                        <i class="bi bi-person"></i>
                        My Profile
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="change_password.php">
                        <i class="bi bi-lock"></i>
                        Change Password
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="settings.php">
                        <i class="bi bi-gear"></i>
                        Settings
                    </a>
                </li>

                <li><hr></li>

                <li>
                    <a class="dropdown-item text-danger"
                       href="logout.php">

                        <i class="bi bi-box-arrow-right"></i>

                        Logout

                    </a>
                </li>

            </ul>

        </div>

    </div>

</div>

</nav>

<!-- ===========================
SIDEBAR
=========================== -->

<div class="sidebar">

    <div class="text-center py-4">

        <img src="<?= $logo ?>"
             style="width:90px;height:90px;border-radius:50%;background:#fff;padding:6px;">

        <h5 class="text-white mt-3">
            <?= htmlspecialchars($companyName) ?>
        </h5>

        <small class="text-light">
            <?= htmlspecialchars($admin['role']) ?>
        </small>

    </div>

    <a href="notifications.php">

        <i class="bi bi-bell-fill"></i>

        Notifications

        <?php if($adminUnread > 0){ ?>

        <span class="badge bg-danger ms-2">
        <?= $adminUnread ?>
        </span>

        <?php } ?>

    </a>
    
    <a href="dashboard.php" class="active">
        <i class="bi bi-grid-fill"></i>
        <span>Dashboard</span>
    </a>

    <a href="manage_consumers.php">
        <i class="bi bi-people-fill"></i>
        <span>Consumers</span>
    </a>

    <a href="meter_reading.php">
        <i class="bi bi-speedometer2"></i>
        <span>Meter Reading</span>
    </a>

    <a href="generate_bill.php">
        <i class="bi bi-lightning-charge-fill"></i>
        <span>Generate Bill</span>
    </a>

    <a href="manage_bills.php">
        <i class="bi bi-file-earmark-text-fill"></i>
        <span>Manage Bills</span>
    </a>

    <a href="manage_payments.php">
        <i class="bi bi-credit-card-fill"></i>
        <span>Payments</span>
    </a>

    <a href="manage_complaint.php">
        <i class="bi bi-chat-left-text-fill"></i>
        <span>Complaints</span>
    </a>

    <a href="manage_outages.php">
        <i class="bi bi-geo-alt-fill"></i>
        <span>Outages</span>
    </a>

    <a href="manage_notices.php">
        <i class="bi bi-megaphone-fill"></i>
        <span>Notices</span>
    </a>

    <a href="reports.php">
        <i class="bi bi-bar-chart-fill"></i>
        <span>Reports</span>
    </a>

    <a href="settings.php">
        <i class="bi bi-gear-fill"></i>
        <span>Settings</span>
    </a>

    <a href="manage_admins.php">
        <i class="bi bi-person-badge-fill"></i>
        <span>Manage Admins</span>
    </a>

    <a href="logout.php">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
    </a>

</div>

<!-- ===========================
MAIN CONTENT
=========================== -->

<div class="content">
<!-- ==========================================
WELCOME SECTION
========================================== -->

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold">
            Welcome,
            <?= htmlspecialchars($admin['name']) ?>
        </h2>

        <p class="text-muted mb-0">
            APDCL Smart Electricity Billing Management Dashboard
        </p>

    </div>

    <div class="text-end">

        <h5 id="liveDate"></h5>

        <small id="liveTime"></small>

    </div>

</div>


<!-- ==========================================
STATISTICS CARDS
========================================== -->

<div class="row g-4 mb-4">

    <!-- Consumers -->

    <div class="col-xl-4 col-md-6">

        <a href="manage_consumers.php" class="text-decoration-none">

            <div class="dashboard-card card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted">
                                Total Consumers
                            </small>

                            <h2 class="fw-bold counter text-primary"
                                data-target="<?= $totalConsumers ?>">
                                0
                            </h2>

                            <span class="badge bg-primary">
                                Registered
                            </span>

                        </div>

                        <div class="icon-box bg-primary2">

                            <i class="bi bi-people-fill"></i>

                        </div>

                    </div>

                </div>

            </div>

        </a>

    </div>

    <!-- Bills -->

    <div class="col-xl-4 col-md-6">

        <a href="manage_bills.php" class="text-decoration-none">

            <div class="dashboard-card card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Bills Generated
                            </small>

                            <h2 class="fw-bold counter text-success"
                                data-target="<?= $totalBills ?>">
                                0
                            </h2>

                            <span class="badge bg-success">
                                Monthly Bills
                            </span>

                        </div>

                        <div class="icon-box bg-success2">

                            <i class="bi bi-file-earmark-text-fill"></i>

                        </div>

                    </div>

                </div>

            </div>

        </a>

    </div>

    <!-- Payments -->

    <div class="col-xl-4 col-md-6">

        <a href="manage_payments.php" class="text-decoration-none">

            <div class="dashboard-card card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Successful Payments
                            </small>

                            <h2 class="fw-bold counter text-info"
                                data-target="<?= $totalPayments ?>">
                                0
                            </h2>

                            <span class="badge bg-info">
                                Paid
                            </span>

                        </div>

                        <div class="icon-box bg-info2">

                            <i class="bi bi-credit-card-fill"></i>

                        </div>

                    </div>

                </div>

            </div>

        </a>

    </div>

    <!-- Revenue -->

    <div class="col-xl-4 col-md-6">

        <a href="reports.php" class="text-decoration-none">

            <div class="dashboard-card card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Total Revenue
                            </small>

                            <h3 class="fw-bold text-danger">
                                ₹<?= number_format($totalRevenue,2) ?>
                            </h3>

                            <span class="badge bg-danger">
                                Paid Bills
                            </span>

                        </div>

                        <div class="icon-box bg-danger2">

                            <i class="bi bi-cash-stack"></i>

                        </div>

                    </div>

                </div>

            </div>

        </a>

    </div>

    <!-- Complaints -->

    <div class="col-xl-4 col-md-6">

        <a href="manage_complaint.php" class="text-decoration-none">

            <div class="dashboard-card card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Complaints
                            </small>

                            <h2 class="fw-bold counter text-warning"
                                data-target="<?= $totalComplaints ?>">
                                0
                            </h2>

                            <span class="badge bg-warning text-dark">
                                Consumer Issues
                            </span>

                        </div>

                        <div class="icon-box bg-warning2">

                            <i class="bi bi-chat-left-text-fill"></i>

                        </div>

                    </div>

                </div>

            </div>

        </a>

    </div>

    <!-- Outages -->

    <div class="col-xl-4 col-md-6">

        <a href="manage_outages.php" class="text-decoration-none">

            <div class="dashboard-card card h-100">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <small class="text-muted">
                                Active Outages
                            </small>

                            <h2 class="fw-bold counter text-secondary"
                                data-target="<?= $totalOutages ?>">
                                0
                            </h2>

                            <span class="badge bg-secondary">
                                Live
                            </span>

                        </div>

                        <div class="icon-box bg-dark2">

                            <i class="bi bi-lightning-charge-fill"></i>

                        </div>

                    </div>

                </div>

            </div>

        </a>

    </div>

</div>


<!-- ==========================================
QUICK ACCESS
========================================== -->

<h3 class="fw-bold mb-4">

    <i class="bi bi-lightning-charge-fill text-warning"></i>

    Quick Access

</h3>

<div class="row g-4">

<?php

$quickLinks = [

["Consumers","manage_consumers.php","bi-people-fill","primary"],
["Meter Reading","meter_reading.php","bi-speedometer2","info"],
["Generate Bill","generate_bill.php","bi-lightning-charge-fill","warning"],
["Bills","manage_bills.php","bi-file-earmark-text-fill","success"],
["Payments","manage_payments.php","bi-credit-card-fill","danger"],
["Complaints","manage_complaint.php","bi-chat-left-text-fill","secondary"],
["Outages","manage_outages.php","bi-geo-alt-fill","dark"],
["Notices","manage_notices.php","bi-megaphone-fill","primary"],
["Reports","reports.php","bi-bar-chart-fill","success"],
["Settings","settings.php","bi-gear-fill","warning"],
["Manage Admins","manage_admins.php","bi-person-badge-fill","info"],
["Logout","logout.php","bi-box-arrow-right","danger"]

];

foreach($quickLinks as $item){

?>

<div class="col-xl-3 col-lg-4 col-md-6">

    <a href="<?= $item[1] ?>" class="text-decoration-none">

        <div class="card dashboard-card text-center h-100 quick-card">

            <div class="card-body py-4">

                <div class="mx-auto rounded-circle bg-<?= $item[3] ?> d-flex align-items-center justify-content-center"
                     style="width:75px;height:75px;">

                    <i class="bi <?= $item[2] ?> text-white"
                       style="font-size:32px;"></i>

                </div>

                <h5 class="mt-3 fw-bold">

                    <?= $item[0] ?>

                </h5>

                <p class="text-muted mb-0">

                    Open <?= $item[0] ?>

                </p>

            </div>

        </div>

    </a>

</div>

<?php } ?>

</div>

<style>

.quick-card{

    transition:.35s;
    cursor:pointer;

}

.quick-card:hover{

    transform:translateY(-10px);
    box-shadow:0 20px 40px rgba(0,0,0,.18);

}

.quick-card:hover i{

    transform:scale(1.2);
    transition:.3s;

}

</style>

<?php

/*=========================================
RECENT COMPLAINTS
=========================================*/

$recentComplaints = mysqli_query($conn,"
SELECT
c.complaint_id,
c.subject,
c.status,
c.created_at,
u.name
FROM complaint c
INNER JOIN users u
ON c.consumer_no=u.consumer_no
WHERE u.sub_division='".mysqli_real_escape_string($conn,$subDivision)."'
ORDER BY c.id DESC
LIMIT 5
");

/*=========================================
RECENT BILLS
=========================================*/

$recentBills = mysqli_query($conn,"
SELECT
b.bill_no,
b.consumer_no,
b.total_bill,
b.status,
b.bill_date
FROM bills b
INNER JOIN users u
ON b.consumer_no=u.consumer_no
WHERE u.sub_division='".mysqli_real_escape_string($conn,$subDivision)."'
ORDER BY b.id DESC
LIMIT 5
");

?>
<div class="row mt-5 g-4">

    <!-- Revenue Analytics -->

    <div class="col-lg-8">

        <div class="card dashboard-card h-100">

            <div class="card-header bg-white d-flex justify-content-between align-items-center">

                <div>

                    <h4 class="fw-bold mb-1">

                        <i class="bi bi-bar-chart-line-fill text-success me-2"></i>

                        Revenue Analytics

                    </h4>

                    <small class="text-muted">

                        Monthly electricity bill collection

                    </small>

                </div>

                <a href="reports.php"
                   class="btn btn-primary btn-sm">

                    <i class="bi bi-arrow-right-circle"></i>

                    View Reports

                </a>

            </div>

            <div class="card-body">

                <canvas id="revenueChart" height="100"></canvas>

            </div>

        </div>

    </div>



    <!-- Complaint Status -->

    <div class="col-lg-4">

        <div class="card dashboard-card h-100">

            <div class="card-header bg-white">

                <h4 class="fw-bold mb-1">

                    <i class="bi bi-pie-chart-fill text-warning me-2"></i>

                    Complaint Status

                </h4>

                <small class="text-muted">

                    Current complaint distribution

                </small>

            </div>

            <div class="card-body">

                <canvas id="complaintChart" height="230"></canvas>

                <hr>

                <div class="row text-center">

                    <div class="col-6 mb-3">

                        <h4 class="text-warning"><?= $pending ?></h4>

                        <small>Pending</small>

                    </div>

                    <div class="col-6 mb-3">

                        <h4 class="text-info"><?= $assigned ?></h4>

                        <small>Assigned</small>

                    </div>

                    <div class="col-6">

                        <h4 class="text-primary"><?= $inprogress ?></h4>

                        <small>In Progress</small>

                    </div>

                    <div class="col-6">

                        <h4 class="text-success"><?= $resolved ?></h4>

                        <small>Resolved</small>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- Live Outage Map -->

    <div class="col-lg-8">

        <div class="card dashboard-card">

            <div class="card-header bg-white d-flex justify-content-between align-items-center">

                <div>

                    <h4 class="fw-bold mb-1">

                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>

                        Live Outage Map

                    </h4>

                    <small class="text-muted">

                        Active outages in <?= htmlspecialchars($subDivision) ?>

                    </small>

                </div>

                <a href="manage_outages.php"
                   class="btn btn-danger btn-sm">

                    <i class="bi bi-lightning-charge-fill"></i>

                    Manage

                </a>

            </div>

            <div class="card-body p-0">

                <div id="map" style="height:430px;"></div>

            </div>

        </div>

    </div>



    <!-- Recent Complaints -->

    <div class="col-lg-4">

        <div class="card dashboard-card">

            <div class="card-header bg-white d-flex justify-content-between">

                <h4 class="fw-bold mb-0">

                    <i class="bi bi-chat-left-text-fill text-primary me-2"></i>

                    Recent Complaints

                </h4>

                <a href="manage_complaint.php"
                   class="btn btn-primary btn-sm">

                    View

                </a>

            </div>

            <div class="card-body p-0">

                <table class="table table-hover mb-0">

                    <tbody>

                    <?php while($row=mysqli_fetch_assoc($recentComplaints)){ ?>

                    <tr>

                        <td>

                            <strong><?= $row['complaint_id'] ?></strong>

                            <br>

                            <small class="text-muted">

                                <?= htmlspecialchars($row['name']) ?>

                            </small>

                        </td>

                        <td class="text-end">

                            <?php

                            $color="secondary";

                            if($row['status']=="Pending"){

                                $color="warning";

                            }elseif($row['status']=="Assigned"){

                                $color="info";

                            }elseif($row['status']=="In Progress"){

                                $color="primary";

                            }elseif($row['status']=="Resolved"){

                                $color="success";

                            }elseif($row['status']=="Rejected"){

                                $color="danger";

                            }

                            ?>

                            <span class="badge bg-<?= $color ?>">

                                <?= $row['status'] ?>

                            </span>

                        </td>

                    </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>
<script>

/*=========================================
ANIMATED COUNTERS
=========================================*/

document.querySelectorAll(".counter").forEach(counter=>{

    let target=parseInt(counter.dataset.target)||0;

    let count=0;

    let speed=Math.max(1,Math.ceil(target/80));

    function update(){

        count+=speed;

        if(count>=target){

            counter.innerText=target.toLocaleString();

        }else{

            counter.innerText=count.toLocaleString();

            requestAnimationFrame(update);

        }

    }

    update();

});


/*=========================================
LIVE DATE & TIME
=========================================*/

function updateClock(){

    let now=new Date();

    let dateOptions={

        weekday:"long",

        day:"numeric",

        month:"long",

        year:"numeric"

    };

    document.getElementById("liveDate").innerHTML=
    now.toLocaleDateString("en-IN",dateOptions);

    document.getElementById("liveTime").innerHTML=
    now.toLocaleTimeString("en-IN");

}

updateClock();

setInterval(updateClock,1000);


/*=========================================
DARK MODE
=========================================*/

const themeBtn=document.getElementById("themeToggle");

if(localStorage.getItem("theme")=="dark"){

    document.body.classList.add("dark");

    themeBtn.innerHTML='<i class="bi bi-sun-fill"></i>';

}

themeBtn.onclick=function(){

    document.body.classList.toggle("dark");

    if(document.body.classList.contains("dark")){

        localStorage.setItem("theme","dark");

        themeBtn.innerHTML='<i class="bi bi-sun-fill"></i>';

    }else{

        localStorage.setItem("theme","light");

        themeBtn.innerHTML='<i class="bi bi-moon-stars-fill"></i>';

    }

};


/*=========================================
REVENUE CHART
=========================================*/

new Chart(document.getElementById("revenueChart"),{

    type:"bar",

    data:{

        labels:[
            "Jan","Feb","Mar","Apr","May","Jun"
        ],

        datasets:[{

            label:"Revenue",

            data:[
                12000,
                18000,
                15000,
                24000,
                30000,
                <?= (int)$totalRevenue ?>
            ],

            backgroundColor:"#1976d2",

            borderRadius:8

        }]

    },

    options:{

        responsive:true,

        plugins:{

            legend:{
                display:false
            }

        },

        scales:{

            y:{
                beginAtZero:true
            }

        }

    }

});
/*=========================================
COMPLAINT STATUS CHART
=========================================*/

new Chart(document.getElementById("complaintChart"),{

    type:"doughnut",

    data:{

        labels:[
            "Pending",
            "Assigned",
            "In Progress",
            "Resolved",
            "Rejected"
        ],

        datasets:[{

            data:[
                <?= $pending ?>,
                <?= $assigned ?>,
                <?= $inprogress ?>,
                <?= $resolved ?>,
                <?= $rejected ?>
            ],

            backgroundColor:[
                "#ffc107",
                "#0dcaf0",
                "#0d6efd",
                "#198754",
                "#dc3545"
            ],

            borderWidth:0

        }]

    },

    options:{

        responsive:true,

        plugins:{

            legend:{
                position:"bottom"
            }

        }

    }

});


/*=========================================
LEAFLET OUTAGE MAP
=========================================*/

var map = L.map("map").setView([26.1445,91.7362],7);

L.tileLayer(
"https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
{
    maxZoom:19,
    attribution:"© OpenStreetMap"
}
).addTo(map);

/* Custom Red Circle Marker */

var redIcon = L.divIcon({

    className:"",

    html:`
    <div style="
        width:34px;
        height:34px;
        background:#dc3545;
        border:3px solid #ffffff;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        box-shadow:0 0 12px rgba(220,53,69,.5);
    ">
        <i class="bi bi-geo-alt-fill"
           style="
                color:#ffffff;
                font-size:18px;
           ">
        </i>
    </div>
    `,

    iconSize:[34,34],
    iconAnchor:[17,17],
    popupAnchor:[0,-18]

});

<?php

$out=mysqli_query($conn,"
SELECT *
FROM outages
WHERE sub_division='".mysqli_real_escape_string($conn,$subDivision)."'
AND status='Pending'
");

while($o=mysqli_fetch_assoc($out)){

if($o['latitude']!="" && $o['longitude']!=""){

?>

L.marker(
[
<?= $o['latitude'] ?>,
<?= $o['longitude'] ?>
],
{
    icon:redIcon
}
)

.addTo(map)

.bindPopup(`
<b><?= htmlspecialchars($o['feeder_name']) ?></b><br>
<?= htmlspecialchars($o['transformer']) ?><br>
Status :
<b><?= htmlspecialchars($o['status']) ?></b>
`);

<?php

}

}

?>

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>