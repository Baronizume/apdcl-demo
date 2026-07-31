<?php
session_start();

if(!isset($_SESSION['consumer_no'])){
    header("Location: login.php");
    exit();
}

include("db.php");

$query = mysqli_query($conn,"
SELECT *
FROM notices
ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Notice Board</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f7fb;
}

.card{
margin-top:25px;
border-radius:15px;
box-shadow:0 5px 15px rgba(0,0,0,.15);
}

</style>

</head>

<body>

<div class="container">

<h2 class="mt-4 text-primary">

APDCL Notice Board

</h2>

<?php

if(mysqli_num_rows($query)>0){

while($row=mysqli_fetch_assoc($query)){

?>

<div class="card">

<div class="card-body">

<h4 class="text-primary">

<?= htmlspecialchars($row['title']); ?>

</h4>

<p>

<?= nl2br(htmlspecialchars($row['message'])); ?>

</p>

<small class="text-muted">

<?= $row['created_at']; ?>

</small>

</div>

</div>

<?php

}

}else{

?>

<div class="alert alert-info mt-3">

No notices available.

</div>

<?php

}

?>

<a href="dashboard.php" class="btn btn-primary mt-3">

← Back to Dashboard

</a>

</div>

</body>

</html>