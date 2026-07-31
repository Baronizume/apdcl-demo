<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

include("../../db.php");

$search = $_GET['search'] ?? "";

$where = "";

if($search != ""){
    $where = "WHERE consumer_no LIKE '%$search%'";
}

$result = mysqli_query($conn,"
SELECT *
FROM payments
$where
ORDER BY payment_date DESC
");

$totalPayments = mysqli_num_rows(mysqli_query($conn,"
SELECT *
FROM payments
"));

$totalAmountQuery = mysqli_query($conn,"
SELECT SUM(amount) AS total
FROM payments
");

$totalAmount = mysqli_fetch_assoc($totalAmountQuery);
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Payment Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-4">

<h2 class="text-primary">
💳 Payment Report
</h2>

<hr>

<form method="GET">

<div class="row">

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

<hr>

<div class="row">

<div class="col-md-6">

<div class="card bg-success text-white shadow">

<div class="card-body text-center">

<h5>Total Payments</h5>

<h2><?php echo $totalPayments; ?></h2>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card bg-primary text-white shadow">

<div class="card-body text-center">

<h5>Total Collection</h5>

<h2>

₹ <?php echo number_format($totalAmount['total'] ?? 0,2); ?>

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

<?php while($row=mysqli_fetch_assoc($result)){ ?>

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

<?php } ?>

</tbody>

</table>

<div class="mt-3">

<a href="index.php" class="btn btn-secondary">

⬅ Back

</a>

<a href="export_excel.php?type=payment" class="btn btn-success">
    📊 Export Excel
</a>

<a href="export_pdf.php?type=payment" class="btn btn-danger">

📄 Download PDF

</a>


</a>

</div>

</div>

</body>

</html>