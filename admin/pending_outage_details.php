<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================
VALIDATE OUTAGE ID
=========================================*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Outage ID.");
}

$id = (int)$_GET['id'];

/*=========================================
FETCH ONLY ACTIVE OUTAGE
=========================================*/

$stmt = mysqli_prepare($conn,"
SELECT *
FROM outages
WHERE id=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"i",$id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){
    die("Outage not found.");
}

$outage = mysqli_fetch_assoc($result);

/*=========================================
ALLOW ONLY ACTIVE OUTAGES
=========================================*/

if($outage['status']=="Restored"){
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Already Restored</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#eef3f9;
}

.box{
    max-width:650px;
    margin:120px auto;
}

</style>

</head>

<body>

<div class="box">

<div class="card shadow">

<div class="card-body text-center p-5">

<h2 class="text-success mb-4">
✅ Outage Already Restored
</h2>

<p class="lead">

This outage has already been restored.

</p>

<p class="text-muted">

Only pending (Active) outages can be viewed here.

</p>

<a href="resolved_outages.php"
class="btn btn-success">

View Resolved Outages

</a>

<a href="manage_outages.php"
class="btn btn-secondary">

Back

</a>

</div>

</div>

</div>

</body>
</html>
<?php
exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Pending Outage Details</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#eef3f9;
    font-family:Segoe UI;
}

.container{
    max-width:1100px;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.08);
}

.card-header{
    background:#0d6efd;
    color:#fff;
    font-size:22px;
    font-weight:bold;
}

.table th{
    width:280px;
    background:#f7f7f7;
}

</style>

</head>

<body>

<div class="container py-5">

<div class="d-flex justify-content-between mb-4">

<h2>

<i class="fa-solid fa-bolt text-warning"></i>

Pending Outage Details

</h2>

<a href="manage_outages.php"
class="btn btn-secondary">

Back

</a>

</div>

<div class="card">

<div class="card-header">

Outage Information

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th>Outage ID</th>
<td>#<?= $outage['id']; ?></td>
</tr>

<tr>
<th>Status</th>
<td>
<span class="badge bg-danger">
<?= $outage['status']; ?>
</span>
</td>
</tr>

<tr>
<th>District</th>
<td><?= htmlspecialchars($outage['district']); ?></td>
</tr>

<tr>
<th>Zone</th>
<td><?= htmlspecialchars($outage['zone']); ?></td>
</tr>

<tr>
<th>Circle</th>
<td><?= htmlspecialchars($outage['circle']); ?></td>
</tr>

<tr>
<th>Sub Division</th>
<td><?= htmlspecialchars($outage['sub_division']); ?></td>
</tr>

<tr>
<th>Feeder</th>
<td><?= htmlspecialchars($outage['feeder_name']); ?></td>
</tr>

<tr>
<th>Transformer</th>
<td><?= htmlspecialchars($outage['transformer']); ?></td>
</tr>

<tr>
<th>Consumers Affected</th>
<td><?= number_format($outage['consumers_affected']); ?></td>
</tr>

<tr>
<th>Reason</th>
<td><?= nl2br(htmlspecialchars($outage['outage_reason'])); ?></td>
</tr>

<tr>
<th>Start Time</th>
<td><?= date("d M Y h:i A",strtotime($outage['start_time'])); ?></td>
</tr>

<tr>
<th>Estimated Restore</th>
<td>
<?=
!empty($outage['estimated_restore'])
? date("d M Y h:i A",strtotime($outage['estimated_restore']))
: "Not Available";
?>
</td>
</tr>

<tr>
<th>Latitude</th>
<td><?= $outage['latitude']; ?></td>
</tr>

<tr>
<th>Longitude</th>
<td><?= $outage['longitude']; ?></td>
</tr>

</table>

<div class="mt-4">

<a href="edit_outage.php?id=<?= $outage['id']; ?>"
class="btn btn-warning">

<i class="fa fa-edit"></i>

Edit Outage

</a>

<a href="manage_outages.php"
class="btn btn-secondary">

Back

</a>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>