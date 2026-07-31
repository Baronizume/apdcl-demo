<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit();
}

include("../../db.php");

$search = $_GET['search'] ?? "";

if ($search != "") {
    $query = mysqli_query($conn,"
        SELECT *
        FROM users
        WHERE consumer_no LIKE '%$search%'
        OR name LIKE '%$search%'
        OR email LIKE '%$search%'
        ORDER BY id DESC
    ");
} else {
    $query = mysqli_query($conn,"
        SELECT *
        FROM users
        ORDER BY id DESC
    ");
}

$totalConsumers = mysqli_num_rows(mysqli_query($conn,"SELECT * FROM users"));
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Consumer Report</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-4">

<h2 class="text-primary">👥 Consumer Report</h2>

<hr>

<form method="GET">

<div class="row">

<div class="col-md-10">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Consumer Number, Name or Email"
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

<div class="card bg-success text-white shadow">

<div class="card-body text-center">

<h5>Total Consumers</h5>

<h2><?php echo $totalConsumers; ?></h2>

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

<th>Name</th>

<th>Email</th>

<th>Phone</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($query)){ ?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['consumer_no']; ?></td>

<td><?php echo $row['name']; ?></td>

<td><?php echo $row['email']; ?></td>

<td><?php echo $row['phone'] ?? "-"; ?></td>

</tr>

<?php } ?>

</tbody>

</table>

<div class="mt-3">

<a href="index.php" class="btn btn-secondary">
⬅ Back
</a>

<a href="export_excel.php?type=consumer" class="btn btn-success">
    📊 Export Excel
</a>

<a href="export_pdf.php?type=consumer" class="btn btn-danger">
📄 Download PDF
</a>

<a href="exports\_excel.php?type=consumer" class="btn btn-success">
📊 Export Excel
</a>

</div>

</div>

</body>

</html>