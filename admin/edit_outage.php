<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================
    GET LOGGED IN ADMIN
=========================================*/

$admin_username = $_SESSION['admin'];

$stmt = mysqli_prepare($conn,"
SELECT id,username,name
FROM admin
WHERE username=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$admin_username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){
    die("Admin account not found.");
}

$admin=mysqli_fetch_assoc($result);

/*=========================================
    VALIDATE OUTAGE ID
=========================================*/

if(
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
){
    die("Invalid Outage ID.");
}

$id=(int)$_GET['id'];

/*=========================================
    LOAD OUTAGE
=========================================*/

$stmt=mysqli_prepare($conn,"
SELECT *
FROM outages
WHERE id=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"i",$id);
mysqli_stmt_execute($stmt);

$result=mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){
    die("Outage record not found.");
}

$outage=mysqli_fetch_assoc($result);

$error="";
$success="";

/*=========================================
    UPDATE OUTAGE
=========================================*/

if(isset($_POST['update_outage'])){

    $district          = trim($_POST['district']);
    $zone              = trim($_POST['zone']);
    $circle            = trim($_POST['circle']);
    $sub_division      = trim($_POST['sub_division']);
    $feeder_name       = trim($_POST['feeder_name']);
    $transformer       = trim($_POST['transformer']);

    $latitude          = trim($_POST['latitude']);
    $longitude         = trim($_POST['longitude']);

    $outage_reason     = trim($_POST['outage_reason']);
    $consumersAffected = (int)$_POST['consumers_affected'];

    $startTime         = $_POST['start_time'];
    $estimatedRestore  = $_POST['estimated_restore'];

    $status            = $_POST['status'];

    $resolvedBy        = trim($_POST['resolved_by']);
    $resolutionNote    = trim($_POST['resolution_note']);

    if(

        empty($district) ||

        empty($zone) ||

        empty($circle) ||

        empty($sub_division) ||

        empty($feeder_name) ||

        empty($latitude) ||

        empty($longitude) ||

        empty($outage_reason) ||

        empty($startTime)

    ){

        $error="Please fill all required fields.";

    }else{

        if($status=="Resolved"){

            if(empty($resolvedBy)){

                $resolvedBy = !empty($admin['name'])
                            ? $admin['name']
                            : $admin['username'];

            }

            if(empty($outage['resolved_at'])){

                $resolvedAt=date("Y-m-d H:i:s");

            }else{

                $resolvedAt=$outage['resolved_at'];

            }

        }else{

            $resolvedBy="";

            $resolvedAt=NULL;

            $resolutionNote="";

        }

        $stmt=mysqli_prepare($conn,"
        UPDATE outages
        SET

        district=?,
        zone=?,
        circle=?,
        sub_division=?,
        feeder_name=?,
        transformer=?,
        latitude=?,
        longitude=?,
        outage_reason=?,
        consumers_affected=?,
        start_time=?,
        estimated_restore=?,
        status=?,
        resolved_by=?,
        resolved_at=?,
        resolution_note=?

        WHERE id=?
        ");

        mysqli_stmt_bind_param(

            $stmt,

            "ssssssddsissssssi",

            $district,
            $zone,
            $circle,
            $sub_division,
            $feeder_name,
            $transformer,
            $latitude,
            $longitude,
            $outage_reason,
            $consumersAffected,
            $startTime,
            $estimatedRestore,
            $status,
            $resolvedBy,
            $resolvedAt,
            $resolutionNote,
            $id

        );

        if(mysqli_stmt_execute($stmt)){

            $_SESSION['success']="Outage updated successfully.";

            header("Location: manage_outages.php");

            exit();

        }else{

            $error=mysqli_stmt_error($stmt);

        }

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Edit Power Outage | APDCL Admin</title>

<!-- Bootstrap -->

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Font Awesome -->

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

<!-- Leaflet CSS -->

<link rel="stylesheet"
href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>

*{
margin:0;
padding:0;
box-sizing:border-box;
}

body{

background:#eef3f9;

font-family:"Segoe UI",sans-serif;

}

/*==========================
PAGE CONTAINER
===========================*/

.container-fluid{

max-width:1500px;

padding:25px;

}

/*==========================
HEADER
===========================*/

.page-header{

background:#ffffff;

padding:25px;

border-radius:20px;

display:flex;

justify-content:space-between;

align-items:center;

box-shadow:0 8px 25px rgba(0,0,0,.08);

margin-bottom:25px;

}

.header-left{

display:flex;

align-items:center;

gap:20px;

}

.logo{

width:85px;

height:85px;

object-fit:contain;

}

.page-header h2{

font-weight:700;

color:#c62828;

margin-bottom:5px;

}

.page-header p{

margin:0;

color:#666;

}

/*==========================
CARD
===========================*/

.card{

border:none;

border-radius:20px;

box-shadow:0 10px 25px rgba(0,0,0,.08);

overflow:hidden;

}

.card-header{

background:#c62828;

color:#fff;

font-size:22px;

font-weight:700;

padding:18px 25px;

}

/*==========================
SECTION TITLE
===========================*/

.section-title{

font-size:21px;

font-weight:700;

color:#c62828;

margin-top:35px;

margin-bottom:20px;

padding-left:12px;

border-left:6px solid #c62828;

}

/*==========================
FORM
===========================*/

.form-label{

font-weight:600;

margin-bottom:8px;

}

.form-control,

.form-select{

height:50px;

border-radius:12px;

}

textarea.form-control{

height:130px;

resize:vertical;

}

/*==========================
MAP
===========================*/

#map{

width:100%;

height:700px;

border-radius:18px;

border:4px solid #dc3545;

overflow:hidden;

box-shadow:0 10px 20px rgba(0,0,0,.12);

}

/*==========================
SUMMARY CARD
===========================*/

.summary-card{

background:#fff;

border-radius:18px;

padding:20px;

box-shadow:0 8px 20px rgba(0,0,0,.08);

margin-bottom:20px;

}

.summary-card h5{

font-weight:700;

color:#c62828;

margin-bottom:15px;

}

.summary-card table{

margin-bottom:0;

}

/*==========================
BUTTON
===========================*/

.btn{

border-radius:12px;

padding:10px 20px;

font-weight:600;

}

.btn-success{

background:#198754;

}

.btn-primary{

background:#0d6efd;

}

.btn-secondary{

background:#6c757d;

}

.btn-dark{

background:#212529;

}

/*==========================
BADGE
===========================*/

.badge{

font-size:14px;

padding:10px 18px;

border-radius:30px;

}

/*==========================
ALERT
===========================*/

.alert{

border-radius:15px;

}

/*==========================
TABLE
===========================*/

.table td{

padding:10px;

vertical-align:middle;

}

/*==========================
INPUT GROUP
===========================*/

.input-group-text{

background:#c62828;

color:#fff;

border:none;

}

/*==========================
RESPONSIVE
===========================*/

@media(max-width:992px){

.page-header{

flex-direction:column;

text-align:center;

gap:20px;

}

.header-left{

flex-direction:column;

}

#map{

height:500px;

}

}

@media(max-width:768px){

.container-fluid{

padding:12px;

}

.card-header{

font-size:18px;

}

.section-title{

font-size:18px;

}

#map{

height:400px;

}

}

</style>

</head>

<body>

<div class="container-fluid">

<!-- ==========================
PAGE HEADER
========================== -->

<div class="page-header">

<div class="header-left">

<img
src="../assets/images/logo-circle.png"
class="logo"
alt="APDCL">

<div>

<h2>

<i class="fa-solid fa-bolt"></i>

Edit Power Outage

</h2>

<p>

Modify outage information, update GPS location and restore status.

</p>

</div>

</div>

<div>

<a href="dashboard.php"
class="btn btn-dark">

<i class="fa-solid fa-house"></i>

Dashboard

</a>

<a href="manage_outages.php"
class="btn btn-secondary">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

</div>

<div class="card">

<div class="card-header">

<i class="fa-solid fa-pen-to-square"></i>

Edit Outage Details

</div>

<div class="card-body">

<form method="POST">

<?php if($error!=""){ ?>

<div class="alert alert-danger">

<i class="fa-solid fa-circle-exclamation"></i>

<?= $error ?>

</div>

<?php } ?>

<?php if(isset($_SESSION['success'])){ ?>

<div class="alert alert-success">

<i class="fa-solid fa-circle-check"></i>

<?= $_SESSION['success']; unset($_SESSION['success']); ?>

</div>

<?php } ?>

<!-- =======================================
LOCATION DETAILS
======================================= -->

<div class="section-title">

<i class="fa-solid fa-location-dot"></i>

Location Details

</div>

<div class="row">

<div class="col-md-3 mb-3">

<label class="form-label">

District

</label>

<input
type="text"
name="district"
class="form-control"
value="<?= htmlspecialchars($outage['district']) ?>"
required>

</div>

<div class="col-md-3 mb-3">

<label class="form-label">

Zone

</label>

<input
type="text"
name="zone"
class="form-control"
value="<?= htmlspecialchars($outage['zone']) ?>"
required>

</div>

<div class="col-md-3 mb-3">

<label class="form-label">

Circle

</label>

<input
type="text"
name="circle"
class="form-control"
value="<?= htmlspecialchars($outage['circle']) ?>"
required>

</div>

<div class="col-md-3 mb-3">

<label class="form-label">

Sub Division

</label>

<input
type="text"
name="sub_division"
class="form-control"
value="<?= htmlspecialchars($outage['sub_division']) ?>"
required>

</div>

</div>

<div class="row">

<div class="col-md-6 mb-3">

<label class="form-label">

Feeder Name

</label>

<input
type="text"
name="feeder_name"
class="form-control"
value="<?= htmlspecialchars($outage['feeder_name']) ?>"
required>

</div>

<div class="col-md-6 mb-3">

<label class="form-label">

Transformer

</label>

<input
type="text"
name="transformer"
class="form-control"
value="<?= htmlspecialchars($outage['transformer']) ?>">

</div>

</div>

<!-- =======================================
GPS LOCATION
======================================= -->

<div class="section-title">

<i class="fa-solid fa-map-location-dot"></i>

Outage Location

</div>

<div class="row">

<div class="col-lg-9">

<div id="map"></div>

</div>

<div class="col-lg-3">

<div class="summary-card">

<h5>

<i class="fa-solid fa-location-crosshairs"></i>

GPS Coordinates

</h5>

<div class="mb-3">

<label class="form-label">

Latitude

</label>

<input
type="text"
id="latitude"
name="latitude"
class="form-control"
value="<?= htmlspecialchars($outage['latitude']) ?>"
required>

</div>

<div class="mb-3">

<label class="form-label">

Longitude

</label>

<input
type="text"
id="longitude"
name="longitude"
class="form-control"
value="<?= htmlspecialchars($outage['longitude']) ?>"
required>

</div>

<div class="d-grid gap-2">

<button
type="button"
class="btn btn-success"
onclick="getLocation()">

<i class="fa-solid fa-location-crosshairs"></i>

Current Location

</button>

<button
type="button"
class="btn btn-primary"
onclick="centerMarker()">

<i class="fa-solid fa-map-pin"></i>

Center Marker

</button>

</div>

</div>

</div>

</div>

<hr class="my-5">

<!-- =======================================
OUTAGE DETAILS
======================================= -->

<div class="section-title">

<i class="fa-solid fa-bolt"></i>

Outage Details

</div>

<div class="row">

<div class="col-12 mb-3">

<label class="form-label">

Outage Reason

</label>

<textarea
name="outage_reason"
class="form-control"
required><?= htmlspecialchars($outage['outage_reason']) ?></textarea>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Consumers Affected

</label>

<input
type="number"
name="consumers_affected"
class="form-control"
value="<?= htmlspecialchars($outage['consumers_affected']) ?>">

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Start Time

</label>

<input
type="datetime-local"
name="start_time"
class="form-control"
value="<?= !empty($outage['start_time']) ? date('Y-m-d\TH:i',strtotime($outage['start_time'])) : '' ?>"
required>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Estimated Restore Time

</label>

<input
type="datetime-local"
name="estimated_restore"
class="form-control"
value="<?= !empty($outage['estimated_restore']) ? date('Y-m-d\TH:i',strtotime($outage['estimated_restore'])) : '' ?>">

</div>

</div>

<!-- =======================================
STATUS INFORMATION
======================================= -->

<div class="section-title">

<i class="fa-solid fa-circle-info"></i>

Status Information

</div>

<div class="row">

<div class="col-md-4 mb-3">

<label class="form-label">

Current Status

</label>

<select
name="status"
id="status"
class="form-select">

<option value="Issued"
<?= ($outage['status']=="Issued") ? "selected" : "" ?>>

Issued

</option>

<option value="Resolved"
<?= ($outage['status']=="Resolved") ? "selected" : "" ?>>

Resolved

</option>

</select>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Resolved By

</label>

<select
name="resolved_by"
class="form-select">

<option value="">

Select SDE Office

</option>

<?php

$admins=mysqli_query($conn,"
SELECT name,username
FROM admin
ORDER BY name ASC
");

while($row=mysqli_fetch_assoc($admins)){

$office=!empty($row['name'])
? $row['name']
: $row['username'];

?>

<option
value="<?= htmlspecialchars($office) ?>"
<?= ($outage['resolved_by']==$office) ? "selected" : "" ?>>

<?= htmlspecialchars($office) ?>

</option>

<?php } ?>

</select>

</div>

<div class="col-md-4 mb-3">

<label class="form-label">

Resolved At

</label>

<input
type="text"
id="resolved_at"
class="form-control"
value="<?= !empty($outage['resolved_at']) ? date('d M Y h:i A',strtotime($outage['resolved_at'])) : 'Not Resolved Yet' ?>"
readonly>

</div>

</div>

<div class="row">

<div class="col-12 mb-4">

<label class="form-label">

Resolution Note

</label>

<textarea
name="resolution_note"
class="form-control"
placeholder="Write the work carried out to resolve the outage..."><?= htmlspecialchars($outage['resolution_note']) ?></textarea>

</div>

</div>

<!-- =======================================
ACTION BUTTONS
======================================= -->

<hr>

<div class="d-flex justify-content-center gap-3 flex-wrap">

<button
type="submit"
name="update_outage"
class="btn btn-success btn-lg">

<i class="fa-solid fa-floppy-disk"></i>

Update Outage

</button>

<a
href="manage_outages.php"
class="btn btn-secondary btn-lg">

<i class="fa-solid fa-arrow-left"></i>

Cancel

</a>

</div>

</form>

</div>

</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

// =======================================
// READ SAVED COORDINATES
// =======================================

var lat = parseFloat(document.getElementById("latitude").value) || 26.1445;
var lng = parseFloat(document.getElementById("longitude").value) || 91.7362;

// =======================================
// CREATE MAP
// =======================================

var map = L.map("map",{

    zoomControl:true,

    minZoom:15,

    maxZoom:20

}).setView([lat,lng],17);

// =======================================
// OPENSTREETMAP TILE
// =======================================

L.tileLayer(
'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
{

    maxZoom:20,

    attribution:'&copy; OpenStreetMap'

}).addTo(map);

// =======================================
// RED MARKER ICON
// =======================================

var redIcon = new L.Icon({

iconUrl:
'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',

shadowUrl:
'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',

iconSize:[25,41],

iconAnchor:[12,41],

popupAnchor:[1,-34],

shadowSize:[41,41]

});

// =======================================
// MARKER
// =======================================

var marker=L.marker(

[lat,lng],

{

draggable:true,

icon:redIcon

}

).addTo(map);

marker.bindPopup(

"<b>Outage Location</b><br><?= htmlspecialchars($outage['feeder_name']) ?>"

).openPopup();

// =======================================
// SHOW OUTAGE AREA
// =======================================

L.circle([lat,lng],{

radius:300,

color:"red",

fillColor:"#ff4d4d",

fillOpacity:0.25,

weight:2

}).addTo(map);

// =======================================
// UPDATE COORDINATES
// =======================================

marker.on("dragend",function(){

var p=marker.getLatLng();

document.getElementById("latitude").value=p.lat.toFixed(8);

document.getElementById("longitude").value=p.lng.toFixed(8);

});

// =======================================
// CLICK TO MOVE MARKER
// =======================================

map.on("click",function(e){

marker.setLatLng(e.latlng);

document.getElementById("latitude").value=e.latlng.lat.toFixed(8);

document.getElementById("longitude").value=e.latlng.lng.toFixed(8);

});

// =======================================
// CURRENT LOCATION
// =======================================

function getLocation(){

if(navigator.geolocation){

navigator.geolocation.getCurrentPosition(

function(position){

var lat=position.coords.latitude;

var lng=position.coords.longitude;

document.getElementById("latitude").value=lat.toFixed(8);

document.getElementById("longitude").value=lng.toFixed(8);

marker.setLatLng([lat,lng]);

map.setView([lat,lng],18);

},

function(){

alert("Unable to detect current location.");

}

);

}else{

alert("Geolocation is not supported.");

}

}

// =======================================
// CENTER MARKER
// =======================================

function centerMarker(){

var lat=parseFloat(document.getElementById("latitude").value);

var lng=parseFloat(document.getElementById("longitude").value);

marker.setLatLng([lat,lng]);

map.setView([lat,lng],18);

}

// =======================================
// STATUS CHANGE
// =======================================

var status=document.getElementById("status");

if(status){

status.addEventListener("change",function(){

var resolved=document.getElementById("resolved_at");

if(this.value==="Resolved"){

var now=new Date();

resolved.value=now.toLocaleString();

}else{

resolved.value="Not Resolved Yet";

}

});

}

</script>

</body>
</html>