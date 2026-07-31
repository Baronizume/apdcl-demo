<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================================
DELETE COMPLAINT
=========================================================*/

if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    mysqli_query($conn, "DELETE FROM complaint WHERE id='$id'");

    $_SESSION['success'] = "Complaint deleted successfully.";

    header("Location: manage_complaint.php");
    exit();
}

/*=========================================================
ADMIN DETAILS
=========================================================*/

$admin_username = $_SESSION['admin'];

$adminQuery = mysqli_query($conn,"
SELECT *
FROM admin
WHERE username='$admin_username'
LIMIT 1
");

$admin = mysqli_fetch_assoc($adminQuery);

$subDivision = mysqli_real_escape_string($conn, $admin['sub_division']);


/*=========================================================
DASHBOARD STATISTICS
=========================================================*/

$totalComplaints = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM complaint
WHERE sub_division='$subDivision'
"))['total'];

$pendingComplaints = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM complaint
WHERE sub_division='$subDivision'
AND status='Pending'
"))['total'];

$assignedComplaints = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM complaint
WHERE sub_division='$subDivision'
AND status='Assigned'
"))['total'];

$progressComplaints = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM complaint
WHERE sub_division='$subDivision'
AND status='In Progress'
"))['total'];

$resolvedComplaints = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM complaint
WHERE sub_division='$subDivision'
AND status='Resolved'
"))['total'];

/*=========================================================
SEARCH FILTER
=========================================================*/

$search = "";
$status = "";
$where = " WHERE sub_division='$subDivision' ";

if (!empty($_GET['search'])) {

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $where .= "
    AND (
        complaint_id LIKE '%$search%'
        OR consumer_no LIKE '%$search%'
        OR consumer_name LIKE '%$search%'
        OR mobile LIKE '%$search%'
        OR category LIKE '%$search%'
        OR zone LIKE '%$search%'
        OR circle LIKE '%$search%'
        OR sub_division LIKE '%$search%'
        OR assigned_to LIKE '%$search%'
    )
    ";

}

if (!empty($_GET['status'])) {

    $status = mysqli_real_escape_string($conn,$_GET['status']);

    $where .= " AND status='$status'";

}

/*=========================================================
FETCH COMPLAINTS
=========================================================*/

$query = mysqli_query($conn,"
SELECT *
FROM complaint
$where
ORDER BY created_at DESC
");

if (!$query) {
    die(mysqli_error($conn));
}

$totalRecords = mysqli_num_rows($query);

/*=========================================================
STATUS BADGE FUNCTION
=========================================================*/

function complaintBadge($status)
{
    switch($status){

        case "Pending":
            return "<span class='badge bg-danger'>Pending</span>";

        case "Assigned":
            return "<span class='badge bg-primary'>Assigned</span>";

        case "In Progress":
            return "<span class='badge bg-warning text-dark'>In Progress</span>";

        case "Resolved":
            return "<span class='badge bg-success'>Resolved</span>";

        default:
            return "<span class='badge bg-secondary'>$status</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Complaint Management | APDCL Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

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
    color:#0d47a1;
}

.page-subtitle{
    color:#6c757d;
    margin-top:5px;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
    transition:.25s;
}

.card:hover{
    transform:translateY(-4px);
}

.stat-card{
    color:#fff;
    min-height:140px;
}

.stat-card h6{
    font-size:15px;
    margin-top:10px;
}

.stat-card h2{
    font-size:34px;
    font-weight:bold;
}

.stat-icon{
    font-size:40px;
    opacity:.85;
}

.form-control,
.form-select{
    min-height:48px;
    border-radius:10px;
}

.btn{
    border-radius:10px;
}

.table th{
    background:#1565c0;
    color:#fff;
    text-align:center;
}

.table td{
    vertical-align:middle;
}

.badge{
    font-size:13px;
}

@media(max-width:768px){

.container-fluid{
padding:15px;
}

.page-title{
font-size:24px;
}

}

</style>

</head>

<body>

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">

<div>

<h2 class="page-title">

<i class="fa-solid fa-triangle-exclamation text-danger"></i>

Complaint Management

</h2>

<p class="page-subtitle">

Manage, Assign and Resolve Consumer Complaints

</p>

</div>

<div>

<a href="dashboard.php" class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Back to Dashboard

</a>

</div>

</div>

<?php
if(isset($_SESSION['success'])){
?>

<div class="alert alert-success alert-dismissible fade show">

<?= $_SESSION['success']; ?>

<button class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php
unset($_SESSION['success']);
}
?>

<!-- ==========================================
DASHBOARD CARDS
Only show cards when value > 0
========================================== -->

<div class="row g-4 mb-4">

<?php if($totalComplaints>0){ ?>

<div class="col-lg-3 col-md-6">

<div class="card stat-card bg-primary">

<div class="card-body text-center">

<i class="fa-solid fa-folder-open stat-icon"></i>

<h6>Total Complaints</h6>

<h2><?= number_format($totalComplaints) ?></h2>

</div>

</div>

</div>

<?php } ?>


<?php if($pendingComplaints>0){ ?>

<div class="col-lg-3 col-md-6">

<div class="card stat-card bg-danger">

<div class="card-body text-center">

<i class="fa-solid fa-clock stat-icon"></i>

<h6>Pending</h6>

<h2><?= number_format($pendingComplaints) ?></h2>

</div>

</div>

</div>

<?php } ?>


<?php if($assignedComplaints>0){ ?>

<div class="col-lg-3 col-md-6">

<div class="card stat-card bg-info">

<div class="card-body text-center">

<i class="fa-solid fa-user-check stat-icon"></i>

<h6>Assigned</h6>

<h2><?= number_format($assignedComplaints) ?></h2>

</div>

</div>

</div>

<?php } ?>


<?php if($progressComplaints>0){ ?>

<div class="col-lg-3 col-md-6">

<div class="card stat-card bg-warning text-dark">

<div class="card-body text-center">

<i class="fa-solid fa-spinner stat-icon"></i>

<h6>In Progress</h6>

<h2><?= number_format($progressComplaints) ?></h2>

</div>

</div>

</div>

<?php } ?>


<?php if($resolvedComplaints>0){ ?>

<div class="col-lg-3 col-md-6">

<div class="card stat-card bg-success">

<div class="card-body text-center">

<i class="fa-solid fa-circle-check stat-icon"></i>

<h6>Resolved</h6>

<h2><?= number_format($resolvedComplaints) ?></h2>

</div>

</div>

</div>

<?php } ?>

</div>

<!-- ==========================================
SEARCH PANEL
========================================== -->

<div class="card mb-4">

<div class="card-header bg-white">

<h5 class="mb-0">

<i class="fa fa-search text-primary"></i>

Search Complaints

</h5>

</div>

<div class="card-body">

<form method="GET">

<div class="row g-3">

<div class="col-lg-6">

<input
type="text"
name="search"
class="form-control"
placeholder="Complaint ID / Consumer / Mobile / Zone / SDE"
value="<?= htmlspecialchars($search) ?>">

</div>

<div class="col-lg-3">

<select
name="status"
class="form-select">

<option value="">All Status</option>

<option value="Pending" <?=($status=="Pending")?"selected":"";?>>

Pending

</option>

<option value="Assigned" <?=($status=="Assigned")?"selected":"";?>>

Assigned

</option>

<option value="In Progress" <?=($status=="In Progress")?"selected":"";?>>

In Progress

</option>

<option value="Resolved" <?=($status=="Resolved")?"selected":"";?>>

Resolved

</option>

</select>

</div>

<div class="col-lg-1">

<button class="btn btn-primary w-100">

<i class="fa fa-search"></i>

</button>

</div>

<div class="col-lg-2">

<a href="manage_complaint.php" class="btn btn-secondary w-100">

Reset

</a>

</div>

</div>

</form>

</div>

</div>

<!-- ==========================================
COMPLAINT LIST
========================================== -->

<div class="card border-0 shadow-lg">

<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">

<h5 class="mb-0">

<i class="fa-solid fa-list-check"></i>

Complaint List

</h5>

<span class="badge bg-light text-dark">

<?= $totalRecords ?> Records

</span>

</div>

<div class="card-body p-0">

<div class="table-responsive">

<table class="table table-hover table-bordered align-middle mb-0">

<thead>

<tr>

<th width="70">#</th>

<th width="170">Complaint ID</th>

<th width="260">Consumer Details</th>

<th width="170">Category</th>

<th width="140">Status</th>

<th width="180">Assigned Officer</th>

<th width="150">Created</th>

<th width="180">Actions</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($query)>0){

$count=1;

while($row=mysqli_fetch_assoc($query)){

?>

<tr>

<td class="text-center">

<strong><?= $count++; ?></strong>

</td>

<td>

<strong class="text-primary">

<?= htmlspecialchars($row['complaint_id']) ?>

</strong>

</td>

<td>

<strong>

<i class="fa fa-user text-primary"></i>

<?= htmlspecialchars($row['consumer_name']) ?>

</strong>

<br>

<small>

<i class="fa fa-bolt text-warning"></i>

<?= htmlspecialchars($row['consumer_no']) ?>

</small>

<br>

<small>

<i class="fa fa-phone text-success"></i>

<?= htmlspecialchars($row['mobile']) ?>

</small>

</td>

<td>

<span class="badge bg-secondary">

<?= htmlspecialchars($row['category']) ?>

</span>

</td>

<td class="text-center">

<?= complaintBadge($row['status']); ?>

</td>

<td class="text-center">

<?php

if(!empty($row['assigned_to'])){

?>

<span class="text-success fw-bold">

<i class="fa-solid fa-user-check"></i>

<?= htmlspecialchars($row['assigned_to']) ?>

</span>

<?php

}else{

?>

<span class="text-muted">

Not Assigned

</span>

<?php } ?>

</td>

<td class="text-center">

<?= date("d M Y",strtotime($row['created_at'])) ?>

<br>

<small class="text-muted">

<?= date("h:i A",strtotime($row['created_at'])) ?>

</small>

</td>

<td>

<div class="d-flex justify-content-center gap-2">

<a
href="view_complaint.php?id=<?= $row['id']; ?>"
class="btn btn-info btn-sm"
title="View">

<i class="fa fa-eye"></i>

</a>

<a
href="edit_complaint.php?id=<?= $row['id']; ?>"
class="btn btn-warning btn-sm"
title="Edit">

<i class="fa fa-edit"></i>

</a>

<a
href="assign_complaint.php?id=<?= $row['id']; ?>"
class="btn btn-primary btn-sm"
title="Assign">

<i class="fa-solid fa-user-check"></i>

</a>

<a
href="?delete=<?= $row['id']; ?>"
onclick="return confirm('Delete this complaint?')"
class="btn btn-danger btn-sm"
title="Delete">

<i class="fa fa-trash"></i>

</a>

</div>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="8" class="text-center p-5">

<i class="fa-solid fa-folder-open fa-3x text-secondary mb-3"></i>

<h5>No Complaints Found</h5>

<p class="text-muted">

There are currently no complaint records available.

</p>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<div class="card-footer bg-light d-flex justify-content-between align-items-center">

<div>

Showing

<strong><?= $totalRecords ?></strong>

Complaint(s)

</div>

<div>

<a href="dashboard.php" class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Back to Dashboard

</a>

</div>

</div>

</div>

<!-- ==========================================
PAGE ACTION BUTTONS
========================================== -->

<div class="mt-4 d-flex justify-content-between flex-wrap">

<a href="dashboard.php" class="btn btn-dark">

<i class="fa fa-arrow-left"></i>

Back to Dashboard

</a>

<div>

<button onclick="window.print()" class="btn btn-primary me-2">

<i class="fa fa-print"></i>

Print

</button>

<button onclick="exportTableToCSV()" class="btn btn-success">

<i class="fa fa-file-excel"></i>

Export CSV

</button>

</div>

</div>

<!-- ==========================================
FOOTER
========================================== -->

<footer class="mt-5">

<div class="card border-0 shadow-sm">

<div class="card-body text-center">

<h6 class="fw-bold text-primary">

Assam Power Distribution Company Limited (APDCL)

</h6>

<p class="text-muted mb-1">

Complaint Management System

</p>

<small class="text-secondary">

© <?= date("Y") ?> APDCL. All Rights Reserved.

</small>

</div>

</div>

</footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

/*=========================================
AUTO HIDE SUCCESS ALERT
=========================================*/

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


/*=========================================
TABLE HOVER EFFECT
=========================================*/

document.querySelectorAll("tbody tr").forEach(function(row){

row.addEventListener("mouseenter",function(){

this.style.transition=".2s";

this.style.transform="scale(1.01)";

});

row.addEventListener("mouseleave",function(){

this.style.transform="scale(1)";

});

});


/*=========================================
EXPORT TABLE TO CSV
=========================================*/

function exportTableToCSV(){

let csv=[];

let rows=document.querySelectorAll("table tr");

rows.forEach(function(row){

let cols=row.querySelectorAll("th,td");

let data=[];

cols.forEach(function(col){

data.push('"' + col.innerText.replace(/"/g,'""') + '"');

});

csv.push(data.join(","));

});

let csvFile=new Blob([csv.join("\n")],{

type:"text/csv"

});

let downloadLink=document.createElement("a");

downloadLink.download="Complaint_Report.csv";

downloadLink.href=window.URL.createObjectURL(csvFile);

downloadLink.style.display="none";

document.body.appendChild(downloadLink);

downloadLink.click();

document.body.removeChild(downloadLink);

}

</script>

</body>

</html>