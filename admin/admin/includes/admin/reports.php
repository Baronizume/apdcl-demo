<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

// Summary Counts
$totalConsumers = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM users"));
$totalBills = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM bills"));
$totalPayments = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM payments"));
$totalComplaints = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM complaints"));

// Total Revenue
$revenueQuery = mysqli_query($conn, "SELECT SUM(amount) AS revenue FROM payments");
$revenue = mysqli_fetch_assoc($revenueQuery)['revenue'] ?? 0;

// Fetch Reports
$consumerReport = mysqli_query($conn, "SELECT * FROM users ORDER BY id DESC");
$billReport = mysqli_query($conn, "SELECT * FROM bills ORDER BY id DESC");
$paymentReport = mysqli_query($conn, "SELECT * FROM payments ORDER BY id DESC");
$complaintReport = mysqli_query($conn, "SELECT * FROM complaints ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Reports</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f4f7fb;
}

.main-content{
    margin-left:250px;
    padding:30px;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.10);
}

.table{
    font-size:14px;
}


.main-content{
    margin:0;
}


</style>

</head>

<body>



<div class="main-content">

<div class="d-flex justify-content-between mb-4">

<h2>
<i class="bi bi-bar-chart-fill"></i>
Reports
</h2>

<div>

<button onclick="window.print()" class="btn btn-primary">
<i class="bi bi-printer-fill"></i>
Print
</button>

</div>

</div>

<!-- Summary Cards -->

<div class="row g-3">

<div class="col-md-3">

<div class="card bg-primary text-white">

<div class="card-body text-center">

<h5>Consumers</h5>

<h2><?php echo $totalConsumers; ?></h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card bg-success text-white">

<div class="card-body text-center">

<h5>Bills</h5>

<h2><?php echo $totalBills; ?></h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card bg-info text-white">

<div class="card-body text-center">

<h5>Payments</h5>

<h2><?php echo $totalPayments; ?></h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card bg-danger text-white">

<div class="card-body text-center">

<h5>Revenue</h5>

<h2>₹ <?php echo number_format($revenue,2); ?></h2>

</div>

</div>

</div>

</div>

<hr>

<!-- Consumer Report -->

<div class="card mb-4">

<div class="card-header bg-primary text-white">

Consumer Report

</div>

<div class="card-body table-responsive">

<table class="table table-bordered table-hover">

<thead>

<tr>

<th>ID</th>
<th>Consumer No</th>
<th>Name</th>
<th>Mobile</th>

</tr>

</thead>

<tbody>

<?php
while($row = mysqli_fetch_assoc($consumerReport)){

    echo "<pre>";
    print_r($row);
    echo "</pre>";
?>
<tr>
    <td><?= $row['id']; ?></td>
    <td><?= $row['consumer_no']; ?></td>
    <td><?= $row['name']; ?></td>
    <td><?= $row['mobile']; ?></td>
</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<!-- Bill Report -->

<div class="card mb-4">

<div class="card-header bg-success text-white">

Bill Report

</div>

<div class="card-body table-responsive">

<table class="table table-bordered">

<thead>

<tr>

<th>ID</th>
<th>Consumer</th>
<th>Month</th>
<th>Total Bill</th>
<th>Status</th>

</tr>

</thead>

<tbody>

<?php while($bill=mysqli_fetch_assoc($billReport)){ ?>

<tr>

<td><?= $bill['id']; ?></td>
<td><?= $bill['consumer_no']; ?></td>
<td><?= $bill['month']; ?></td>
<td>₹ <?= number_format($bill['total_bill'],2); ?></td>
<td><?= $bill['status']; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<!-- Payment Report -->

<div class="card mb-4">

<div class="card-header bg-info text-white">

Payment Report

</div>

<div class="card-body table-responsive">

<table class="table table-bordered">

<thead>

<tr>

<th>ID</th>
<th>Consumer</th>
<th>Amount</th>
<th>Date</th>

</tr>

</thead>

<tbody>

<?php while($payment=mysqli_fetch_assoc($paymentReport)){ ?>

<tr>

<td><?= $payment['id']; ?></td>
<td><?= $payment['consumer_no']; ?></td>
<td>₹ <?= number_format($payment['amount'],2); ?></td>
<td><?= $payment['payment_date']; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<!-- Complaint Report -->

<div class="card">

<div class="card-header bg-danger text-white">

Complaint Report

</div>

<div class="card-body table-responsive">

<table class="table table-bordered">

<thead>

<tr>

<th>ID</th>
<th>Consumer</th>
<th>Subject</th>
<th>Status</th>

</tr>

</thead>

<tbody>

<?php while($c=mysqli_fetch_assoc($complaintReport)){ ?>

<tr>

<td><?= $c['id']; ?></td>
<td><?= $c['consumer_no']; ?></td>
<td><?= $c['subject']; ?></td>
<td><?= $c['status']; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</body>

</html>