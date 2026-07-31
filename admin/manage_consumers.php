<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=====================================================
    FETCH LOGGED-IN ADMIN
=====================================================*/

$adminUsername = $_SESSION['admin'];

$stmt = mysqli_prepare($conn,"
    SELECT *
    FROM admin
    WHERE username=?
    LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$adminUsername);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){

    session_destroy();

    header("Location: login.php");

    exit();

}

$admin = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/*=====================================================
    ADMIN DETAILS
=====================================================*/

$adminName = $admin['name'];
$adminRole = $admin['role'];

$adminZone = trim($admin['zone']);
$adminCircle = trim($admin['circle']);
$adminSubdivision = trim($admin['sub_division']);

/*=====================================================
    SEARCH
=====================================================*/

$search = trim($_GET['search'] ?? "");

/*=====================================================
    PAGINATION
=====================================================*/

$limit = 20;

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page<1){
    $page=1;
}

$offset = ($page-1)*$limit;

/*=====================================================
    WHERE CONDITION
=====================================================*/

$where = [];

$params = [];

$types = "";

/*---------------------------
    SDE FILTER
---------------------------*/

if($adminRole!="Super Admin"){

    $where[]="sub_division=?";

    $params[]=$adminSubdivision;

    $types.="s";

}

/*---------------------------
    SEARCH FILTER
---------------------------*/

if($search!=""){

    $where[]="(

        consumer_no LIKE ?

        OR name LIKE ?

        OR father_name LIKE ?

        OR mobile LIKE ?

        OR meter_no LIKE ?

        OR sub_division LIKE ?

    )";

    $keyword="%".$search."%";

    for($i=0;$i<6;$i++){

        $params[]=$keyword;

        $types.="s";

    }

}

$whereSQL="";

if(!empty($where)){

    $whereSQL=" WHERE ".implode(" AND ",$where);

}

/*=====================================================
    TOTAL CONSUMERS
=====================================================*/

$countSQL="

SELECT COUNT(*) total

FROM users

$whereSQL

";

$stmt=mysqli_prepare($conn,$countSQL);

if(!empty($params)){

    mysqli_stmt_bind_param($stmt,$types,...$params);

}

mysqli_stmt_execute($stmt);

$totalConsumers=mysqli_fetch_assoc(

    mysqli_stmt_get_result($stmt)

)['total'];

mysqli_stmt_close($stmt);

$totalPages=ceil($totalConsumers/$limit);

$activeConsumers=$totalConsumers;

/*=====================================================
    CONSUMER LIST
=====================================================*/

$listSQL="

SELECT *

FROM users

$whereSQL

ORDER BY id DESC

LIMIT ?,?

";

$stmt=mysqli_prepare($conn,$listSQL);

$listTypes=$types."ii";

$listParams=$params;

$listParams[]=$offset;

$listParams[]=$limit;

mysqli_stmt_bind_param(

    $stmt,

    $listTypes,

    ...$listParams

);

mysqli_stmt_execute($stmt);

$consumers=mysqli_stmt_get_result($stmt);

$totalSearch=mysqli_num_rows($consumers);

mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Manage Consumers | APDCL</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#eef3f8;
    font-family:'Segoe UI',sans-serif;
}

/* NAVBAR */

.navbar{
    background:linear-gradient(90deg,#0d47a1,#1565c0,#1976d2);
    padding:14px 25px;
    box-shadow:0 4px 18px rgba(0,0,0,.15);
}

.logo{
    width:60px;
    height:60px;
    border-radius:50%;
    background:#fff;
    padding:5px;
    margin-right:15px;
}

.brand-title{
    color:#fff;
    font-size:24px;
    font-weight:bold;
}

.brand-sub{
    color:#dfefff;
    font-size:14px;
}

.profile-box{
    display:flex;
    align-items:center;
    color:#fff;
}

.profile-icon{

    width:50px;
    height:50px;
    border-radius:50%;
    background:#fff;
    color:#1565c0;

    display:flex;
    justify-content:center;
    align-items:center;

    font-size:25px;
    margin-right:12px;

}

/* PAGE */

.main-content{
    padding:35px;
}

.page-title{
    color:#0d47a1;
    font-weight:700;
}

/* CARDS */

.stats-card{

    border:none;
    border-radius:18px;
    color:#fff;
    padding:28px;

    box-shadow:0 10px 30px rgba(0,0,0,.08);

}

.stats-card i{

    font-size:45px;

}

.stats-card h2{

    margin-top:12px;
    font-size:34px;
    font-weight:700;

}

.blue{
    background:linear-gradient(135deg,#1565c0,#42a5f5);
}

.green{
    background:linear-gradient(135deg,#2e7d32,#66bb6a);
}

.orange{
    background:linear-gradient(135deg,#ef6c00,#ffa726);
}

/* SEARCH */

.search-card{

    border:none;
    border-radius:18px;
    box-shadow:0 8px 25px rgba(0,0,0,.08);

}

.form-control{

    height:55px;
    border-radius:12px;

}

.btn{

    border-radius:12px;

}

</style>

</head>

<body>

<nav class="navbar">

<div class="container-fluid">

<div class="d-flex align-items-center">

<img src="../assets/images/logo-circle.png" class="logo">

<div>

<div class="brand-title">

APDCL

</div>

<div class="brand-sub">

Assam Power Distribution Company Limited

</div>

</div>

</div>

<div class="profile-box">

<div class="profile-icon">

<i class="bi bi-person-fill"></i>

</div>

<div>

<b><?= htmlspecialchars($adminName) ?></b><br>

<?= htmlspecialchars($adminRole) ?>

</div>

</div>

</div>

</nav>

<div class="container-fluid main-content">

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="page-title">

<i class="bi bi-people-fill"></i>

Manage Consumers

</h2>

<p class="text-muted">

Manage APDCL Registered Consumers

</p>

</div>

<div>

<a href="dashboard.php" class="btn btn-outline-primary btn-lg">

<i class="bi bi-house-fill"></i>

Dashboard

</a>

<a href="add_consumer.php" class="btn btn-success btn-lg ms-2">

<i class="bi bi-person-plus-fill"></i>

Add Consumer

</a>

</div>

</div>

<div class="row mb-4">

<div class="col-md-4">

<div class="stats-card blue">

<i class="bi bi-people-fill"></i>

<h2><?= $totalConsumers ?></h2>

<p>Total Consumers</p>

</div>

</div>

<div class="col-md-4">

<div class="stats-card green">

<i class="bi bi-person-check-fill"></i>

<h2><?= $activeConsumers ?></h2>

<p>Active Consumers</p>

</div>

</div>

<div class="col-md-4">

<div class="stats-card orange">

<i class="bi bi-search"></i>

<h2><?= $totalSearch ?></h2>

<p>Search Result</p>

</div>

</div>

</div>

<div class="card search-card mb-4">

<div class="card-body">

<form method="GET">

<div class="row">

<div class="col-md-10">

<input
type="text"
name="search"
class="form-control form-control-lg"
placeholder="Search Consumer No, Name, Mobile, Meter No..."
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

<!-- ===================== CONSUMER TABLE ===================== -->

<div class="card border-0 shadow-lg rounded-4">

    <div class="card-header bg-primary text-white py-3">

        <div class="d-flex justify-content-between align-items-center">

            <h4 class="mb-0">

                <i class="bi bi-table"></i>

                Consumer List

            </h4>

            <span class="badge bg-light text-primary fs-6">

                <?= $totalConsumers ?> Consumers

            </span>

        </div>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover table-striped align-middle mb-0">

                <thead class="table-dark">

                    <tr>

                        <th>ID</th>
                        <th>Consumer No</th>
                        <th>Name</th>
                        <th>Father Name</th>
                        <th>Mobile</th>
                        <th>Meter No</th>
                        <th>Category</th>
                        <th>Sub-Division</th>
                        <th>Status</th>
                        <th width="240">Actions</th>

                    </tr>

                </thead>

                <tbody>

<?php

if(mysqli_num_rows($consumers)>0){

while($row=mysqli_fetch_assoc($consumers)){

?>

<tr>

<td>

<?= $row['id']; ?>

</td>

<td>

<span class="fw-bold text-primary">

<?= htmlspecialchars($row['consumer_no']); ?>

</span>

</td>

<td>

<?= htmlspecialchars($row['name']); ?>

</td>

<td>

<?= htmlspecialchars($row['father_name']); ?>

</td>

<td>

<?= htmlspecialchars($row['mobile']); ?>

</td>

<td>

<?= htmlspecialchars($row['meter_no']); ?>

</td>

<td>

<span class="badge bg-info">

<?= htmlspecialchars($row['category']); ?>

</span>

</td>

<td>

<?= htmlspecialchars($row['sub_division']); ?>

</td>

<td>

<?php if(isset($row['status']) && $row['status']=="Inactive"){ ?>

<span class="badge bg-danger">

Inactive

</span>

<?php }else{ ?>

<span class="badge bg-success">

Active

</span>

<?php } ?>

</td>

<td>

<a href="view_consumer.php?id=<?= $row['id']; ?>"
class="btn btn-sm btn-info">

<i class="bi bi-eye-fill"></i>

View

</a>

<a href="edit_consumer.php?id=<?= $row['id']; ?>"
class="btn btn-sm btn-warning">

<i class="bi bi-pencil-fill"></i>

Edit

</a>

<a href="delete_consumer.php?id=<?= $row['id']; ?>"
class="btn btn-sm btn-danger"
onclick="return confirm('Delete this consumer?');">

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

<i class="bi bi-person-x-fill display-3 text-secondary"></i>

<h4 class="mt-3">

No Consumers Found

</h4>

<p class="text-muted">

Try another search keyword.

</p>

</td>

</tr>

<?php

}

?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- ================= PAGINATION ================= -->

<?php if($totalPages>1){ ?>

<nav class="mt-4">

<ul class="pagination justify-content-center">

<?php

for($i=1;$i<=$totalPages;$i++){

?>

<li class="page-item <?= ($page==$i)?'active':''; ?>">

<a class="page-link"

href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">

<?= $i ?>

</a>

</li>

<?php

}

?>

</ul>

</nav>

<?php } ?>

</div>

<!-- ================= FOOTER ================= -->

<footer class="bg-white shadow-sm mt-5 py-4">

<div class="container-fluid">

<div class="row align-items-center">

<div class="col-md-6">

<h5 class="text-primary mb-1">

⚡ APDCL Electricity Billing Management System

</h5>

<small class="text-muted">

Assam Power Distribution Company Limited

</small>

</div>

<div class="col-md-6 text-end">

<strong>

<?= htmlspecialchars($adminName) ?>

</strong>

<br>

<span class="badge bg-primary">

<?= htmlspecialchars($adminRole) ?>

</span>

</div>

</div>

<hr>

<div class="d-flex justify-content-between">

<span>

© <?= date("Y") ?> APDCL. All Rights Reserved.

</span>

<span id="clock" class="fw-bold text-primary"></span>

</div>

</div>

</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

function updateClock(){

    let now=new Date();

    document.getElementById("clock").innerHTML=
        now.toLocaleDateString("en-IN")+" | "+
        now.toLocaleTimeString("en-IN");

}

updateClock();

setInterval(updateClock,1000);

</script>

</body>
</html>