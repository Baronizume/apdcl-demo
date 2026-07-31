<?php
session_start();

/*=========================================================
    ADMIN LOGIN CHECK
=========================================================*/

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================================
    GET ADMIN DETAILS
=========================================================*/

$admin_username = $_SESSION['admin'];

$stmt = mysqli_prepare($conn,"
SELECT *
FROM admin
WHERE username=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$admin_username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){
    die("Admin account not found.");
}

$admin = mysqli_fetch_assoc($result);

/*=========================================================
    DELETE RESTORED OUTAGE
=========================================================*/

if(isset($_GET['delete'])){

    $id = (int)$_GET['delete'];

    mysqli_query($conn,"
    DELETE FROM outages
    WHERE id='$id'
    ");

    $_SESSION['success']="Restored outage deleted successfully.";

    header("Location: resolved_outages.php");
    exit();

}

/*=========================================================
    DASHBOARD STATISTICS
=========================================================*/

// Total Restored

$result=mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
WHERE status='Restored'
");

$totalRestored=mysqli_fetch_assoc($result)['total'];


// Today's Restored

$result=mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
WHERE status='Restored'
AND DATE(resolved_at)=CURDATE()
");

$todayRestored=mysqli_fetch_assoc($result)['total'];


// This Month Restored

$result=mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
WHERE status='Restored'
AND MONTH(resolved_at)=MONTH(CURDATE())
AND YEAR(resolved_at)=YEAR(CURDATE())
");

$monthRestored=mysqli_fetch_assoc($result)['total'];

/*=========================================================
    SEARCH FILTER
=========================================================*/

$search="";
$where=" WHERE status='Restored' ";

if(!empty($_GET['search'])){

    $search=mysqli_real_escape_string(
        $conn,
        $_GET['search']
    );

    $where.="

    AND (

        district LIKE '%$search%'

        OR

        zone LIKE '%$search%'

        OR

        circle LIKE '%$search%'

        OR

        sub_division LIKE '%$search%'

        OR

        feeder_name LIKE '%$search%'

        OR

        transformer LIKE '%$search%'

        OR

        resolved_by LIKE '%$search%'

    )

    ";

}

/*=========================================================
    FETCH RESTORED OUTAGES
=========================================================*/

$query=mysqli_query($conn,"

SELECT *

FROM outages

$where

ORDER BY resolved_at DESC

");

if(!$query){

    die(mysqli_error($conn));

}

$totalRecords=mysqli_num_rows($query);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>

Resolved Outages | APDCL Admin

</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
rel="stylesheet">

<style>

body{

    background:#eef3f8;
    font-family:'Segoe UI',sans-serif;

}

.container-fluid{

    padding:30px;

}

.page-title{

    font-size:30px;
    font-weight:700;
    color:#198754;

}

.page-subtitle{

    color:#6c757d;

}

.card{

    border:none;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
    transition:.25s;

}

.card:hover{

    transform:translateY(-3px);

}

.stat-card{

    color:#fff;
    min-height:140px;

}

.stat-card h2{

    font-size:34px;
    font-weight:bold;

}

.stat-icon{

    font-size:42px;
    opacity:.9;

}

.table th{

    background:#198754;
    color:#fff;
    text-align:center;
    vertical-align:middle;

}

.table td{

    vertical-align:middle;

}

.form-control{

    height:48px;
    border-radius:10px;

}

.btn{

    border-radius:10px;

}

.badge{

    font-size:13px;

}

</style>

</head>

<body>

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">

<div>

<h2 class="page-title">

<i class="fa-solid fa-circle-check"></i>

Resolved Outages

</h2>

<p class="page-subtitle">

View all restored power outages across APDCL.

</p>

</div>

<div>

<a href="dashboard.php"
class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Dashboard

</a>

</div>

</div>

<?php if(isset($_SESSION['success'])){ ?>

<div class="alert alert-success alert-dismissible fade show">

<?= $_SESSION['success']; ?>

<button class="btn-close"
data-bs-dismiss="alert"></button>

</div>

<?php unset($_SESSION['success']); } ?>

<!-- DASHBOARD CARDS -->

<div class="row g-4 mb-4">

<div class="col-lg-4">

<div class="card stat-card bg-success">

<div class="card-body text-center">

<i class="fa-solid fa-circle-check stat-icon"></i>

<h6>Total Restored</h6>

<h2><?= number_format($totalRestored) ?></h2>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card stat-card bg-primary">

<div class="card-body text-center">

<i class="fa-solid fa-calendar-day stat-icon"></i>

<h6>Restored Today</h6>

<h2><?= number_format($todayRestored) ?></h2>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card stat-card bg-dark">

<div class="card-body text-center">

<i class="fa-solid fa-calendar-check stat-icon"></i>

<h6>This Month</h6>

<h2><?= number_format($monthRestored) ?></h2>

</div>

</div>

</div>

</div>

<!-- SEARCH CARD -->

<div class="card mb-4">

<div class="card-header bg-white">

<h5 class="mb-0">

<i class="fa fa-search text-success"></i>

Search Restored Outages

</h5>

</div>

<div class="card-body">

<form method="GET">

<div class="row g-3">

<div class="col-lg-10">

<input
type="text"
name="search"
class="form-control"
placeholder="District, Zone, Circle, Feeder, Restored By..."
value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-lg-2">

<button class="btn btn-success w-100">

<i class="fa fa-search"></i>

Search

</button>

</div>

</div>

</form>

</div>

</div>

<!-- =========================================================
    RESTORED OUTAGES TABLE
========================================================= -->

<div class="card">

    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">

        <h5 class="mb-0">
            <i class="fa-solid fa-list-check"></i>
            Restored Outages
            (<?= number_format($totalRecords); ?>)
        </h5>

        <div>

            <button
                onclick="window.print();"
                class="btn btn-light btn-sm me-2">

                <i class="fa fa-print"></i>
                Print

            </button>

            <button
                onclick="exportTableToCSV();"
                class="btn btn-warning btn-sm">

                <i class="fa-solid fa-file-csv"></i>
                Export CSV

            </button>

        </div>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover table-bordered align-middle mb-0">

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>Location</th>
                        <th>Feeder</th>
                        <th>Consumers</th>
                        <th>Reason</th>
                        <th>Started</th>
                        <th>Restored</th>
                        <th>Restored By</th>
                        <th>Status</th>
                        <th width="170">Action</th>

                    </tr>

                </thead>

                <tbody>

<?php if(mysqli_num_rows($query)>0){ ?>

<?php while($row=mysqli_fetch_assoc($query)){ ?>

<tr>

<td class="text-center">

<strong>#<?= $row['id']; ?></strong>

</td>

<td>

<strong>

<?= htmlspecialchars($row['district']); ?>

</strong>

<br>

<small class="text-muted">

<?= htmlspecialchars($row['zone']); ?>

</small>

<br>

<small>

<?= htmlspecialchars($row['circle']); ?>

</small>

<br>

<small>

<?= htmlspecialchars($row['sub_division']); ?>

</small>

</td>

<td>

<strong>

<?= htmlspecialchars($row['feeder_name']); ?>

</strong>

<?php if(!empty($row['transformer'])){ ?>

<br>

<small class="text-muted">

Transformer :
<?= htmlspecialchars($row['transformer']); ?>

</small>

<?php } ?>

</td>

<td class="text-center">

<?= number_format($row['consumers_affected']); ?>

</td>

<td>

<?= nl2br(htmlspecialchars($row['outage_reason'])); ?>

</td>

<td class="text-center">

<?= date("d M Y",strtotime($row['start_time'])); ?>

<br>

<small class="text-muted">

<?= date("h:i A",strtotime($row['start_time'])); ?>

</small>

</td>

<td class="text-center">

<?php if(!empty($row['resolved_at'])){ ?>

<?= date("d M Y",strtotime($row['resolved_at'])); ?>

<br>

<small class="text-muted">

<?= date("h:i A",strtotime($row['resolved_at'])); ?>

</small>

<?php }else{ ?>

-

<?php } ?>

</td>

<td class="text-center">

<?= !empty($row['resolved_by'])
? htmlspecialchars($row['resolved_by'])
: "-"; ?>

</td>

<td class="text-center">

<span class="badge bg-success">

Restored

</span>

</td>

<td>

<div class="d-flex justify-content-center gap-2">

<a
href="resolved_outage_details.php?id=<?= $row['id']; ?>"
class="btn btn-info btn-sm"
title="View">

<i class="fa fa-eye"></i>

</a>

<a
href="?delete=<?= $row['id']; ?>"
class="btn btn-danger btn-sm"
title="Delete"
onclick="return confirm('Delete this restored outage?');">

<i class="fa fa-trash"></i>

</a>

</div>

</td>

</tr>

<?php } ?>

<?php }else{ ?>

<tr>

<td colspan="10" class="text-center py-5">

<i class="fa-solid fa-circle-check fa-4x text-success mb-3"></i>

<h4>

No Restored Outages Found

</h4>

<p class="text-muted">

There are no restored outage records available.

</p>

</td>

</tr>

<?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- =========================================================
    FOOTER BUTTONS
========================================================= -->

<div class="mt-4 d-flex justify-content-between flex-wrap">

<a
href="manage_outages.php"
class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Pending Outages

</a>

<a
href="dashboard.php"
class="btn btn-dark">

<i class="fa fa-home"></i>

Dashboard

</a>

</div>

<!-- =========================================================
    APDCL FOOTER
========================================================= -->

<footer class="mt-5">

    <div class="card border-0 shadow-sm">

        <div class="card-body text-center">

            <h5 class="fw-bold text-success">

                ⚡ Assam Power Distribution Company Limited (APDCL)

            </h5>

            <p class="text-muted mb-1">

                Resolved Outage Management System

            </p>

            <small class="text-secondary">

                © <?= date("Y"); ?> APDCL. All Rights Reserved.

            </small>

        </div>

    </div>

</footer>

</div>

<!-- =========================================================
    BOOTSTRAP
========================================================= -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- =========================================================
    EXPORT CSV
========================================================= -->

<script>

function exportTableToCSV(){

    let csv=[];

    let rows=document.querySelectorAll("table tr");

    rows.forEach(function(row){

        let cols=row.querySelectorAll("th,td");

        let data=[];

        cols.forEach(function(col){

            data.push(
                '"' +
                col.innerText.replace(/"/g,'""') +
                '"'
            );

        });

        csv.push(data.join(","));

    });

    let csvFile=new Blob(
        [csv.join("\n")],
        {
            type:"text/csv"
        }
    );

    let download=document.createElement("a");

    download.download="resolved_outages.csv";

    download.href=
    window.URL.createObjectURL(csvFile);

    download.click();

}

</script>

<!-- =========================================================
    CARD ANIMATION
========================================================= -->

<script>

document.querySelectorAll(".card").forEach(function(card){

    card.addEventListener("mouseenter",function(){

        this.style.transition=".25s";

        this.style.transform="translateY(-4px)";

    });

    card.addEventListener("mouseleave",function(){

        this.style.transform="translateY(0px)";

    });

});

</script>

<!-- =========================================================
    TABLE ROW HOVER
========================================================= -->

<script>

document.querySelectorAll("tbody tr").forEach(function(row){

    row.addEventListener("mouseenter",function(){

        this.style.transition=".2s";

        this.style.transform="scale(1.01)";

    });

    row.addEventListener("mouseleave",function(){

        this.style.transform="scale(1)";

    });

});

</script>

<!-- =========================================================
    AUTO HIDE SUCCESS MESSAGE
========================================================= -->

<script>

setTimeout(function(){

    let alert=document.querySelector(".alert-success");

    if(alert){

        alert.style.transition=".5s";

        alert.style.opacity="0";

        setTimeout(function(){

            alert.remove();

        },500);

    }

},4000);

</script>

</body>
</html>