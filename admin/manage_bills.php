<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================
    LOGGED IN ADMIN
=========================================*/

$admin_username = $_SESSION['admin'];

$stmt = mysqli_prepare($conn,"
SELECT *
FROM admin
WHERE username=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$admin_username);
mysqli_stmt_execute($stmt);

$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

mysqli_stmt_close($stmt);

$isSuperAdmin = ($admin['role'] == "Super Admin");

/*=========================================
    SEARCH
=========================================*/

$search = "";

$where = [];

if(!$isSuperAdmin){

    $subDivision = mysqli_real_escape_string(
        $conn,
        $admin['sub_division']
    );

    $where[] = "sub_division='$subDivision'";
}

if(isset($_GET['search']) && trim($_GET['search'])!=""){

    $search = trim($_GET['search']);

    $safe = mysqli_real_escape_string($conn,$search);

    $where[] = "(
        bill_no LIKE '%$safe%'
        OR consumer_no LIKE '%$safe%'
        OR consumer_name LIKE '%$safe%'
    )";
}

$whereSQL = "";

if(count($where)>0){
    $whereSQL = "WHERE ".implode(" AND ",$where);
}

/*=========================================
    STATISTICS
=========================================*/

$totalBills = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT COUNT(*) total
        FROM bills
        $whereSQL
    ")
)['total'];

$pendingBills = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT COUNT(*) total
        FROM bills
        $whereSQL
        ".($whereSQL==""?"WHERE":"AND")."
        status='Pending'
    ")
)['total'];

$paidBills = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT COUNT(*) total
        FROM bills
        $whereSQL
        ".($whereSQL==""?"WHERE":"AND")."
        status='Paid'
    ")
)['total'];

$totalRevenue = mysqli_fetch_assoc(
    mysqli_query($conn,"
        SELECT SUM(total_bill) revenue
        FROM bills
        $whereSQL
    ")
)['revenue'];

if($totalRevenue==""){
    $totalRevenue=0;
}

/*=========================================
    PAGINATION
=========================================*/

$limit = 15;

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

if($page<1){
    $page=1;
}

$offset = ($page-1)*$limit;

$countQuery = mysqli_query($conn,"
SELECT COUNT(*) total
FROM bills
$whereSQL
");

$totalRows = mysqli_fetch_assoc($countQuery)['total'];

$totalPages = ceil($totalRows/$limit);

/*=========================================
    BILL LIST
=========================================*/

$query = "
SELECT *
FROM bills
$whereSQL
ORDER BY id DESC
LIMIT $offset,$limit
";

$result = mysqli_query($conn,$query);

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Manage Bills | APDCL</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<style>

body{
    background:#eef3f9;
    font-family:'Segoe UI',sans-serif;
}

.navbar{
    background:#0d47a1;
    padding:15px 25px;
    box-shadow:0 4px 15px rgba(0,0,0,.2);
}

.navbar-brand{
    display:flex;
    align-items:center;
    color:#fff!important;
    font-size:24px;
    font-weight:bold;
}

.navbar-brand img{
    width:60px;
    margin-right:15px;
}

.nav-title small{
    display:block;
    font-size:14px;
    font-weight:400;
}

.profile-btn{
    color:#fff;
    text-decoration:none;
}

.profile-avatar{
    width:45px;
    height:45px;
    border-radius:50%;
    background:#fff;
    color:#0d47a1;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:22px;
    margin-right:10px;
}

.main-content{
    padding:30px;
}

.page-title{
    color:#0d47a1;
    font-weight:bold;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
}

.stats-card{
    border-radius:18px;
    color:#fff;
    padding:25px;
}

.stats-card i{
    font-size:42px;
}

.stats-card h2{
    margin-top:12px;
    font-weight:bold;
}

.bg-blue{
    background:linear-gradient(135deg,#1565c0,#42a5f5);
}

.bg-orange{
    background:linear-gradient(135deg,#ef6c00,#ff9800);
}

.bg-green{
    background:linear-gradient(135deg,#2e7d32,#66bb6a);
}

.bg-purple{
    background:linear-gradient(135deg,#6a1b9a,#ab47bc);
}

.btn{
    border-radius:10px;
}

.table thead{
    background:#0d47a1;
    color:#fff;
}

</style>

</head>

<body>

<nav class="navbar navbar-expand-lg">

<div class="container-fluid">

<a class="navbar-brand" href="dashboard.php">

<img src="../assets/images/logo-circle.png">

<div class="nav-title">

APDCL

<small>Manage Bills</small>

</div>

</a>

<div class="dropdown">

<a class="d-flex align-items-center profile-btn dropdown-toggle"
href="#"
data-bs-toggle="dropdown">

<div class="profile-avatar">

<i class="bi bi-person-fill"></i>

</div>

<div>

<b><?= htmlspecialchars($admin['name']); ?></b><br>

<small><?= htmlspecialchars($admin['username']); ?></small>

</div>

</a>

<ul class="dropdown-menu dropdown-menu-end">

<li>

<a class="dropdown-item" href="profile.php">

<i class="bi bi-person-circle"></i>

My Profile

</a>

</li>

<li><hr class="dropdown-divider"></li>

<li>

<a class="dropdown-item text-danger" href="../logout.php">

<i class="bi bi-box-arrow-right"></i>

Logout

</a>

</li>

</ul>

</div>

</div>

</nav>

<div class="container-fluid main-content">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="page-title">

<i class="bi bi-receipt-cutoff"></i>

Manage Bills

</h2>

<p class="text-muted">

Generate, search and manage electricity bills.

</p>

</div>

<div>

<a href="dashboard.php" class="btn btn-outline-primary btn-lg me-2">

    <i class="bi bi-arrow-left-circle-fill"></i>

    Back to Dashboard

</a>

<a href="generate_bill.php" class="btn btn-success btn-lg">

    <i class="bi bi-plus-circle-fill"></i>

    Generate Bill

</a>

</div>

</div>

<div class="row mb-4">

<div class="col-md-3">

<div class="stats-card bg-blue">

<i class="bi bi-file-earmark-text-fill"></i>

<h2><?= $totalBills ?></h2>

<p>Total Bills</p>

</div>

</div>

<div class="col-md-3">

<div class="stats-card bg-orange">

<i class="bi bi-clock-history"></i>

<h2><?= $pendingBills ?></h2>

<p>Pending Bills</p>

</div>

</div>

<div class="col-md-3">

<div class="stats-card bg-green">

<i class="bi bi-check-circle-fill"></i>

<h2><?= $paidBills ?></h2>

<p>Paid Bills</p>

</div>

</div>

<div class="col-md-3">

<div class="stats-card bg-purple">

<i class="bi bi-currency-rupee"></i>

<h2>₹ <?= number_format($totalRevenue,2) ?></h2>

<p>Total Revenue</p>

</div>

</div>

</div>

<div class="card mb-4">

<div class="card-body">

<form method="GET">

<div class="row">

<div class="col-md-10">

<input
type="text"
name="search"
class="form-control form-control-lg"
placeholder="Search Bill No, Consumer No or Consumer Name..."
value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-md-2 d-grid">

<button class="btn btn-primary btn-lg">

<i class="bi bi-search"></i>

Search

</button>

</div>

</div>

</form>

</div>

</div>

<!-- ================= BILL TABLE ================= -->

<div class="card">

    <div class="card-header bg-white">

        <div class="d-flex justify-content-between align-items-center">

            <h4 class="mb-0">

                <i class="bi bi-receipt-cutoff text-primary"></i>

                Bill List

            </h4>

            <span class="badge bg-primary fs-6">

                <?= $totalRows ?> Bills

            </span>

        </div>

    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-hover table-bordered align-middle">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Bill No</th>
                        <th>Consumer No</th>
                        <th>Consumer Name</th>
                        <th>Month</th>
                        <th>Units</th>
                        <th>Total Bill</th>
                        <th>Status</th>
                        <th>Sub Division</th>
                        <th width="260">Actions</th>

                    </tr>

                </thead>

                <tbody>

                <?php

                if(mysqli_num_rows($result)>0){

                    while($row=mysqli_fetch_assoc($result)){

                        if($row['status']=="Paid"){
                            $badge="success";
                        }elseif($row['status']=="Pending"){
                            $badge="warning";
                        }else{
                            $badge="secondary";
                        }

                ?>

                <tr>

                    <td><?= $row['id']; ?></td>

                    <td>

                        <span class="fw-bold text-primary">

                            <?= htmlspecialchars($row['bill_no']); ?>

                        </span>

                    </td>

                    <td><?= htmlspecialchars($row['consumer_no']); ?></td>

                    <td><?= htmlspecialchars($row['consumer_name']); ?></td>

                    <td><?= htmlspecialchars($row['month']); ?></td>

                    <td><?= number_format($row['units'],2); ?></td>

                    <td>

                        <strong class="text-success">

                            ₹ <?= number_format($row['total_bill'],2); ?>

                        </strong>

                    </td>

                    <td>

                        <span class="badge bg-<?= $badge; ?>">

                            <?= htmlspecialchars($row['status']); ?>

                        </span>

                    </td>

                    <td><?= htmlspecialchars($row['sub_division']); ?></td>

                    <td>

                        <a href="view_bill.php?id=<?= $row['id']; ?>"
                           class="btn btn-info btn-sm">

                            <i class="bi bi-eye-fill"></i>

                            View

                        </a>

                        <a href="edit_bill.php?id=<?= $row['id']; ?>"
                           class="btn btn-warning btn-sm">

                            <i class="bi bi-pencil-fill"></i>

                            Edit

                        </a>

                        <a href="print_bill.php?id=<?= $row['id']; ?>"
                           target="_blank"
                           class="btn btn-success btn-sm">

                            <i class="bi bi-printer-fill"></i>

                            Print

                        </a>

                        <a href="delete_bill.php?id=<?= $row['id']; ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this bill?')">

                            <i class="bi bi-trash-fill"></i>

                            Delete

                        </a>

                    </td>

                </tr>

                <?php

                    }

                }else{

                ?>

                <tr>

                    <td colspan="10" class="text-center py-5">

                        <i class="bi bi-receipt fs-1 text-secondary"></i>

                        <h4 class="mt-3">

                            No Bills Found

                        </h4>

                    </td>

                </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- ================= FOOTER ================= -->

<footer class="card mt-4 border-0 shadow-sm">

    <div class="card-body">

        <div class="row">

            <div class="col-md-6">

                <h5 class="text-primary">

                    ⚡ APDCL Electricity Billing Management System

                </h5>

                <small class="text-muted">

                    Assam Power Distribution Company Limited

                </small>

            </div>

            <div class="col-md-6 text-end">

                <strong>

                    <?= htmlspecialchars($admin['name']); ?>

                </strong>

                <br>

                <span class="badge bg-primary">

                    <?= htmlspecialchars($admin['role']); ?>

                </span>

            </div>

        </div>

        <hr>

        <div class="d-flex justify-content-between align-items-center">

            <span>

                © <?= date("Y") ?> APDCL. All Rights Reserved.

            </span>

            <span id="clock" class="fw-bold text-primary"></span>

        </div>

    </div>

</footer>

<!-- ================= PAGINATION ================= -->

<?php if($totalPages>1){ ?>

<nav class="mt-4">

<ul class="pagination justify-content-center">

<?php if($page>1){ ?>

<li class="page-item">

<a class="page-link"
href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>">

Previous

</a>

</li>

<?php } ?>

<?php

for($i=1;$i<=$totalPages;$i++){

?>

<li class="page-item <?= ($i==$page)?'active':''; ?>">

<a class="page-link"
href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">

<?= $i ?>

</a>

</li>

<?php } ?>

<?php if($page<$totalPages){ ?>

<li class="page-item">

<a class="page-link"
href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>">

Next

</a>

</li>

<?php } ?>

</ul>

</nav>

<?php } ?>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

function updateClock(){

    let now = new Date();

    document.getElementById("clock").innerHTML =
        now.toLocaleDateString("en-IN") +
        " | " +
        now.toLocaleTimeString("en-IN");

}

updateClock();

setInterval(updateClock,1000);

</script>

</body>

</html>