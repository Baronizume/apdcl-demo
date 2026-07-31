<?php

session_start();

include("../db.php");


/*=========================================
    ADMIN LOGIN CHECK
=========================================*/

if(!isset($_SESSION['admin'])){

    header("Location: login.php");
    exit();

}


/*=========================================
    VALIDATE COMPLAINT ID
=========================================*/

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){

    die("Invalid Complaint ID");

}


$id = intval($_GET['id']);



/*=========================================
    FETCH COMPLAINT DETAILS
=========================================*/


$query = mysqli_query($conn,"
SELECT 
    c.*,
    a.name AS assigned_admin

FROM complaint c

LEFT JOIN admin a

ON c.assigned_admin_id=a.id

WHERE c.id='$id'

LIMIT 1

");



if(mysqli_num_rows($query)==0){

    die("Complaint Not Found");

}



$complaint=mysqli_fetch_assoc($query);



/*=========================================
    STATUS UPDATE
=========================================*/


if(isset($_POST['update_status'])){


$status=mysqli_real_escape_string(
$conn,
$_POST['status']
);



$remark=mysqli_real_escape_string(
$conn,
$_POST['remark']
);



mysqli_query($conn,"
UPDATE complaint

SET 

status='$status',

remark='$remark'

WHERE id='$id'

");



$_SESSION['success']="Complaint updated successfully.";


header("Location:view_complaint.php?id=".$id);

exit();


}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>View Complaint | APDCL Admin</title>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">


<style>

body{

    background:#f4f7fb;

    font-family:'Segoe UI',sans-serif;

}


.container{

    padding:30px;

}


.card{

    border:none;

    border-radius:18px;

    box-shadow:0 8px 25px rgba(0,0,0,.08);

    margin-bottom:25px;

}


.header-card{

    background:linear-gradient(135deg,#0d47a1,#1976d2);

    color:white;

}


.complaint-id{

    font-size:28px;

    font-weight:700;

}


.info-title{

    font-weight:700;

    color:#0d47a1;

    border-bottom:2px solid #0d6efd;

    padding-bottom:8px;

    margin-bottom:20px;

}


.info-row{

    padding:10px 0;

    border-bottom:1px solid #eee;

}


.label{

    font-weight:600;

    color:#555;

}


.badge{

    font-size:14px;

    padding:8px 15px;

}



.timeline{

    border-left:4px solid #0d6efd;

    padding-left:20px;

}


.timeline-item{

    margin-bottom:20px;

    position:relative;

}


.timeline-item:before{

    content:"";

    width:15px;

    height:15px;

    background:#0d6efd;

    border-radius:50%;

    position:absolute;

    left:-30px;

    top:5px;

}


textarea{

    resize:none;

}



</style>


</head>


<body>


<div class="container">



<!-- SUCCESS MESSAGE -->

<?php

if(isset($_SESSION['success'])){

?>

<div class="alert alert-success">

<i class="bi bi-check-circle-fill"></i>

<?= $_SESSION['success']; ?>

</div>

<?php

unset($_SESSION['success']);

}

?>




<!-- HEADER -->

<div class="card header-card">


<div class="card-body">


<div class="row align-items-center">


<div class="col-md-8">


<h3>

<i class="bi bi-exclamation-triangle-fill"></i>

Complaint Details

</h3>


<div class="complaint-id">

<?= htmlspecialchars($complaint['complaint_id']); ?>

</div>


</div>



<div class="col-md-4 text-md-end mt-3 mt-md-0">


<?php

$status=$complaint['status'];


if($status=="Pending"){

?>

<span class="badge bg-danger">

Pending

</span>

<?php

}

elseif($status=="Assigned"){

?>

<span class="badge bg-info">

Assigned

</span>

<?php

}

elseif($status=="In Progress"){

?>

<span class="badge bg-warning text-dark">

In Progress

</span>

<?php

}

else{

?>

<span class="badge bg-success">

Resolved

</span>

<?php

}

?>


</div>


</div>


</div>


</div>





<div class="row">



<!-- CONSUMER DETAILS -->

<div class="col-lg-6">


<div class="card">


<div class="card-body">


<h5 class="info-title">

<i class="bi bi-person-circle"></i>

Consumer Information

</h5>



<div class="info-row">

<span class="label">Consumer Name :</span>

<?= htmlspecialchars($complaint['consumer_name'] ?? '') ?>

</div>



<div class="info-row">

<span class="label">Consumer No :</span>

<?= htmlspecialchars($complaint['consumer_no']); ?>

</div>



<div class="info-row">

<span class="label">Mobile :</span>

<?= htmlspecialchars($complaint['mobile']); ?>

</div>



<div class="info-row">

<span class="label">Address :</span>

<?= htmlspecialchars($complaint['address']); ?>

</div>


<div class="info-row">

<span class="label">Sub Division :</span>

<?= htmlspecialchars($complaint['sub_division']); ?>

</div>



</div>


</div>


</div>





<!-- COMPLAINT DETAILS -->

<div class="col-lg-6">


<div class="card">


<div class="card-body">


<h5 class="info-title">

<i class="bi bi-file-earmark-text"></i>

Complaint Information

</h5>



<div class="info-row">

<span class="label">Category :</span>

<?= htmlspecialchars($complaint['category']); ?>

</div>



<div class="info-row">

<span class="label">Description :</span>

<br>

<?= nl2br(htmlspecialchars($complaint['description'])); ?>

</div>



<div class="info-row">

<span class="label">Assigned Admin :</span>

<?= !empty($complaint['assigned_admin']) 
? htmlspecialchars($complaint['assigned_admin']) 
: "Not Assigned"; ?>

</div>



<div class="info-row">

<span class="label">Created Date :</span>

<?= date("d M Y h:i A",strtotime($complaint['created_at'])); ?>

</div>


</div>


</div>


</div>



</div>

<!-- STATUS UPDATE SECTION -->

<div class="card">

<div class="card-body">


<h5 class="info-title">

<i class="bi bi-arrow-repeat"></i>

Update Complaint Status

</h5>



<form method="POST">



<div class="row">


<div class="col-md-6 mb-3">


<label class="fw-bold">

Change Status

</label>


<select 
name="status"
class="form-select"
required>


<option value="Pending"
<?= $complaint['status']=="Pending"?'selected':''; ?>>
Pending
</option>


<option value="Assigned"
<?= $complaint['status']=="Assigned"?'selected':''; ?>>
Assigned
</option>


<option value="In Progress"
<?= $complaint['status']=="In Progress"?'selected':''; ?>>
In Progress
</option>


<option value="Resolved"
<?= $complaint['status']=="Resolved"?'selected':''; ?>>
Resolved
</option>


</select>


</div>



<div class="col-md-6 mb-3">


<label class="fw-bold">

Current Status

</label>


<div class="form-control bg-light">


<?= htmlspecialchars($complaint['status']); ?>


</div>


</div>


</div>





<div class="mb-3">


<label class="fw-bold">

Admin Remark

</label>


<textarea

name="remark"

class="form-control"

rows="4"

placeholder="Enter remark or update note...">

<?= htmlspecialchars($complaint['remark'] ?? ''); ?>

</textarea>


</div>




<button

type="submit"

name="update_status"

class="btn btn-primary px-4">


<i class="bi bi-save"></i>

Update Complaint


</button>



</form>


</div>


</div>





<!-- STATUS TIMELINE -->


<div class="card">


<div class="card-body">


<h5 class="info-title">

<i class="bi bi-clock-history"></i>

Complaint Timeline

</h5>



<div class="timeline">


<div class="timeline-item">


<h6>

Complaint Created

</h6>


<p class="text-muted mb-0">

<?= date(
"d M Y h:i A",
strtotime($complaint['created_at'])
); ?>

</p>


</div>





<div class="timeline-item">


<h6>

Current Status

</h6>


<p>

<span class="badge bg-primary">

<?= htmlspecialchars($complaint['status']); ?>

</span>

</p>


</div>





<?php if(!empty($complaint['remark'])){ ?>


<div class="timeline-item">


<h6>

Admin Remark

</h6>


<p class="text-muted">

<?= nl2br(
htmlspecialchars($complaint['remark'])
); ?>

</p>


</div>


<?php } ?>



</div>


</div>


</div>





<!-- ACTION BUTTONS -->


<div class="d-flex justify-content-between mb-4">


<a href="manage_complaint.php"

class="btn btn-secondary">


<i class="bi bi-arrow-left"></i>

Back To Complaints


</a>




<a href="edit_complaint.php?id=<?= $complaint['id']; ?>"

class="btn btn-warning">


<i class="bi bi-pencil-square"></i>

Edit Complaint


</a>


</div>





</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>