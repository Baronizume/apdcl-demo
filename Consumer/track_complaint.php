<?php
session_start();

require_once("../db.php");


/*========================================
LOGIN CHECK
========================================*/

if(!isset($_SESSION['consumer'])){

    header("Location: login.php");
    exit();

}


$consumer_no = $_SESSION['consumer'];

if(!isset($_GET['id'])){

    $latest = mysqli_query($conn,"
    SELECT complaint_id
    FROM complaint
    WHERE consumer_no='$consumer_no'
    ORDER BY id DESC
    LIMIT 1
    ");

    $row=mysqli_fetch_assoc($latest);

    if($row){

        header("Location: view_complaint.php?id=".$row['complaint_id']);
        exit();

    }else{

        die("No complaint found.");

    }

}

/*========================================
GET COMPLAINT ID
========================================*/

if(!isset($_GET['id']) || empty($_GET['id'])){

    die("Invalid Complaint ID.");

}


$complaint_id = $_GET['id'];

/*========================================
LOAD CONSUMER
========================================*/

$stmt=mysqli_prepare($conn,"
SELECT *
FROM users
WHERE consumer_no=?
LIMIT 1
");


mysqli_stmt_bind_param(
    $stmt,
    "s",
    $consumer_no
);


mysqli_stmt_execute($stmt);


$userResult=mysqli_stmt_get_result($stmt);


if(mysqli_num_rows($userResult)==0){

    die("Consumer not found.");

}


$user=mysqli_fetch_assoc($userResult);

$name = $user['name'] ?? "Consumer";

/*========================================
LOAD COMPLAINT
========================================*/

$complaint_id = trim($_GET['id']);
$consumer_no  = trim($consumer_no);


$stmt = mysqli_prepare($conn,"
SELECT *
FROM complaint
WHERE TRIM(complaint_id)=?
AND TRIM(consumer_no)=?
LIMIT 1
");


if(!$stmt){

    die("SQL Error : ".mysqli_error($conn));

}


mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $complaint_id,
    $consumer_no
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


if(mysqli_num_rows($result)==0){

    echo "<pre>";

    echo "Searching Complaint ID : ".$complaint_id."\n";
    echo "Searching Consumer No : ".$consumer_no."\n\n";


    $check=mysqli_query($conn,"
    SELECT complaint_id,consumer_no
    FROM complaint
    WHERE complaint_id='$complaint_id'
    ");


    print_r(mysqli_fetch_assoc($check));


    echo "</pre>";

    exit();

}


$complaint=mysqli_fetch_assoc($result);

/*=========================================================
    EDIT PERMISSION
=========================================================*/

$editable = true;

/*=========================================================
    UPDATE COMPLAINT
=========================================================*/

if(isset($_POST['update']) && $editable){

    $category    = trim($_POST['category']);
    $priority    = trim($_POST['priority']);
    $subject     = trim($_POST['subject']);
    $description = trim($_POST['description']);
    $address     = trim($_POST['address']);
    $latitude    = trim($_POST['latitude']);
    $longitude   = trim($_POST['longitude']);

    $photo = $complaint['photo'];

    /* PHOTO UPLOAD */

    if(isset($_FILES['photo']) && $_FILES['photo']['error']==0){

        $folder="../uploads/complaint/";

        if(!is_dir($folder)){
            mkdir($folder,0777,true);
        }

        $extension=strtolower(pathinfo(
            $_FILES['photo']['name'],
            PATHINFO_EXTENSION
        ));

        $allowed=["jpg","jpeg","png","webp"];

        if(in_array($extension,$allowed)){

            $photo=uniqid("CMP_").".".$extension;

            move_uploaded_file(
                $_FILES['photo']['tmp_name'],
                $folder.$photo
            );

        }

    }

    $update=mysqli_prepare($conn,"
    UPDATE complaint
    SET
        category=?,
        priority=?,
        subject=?,
        description=?,
        address=?,
        latitude=?,
        longitude=?,
        photo=?
    WHERE id=?
    ");

    mysqli_stmt_bind_param(
        $update,
        "ssssssssi",
        $category,
        $priority,
        $subject,
        $description,
        $address,
        $latitude,
        $longitude,
        $photo,
        $id
    );

    if(mysqli_stmt_execute($update)){

        $_SESSION['success']="Complaint updated successfully.";

        header("Location: track_complaint.php?id=".$id);

        exit();

    }else{

        $error=mysqli_error($conn);

    }

}

/*=========================================================
    SUCCESS MESSAGE
=========================================================*/

if(isset($_SESSION['success'])){

    $success=$_SESSION['success'];

    unset($_SESSION['success']);

}

/*=========================================================
    STATUS BADGE
=========================================================*/

switch($complaint['status']){

    case "Pending":
        $badge="warning";
        break;

    case "Assigned":
        $badge="info";
        break;

    case "In Progress":
        $badge="primary";
        break;

    case "Resolved":
        $badge="success";
        break;

    case "Rejected":
        $badge="danger";
        break;

    default:
        $badge="secondary";
}

/*=========================================================
    PHOTO
=========================================================*/

$photoPath="../assets/images/no-image.jpg";

if(
    !empty($complaint['photo']) &&
    file_exists("../uploads/complaint/".$complaint['photo'])
){
    $photoPath="../uploads/complaint/".$complaint['photo'];
}

/*=========================================================
    COMPLAINT TIMELINE
=========================================================*/

$timeline = [];

$timeline[] = [
    "title" => "Complaint Registered",
    "date"  => $complaint['created_at'] ?? "",
    "icon"  => "bi bi-file-earmark-plus-fill",
    "color" => "primary"
];

if (!empty($complaint['assigned_to'])) {

    $timeline[] = [
        "title" => "Assigned to Officer: ".$complaint['assigned_to'],
        "date"  => !empty($complaint['assigned_date'])
                    ? $complaint['assigned_date']
                    : $complaint['updated_at'],
        "icon"  => "bi bi-person-check-fill",
        "color" => "info"
    ];

}

if (($complaint['status'] ?? "") == "In Progress") {
    $timeline[] = [
        "title" => "Work In Progress",
        "date"  => $complaint['updated_at'] ?? "",
        "icon"  => "bi bi-tools",
        "color" => "warning"
    ];
}

if (($complaint['status'] ?? "") == "Resolved") {
    $timeline[] = [
        "title" => "Complaint Resolved",
        "date"  => $complaint['resolved_at'] ?? $complaint['updated_at'] ?? "",
        "icon"  => "bi bi-check-circle-fill",
        "color" => "success"
    ];
}

if (($complaint['status'] ?? "") == "Rejected") {
    $timeline[] = [
        "title" => "Complaint Rejected",
        "date"  => $complaint['updated_at'] ?? "",
        "icon"  => "bi bi-x-circle-fill",
        "color" => "danger"
    ];
}

/*=========================================================
    LOCATION
=========================================================*/

$latitude = !empty($complaint['latitude'])
    ? $complaint['latitude']
    : "26.1445";

$longitude = !empty($complaint['longitude'])
    ? $complaint['longitude']
    : "91.7362";

/*=========================================================
    PROGRESS PERCENTAGE
=========================================================*/

switch ($complaint['status']) {

    case "Pending":
        $progress = 20;
        break;

    case "Assigned":
        $progress = 40;
        break;

    case "In Progress":
        $progress = 70;
        break;

    case "Resolved":
        $progress = 100;
        break;

    case "Rejected":
        $progress = 100;
        break;

    default:
        $progress = 0;
}

/*=========================================================
    PAGE TITLE
=========================================================*/

$pageTitle = "Track Complaint";

/*=========================================
 OFFICER DETAILS
=========================================*/

$assigned_to = $complaint['assigned_to'] ?? "Not Assigned Yet";

$remarks = $complaint['remarks'] ?? "No remarks available.";

$updated_at = $complaint['updated_at'] ?? "";

$assigned_date = $complaint['assigned_date'] ?? "";

$resolved_date = $complaint['resolved_date'] ?? "";

/*=========================================================
    READY FOR HTML
=========================================================*/
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title><?= $pageTitle ?></title>

<!-- Bootstrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<!-- Font Awesome -->
<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

<!-- Leaflet -->
<link rel="stylesheet"
href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<!-- Google Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;
}

body{

background:#f5f7fb;
overflow-x:hidden;

}

/* Sidebar */

.sidebar{

position:fixed;
left:0;
top:0;
width:270px;
height:100vh;
background:#003366;
color:#fff;
padding:25px;
overflow:auto;
z-index:999;

}

.sidebar img{

width:85px;
display:block;
margin:auto;

}

.sidebar h4{

text-align:center;
margin-top:10px;
margin-bottom:30px;
font-weight:700;

}

.sidebar a{

display:block;
padding:14px 18px;
color:#fff;
text-decoration:none;
border-radius:10px;
margin-bottom:10px;
transition:.3s;

}

.sidebar a:hover{

background:#0d6efd;

}

.sidebar a.active{

background:#0d6efd;

}

/* Main */

.main{

margin-left:270px;
padding:30px;

}

/* Header */

.topbar{

background:#fff;
padding:20px;
border-radius:15px;
box-shadow:0 5px 20px rgba(0,0,0,.08);
margin-bottom:25px;

display:flex;
justify-content:space-between;
align-items:center;

}

/* Cards */

.card{

border:none;
border-radius:18px;
box-shadow:0 5px 18px rgba(0,0,0,.08);

}

.card-header{

background:#0d6efd;
color:#fff;
font-weight:600;
border-radius:18px 18px 0 0 !important;

}

.form-control,
.form-select{

border-radius:12px;

}

.btn{

border-radius:10px;

}

/* Status Badge */

.status-badge{

font-size:16px;
padding:10px 18px;

}

/* Map */

#map{

height:350px;
border-radius:15px;

}

/* Photo */

.preview{

width:100%;
border-radius:15px;
border:2px solid #ddd;

}

/* Timeline */

.timeline{

position:relative;
padding-left:25px;

}

.timeline::before{

content:"";
position:absolute;
left:9px;
top:0;
bottom:0;
width:3px;
background:#0d6efd;

}

.timeline-item{

position:relative;
margin-bottom:30px;

}

.timeline-item::before{

content:"";
position:absolute;
left:-20px;
width:18px;
height:18px;
border-radius:50%;
background:#0d6efd;

}

footer{

margin-top:40px;
padding:15px;
text-align:center;
color:#666;

}

/* Responsive */

@media(max-width:991px){

.sidebar{

width:100%;
height:auto;
position:relative;

}

.main{

margin-left:0;

}

.topbar{

flex-direction:column;
gap:10px;

}

}

#map{
    height:400px;
    width:100%;
    border-radius:15px;
}

</style>

</head>

<body>
<!-- ===========================
     SIDEBAR
=========================== -->

<div class="sidebar">

    <div class="text-center">

        <img src="../assets/images/logo-circle.png"
             alt="APDCL Logo">

        <h4>APDCL</h4>

        <small class="text-light">
            Consumer Portal
        </small>

    </div>

    <hr class="border-light">

    <a href="dashboard.php">
        <i class="bi bi-speedometer2 me-2"></i>
        Dashboard
    </a>

    <a href="bill.php">
        <i class="bi bi-receipt me-2"></i>
        Current Bill
    </a>

    <a href="bill_history.php">
        <i class="bi bi-clock-history me-2"></i>
        Bill History
    </a>

    <a href="payment.php">
        <i class="bi bi-credit-card me-2"></i>
        Pay Bill
    </a>

    <a href="payment_history.php">
        <i class="bi bi-wallet2 me-2"></i>
        Payment History
    </a>

    <a href="complaint.php">
        <i class="bi bi-pencil-square me-2"></i>
        Register Complaint
    </a>

    <a href="complaint_history.php">

        <i class="bi bi-geo-alt"></i>

        Track Complaint

    </a>

    <a href="profile.php">
        <i class="bi bi-person-circle me-2"></i>
        My Profile
    </a>

    <a href="../logout.php"
       onclick="return confirm('Logout from Consumer Portal?')">

        <i class="bi bi-box-arrow-right me-2"></i>

        Logout

    </a>

</div>

<!-- ===========================
     MAIN CONTENT
=========================== -->

<div class="main">

<!-- ===========================
     HEADER
=========================== -->

<div class="topbar">

    <div>

        <h3 class="fw-bold text-primary mb-1">

            Track Complaint

        </h3>

        <small class="text-muted">

            Monitor complaint progress and updates.

        </small>

    </div>

    <div class="text-end">

        <h6 class="mb-1">

            <?= htmlspecialchars($name) ?>

        </h6>

        <small class="text-muted">

            Consumer No :
            <?= htmlspecialchars($consumer_no) ?>

        </small>

        <br>

        <span class="badge bg-<?= $badge ?> mt-2">

            <?= htmlspecialchars($complaint['status']) ?>

        </span>

    </div>

</div>

<!-- ===========================
     SUCCESS / ERROR MESSAGE
=========================== -->

<?php if(!empty($success)){ ?>

<div class="alert alert-success alert-dismissible fade show">

    <i class="bi bi-check-circle-fill me-2"></i>

    <?= htmlspecialchars($success) ?>

    <button class="btn-close"
            data-bs-dismiss="alert"></button>

</div>

<?php } ?>

<?php if(!empty($error)){ ?>

<div class="alert alert-danger alert-dismissible fade show">

    <i class="bi bi-exclamation-circle-fill me-2"></i>

    <?= htmlspecialchars($error) ?>

    <button class="btn-close"
            data-bs-dismiss="alert"></button>

</div>

<?php } ?>

<!-- ===========================
     SUMMARY ROW STARTS
=========================== -->

<div class="row g-4">
<!-- ===========================
     COMPLAINT SUMMARY CARDS
=========================== -->


<div class="col-lg-3 col-md-6">

<div class="card text-center p-3">

    <div class="card-body">

        <div class="text-primary fs-1">

            <i class="bi bi-hash"></i>

        </div>

        <h6 class="text-muted mt-2">

            Complaint ID

        </h6>

        <h5 class="fw-bold">

            <?= htmlspecialchars($complaint['complaint_id'] ?? $id) ?>

        </h5>

    </div>

</div>

</div>


<div class="col-lg-3 col-md-6">

<div class="card text-center p-3">

    <div class="card-body">

        <div class="text-danger fs-1">

            <i class="bi bi-exclamation-triangle-fill"></i>

        </div>


        <h6 class="text-muted mt-2">

            Priority

        </h6>


        <h5 class="fw-bold">

            <?= htmlspecialchars($complaint['priority'] ?? 'Medium') ?>

        </h5>

    </div>

</div>

</div>



<div class="col-lg-3 col-md-6">

<div class="card text-center p-3">

    <div class="card-body">

        <div class="text-success fs-1">

            <i class="bi bi-check-circle-fill"></i>

        </div>


        <h6 class="text-muted mt-2">

            Status

        </h6>


        <span class="badge bg-<?= $badge ?>">

            <?= htmlspecialchars($complaint['status']) ?>

        </span>


    </div>

</div>

</div>




<div class="col-lg-3 col-md-6">

<div class="card text-center p-3">

    <div class="card-body">


        <div class="text-info fs-1">

            <i class="bi bi-calendar-event"></i>

        </div>


        <h6 class="text-muted mt-2">

            Submitted Date

        </h6>


        <h6 class="fw-bold">

            <?= !empty($complaint['created_at']) 
                ? date("d M Y",strtotime($complaint['created_at'])) 
                : "N/A" ?>

        </h6>


    </div>

</div>

</div>


</div>
<!-- END SUMMARY ROW -->


<br>


<!-- ===========================
     COMPLAINT DETAILS TITLE
=========================== -->

<div class="card">

<div class="card-header">

<i class="bi bi-file-earmark-text-fill me-2"></i>

Complaint Details

</div>


<div class="card-body">
<!-- ===========================
     COMPLAINT FORM
=========================== -->

<form method="POST" enctype="multipart/form-data">


<div class="row g-4">


<!-- Category -->

<div class="col-md-6">

<label class="form-label fw-semibold">

<i class="bi bi-grid-fill text-primary"></i>
Category

</label>


<select name="category"
class="form-select"
<?= !$editable ? 'disabled' : '' ?>>

<option value="">Select Category</option>

<option value="Power Failure"
<?= ($complaint['category']=="Power Failure")?'selected':'' ?>>
Power Failure
</option>

<option value="Billing Issue"
<?= ($complaint['category']=="Billing Issue")?'selected':'' ?>>
Billing Issue
</option>

<option value="Meter Problem"
<?= ($complaint['category']=="Meter Problem")?'selected':'' ?>>
Meter Problem
</option>

<option value="Other"
<?= ($complaint['category']=="Other")?'selected':'' ?>>
Other
</option>

</select>

</div>



<!-- Priority -->

<div class="col-md-6">

<label class="form-label fw-semibold">

<i class="bi bi-exclamation-circle text-danger"></i>
Priority

</label>


<select name="priority"
class="form-select"
<?= !$editable ? 'disabled' : '' ?>>


<option value="Low"
<?= ($complaint['priority']=="Low")?'selected':'' ?>>
Low
</option>


<option value="Medium"
<?= ($complaint['priority']=="Medium")?'selected':'' ?>>
Medium
</option>


<option value="High"
<?= ($complaint['priority']=="High")?'selected':'' ?>>
High
</option>


</select>

</div>




<!-- Subject -->

<div class="col-12">

<label class="form-label fw-semibold">

<i class="bi bi-chat-left-text text-primary"></i>
Subject

</label>


<input type="text"
name="subject"
class="form-control"
value="<?= htmlspecialchars($complaint['subject'] ?? '') ?>"
<?= !$editable ? 'readonly' : '' ?>>


</div>





<!-- Description -->

<div class="col-12">

<label class="form-label fw-semibold">

<i class="bi bi-card-text text-primary"></i>
Description

</label>


<textarea name="description"
class="form-control"
rows="5"
<?= !$editable ? 'readonly' : '' ?>><?= htmlspecialchars($complaint['description'] ?? '') ?></textarea>


</div>





<!-- Address -->

<div class="col-12">

<label class="form-label fw-semibold">

<i class="bi bi-geo-alt text-danger"></i>
Complaint Location

</label>


<textarea name="address"
class="form-control"
rows="3"
<?= !$editable ? 'readonly' : '' ?>><?= htmlspecialchars($complaint['address'] ?? '') ?></textarea>


</div>






<!-- Latitude -->

<div class="col-md-6">

<label class="form-label fw-semibold">

Latitude

</label>


<input type="text"
name="latitude"
id="latitude"
class="form-control"
value="<?= htmlspecialchars($complaint['latitude'] ?? '') ?>"
<?= !$editable ? 'readonly' : '' ?>>


</div>





<!-- Longitude -->

<div class="col-md-6">

<label class="form-label fw-semibold">

Longitude

</label>


<input type="text"
name="longitude"
id="longitude"
class="form-control"
value="<?= htmlspecialchars($complaint['longitude'] ?? '') ?>"
<?= !$editable ? 'readonly' : '' ?>>


</div>





<!-- Photo Upload -->

<div class="col-12">


<label class="form-label fw-semibold">

<i class="bi bi-image text-success"></i>
Upload Complaint Image

</label>


<input type="file"
name="photo"
class="form-control"
accept="image/*"
<?= !$editable ? 'disabled' : '' ?>>


</div>



</div>


<br>


<?php if($editable){ ?>


<button type="submit"
name="update"
class="btn btn-primary px-4">


<i class="bi bi-save me-2"></i>

Update Complaint

</button>


<?php } ?>


<a href="dashboard.php"
class="btn btn-secondary px-4 ms-2">

<i class="bi bi-arrow-left me-2"></i>

Back

</a>



</form>

</div>

</div>
<!-- ===========================
     PHOTO & MAP SECTION
=========================== -->

<div class="row g-4">


<!-- Complaint Image -->

<div class="col-lg-5">

<div class="card">

<div class="card-header">

<i class="bi bi-image-fill me-2"></i>

Complaint Photo

</div>


<div class="card-body text-center">


<img src="<?= $photoPath ?>"
class="preview"
alt="Complaint Image">


<?php if($photoPath != "../assets/images/no-image.jpg"){ ?>

<br><br>

<a href="<?= $photoPath ?>"
target="_blank"
class="btn btn-outline-primary">

<i class="bi bi-zoom-in me-2"></i>

View Full Image

</a>

<?php } ?>


</div>

</div>

</div>





<!-- Map -->

<div class="col-lg-7">


<div class="card">


<div class="card-header">

<i class="bi bi-map-fill me-2"></i>

Complaint Location Map

</div>



<div class="card-body">


<div id="map"></div>


<div class="row mt-3">


<div class="col-md-6">

<label class="fw-semibold">

Latitude

</label>

<input type="text"
class="form-control"
value="<?= htmlspecialchars($latitude) ?>"
readonly>


</div>


<div class="col-md-6">

<label class="fw-semibold">

Longitude

</label>

<input type="text"
class="form-control"
value="<?= htmlspecialchars($longitude) ?>"
readonly>

</div>

</div>

</div>

</div>

</div>

</div>
<!-- ===========================
     STATUS PROGRESS
=========================== -->

<div class="card mt-4">

<div class="card-header">

<i class="bi bi-bar-chart-fill me-2"></i>

Complaint Progress

</div>


<div class="card-body">


<div class="progress mb-3"
style="height:25px;">

<div class="progress-bar bg-primary"
role="progressbar"
style="width: <?= $progress ?>%;">

<?= $progress ?>%

</div>

</div>


<div class="text-center">

<span class="badge bg-<?= $badge ?> status-badge">

<?= htmlspecialchars($complaint['status']) ?>

</span>

</div>


</div>

</div>



<!-- ===========================
     TIMELINE
=========================== -->

<div class="card mt-4">


<div class="card-header">

<i class="bi bi-clock-history me-2"></i>

Complaint Timeline

</div>


<div class="card-body">


<div class="timeline">


<?php foreach($timeline as $item){ ?>


<div class="timeline-item">


<h6 class="fw-bold">

<i class="<?= $item['icon'] ?> text-<?= $item['color'] ?>"></i>

<?= $item['title'] ?>

</h6>


<small class="text-muted">

<?= !empty($item['date']) 
? date("d M Y h:i A",strtotime($item['date'])) 
: "N/A" ?>

</small>


</div>


<?php } ?>


</div>


</div>


</div>

<!-- ===========================
     OFFICER REMARKS
=========================== -->

<div class="card mt-4">


<div class="card-header">

<i class="bi bi-person-workspace me-2"></i>

Officer Details & Remarks

</div>


<div class="card-body">


<div class="row">


<div class="col-md-6">

<h6 class="fw-bold">

Assigned Officer

</h6>


<p>

<?= htmlspecialchars($assigned_to) ?>

</p>


</div>



<div class="col-md-6">

<h6 class="fw-bold">

Last Updated

</h6>


<p>

<?= !empty($updated_at)
? date("d M Y h:i A",strtotime($updated_at))
: "N/A" ?>

</p>


</div>


</div>


<hr>


<h6 class="fw-bold">

Remarks

</h6>


<p class="text-muted">

<?= !empty($remarks)
? htmlspecialchars($remarks)
: "No remarks available." ?>

</p>

</div>

</div>

<!-- ===========================
     FOOTER
=========================== -->

<footer>

    <hr>

    <h6 class="fw-bold">
        Assam Power Distribution Company Limited
    </h6>

    <p class="mb-1">
        APDCL Consumer Self Service Portal
    </p>

    <p>
        Customer Care : 1912 |
        © 2026 APDCL Internship Demo
    </p>

</footer>


</div> <!-- content -->

</div> <!-- main -->


<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

document.addEventListener("DOMContentLoaded", function(){

let lat = <?= !empty($latitude) ? $latitude : 26.1445 ?>;
let lng = <?= !empty($longitude) ? $longitude : 91.7362 ?>;


let map = L.map('map').setView(
    [lat,lng],
    15
);


L.tileLayer(
'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
{
    maxZoom:19,
    attribution:'© OpenStreetMap'
}
).addTo(map);


// =============================
// RED LOCATION PIN
// =============================

let redIcon = L.icon({

    iconUrl:
    'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',

    shadowUrl:
    'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',

    iconSize:[25,41],

    iconAnchor:[12,41],

    popupAnchor:[1,-34],

    shadowSize:[41,41]

});


// =============================
// RED MARKER
// =============================

L.marker(
    [lat,lng],
    {
        icon:redIcon
    }
)
.addTo(map)
.bindPopup(
`
<b>⚡ Complaint Location</b><br>
Latitude: ${lat}<br>
Longitude: ${lng}<br>
Status:
<span style="color:red;font-weight:bold;">
<?= htmlspecialchars($complaint['status']) ?>
</span>
`
)
.openPopup();



setTimeout(function(){

    map.invalidateSize();

},500);


});

</script>

</body>

</html>