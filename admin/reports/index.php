<?php
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>

<title>Reports</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<h2 class="mb-4">

📊 APDCL Reports

</h2>

<div class="row">

<div class="col-md-3">

<div class="card shadow">

<div class="card-body text-center">

<h4>Revenue Report</h4>

<a href="revenue_report.php" class="btn btn-success">

Open

</a>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card shadow">

<div class="card-body text-center">

<h4>Consumer Report</h4>

<a href="consumer_report.php" class="btn btn-primary">

Open

</a>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card shadow">

<div class="card-body text-center">

<h4>Payment Report</h4>

<a href="payment_report.php" class="btn btn-info">

Open

</a>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card shadow">

<div class="card-body text-center">

<h4>Complaint Report</h4>

<a href="complaint_report.php" class="btn btn-warning">

Open

</a>

</div>

</div>

</div>

</div>

<br>

<a href="../dashboard.php" class="btn btn-dark">

⬅ Back to Dashboard

</a>

</div>

</body>

</html>