<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

include("../../db.php");

$search = "";

if(isset($_GET['search'])){
    $search = mysqli_real_escape_string($conn,$_GET['search']);
}

if($search!=""){
    $sql="SELECT * FROM complaints
          WHERE consumer_no LIKE '%$search%'
          ORDER BY id DESC";
}else{
    $sql="SELECT * FROM complaints
          ORDER BY id DESC";
}

$result=mysqli_query($conn,$sql);

// Dashboard Counts
$totalComplaints=mysqli_num_rows(mysqli_query($conn,"
SELECT * FROM complaints
"));

$pendingComplaints=mysqli_num_rows(mysqli_query($conn,"
SELECT * FROM complaints
WHERE status='Pending'
"));

$resolvedComplaints=mysqli_num_rows(mysqli_query($conn,"
SELECT * FROM complaints
WHERE status='Resolved'
"));
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Complaint Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f4f6f9;
}

.card{
    border:none;
    border-radius:12px;
    box-shadow:0 4px 10px rgba(0,0,0,.15);
}

.table th{
    background:#0d6efd;
    color:#fff;
}

</style>

</head>

<body>

<div class="container mt-4">

<h2 class="text-primary mb-4">

⚠ Complaint Report

</h2>

<div class="row mb-4">

<div class="col-md-4">

<div class="card bg-primary text-white">

<div class="card-body text-center">

<h5>Total Complaints</h5>

<h2><?php echo $totalComplaints; ?></h2>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card bg-warning">

<div class="card-body text-center">

<h5>Pending</h5>

<h2><?php echo $pendingComplaints; ?></h2>

</div>

</div>

</div>

<div class="col-md-4">

<div class="card bg-success text-white">

<div class="card-body text-center">

<h5>Resolved</h5>

<h2><?php echo $resolvedComplaints; ?></h2>

</div>

</div>

</div>

</div>

<form method="GET">

<div class="row mb-4">

<div class="col-md-10">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Consumer Number"
value="<?php echo htmlspecialchars($search); ?>">

</div>

<div class="col-md-2">

<button class="btn btn-primary w-100">

Search

</button>

</div>

</div>

</form>

<div class="card">

<div class="card-header bg-dark text-white">

Complaint Details

</div>

<div class="card-body">

<table class="table table-bordered table-striped">

<thead>

<tr>

<th>ID</th>

<th>Consumer No</th>

<th>Subject</th>

<th>Description</th>

<th>Status</th>

<th>Date</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($result)>0){

while($row=mysqli_fetch_assoc($result)){

?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['consumer_no']; ?></td>

<td><?php echo $row['subject']; ?></td>

<td><?php echo $row['description']; ?></td>

<td>

<?php

if($row['status']=="Resolved"){

echo "<span class='badge bg-success'>Resolved</span>";

}else{

echo "<span class='badge bg-warning text-dark'>Pending</span>";

}

?>

</td>

<td><?php echo $row['date']; ?></td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="6" class="text-center">

No Complaint Found

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</div>

<div class="mt-4">

<a href="index.php" class="btn btn-secondary">

⬅ Back

</a>

<a href="export_pdf.php?type=complaint" class="btn btn-danger">

📄 Download PDF

</a>

<a href="export_excel.php?type=complaint" class="btn btn-success">

📊 Export Excel

</a>

</div>

</div>

</body>

</html>