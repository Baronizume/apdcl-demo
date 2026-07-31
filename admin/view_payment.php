<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Payment ID.");
}

$id = intval($_GET['id']);

$query = mysqli_query($conn, "SELECT * FROM payments WHERE id='$id'");

if (mysqli_num_rows($query) == 0) {
    die("Payment record not found.");
}

$payment = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>View Payment | APDCL</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#f4f7fc;
    font-family:'Segoe UI',sans-serif;
}

.navbar{
    background:#0d6efd;
}

.logo{
    width:55px;
    height:55px;
    border-radius:50%;
    background:#fff;
    padding:5px;
    object-fit:contain;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,.12);
}

.card-header{
    background:#0d6efd;
    color:#fff;
    border-radius:15px 15px 0 0!important;
}

.table td{
    padding:14px;
}

.label{
    font-weight:600;
    color:#555;
    width:220px;
}

</style>

</head>

<body>

<nav class="navbar navbar-dark">

<div class="container">

<div class="d-flex align-items-center">

<img src="../assets/images/logo-circle.png" class="logo me-3">

<div>

<h4 class="mb-0 text-white">

APDCL Admin Panel

</h4>

<small class="text-light">

Payment Details

</small>

</div>

</div>

</div>

</nav>

<div class="container py-5">

<div class="card">

<div class="card-header">

<h4 class="mb-0">

<i class="bi bi-credit-card-fill"></i>

Payment Information

</h4>

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<td class="label">Payment ID</td>

<td><?= $payment['id']; ?></td>

</tr>

<tr>

<td class="label">Consumer Number</td>

<td><?= htmlspecialchars($payment['consumer_no']); ?></td>

</tr>

<tr>

<td class="label">Bill ID</td>

<td><?= htmlspecialchars($payment['bill_no']); ?></td>

</tr>

<tr>

<td class="label">Amount Paid</td>

<td class="fw-bold text-success">

₹<?= number_format($payment['amount'],2); ?>

</td>

</tr>

<tr>

<td class="label">Payment Mode</td>

<td>

<span class="badge bg-primary">

<?= htmlspecialchars($payment['bill_no']); ?>

</span>

</td>

</tr>

<tr>

<td class="label">Transaction ID</td>

<td>

<?= htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?>

</td>

</tr>

<tr>

<td class="label">Payment Status</td>

<td>

<span class="badge bg-success">

Successful

</span>

</td>

</tr>

<tr>

<td class="label">Payment Date</td>

<td>

<?= date("d M Y h:i A",strtotime($payment['payment_date'])); ?>

</td>

</tr>

</table>

<div class="text-end mt-4">

<a href="manage_payments.php" class="btn btn-secondary">

<i class="bi bi-arrow-left-circle"></i>

Back

</a>

<button onclick="window.print();" class="btn btn-success">

<i class="bi bi-printer-fill"></i>

Print

</button>

</div>

</div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>