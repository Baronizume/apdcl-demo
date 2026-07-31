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

$query = mysqli_query($conn,"
SELECT *
FROM complaints
$where
ORDER BY id DESC
");

$totalComplaints = mysqli_num_rows(mysqli_query($conn,"
SELECT * FROM complaints
"));

$pendingComplaints = mysqli_num_rows(mysqli_query($conn,"
SELECT * FROM complaints
WHERE status='Pending'
"));

$resolvedComplaints = mysqli_num_rows(mysqli_query($conn,"
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

</head>

<body class="bg-light">

<div class="container mt-4">

<h2 class="text-primary">⚠ Complaint Report</h2>

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

<div class="col-md-4">

<div class="card bg-danger text-white">

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

<hr>

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Consumer No</th>
<th>Subject</th>
<th>Status</th>
<th>Date</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($query)){ ?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['consumer_no']; ?></td>

<td><?php echo $row['subject']; ?></td>

<td><?php echo $row['status']; ?></td>

<td><?php echo $row['date']; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

<div class="mt-3">

<a href="index.php" class="btn btn-secondary">
⬅ Back
</a>

<a href="export_excel.php?type=complaint" class="btn btn-success">
    📊 Export Excel
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