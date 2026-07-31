<?php
session_start();

include("../db.php");

// Delete Payment
if(isset($_GET['delete'])){

    $id = intval($_GET['delete']);

    mysqli_query($conn,"DELETE FROM payments WHERE id='$id'");

    header("Location: manage_payments.php");
    exit();
}

// Search
$search = "";

$sql = "SELECT * FROM payments";

if(isset($_GET['search']) && $_GET['search']!=""){

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $sql .= " WHERE consumer_no LIKE '%$search%'";
}

$sql .= " ORDER BY id DESC";

$result = mysqli_query($conn,$sql);

?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Manage Payments</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#f4f7fb;
}

.card{
    margin-top:30px;
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

.table th{
    background:#0d6efd;
    color:white;
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header bg-success text-white">

<h3 class="mb-0">

<i class="fas fa-credit-card"></i>

Manage Payments

</h3>

</div>

<div class="card-body">

<form method="GET" class="row mb-4">

<div class="col-md-10">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Consumer Number"
value="<?= htmlspecialchars($search); ?>">

</div>

<div class="col-md-2">

<button class="btn btn-primary w-100">

<i class="fas fa-search"></i>

Search

</button>

</div>

</form>

<div class="table-responsive">

<table class="table table-bordered table-hover align-middle">

<thead>

<tr>

<th>ID</th>

<th>Consumer No</th>

<th>Bill ID</th>

<th>Amount</th>

<th>Payment Method</th>

<th>Payment Date</th>

<th width="170">Action</th>

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

<td><?= htmlspecialchars($row['bill_id']); ?></td>

<td>₹<?= number_format($row['amount'],2); ?></td>

<td><?= htmlspecialchars($row['payment_method']); ?></td>

<td><?= htmlspecialchars($row['payment_date']); ?></td>

<td>

<a href="../consumer/payment_history.php"
class="btn btn-info btn-sm">

<i class="fas fa-eye"></i>

View

</a>

<a href="?delete=<?= $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this payment?');">

<i class="fas fa-trash"></i>

Delete

</a>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="7" class="text-center">

No Payment Records Found

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

<a href="dashboard.php" class="btn btn-secondary mt-3">

<i class="fas fa-arrow-left"></i>

Back Dashboard

</a>

</div>

</div>

</div>

</body>

</html>