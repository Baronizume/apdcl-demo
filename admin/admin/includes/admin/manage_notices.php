<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/* Add Notice */
if (isset($_POST['add_notice'])) {

    $title = mysqli_real_escape_string($conn, trim($_POST['title']));
    $message = mysqli_real_escape_string($conn, trim($_POST['message']));
$sql = "INSERT INTO Manage_notices (title, message)
VALUES ('$title','$message')";

$query = mysqli_query($conn,
"SELECT * FROM Manage_notices ORDER BY id DESC");

    if (!mysqli_query($conn, $sql)) {
        die("Database Error: " . mysqli_error($conn));
    }

    header("Location: manage_notices.php?success=1");
    exit();
}

/* Delete Notice */
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $delete = "DELETE FROM notices WHERE id=$id";

    if (!mysqli_query($conn, $delete)) {
        die("Database Error: " . mysqli_error($conn));
    }

    header("Location: manage_notices.php");
    exit();
}

/* Fetch Notices */
$query = mysqli_query($conn, "SELECT * FROM notices ORDER BY id DESC");

if (!$query) {
    die("Query Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>Manage Notices</title>

<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f4f7fb;
}

.main-content{
    margin-left:250px;
    padding:30px;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0 5px 15px rgba(0,0,0,.15);
}

</style>

</head>

<body>

<?php
if(file_exists("sidebar.php")){
    include("sidebar.php");
}
?>

<div class="main-content">

<div class="card">

<div class="card-header bg-primary text-white">

<h3>📢 Notice Management</h3>

</div>

<div class="card-body">

<?php if(isset($_GET['success'])){ ?>

<div class="alert alert-success">
Notice Published Successfully.
</div>

<?php } ?>

<form method="POST">

<div class="mb-3">

<label class="form-label">Notice Title</label>

<input
type="text"
name="title"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">Notice Message</label>

<textarea
name="message"
class="form-control"
rows="4"
required></textarea>

</div>

<button
type="submit"
name="add_notice"
class="btn btn-success">

Publish Notice

</button>

</form>

<hr>

<h4>Published Notices</h4>

<div class="table-responsive">

<table class="table table-bordered table-hover">

<thead class="table-dark">

<tr>

<th>ID</th>
<th>Title</th>
<th>Message</th>
<th>Date</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php
if(mysqli_num_rows($query)>0){

while($row=mysqli_fetch_assoc($query)){
?>

<tr>

<td><?php echo $row['id']; ?></td>

<td><?php echo htmlspecialchars($row['title']); ?></td>

<td><?php echo htmlspecialchars($row['message']); ?></td>

<td>
<?php
echo isset($row['created_at']) ? $row['created_at'] : "-";
?>
</td>

<td>

<a
href="manage_notices.php?delete=<?php echo $row['id']; ?>"
class="btn btn-danger btn-sm"
onclick="return confirm('Delete this notice?')">

Delete

</a>

</td>

</tr>

<?php
}

}else{
?>

<tr>

<td colspan="5" class="text-center">

No Notices Found

</td>

</tr>

<?php
}
?>

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