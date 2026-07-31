<?php
session_start();
include("../db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/*==================================================
    ADMIN DETAILS
==================================================*/

$username = $_SESSION['admin'];

$adminQuery = mysqli_query($conn,"
SELECT *
FROM admin
WHERE username='$username'
LIMIT 1
");

$admin = mysqli_fetch_assoc($adminQuery);

if(!$admin){
    die("Admin not found.");
}

$subDivision = $admin['sub_division'];

/*==================================================
    DASHBOARD COUNTS
==================================================*/

function getCount($conn,$table,$subDivision){

    if($table=="users"){

        $sql="SELECT COUNT(*) total
              FROM users
              WHERE sub_division=?";

    }
    elseif($table=="complaint"){

        $sql="SELECT COUNT(*) total
              FROM complaint
              WHERE sub_division=?";

    }
    elseif($table=="bills"){

        $sql="SELECT COUNT(*) total
              FROM bills b
              INNER JOIN users u
              ON b.consumer_no=u.consumer_no
              WHERE u.sub_division=?";

    }
    elseif($table=="payments"){

        $sql="SELECT COUNT(*) total
              FROM payments p
              INNER JOIN users u
              ON p.consumer_no=u.consumer_no
              WHERE u.sub_division=?";

    }

    $stmt=mysqli_prepare($conn,$sql);

    mysqli_stmt_bind_param($stmt,"s",$subDivision);

    mysqli_stmt_execute($stmt);

    $result=mysqli_stmt_get_result($stmt);

    $row=mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);

    return $row['total'];
}

$totalConsumers = getCount($conn,"users",$subDivision);
$totalBills     = getCount($conn,"bills",$subDivision);
$totalPayments  = getCount($conn,"payments",$subDivision);
$totalComplaint = getCount($conn,"complaint",$subDivision);

/*==================================================
    TOTAL REVENUE
==================================================*/

$revenue = 0;

$stmt=mysqli_prepare($conn,"
SELECT SUM(b.total_bill)
FROM bills b
INNER JOIN users u
ON b.consumer_no=u.consumer_no
WHERE b.status='Paid'
AND u.sub_division=?
");

mysqli_stmt_bind_param($stmt,"s",$subDivision);

mysqli_stmt_execute($stmt);

mysqli_stmt_bind_result($stmt,$revenue);

mysqli_stmt_fetch($stmt);

mysqli_stmt_close($stmt);

$revenue = $revenue ?: 0;

/*=========================================
CHART DATA
=========================================*/

/* Monthly Revenue */

$monthLabel = [];
$monthRevenue = [];

$stmt=mysqli_prepare($conn,"
SELECT
DATE_FORMAT(payment_date,'%b') AS m,
SUM(amount) AS total
FROM payments p
INNER JOIN users u
ON p.consumer_no=u.consumer_no
WHERE u.sub_division=?
GROUP BY MONTH(payment_date)
ORDER BY MONTH(payment_date)
");

mysqli_stmt_bind_param($stmt,"s",$subDivision);

mysqli_stmt_execute($stmt);

$sql=mysqli_stmt_get_result($stmt);

while($row=mysqli_fetch_assoc($sql)){
    $monthLabel[] = $row['m'];
    $monthRevenue[] = $row['total'];
}

/* Complaint Status */

$pending = 0;
$resolved = 0;
$progress = 0;

$stmt=mysqli_prepare($conn,"
SELECT status,COUNT(*) total
FROM complaint
WHERE sub_division=?
GROUP BY status
");

mysqli_stmt_bind_param($stmt,"s",$subDivision);

mysqli_stmt_execute($stmt);

$q=mysqli_stmt_get_result($stmt);

while($r=mysqli_fetch_assoc($q)){

    if($r['status']=="Pending")
        $pending=$r['total'];

    elseif($r['status']=="Resolved")
        $resolved=$r['total'];

    else
        $progress += $r['total'];

}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Reports Dashboard | APDCL</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    background:#edf3fb;

    font-family:"Segoe UI",sans-serif;

}

/*=========================
NAVBAR
==========================*/

.navbar{

height:75px;

background:linear-gradient(90deg,#0d47a1,#1565c0,#1e88e5);

box-shadow:0 5px 20px rgba(0,0,0,.15);

}

.logo{

width:55px;

height:55px;

border-radius:50%;

background:#fff;

padding:4px;

margin-right:15px;

}

.brand-title{

font-size:24px;

font-weight:bold;

color:#fff;

}

.brand-sub{

font-size:13px;

color:#e8f2ff;

}

/*=========================
SIDEBAR
==========================*/

.sidebar{

position:fixed;

top:75px;

left:0;

width:260px;

height:100%;

background:#083b8a;

overflow-y:auto;

padding-top:15px;

box-shadow:5px 0 15px rgba(0,0,0,.15);

}

.sidebar a{

display:flex;

align-items:center;

padding:15px 22px;

color:#fff;

text-decoration:none;

transition:.3s;

font-size:15px;

}

.sidebar a:hover{

background:#1565c0;

padding-left:28px;

}

.sidebar a.active{

background:#1976d2;

border-left:5px solid gold;

}

.sidebar i{

font-size:20px;

margin-right:14px;

width:25px;

}

/*=========================
CONTENT
==========================*/

.content{

margin-left:270px;

padding:30px;

margin-top:90px;

}

/*=========================
PAGE TITLE
==========================*/

.page-title{

font-size:34px;

font-weight:700;

color:#0d47a1;

}

.page-sub{

color:#666;

}

/*=========================
CARDS
==========================*/

.card{

border:none;

border-radius:20px;

box-shadow:0 10px 25px rgba(0,0,0,.08);

transition:.3s;

}

.card:hover{

transform:translateY(-6px);

}

.chart-card{

padding:20px;

}

</style>

</head>

<body>
<body style="background:#eef4fb;">

<div class="container-fluid py-4">

    <!-- HEADER -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center">

                <div class="d-flex align-items-center">

                    <img src="../assets/images/logo-circle.png"
                         width="60"
                         class="me-3">

                    <div>

                        <h2 class="fw-bold text-primary mb-0">
                            APDCL Reports Dashboard
                        </h2>

                        <small class="text-muted">
                            Assam Power Distribution Company Limited
                        </small>

                    </div>

                </div>

                <div class="text-end">

                    <h6 class="mb-1">

                        Welcome,

                        <span class="text-primary">

                            <?= htmlspecialchars($admin['name']) ?>

                        </span>

                    </h6>

                    <a href="dashboard.php"
                       class="btn btn-primary">

                        <i class="bi bi-arrow-left-circle"></i>

                        Back to Dashboard

                    </a>

                </div>

            </div>

        </div>

    </div>

    <!-- SUMMARY CARDS -->

    <div class="row g-4 mb-4">

        <div class="col-lg-3 col-md-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted">
                                Total Consumers
                            </small>

                            <h2 class="fw-bold text-primary">
                                <?= $totalConsumers ?>
                            </h2>

                        </div>

                        <div class="bg-primary rounded-circle p-3">

                            <i class="bi bi-people-fill text-white fs-3"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted">
                                Total Bills
                            </small>

                            <h2 class="fw-bold text-success">
                                <?= $totalBills ?>
                            </h2>

                        </div>

                        <div class="bg-success rounded-circle p-3">

                            <i class="bi bi-receipt-cutoff text-white fs-3"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted">
                                Payments
                            </small>

                            <h2 class="fw-bold text-info">
                                <?= $totalPayments ?>
                            </h2>

                        </div>

                        <div class="bg-info rounded-circle p-3">

                            <i class="bi bi-credit-card-fill text-white fs-3"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <div class="col-lg-3 col-md-6">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <small class="text-muted">
                                Revenue
                            </small>

                            <h3 class="fw-bold text-danger">

                                ₹ <?= number_format($revenue,2) ?>

                            </h3>

                        </div>

                        <div class="bg-danger rounded-circle p-3">

                            <i class="bi bi-cash-stack text-white fs-3"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
    <!-- ================= CHARTS ================= -->

<div class="row mb-4">

    <!-- Revenue Line Chart -->

    <div class="col-lg-8 mb-4">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-white">

                <h5 class="mb-0 text-primary">
                    <i class="bi bi-graph-up-arrow"></i>
                    Monthly Revenue
                </h5>

            </div>

            <div class="card-body">

                <canvas id="revenueChart" height="120"></canvas>

            </div>

        </div>

    </div>

    <!-- Complaint Pie Chart -->

    <div class="col-lg-4 mb-4">

        <div class="card shadow-sm border-0">

            <div class="card-header bg-white">

                <h5 class="mb-0 text-danger">
                    <i class="bi bi-pie-chart-fill"></i>
                    Complaint Status
                </h5>

            </div>

            <div class="card-body">

                <canvas id="complaintChart"></canvas>

            </div>

        </div>

    </div>

</div>
<!-- ================= FOOTER ================= -->

<footer class="text-center py-4 mt-5">

    <hr>

    <p class="mb-0 text-muted">

        © <?= date("Y") ?> APDCL Electricity Billing Management System

    </p>

</footer>


<!-- ================= JAVASCRIPT ================= -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

new Chart(document.getElementById("revenueChart"),{

    type:'line',

    data:{

        labels:<?= json_encode($monthLabel) ?>,

        datasets:[{

            label:'Revenue',

            data:<?= json_encode($monthRevenue) ?>,

            borderColor:'#1565c0',

            backgroundColor:'rgba(21,101,192,.15)',

            fill:true,

            tension:.4,

            borderWidth:3

        }]

    },

    options:{

        responsive:true,

        plugins:{
            legend:{
                display:false
            }
        }

    }

});


new Chart(document.getElementById("complaintChart"),{

    type:'pie',

    data:{

        labels:[
            'Pending',
            'Resolved',
            'Others'
        ],

        datasets:[{

            data:[
                <?= $pending ?>,
                <?= $resolved ?>,
                <?= $progress ?>
            ],

            backgroundColor:[
                '#ffc107',
                '#198754',
                '#0dcaf0'
            ]

        }]

    },

    options:{
        responsive:true
    }

});

</script>

</body>
</html>