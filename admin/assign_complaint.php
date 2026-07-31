<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=============================
GET COMPLAINT ID
=============================*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Complaint ID.");
}

$id = (int)$_GET['id'];

/*=============================
FETCH COMPLAINT
=============================*/

$complaintQuery = mysqli_query($conn,"
SELECT *
FROM complaint
WHERE id='$id'
LIMIT 1
");

if(mysqli_num_rows($complaintQuery)==0){
    die("Complaint not found.");
}

$complaint = mysqli_fetch_assoc($complaintQuery);

/*=============================
FETCH ACTIVE ADMINS / SDEs
=============================*/

$adminQuery = mysqli_query($conn,"
SELECT
id,
name,
role,
zone,
circle,
sub_division
FROM admin
WHERE status='Active'
ORDER BY role,name
");

/*=============================
UPDATE ASSIGNMENT
=============================*/

$success = "";
$error = "";

if(isset($_POST['assign'])){

    $assigned_admin_id = (int)$_POST['assigned_admin_id'];

    $status = mysqli_real_escape_string(
        $conn,
        $_POST['status']
    );

    $remarks = mysqli_real_escape_string(
        $conn,
        trim($_POST['remarks'])
    );

    /* Fetch selected admin */

    $selectedAdmin = mysqli_query($conn,"
    SELECT *
    FROM admin
    WHERE id='$assigned_admin_id'
    LIMIT 1
    ");

    if(mysqli_num_rows($selectedAdmin)==1){

        $adminData = mysqli_fetch_assoc($selectedAdmin);

        $assigned_to = mysqli_real_escape_string(
            $conn,
            $adminData['name']
        );

        $update = mysqli_query($conn,"
        UPDATE complaint
        SET

        assigned_admin_id='$assigned_admin_id',

        assigned_to='$assigned_to',

        assigned_date=NOW(),

        status='$status',

        remarks='$remarks',

        updated_at=NOW()

        WHERE id='$id'
        ");

        if($update){

            $success = "Complaint assigned successfully.";

            // Refresh complaint details

            $complaintQuery = mysqli_query($conn,"
            SELECT *
            FROM complaint
            WHERE id='$id'
            LIMIT 1
            ");

            $complaint = mysqli_fetch_assoc($complaintQuery);

        }else{

            $error = mysqli_error($conn);

        }

    }else{

        $error = "Please select a valid administrator.";

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Assign Complaint</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<style>

body{
    background:#eef3f9;
    font-family:'Segoe UI',Tahoma,sans-serif;
}

/* Page Header */

.card-header{
    font-size:18px;
    font-weight:600;
}

/* Main Card */

.card{
    border:none;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 12px 30px rgba(0,0,0,.08);
}

/* Inner Cards */

.card.border-primary,
.card.border-success{
    border:0!important;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

/* Table */

.table th{
    background:#f7f9fc;
    width:38%;
    color:#0d47a1;
    font-weight:600;
}

.table td{
    vertical-align:middle;
}

/* Labels */

label{
    font-weight:600;
    color:#0d47a1;
}

/* Inputs */

.form-control,
.form-select{

    border-radius:10px;

    padding:11px;

    border:1px solid #ced4da;

}

.form-control:focus,
.form-select:focus{

    border-color:#0d6efd;

    box-shadow:0 0 0 .15rem rgba(13,110,253,.20);

}

/* Buttons */

.btn{

    border-radius:10px;

    padding:10px 18px;

    font-weight:600;

}

/* Success */

.alert-success{

    border-radius:12px;

}

/* Error */

.alert-danger{

    border-radius:12px;

}

/* Badges */

.badge{

    font-size:13px;

    padding:8px 14px;

    border-radius:20px;

}

/* Complaint Description */

.table td{

    word-break:break-word;

}

/* Header */

.card-header.bg-primary{

    background:linear-gradient(90deg,#0d47a1,#1976d2)!important;

}

.card-header.bg-success{

    background:linear-gradient(90deg,#2e7d32,#43a047)!important;

}

/* Hover Effect */

.card:hover{

    transform:translateY(-3px);

    transition:.3s;

}

/* Save Button */

.btn-success{

    font-size:16px;

    letter-spacing:.5px;

}

/* Mobile */

@media(max-width:768px){

.card{

margin-bottom:20px;

}

.table th{

width:45%;

}

}

</style>

<body>

<div class="container py-5">

<div class="row justify-content-center">

<div class="col-xl-11">

<div class="card shadow-lg border-0">

<div class="card-header bg-primary text-white">

<div class="d-flex justify-content-between align-items-center">

<h3 class="mb-0">

<i class="bi bi-person-check-fill"></i>

Assign Complaint

</h3>

<a href="complaint.php"
class="btn btn-light btn-sm">

<i class="bi bi-arrow-left"></i>

Back

</a>

</div>

</div>

<div class="card-body">

<?php if($success!=""){ ?>

<div class="alert alert-success">

<i class="bi bi-check-circle-fill"></i>

<?= $success ?>

</div>

<?php } ?>

<?php if($error!=""){ ?>

<div class="alert alert-danger">

<i class="bi bi-exclamation-triangle-fill"></i>

<?= $error ?>

</div>

<?php } ?>

<form method="POST">

<div class="row">

<!-- Complaint Details -->

<div class="col-lg-6">

<div class="card border-primary mb-4">

<div class="card-header bg-primary text-white">

Complaint Details

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>
<th width="40%">Complaint ID</th>
<td><?= htmlspecialchars($complaint['complaint_id']) ?></td>
</tr>

<tr>
<th>Consumer No</th>
<td><?= htmlspecialchars($complaint['consumer_no']) ?></td>
</tr>

<tr>
<th>Name</th>
<td><?= htmlspecialchars($complaint['consumer_name']) ?></td>
</tr>

<tr>
<th>Mobile</th>
<td><?= htmlspecialchars($complaint['mobile']) ?></td>
</tr>

<tr>
<th>Category</th>
<td><?= htmlspecialchars($complaint['category']) ?></td>
</tr>

<tr>
<th>Subject</th>
<td><?= htmlspecialchars($complaint['subject']) ?></td>
</tr>

<tr>
<th>Description</th>
<td><?= nl2br(htmlspecialchars($complaint['description'])) ?></td>
</tr>

<tr>
<th>Address</th>
<td><?= htmlspecialchars($complaint['address']) ?></td>
</tr>

<tr>
<th>Priority</th>
<td>

<?php

if($complaint['priority']=="High"){

echo '<span class="badge bg-danger">High</span>';

}elseif($complaint['priority']=="Medium"){

echo '<span class="badge bg-warning text-dark">Medium</span>';

}else{

echo '<span class="badge bg-success">Low</span>';

}

?>

</td>

</tr>

</table>

</div>

</div>

</div>

<!-- Assignment -->

<div class="col-lg-6">

<div class="card border-success">

<div class="card-header bg-success text-white">

Assign Complaint

</div>

<div class="card-body">

<div class="mb-3">

<label class="form-label">

Assign To

</label>

<select
name="assigned_admin_id"
class="form-select"
required>

<option value="">Select Administrator</option>

<?php while($admin=mysqli_fetch_assoc($adminQuery)){ ?>

<option
value="<?= $admin['id'] ?>"

<?= ($complaint['assigned_admin_id']==$admin['id'])?'selected':''; ?>

>

<?= htmlspecialchars($admin['name']) ?>

(<?= htmlspecialchars($admin['role']) ?>)

</option>

<?php } ?>

</select>

</div>

<div class="mb-3">

<label class="form-label">

Status

</label>

<select
name="status"
class="form-select">

<option value="Pending"
<?= ($complaint['status']=="Pending")?'selected':''; ?>>

Pending

</option>

<option value="In Progress"
<?= ($complaint['status']=="In Progress")?'selected':''; ?>>

In Progress

</option>

<option value="Resolved"
<?= ($complaint['status']=="Resolved")?'selected':''; ?>>

Resolved

</option>

</select>

</div>

<div class="mb-3">

<label class="form-label">

Remarks

</label>

<textarea
name="remarks"
rows="6"
class="form-control"
placeholder="Write remarks..."><?= htmlspecialchars($complaint['remarks']) ?></textarea>

</div>

<button
type="submit"
name="assign"
class="btn btn-success w-100">

<i class="bi bi-check-circle-fill"></i>

Save Assignment

</button>

</div>

</div>

</div>

</div>

</form>

</div>

</div>

</div>

</div>

</div>

<!-- ==========================================
COMPLAINT LOCATION
========================================== -->

<div class="container mt-4">

<div class="card shadow border-0">

<div class="card-header bg-dark text-white">

<i class="bi bi-geo-alt-fill"></i>

Complaint Location

</div>

<div class="card-body">

<?php

if(!empty($complaint['latitude']) && !empty($complaint['longitude'])){

?>

<div id="complaintMap"
style="height:350px;border-radius:10px;"></div>

<?php

}else{

echo "<div class='alert alert-warning'>
No location available for this complaint.
</div>";

}

?>

</div>

</div>

</div>

<!-- ==========================================
COMPLAINT PHOTO
========================================== -->

<div class="container mt-4">

<div class="card shadow border-0">

<div class="card-header bg-info text-white">

<i class="bi bi-camera-fill"></i>

Complaint Photo

</div>

<div class="card-body text-center">

<?php

if(!empty($complaint['photo'])){

?>

<img
src="../uploads/complaints/<?= htmlspecialchars($complaint['photo']) ?>"
class="img-fluid rounded shadow"
style="max-height:400px;">

<?php

}else{

echo "<div class='alert alert-secondary'>
No photo uploaded.
</div>";

}

?>

</div>

</div>

</div>

<!-- ==========================================
ASSIGNMENT DETAILS
========================================== -->

<div class="container mt-4 mb-5">

<div class="card shadow border-0">

<div class="card-header bg-secondary text-white">

<i class="bi bi-person-badge-fill"></i>

Current Assignment

</div>

<div class="card-body">

<table class="table table-bordered">

<tr>

<th width="30%">Assigned To</th>

<td>

<?= htmlspecialchars($complaint['assigned_to'] ?: "Not Assigned") ?>

</td>

</tr>

<tr>

<th>Status</th>

<td>

<?= htmlspecialchars($complaint['status']) ?>

</td>

</tr>

<tr>

<th>Assigned Date</th>

<td>

<?= !empty($complaint['assigned_date']) ? date("d-m-Y h:i A",strtotime($complaint['assigned_date'])) : "-" ?>

</td>

</tr>

<tr>

<th>Updated</th>

<td>

<?= !empty($complaint['updated_at']) ? date("d-m-Y h:i A",strtotime($complaint['updated_at'])) : "-" ?>

</td>

</tr>

</table>

</div>

</div>

</div>

<!-- Leaflet -->

<link rel="stylesheet"
href="https://unpkg.com/leaflet/dist/leaflet.css">

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>

<?php if(!empty($complaint['latitude']) && !empty($complaint['longitude'])){ ?>

var map = L.map('complaintMap').setView(
[
<?= $complaint['latitude'] ?>,
<?= $complaint['longitude'] ?>
],
16
);

L.tileLayer(
'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
{
maxZoom:19
}
).addTo(map);

L.marker([
<?= $complaint['latitude'] ?>,
<?= $complaint['longitude'] ?>
]).addTo(map)

.bindPopup("<b><?= htmlspecialchars($complaint['consumer_no']) ?></b><br><?= htmlspecialchars($complaint['category']) ?>")

.openPopup();

<?php } ?>

/* Confirm Save */

document.querySelector("form").addEventListener("submit",function(e){

if(!confirm("Assign this complaint?")){

e.preventDefault();

}

});

</script>

</html>

</body>