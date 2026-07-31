<?php
session_start();
include("../db.php");

/* ----------------------------
   LOGIN CHECK
---------------------------- */

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['admin'];

$getAdmin = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username'");
$admin = mysqli_fetch_assoc($getAdmin);

if (!$admin) {
    session_destroy();
    header("Location: login.php");
    exit();
}

/* ----------------------------
   ROLE CHECK
---------------------------- */

$allowedRoles = ['Super Admin', 'SDE'];

if (!in_array($admin['role'], $allowedRoles)) {
    die("<h2 style='text-align:center;color:red;margin-top:100px;'>Access Denied</h2>");
}

/* ----------------------------
   DEFAULT
---------------------------- */

$message = "";

/* ----------------------------
   ADD SUB DIVISION
---------------------------- */

if(isset($_POST['save'])){

    $zone = mysqli_real_escape_string($conn,$_POST['zone']);
    $circle = mysqli_real_escape_string($conn,$_POST['circle']);
    $sub_division_name = mysqli_real_escape_string($conn,$_POST['sub_division_name']);

    if($zone=="" || $circle=="" || $sub_division_name==""){

        $message="<div class='alert alert-danger'>
        Please fill all fields.
        </div>";

    }else{

        $check=mysqli_query($conn,"
        SELECT *
        FROM sub_divisions
        WHERE zone='$zone'
        AND circle='$circle'
        AND sub_division_name='$sub_division_name'
        ");

        if(mysqli_num_rows($check)>0){

            $message="<div class='alert alert-warning'>
            Sub Division already exists.
            </div>";

        }else{

            mysqli_query($conn,"
            INSERT INTO sub_divisions
            (
                zone,
                circle,
                sub_division_name
            )
            VALUES
            (
                '$zone',
                '$circle',
                '$sub_division_name'
            )
            ");

            $message="<div class='alert alert-success'>
            Sub Division Added Successfully.
            </div>";
        }

    }

}

/* ----------------------------
   DELETE
---------------------------- */

if(isset($_GET['delete'])){

    $id=(int)$_GET['delete'];

    mysqli_query($conn,"
    DELETE FROM sub_divisions
    WHERE id='$id'
    ");

    header("Location: manage_subdivisions.php");
    exit();

}

/* ----------------------------
   SEARCH
---------------------------- */

$search="";

if(isset($_GET['search'])){
    $search=mysqli_real_escape_string($conn,$_GET['search']);
}

/* ----------------------------
   PAGINATION
---------------------------- */

$limit=10;

$page=isset($_GET['page']) ? (int)$_GET['page'] : 1;

if($page<1) $page=1;

$start=($page-1)*$limit;

$where="";

if($search!=""){

$where="
WHERE
zone LIKE '%$search%'
OR circle LIKE '%$search%'
OR sub_division_name LIKE '%$search%'
";

}

$count=mysqli_query($conn,"
SELECT COUNT(*) total
FROM sub_divisions
$where
");

$total=mysqli_fetch_assoc($count)['total'];

$totalPages=ceil($total/$limit);

$records=mysqli_query($conn,"
SELECT *
FROM sub_divisions
$where
ORDER BY id DESC
LIMIT $start,$limit
");

if(!$records){
    die(mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Manage Sub-Divisions</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#f4f6f9;
}

.card{
    border:none;
    border-radius:12px;
}

.page-title{
    font-size:28px;
    font-weight:700;
}

.stat-card{
    color:#fff;
}

.bg1{background:#0d6efd;}
.bg2{background:#198754;}
.bg3{background:#fd7e14;}

</style>

</head>

<body>

<div class="container-fluid mt-4">

<div class="row mb-3">

<div class="col-md-6">
<h2 class="page-title">
<i class="fa-solid fa-building"></i>
Manage Sub-Divisions
</h2>
</div>

<div class="col-md-6 text-end">

<a href="dashboard.php" class="btn btn-secondary">
<i class="fa fa-arrow-left"></i>
Back to Dashboard
</a>

</div>

</div>

<hr>

<?php echo $message; ?>

<div class="row mb-4">

<div class="col-md-4">

<div class="card stat-card bg1">

<div class="card-body">

<h5>Total Sub-Divisions</h5>

<h2>

<?php echo $total; ?>

</h2>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card stat-card bg2">

<div class="card-body">

<h5>Total Pages</h5>

<h2>

<?php echo $totalPages; ?>

</h2>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card stat-card bg3">

<div class="card-body">

<form method="GET">

<div class="input-group">

<input
type="text"
name="search"
class="form-control"
placeholder="Search..."
value="<?php echo htmlspecialchars($search); ?>">

<button class="btn btn-light">

<i class="fa fa-search"></i>

</button>

</div>

</form>

</div>

</div>

</div>

</div>

<div class="row">

<!-- LEFT SIDE -->

<div class="col-lg-4">

<div class="card shadow">

<div class="card-header bg-primary text-white">

<h5 class="mb-0">

<i class="fa fa-plus-circle"></i>

Add Sub-Division

</h5>

</div>

<div class="card-body">

<form method="POST">

<div class="mb-3">

<label class="form-label">

Zone

</label>

<select
name="zone"
class="form-select"
required>

<option value="">

Select Zone

</option>

<?php

$zones=mysqli_query($conn,"
SELECT *
FROM zones
ORDER BY zone_name
");

while($z=mysqli_fetch_assoc($zones)){

?>

<option value="<?php echo $z['zone_name']; ?>">

<?php echo $z['zone_name']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label class="form-label">

Circle

</label>

<select
name="circle"
class="form-select"
required>

<option value="">

Select Circle

</option>

<?php

$circles=mysqli_query($conn,"
SELECT *
FROM circles
ORDER BY circle_name
");

while($c=mysqli_fetch_assoc($circles)){

?>

<option value="<?php echo $c['circle_name']; ?>">

<?php echo $c['circle_name']; ?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label class="form-label">

Sub-Division Name

</label>

<input
type="text"
name="sub_division_name"
class="form-control"
required>

</div>

<div class="d-grid">

<button
type="submit"
name="save"
class="btn btn-primary">

<i class="fa fa-save"></i>

Save Sub-Division

</button>

</div>

</form>

</div>

</div>

</div>

<!-- RIGHT SIDE -->

<div class="col-lg-8">

<div class="card shadow">

<div class="card-header bg-dark text-white">

<h5 class="mb-0">

<i class="fa fa-list"></i>

Sub-Division List

</h5>

</div>

<div class="card-body">

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle">

    <thead class="table-primary">

        <tr>
            <th width="60">ID</th>
            <th>Zone</th>
            <th>Circle</th>
            <th>Sub-Division</th>
            <th>Created At</th>
            <th width="170">Action</th>
        </tr>

    </thead>

    <tbody>

<?php

if(mysqli_num_rows($records)>0){

while($row=mysqli_fetch_assoc($records)){

?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo htmlspecialchars($row['zone']); ?></td>

<td><?php echo htmlspecialchars($row['circle']); ?></td>

<td><?php echo htmlspecialchars($row['sub_division_name']); ?></td>

<td><?php echo $row['created_at']; ?></td>

<td>

<a href="edit_subdivision.php?id=<?php echo $row['id']; ?>"
class="btn btn-warning btn-sm">

<i class="fa fa-edit"></i>
Edit

</a>

<a href="?delete=<?php echo $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this Sub-Division?');">

<i class="fa fa-trash"></i>
Delete

</a>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="6" class="text-center text-danger">

No Sub-Division Found

</td>

</tr>

<?php } ?>

</tbody>

</table>

<!-- Pagination -->

<?php if($totalPages>1){ ?>

<nav>

<ul class="pagination justify-content-center">

<?php for($i=1;$i<=$totalPages;$i++){ ?>

<li class="page-item <?php if($page==$i) echo 'active'; ?>">

<a class="page-link"

href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">

<?php echo $i; ?>

</a>

</li>

<?php } ?>

</ul>

</nav>

<?php } ?>

</div>

</div>

</div>

</div>

<hr>

<div class="text-center text-muted mt-4 mb-3">

<strong>APDCL Electricity Billing Management System</strong><br>

Internship Demo Project

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>