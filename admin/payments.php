<?php
session_start();

if(!isset($_SESSION['admin'])){
    header("Location: login.php");
    exit();
}

include("../db.php");

$result = mysqli_query($conn,"SELECT * FROM payments ORDER BY payment_date DESC");
?>

<!DOCTYPE html>
<html>
<head>

<title>Payments</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<h2>Payment History</h2>

<a href="dashboard.php" class="btn btn-secondary mb-3">Dashboard</a>

<table class="table table-bordered">

<tr>

<th>ID</th>

<th>Consumer</th>

<th>Amount</th>

<th>Mode</th>

<th>Transaction ID</th>

<th>Date</th>

</tr>

<?php while($row=mysqli_fetch_assoc($result)){ ?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo $row['consumer_no']; ?></td>

<td>₹ <?php echo $row['amount']; ?></td>

<td><?php echo $row['payment_mode']; ?></td>

<td><?php echo $row['transaction_id']; ?></td>

<td><?php echo $row['payment_date']; ?></td>

</tr>

<?php } ?>

</table>

</div>

</body>

</html>