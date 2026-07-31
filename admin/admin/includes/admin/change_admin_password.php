<?php
session_start();
include("../db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['admin'];

if (isset($_POST['change_password'])) {

    $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    $query = mysqli_query($conn, "SELECT * FROM admin WHERE username='$username'");
    $admin = mysqli_fetch_assoc($query);

    if (!$admin) {
        $error = "Admin account not found.";
    } elseif ($admin['password'] != $current_password) {
        $error = "Current password is incorrect.";
    } elseif ($new_password != $confirm_password) {
        $error = "New password and Confirm password do not match.";
    } else {

        mysqli_query($conn, "
        UPDATE admin
        SET password='$new_password'
        WHERE username='$username'
        ");

        $success = "Password changed successfully.";
    }
}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Change Admin Password</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

<style>

body{

background:#f4f7fb;

font-family:Arial;

}

.card{

max-width:550px;

margin:50px auto;

border:none;

border-radius:15px;

box-shadow:0 5px 20px rgba(0,0,0,.15);

}

</style>

</head>

<body>

<div class="container">

<div class="card">

<div class="card-header bg-danger text-white">

<h3>

<i class="fas fa-key"></i>

Change Admin Password

</h3>

</div>

<div class="card-body">

<?php
if(isset($error)){
?>
<div class="alert alert-danger">
<?= $error; ?>
</div>
<?php
}
?>

<?php
if(isset($success)){
?>
<div class="alert alert-success">
<?= $success; ?>
</div>
<?php
}
?>

<form method="POST">

<div class="mb-3">

<label>Current Password</label>

<input
type="password"
name="current_password"
class="form-control"
required>

</div>

<div class="mb-3">

<label>New Password</label>

<input
type="password"
name="new_password"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Confirm New Password</label>

<input
type="password"
name="confirm_password"
class="form-control"
required>

</div>

<button
type="submit"
name="change_password"
class="btn btn-danger">

<i class="fas fa-save"></i>

Update Password

</button>

<a href="settings.php" class="btn btn-secondary">

Back

</a>

</form>

</div>

</div>

</div>

</body>

</html>