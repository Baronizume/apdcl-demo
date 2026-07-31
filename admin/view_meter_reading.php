<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Meter Reading ID.");
}

$id = (int)$_GET['id'];

$query = mysqli_query($conn,"
SELECT mr.*, u.name
FROM meter_reading mr
LEFT JOIN users u
ON mr.consumer_no = u.consumer_no
WHERE mr.id='$id'
LIMIT 1
");

if(mysqli_num_rows($query)==0){
    die("Meter Reading not found.");
}

$row = mysqli_fetch_assoc($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>View Meter Reading</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#f4f7fb;
    font-family:Segoe UI,sans-serif;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.card-header{
    background:#0d6efd;
    color:#fff;
    font-size:22px;
    font-weight:600;
}

.table th{
    width:35%;
    background:#f8f9fa;
}

</style>

</head>

<body>

<div class="container mt-5">

<div class="card">

<div class="card-header">

<i class="fa fa-eye"></i>

View Meter Reading

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th>Reading ID</th>
<td><?= $row['id']; ?></td>
</tr>

<tr>
<th>Consumer Number</th>
<td><?= htmlspecialchars($row['consumer_no']); ?></td>
</tr>

<tr>
<th>Consumer Name</th>
<td><?= htmlspecialchars($row['name']); ?></td>
</tr>

<tr>
<th>Meter Number</th>
<td><?= htmlspecialchars($row['meter_no']); ?></td>
</tr>

<tr>
<th>Previous Reading</th>
<td><?= number_format($row['previous_reading']); ?></td>
</tr>

<tr>
<th>Current Reading</th>
<td><?= number_format($row['current_reading']); ?></td>
</tr>

<tr>
<th>Units Consumed</th>
<td>

<?php
$units = $row['current_reading'] - $row['previous_reading'];
echo number_format($units);
?>

Units

</td>
</tr>

<tr>
<th>Reading Date</th>
<td><?= date("d M Y",strtotime($row['reading_date'])); ?></td>
</tr>

<?php if(isset($row['meter_status'])){ ?>

<tr>
<th>Meter Status</th>
<td><?= htmlspecialchars($row['meter_status']); ?></td>
</tr>

<?php } ?>

<?php if(isset($row['reader_name'])){ ?>

<tr>
<th>Reader Name</th>
<td><?= htmlspecialchars($row['reader_name']); ?></td>
</tr>

<?php } ?>

<?php if(isset($row['remarks'])){ ?>

<tr>
<th>Remarks</th>
<td><?= nl2br(htmlspecialchars($row['remarks'])); ?></td>
</tr>

<?php } ?>

<tr>
<th>Created At</th>
<td><?= $row['created_at']; ?></td>
</tr>

</table>

<div class="text-center mt-4">

<a href="meter_reading.php" class="btn btn-secondary">
<i class="fa fa-arrow-left"></i>
Back
</a>

<a href="edit_meter_reading.php?id=<?= $row['id']; ?>" class="btn btn-warning">
<i class="fa fa-edit"></i>
Edit
</a>

<button onclick="window.print()" class="btn btn-primary">
<i class="fa fa-print"></i>
Print
</button>

</div>

</div>

</div>

</div>

</body>
</html>