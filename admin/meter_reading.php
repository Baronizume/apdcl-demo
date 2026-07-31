<?php
session_start();

/*====================================================
    ADMIN LOGIN CHECK
====================================================*/
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================
GET ADMIN SUB-DIVISION
=========================================*/

$username = $_SESSION['admin'];

$stmt = mysqli_prepare($conn,"
SELECT sub_division
FROM admin
WHERE username=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$username);
mysqli_stmt_execute($stmt);

$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$subDivision = $admin['sub_division'];

mysqli_stmt_close($stmt);

/*====================================================
    ADMIN DETAILS
====================================================*/
$admin_username = $_SESSION['admin'];

$adminQuery = mysqli_query($conn,"
SELECT *
FROM admin
WHERE username='$admin_username'
LIMIT 1
");

$admin = mysqli_fetch_assoc($adminQuery);

/*====================================================
    DELETE METER READING
====================================================*/
if(isset($_GET['delete'])){

    $id = (int)$_GET['delete'];

    mysqli_query($conn,"
        DELETE FROM meter_reading
        WHERE id='$id'
    ");

    $_SESSION['success']="Meter Reading deleted successfully.";

    header("Location: meter_reading.php");
    exit();
}

/*====================================================
    DASHBOARD STATISTICS
====================================================*/

$totalReadings = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM meter_reading mr
INNER JOIN users u ON mr.consumer_no=u.consumer_no
WHERE u.sub_division='$subDivision'
"))['total'];

$todayReadings = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM meter_reading mr
INNER JOIN users u ON mr.consumer_no=u.consumer_no
WHERE u.sub_division='$subDivision'
AND DATE(mr.reading_date)=CURDATE()
"))['total'];

$monthReadings = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM meter_reading mr
INNER JOIN users u ON mr.consumer_no=u.consumer_no
WHERE u.sub_division='$subDivision'
AND MONTH(mr.reading_date)=MONTH(CURDATE())
AND YEAR(mr.reading_date)=YEAR(CURDATE())
"))['total'];

$totalUnits = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT IFNULL(SUM(mr.current_reading-mr.previous_reading),0) AS units
FROM meter_reading mr
INNER JOIN users u ON mr.consumer_no=u.consumer_no
WHERE u.sub_division='$subDivision'
"))['units'];

/*====================================================
    SEARCH FILTER
====================================================*/

$where = "WHERE mr.sub_division='" . mysqli_real_escape_string($conn,$subDivision) . "'";

if(!empty($_GET['search'])){

    $search=mysqli_real_escape_string($conn,$_GET['search']);

    $where.=" AND (
        mr.consumer_no LIKE '%$search%'
        OR mr.meter_no LIKE '%$search%'
        OR u.name LIKE '%$search%'
    )";
}

if(!empty($_GET['month'])){

    $month=(int)$_GET['month'];

    $where.=" AND MONTH(mr.reading_date)='$month'";
}

if(!empty($_GET['year'])){

    $year=(int)$_GET['year'];

    $where.=" AND YEAR(mr.reading_date)='$year'";
}

/*====================================================
    FETCH RECORDS
====================================================*/

$query=mysqli_query($conn,"
SELECT
mr.*,
u.name
FROM meter_reading mr
LEFT JOIN users u
ON mr.consumer_no=u.consumer_no
$where
ORDER BY mr.id DESC
");

if(!$query){
    die(mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Meter Reading Management | APDCL
</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
rel="stylesheet">

<style>

body{
    background:#eef3f9;
    font-family:'Segoe UI',sans-serif;
}

/*=========================
NAVBAR
=========================*/

.navbar{

background:linear-gradient(90deg,#0d47a1,#1565c0);

height:72px;

box-shadow:0 5px 18px rgba(0,0,0,.15);

}

.navbar-brand{

color:#fff!important;

font-size:22px;

font-weight:700;

display:flex;

align-items:center;

gap:12px;

}

.navbar-brand img{

width:48px;

height:48px;

background:#fff;

padding:4px;

border-radius:50%;

}

.admin-box{

display:flex;

align-items:center;

gap:12px;

color:#fff;

}

.admin-box img{

width:45px;

height:45px;

border-radius:50%;

background:#fff;

padding:2px;

}

/*=========================
SIDEBAR
=========================*/

.sidebar{

position:fixed;

top:72px;

left:0;

width:250px;

height:calc(100vh - 72px);

background:#0b3b86;

overflow-y:auto;

}

.sidebar-header{

text-align:center;

padding:20px;

color:#fff;

border-bottom:1px solid rgba(255,255,255,.15);

}

.sidebar-logo{

width:70px;

background:#fff;

padding:5px;

border-radius:50%;

margin-bottom:10px;

}

.sidebar a{

display:flex;

align-items:center;

gap:12px;

padding:14px 22px;

color:#fff;

text-decoration:none;

border-left:4px solid transparent;

transition:.3s;

}

.sidebar a:hover{

background:#1565c0;

padding-left:28px;

border-left:4px solid #ffc107;

}

.sidebar a.active{

background:#1976d2;

border-left:4px solid #ffc107;

}

/*=========================
CONTENT
=========================*/

.content{

margin-left:250px;

padding:30px;

}

.page-title{

font-size:30px;

font-weight:700;

color:#1d3557;

}

.card{

border:none;

border-radius:18px;

box-shadow:0 10px 20px rgba(0,0,0,.08);

}

.stat-card{

border-radius:18px;

color:#fff;

min-height:130px;

}

.stat-card h2{

font-size:34px;

font-weight:bold;

}

.stat-icon{

font-size:36px;

opacity:.85;

}

.table th{

background:#1565c0;

color:#fff;

text-align:center;

vertical-align:middle;

}

.table td{

vertical-align:middle;

}

.form-control,
.form-select{

min-height:46px;

border-radius:10px;

}

.btn{

border-radius:10px;

}

</style>

</head>

<body>

<!-- ================= NAVBAR ================= -->

<nav class="navbar">

<div class="container-fluid">

<a class="navbar-brand" href="dashboard.php">

<img src="../assets/images/logo-circle.png">

<span>APDCL Admin Panel</span>

</a>

<div class="admin-box">

<img src="https://ui-avatars.com/api/?name=<?= urlencode($admin['name']); ?>&background=ffffff&color=1565c0">

<div>

<strong><?= htmlspecialchars($admin['name']); ?></strong>

<br>

<small><?= htmlspecialchars($admin['role']); ?></small>

</div>

</div>

</div>

</nav>

<!-- =====================================================
        PAGE HEADER
====================================================== -->

<div class="container-fluid py-4">

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>

        <h2 class="fw-bold text-primary">
            <i class="fa-solid fa-gauge-high"></i>
            Meter Reading Management
        </h2>

        <p class="text-muted mb-0">
            Manage consumer meter readings, monitor monthly readings and search records.
        </p>

    </div>

    <div>

        <a href="add_meter_reading.php" class="btn btn-success btn-lg">

            <i class="fa fa-plus-circle"></i>

            Add Meter Reading

        </a>

    </div>

</div>

<?php
if(isset($_SESSION['success'])){
?>

<div class="alert alert-success alert-dismissible fade show">

    <i class="fa fa-circle-check"></i>

    <?= $_SESSION['success']; ?>

    <button
        type="button"
        class="btn-close"
        data-bs-dismiss="alert">
    </button>

</div>

<?php
unset($_SESSION['success']);
}
?>

<!-- =====================================================
        DASHBOARD STATISTICS
====================================================== -->

<div class="row g-4 mb-4">

    <div class="col-lg-3 col-md-6">

        <div class="card border-0 shadow bg-primary text-white">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <h6>Total Readings</h6>

                        <h2 class="fw-bold">
                            <?= number_format($totalReadings) ?>
                        </h2>

                    </div>

                    <i class="fa-solid fa-database fa-3x opacity-75"></i>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card border-0 shadow bg-success text-white">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <h6>Today's Readings</h6>

                        <h2 class="fw-bold">
                            <?= number_format($todayReadings) ?>
                        </h2>

                    </div>

                    <i class="fa-solid fa-calendar-day fa-3x opacity-75"></i>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card border-0 shadow bg-warning">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <h6>This Month</h6>

                        <h2 class="fw-bold">
                            <?= number_format($monthReadings) ?>
                        </h2>

                    </div>

                    <i class="fa-solid fa-calendar fa-3x opacity-75"></i>

                </div>

            </div>

        </div>

    </div>

    <div class="col-lg-3 col-md-6">

        <div class="card border-0 shadow bg-danger text-white">

            <div class="card-body">

                <div class="d-flex justify-content-between">

                    <div>

                        <h6>Total Units</h6>

                        <h2 class="fw-bold">
                            <?= number_format($totalUnits) ?>
                        </h2>

                    </div>

                    <i class="fa-solid fa-bolt fa-3x opacity-75"></i>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- =====================================================
        SEARCH & FILTER
====================================================== -->

<div class="card border-0 shadow mb-4">

<div class="card-header bg-white">

    <h5 class="mb-0">

        <i class="fa fa-search text-primary"></i>

        Search Meter Reading

    </h5>

</div>

<div class="card-body">

<form method="GET">

<div class="row g-3">

<div class="col-lg-5">

<input
type="text"
name="search"
class="form-control"

placeholder="Consumer No / Meter No / Consumer Name"

value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

</div>

<div class="col-lg-2">

<select
name="month"
class="form-select">

<option value="">Month</option>

<?php
for($m=1;$m<=12;$m++){
?>

<option
value="<?= $m ?>"
<?= (($_GET['month'] ?? '')==$m) ? "selected" : "" ?>>

<?= date("F",mktime(0,0,0,$m,1)); ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-lg-2">

<select
name="year"
class="form-select">

<option value="">Year</option>

<?php
for($y=date("Y");$y>=2023;$y--){
?>

<option
value="<?= $y ?>"
<?= (($_GET['year'] ?? '')==$y) ? "selected" : "" ?>>

<?= $y ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-lg-1">

<button
class="btn btn-primary w-100">

<i class="fa fa-search"></i>

</button>

</div>

<div class="col-lg-2">

<a
href="meter_reading.php"
class="btn btn-secondary w-100">

Reset

</a>

</div>

</div>

</form>

</div>

</div>

<!-- =====================================================
        METER READING RECORDS
====================================================== -->

<div class="card border-0 shadow">

    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">

        <h5 class="mb-0">

            <i class="fa-solid fa-table"></i>

            Meter Reading Records

        </h5>

        <span class="badge bg-light text-dark fs-6">

            <?= mysqli_num_rows($query) ?> Records

        </span>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover table-bordered align-middle mb-0">

                <thead class="table-primary text-center">

                <tr>

                    <th>ID</th>

                    <th>Consumer</th>

                    <th>Meter No</th>

                    <th>Reading Date</th>

                    <th>Previous</th>

                    <th>Current</th>

                    <th>Units</th>

                    <th width="180">Action</th>

                </tr>

                </thead>

                <tbody>

                <?php

                if(mysqli_num_rows($query)>0){

                    while($row=mysqli_fetch_assoc($query)){

                        $units = $row['current_reading'] - $row['previous_reading'];

                ?>

                <tr>

                    <td class="text-center">

                        <strong><?= $row['id']; ?></strong>

                    </td>

                    <td>

                        <strong>

                            <?= htmlspecialchars($row['consumer_no']); ?>

                        </strong>

                        <br>

                        <small class="text-muted">

                            <?= htmlspecialchars($row['name']); ?>

                        </small>

                    </td>

                    <td class="text-center">

                        <?= htmlspecialchars($row['meter_no']); ?>

                    </td>

                    <td class="text-center">

                        <?= date("d M Y",strtotime($row['reading_date'])); ?>

                        <br>

                        <small class="text-muted">

                            <?= date("h:i A",strtotime($row['reading_date'])); ?>

                        </small>

                    </td>

                    <td class="text-center">

                        <?= number_format($row['previous_reading']); ?>

                    </td>

                    <td class="text-center">

                        <?= number_format($row['current_reading']); ?>

                    </td>

                    <td class="text-center">

                        <?php if($units>=0){ ?>

                            <span class="badge bg-success fs-6">

                                <?= number_format($units); ?> Units

                            </span>

                        <?php } else { ?>

                            <span class="badge bg-danger">

                                Invalid

                            </span>

                        <?php } ?>

                    </td>

                    <td class="text-center">

                        <a

                        href="view_meter_reading.php?id=<?= $row['id']; ?>"

                        class="btn btn-info btn-sm"

                        title="View">

                            <i class="fa fa-eye"></i>

                        </a>

                        <a

                        href="edit_meter_reading.php?id=<?= $row['id']; ?>"

                        class="btn btn-warning btn-sm"

                        title="Edit">

                            <i class="fa fa-edit"></i>

                        </a>

                        <a

                        href="?delete=<?= $row['id']; ?>"

                        class="btn btn-danger btn-sm"

                        title="Delete"

                        onclick="return confirm('Delete this meter reading?')">

                            <i class="fa fa-trash"></i>

                        </a>

                    </td>

                </tr>

                <?php

                    }

                }else{

                ?>

                <tr>

                    <td colspan="8" class="text-center py-5">

                        <i class="fa-solid fa-folder-open fa-3x text-secondary mb-3"></i>

                        <h5>No Meter Readings Found</h5>

                        <p class="text-muted">

                            No meter reading records are available.

                        </p>

                        <a

                        href="add_meter_reading.php"

                        class="btn btn-success">

                            <i class="fa fa-plus-circle"></i>

                            Add First Reading

                        </a>

                    </td>

                </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- =====================================================
        PAGE FOOTER
====================================================== -->

<div class="mt-4 d-flex justify-content-between align-items-center flex-wrap">

    <a
    href="dashboard.php"
    class="btn btn-secondary">

        <i class="fa fa-arrow-left"></i>

        Back to Dashboard

    </a>

    <div>

        <button
        onclick="window.print();"
        class="btn btn-primary me-2">

            <i class="fa fa-print"></i>

            Print

        </button>

        <button
        class="btn btn-success"
        onclick="exportTableToCSV();">

            <i class="fa fa-file-excel"></i>

            Export CSV

        </button>

    </div>

</div>

<!-- =====================================================
        FOOTER
====================================================== -->

<footer class="mt-5">

    <div class="card shadow-sm border-0">

        <div class="card-body text-center">

            <h6 class="mb-2 fw-bold text-primary">

                Assam Power Distribution Company Limited (APDCL)

            </h6>

            <p class="mb-1 text-muted">

                Meter Reading Management System

            </p>

            <small class="text-secondary">

                © <?= date("Y") ?> APDCL. All Rights Reserved.

            </small>

        </div>

    </div>

</footer>

</div>

<!-- =====================================================
        BOOTSTRAP
====================================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- =====================================================
        EXPORT TABLE TO CSV
====================================================== -->

<script>

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

    let csvFile=new Blob([csv.join("\n")],{type:"text/csv"});

    let downloadLink=document.createElement("a");

    downloadLink.download="meter_reading.csv";

    downloadLink.href=window.URL.createObjectURL(csvFile);

    downloadLink.style.display="none";

    document.body.appendChild(downloadLink);

    downloadLink.click();

    document.body.removeChild(downloadLink);

}

</script>

<!-- =====================================================
        TABLE HOVER EFFECT
====================================================== -->

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

<!-- =====================================================
        AUTO HIDE SUCCESS MESSAGE
====================================================== -->

<script>

setTimeout(function(){

    let alert=document.querySelector(".alert-success");

    if(alert){

        alert.style.transition="0.5s";

        alert.style.opacity="0";

        setTimeout(function(){

            alert.remove();

        },500);

    }

},4000);

</script>

</body>

</html>