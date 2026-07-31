<?php
session_start();
include("../db.php");

// ---------------- ADD CONSUMER ----------------
if(isset($_POST['add_consumer'])){

    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $consumer_no = mysqli_real_escape_string($conn,$_POST['consumer_no']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $mobile = mysqli_real_escape_string($conn,$_POST['mobile']);
    $address = mysqli_real_escape_string($conn,$_POST['address']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    mysqli_query($conn,"INSERT INTO users
    (name,consumer_no,email,mobile,address,password,role)
    VALUES
    ('$name','$consumer_no','$email','$mobile','$address','$password','consumer')");

    header("Location: manage_consumers.php");
    exit();
}

// ---------------- UPDATE CONSUMER ----------------
if(isset($_POST['update_consumer'])){

    $id = intval($_POST['id']);

    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $mobile = mysqli_real_escape_string($conn,$_POST['mobile']);
    $address = mysqli_real_escape_string($conn,$_POST['address']);

    mysqli_query($conn,"
    UPDATE users
    SET
    name='$name',
    email='$email',
    mobile='$mobile',
    address='$address'
    WHERE id='$id'
    ");

    header("Location: manage_consumers.php");
    exit();
}

// ---------------- DELETE ----------------
if(isset($_GET['delete'])){

    $id = intval($_GET['delete']);

    mysqli_query($conn,"
    DELETE FROM users
    WHERE id='$id'
    ");

    header("Location: manage_consumers.php");
    exit();
}

// ---------------- SEARCH ----------------
$where="WHERE role='consumer'";

if(isset($_GET['search']) && $_GET['search']!=""){

    $search=mysqli_real_escape_string($conn,$_GET['search']);

    $where.=" AND (
    consumer_no LIKE '%$search%'
    OR
    name LIKE '%$search%'
    )";
}

$query=mysqli_query($conn,"
SELECT *
FROM users
$where
ORDER BY id DESC
");
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="utf-8">

<title>Manage Consumers</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
background:#f4f7fb;
}

.card{
margin-top:25px;
border:none;
border-radius:15px;
box-shadow:0 5px 20px rgba(0,0,0,.15);
}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header bg-primary text-white">

<h3>Manage Consumers</h3>

</div>

<div class="card-body">

<!-- Add Consumer -->

<form method="POST">

<div class="row">

<div class="col-md-3">
<input type="text" name="name" class="form-control" placeholder="Name" required>
</div>

<div class="col-md-2">
<input type="text" name="consumer_no" class="form-control" placeholder="Consumer No" required>
</div>

<div class="col-md-3">
<input type="email" name="email" class="form-control" placeholder="Email">
</div>

<div class="col-md-2">
<input type="text" name="mobile" class="form-control" placeholder="Mobile">
</div>

<div class="col-md-2">
<input type="password" name="password" class="form-control" placeholder="Password" required>
</div>

<div class="col-md-12 mt-3">
<input type="text" name="address" class="form-control" placeholder="Address">
</div>

<div class="col-md-12 mt-3">
<button class="btn btn-success" name="add_consumer">
Add Consumer
</button>
</div>

</div>

</form>

<hr>

<!-- Search -->

<form method="GET">

<div class="row mb-3">

<div class="col-md-10">

<input
type="text"
name="search"
class="form-control"
placeholder="Search Consumer Number or Name">

</div>

<div class="col-md-2">

<button class="btn btn-primary w-100">

Search

</button>

</div>

</div>

</form>

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Consumer No</th>
<th>Name</th>
<th>Email</th>
<th>Mobile</th>
<th>Address</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php while($row=mysqli_fetch_assoc($query)){ ?>

<tr>

<form method="POST">

<td>

<?= $row['id']; ?>

<input
type="hidden"
name="id"
value="<?= $row['id']; ?>">

</td>

<td>

<?= htmlspecialchars($row['consumer_no']); ?>

</td>

<td>

<input
type="text"
name="name"
value="<?= htmlspecialchars($row['name']); ?>"
class="form-control">

</td>

<td>

<input
type="email"
name="email"
value="<?= htmlspecialchars($row['email']); ?>"
class="form-control">

</td>

<td>

<input
type="text"
name="mobile"
value="<?= htmlspecialchars($row['mobile']); ?>"
class="form-control">

</td>

<td>

<input
type="text"
name="address"
value="<?= htmlspecialchars($row['address']); ?>"
class="form-control">

</td>

<td>

<button
class="btn btn-success btn-sm"
name="update_consumer">

Update

</button>

<a
href="?delete=<?= $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this consumer?')">

Delete

</a>

</td>

</form>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<a href="dashboard.php" class="btn btn-secondary mt-3">

← Back to Dashboard

</a>

</div>

</div>

</div>

</body>

</html>