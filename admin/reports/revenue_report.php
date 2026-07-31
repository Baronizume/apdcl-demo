<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

include("../../db.php");

// Date Filter
$from = $_GET['from'] ?? "";
$to   = $_GET['to'] ?? "";

$where = "";

if($from != "" && $to != ""){
    $where = "WHERE payment_date BETWEEN '$from' AND '$to'";
}

// Total Revenue
$revenueQuery = mysqli_query($conn,"
SELECT SUM(amount) AS revenue
FROM payments
$where
");

$revenue = mysqli_fetch_assoc($revenueQuery);

// Total Payments
$totalPayments = mysqli_num_rows(mysqli_query($conn,"
SELECT *
FROM payments
$where
"));

// Payment List
$payments = mysqli_query($conn,"
SELECT *
FROM payments
$where
ORDER BY payment_date DESC
");
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Revenue Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-4">

<h2 class="text-primary">

📊 Revenue Report

</h2>

<hr>

<form method="GET">

<div class="row">

<div class="col-md-4">

<label>From Date</label>

<input
type="date"
name="from"
class="form-control"
value="<?php echo $from; ?>">

</div>

<div class="col-md-4">

<label>To Date</label>

<input
type="date"
name="to"
class="form-control"
value="<?php echo $to; ?>">

</div>

<div class="col-md-4">

<label>&nbsp;</label>

<button class="btn btn-primary w-100">

Generate Report

</button>

</div>

</div>

</form>

<hr>

<div class="row">

<div class="col-md-6">

<div class="card bg-success text-white shadow">

<div class="card-body text-center">

<h5>Total Revenue</h5>

<h2>

₹ <?php echo number_format($revenue['revenue'] ?? 0,2); ?>

</h2>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card bg-info text-white shadow">

<div class="card-body text-center">

<h5>Total Payments</h5>

<h2>

<?php echo $totalPayments; ?>

</h2>

</div>

</div>

</div>

</div>

<hr>

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>ID</th>

<th>Consumer No</th>

<th>Amount</th>

<th>Payment Date</th>

<th>Status</th>

</tr>

</thead>

<tbody>

<?php

while($row = mysqli_fetch_assoc($payments)){

?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['consumer_no']; ?></td>

<td>

₹ <?php echo number_format($row['amount'],2); ?>

</td>

<td><?php echo $row['payment_date']; ?></td>

<td>

<span class="badge bg-success">

Paid

</span>

</td>

</tr>

<?php
}
?>

</tbody>

</table>

<div class="mt-3">

<a href="index.php" class="btn btn-secondary">

⬅ Back

</a>

<a href="export_excel.php?type=revenue" class="btn btn-success">
    📊 Export Excel
</a>

<a href="export_pdf.php" class="btn btn-danger">

📄 Download PDF

</a>

<a href="export_excel.php" class="btn btn-success">

📊 Export Excel

</a>

</div>

</div>

</body>

</html>