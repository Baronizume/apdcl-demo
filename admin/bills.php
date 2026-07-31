<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$sql = "SELECT
            bills.*,
            users.name,
            users.mobile,
            users.category
        FROM bills
        LEFT JOIN users
        ON bills.consumer_no = users.consumer_no
        ORDER BY bills.id DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Manage Bills</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f4f7fc;
    font-family:'Segoe UI',sans-serif;
}

.card{
    margin-top:40px;
    border:none;
    border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,.12);
}

.card-header{
    background:#0d6efd;
    color:#fff;
}

.table th{
    background:#0d6efd;
    color:#fff;
    text-align:center;
}

.table td{
    vertical-align:middle;
    text-align:center;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header d-flex justify-content-between align-items-center">

<h3 class="mb-0">

<i class="bi bi-receipt-cutoff"></i>

Manage Bills

</h3>

<a href="dashboard.php" class="btn btn-light">

<i class="bi bi-arrow-left"></i>

Dashboard

</a>

</div>

<div class="card-body">

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>ID</th>

<th>Consumer No</th>

<th>Consumer Name</th>

<th>Mobile</th>

<th>Category</th>

<th>Month</th>

<th>Units</th>

<th>Total Bill</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php
if(mysqli_num_rows($result)>0){

while($row=mysqli_fetch_assoc($result)){
?>

<tr>

<td><?= $row['id']; ?></td>

<td><?= htmlspecialchars($row['consumer_no']); ?></td>

<td><?= htmlspecialchars($row['name']); ?></td>

<td><?= htmlspecialchars($row['mobile']); ?></td>

<td><?= htmlspecialchars($row['category']); ?></td>

<td><?= htmlspecialchars($row['month']); ?></td>

<td><?= htmlspecialchars($row['units']); ?></td>

<td class="fw-bold text-success">

₹<?= number_format($row['total_bill'],2); ?>

</td>

<td>

<?php

if($row['status']=="Paid"){

echo '<span class="badge bg-success">Paid</span>';

}elseif($row['status']=="Pending"){

echo '<span class="badge bg-warning text-dark">Pending</span>';

}else{

echo '<span class="badge bg-secondary">'.htmlspecialchars($row['status']).'</span>';

}

?>

</td>

</tr>

<?php
}

}else{
?>

<tr>

<td colspan="9" class="text-center text-muted">

No Bills Found

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>