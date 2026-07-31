<?php
session_start();
include("../db.php");

// ===============================
// CHECK LOGIN
// ===============================
if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

$consumer_no = $_SESSION['consumer'];

// ===============================
// LOAD CONSUMER DETAILS
// ===============================
$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

if(mysqli_num_rows($userQuery)==0){
    die("Consumer not found.");
}

$user = mysqli_fetch_assoc($userQuery);

$zone = mysqli_real_escape_string($conn,$user['zone']);
$circle = mysqli_real_escape_string($conn,$user['circle']);
$subDivision = mysqli_real_escape_string($conn,$user['sub_division']);

// ===============================
// LOAD OUTAGES FOR THIS AREA
// ===============================

$outageQuery = mysqli_query($conn,"
SELECT *
FROM outages
WHERE sub_division='$subDivision'
ORDER BY start_time DESC
");

if(!$outageQuery){
    die("SQL Error : ".mysqli_error($conn));
}

$outages = [];

while($row = mysqli_fetch_assoc($outageQuery)){
    $outages[] = $row;
}
// ===============================
// DASHBOARD COUNTS
// ===============================
$totalOutages = count($outages);

$totalActive = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
WHERE zone='$zone'
AND circle='$circle'
AND sub_division='$subDivision'
AND status='Pending'
"))['total'];

$totalRestored = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
WHERE zone='$zone'
AND circle='$circle'
AND sub_division='$subDivision'
AND status='Restored'
"))['total'];

$totalConsumers = mysqli_fetch_assoc(mysqli_query($conn,"
SELECT IFNULL(SUM(consumers_affected),0) total
FROM outages
WHERE sub_division='$subDivision'
AND status='Pending'
"))['total'];

?>
<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Consumer Outage Map</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<style>

body{
    background:#eef3f8;
    font-family:'Segoe UI',sans-serif;
}

.page-header{
    background:linear-gradient(90deg,#0b4ea2,#1976d2);
    color:#fff;
    padding:20px;
    border-radius:18px;
    margin-bottom:25px;
    box-shadow:0 10px 25px rgba(0,0,0,.15);
}

.card-stat{
    border:none;
    border-radius:18px;
    color:#fff;
    transition:.3s;
}

.card-stat:hover{
    transform:translateY(-5px);
}

.bg-active{
    background:linear-gradient(135deg,#dc3545,#ff6b6b);
}

.bg-restored{
    background:linear-gradient(135deg,#198754,#3ddc84);
}

.bg-consumers{
    background:linear-gradient(135deg,#0d6efd,#56a7ff);
}

#map{
    height:650px;
    border-radius:18px;
    overflow:hidden;
    box-shadow:0 10px 25px rgba(0,0,0,.15);
}

.area-card{
    border-radius:18px;
}

</style>

</head>

<body>

<div class="container-fluid p-4">

<div class="page-header d-flex justify-content-between align-items-center">

<div>

<h2>
<i class="bi bi-lightning-charge-fill"></i>
APDCL Consumer Outage Map
</h2>

<small>
View outages affecting your service area
</small>

</div>

<div class="text-end">

    <a href="dashboard.php" class="btn btn-light btn-sm mb-2">
        <i class="bi bi-arrow-left-circle"></i> Back to Dashboard
    </a>

    <br>

    <strong><?= htmlspecialchars($user['name']) ?></strong>

    <br>

    Consumer No :
    <?= htmlspecialchars($consumer_no) ?>

</div>

</div>

<div class="row g-4 mb-4">

<div class="col-lg-4">

<div class="card card-stat bg-active">

<div class="card-body">

<h6>Pending Outages</h6>

<h2><?= $totalActive ?></h2>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card card-stat bg-restored">

<div class="card-body">

<h6>Power Restored</h6>

<h2><?= $totalRestored ?></h2>

</div>

</div>

</div>

<div class="col-lg-4">

<div class="card card-stat bg-consumers">

<div class="card-body">

<h6>Consumers Affected</h6>

<h2><?= number_format($totalConsumers) ?></h2>

</div>

</div>

</div>

</div>

<div class="card area-card shadow-sm mb-4">

<div class="card-header bg-primary text-white">

<h5 class="mb-0">
<i class="bi bi-geo-alt-fill"></i>
Your Service Area
</h5>

</div>

<div class="card-body">

<div class="row">

<div class="col-md-4">

<strong>Zone</strong>

<p><?= htmlspecialchars($zone) ?></p>

</div>

<div class="col-md-4">

<strong>Circle</strong>

<p><?= htmlspecialchars($circle) ?></p>

</div>

<div class="col-md-4">

<strong>Sub-Division</strong>

<p><?= htmlspecialchars($subDivision) ?></p>

</div>

</div>

</div>

</div>

<div class="card shadow border-0 rounded-4">

<div class="card-header bg-success text-white">

<h5 class="mb-0">

<i class="bi bi-map-fill"></i>

Live Outage Map

</h5>

</div>

<div class="card-body p-2">

<div id="map"></div>

</div>

</div>
<!-- ==========================================
        OUTAGE DETAILS TABLE
========================================== -->

<div class="card shadow mt-4">

    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">
            <i class="bi bi-list-ul"></i>
            Outage Details
        </h5>
    </div>

    <div class="card-body">

        <div class="table-responsive">

            <table class="table table-bordered table-hover align-middle">

                <thead class="table-primary">

                <tr>

                    <th>Feeder</th>
                    <th>Transformer</th>
                    <th>Consumers</th>
                    <th>Start Time</th>
                    <th>Status</th>

                </tr>

                </thead>

                <tbody>

                <?php if(count($outages)>0){ ?>

                    <?php foreach($outages as $row){ ?>

                        <tr>

                            <td><?= htmlspecialchars($row['feeder_name']) ?></td>

                            <td><?= htmlspecialchars($row['transformer']) ?></td>

                            <td><?= number_format($row['consumers_affected']) ?></td>

                            <td>

                                <?= date("d M Y h:i A",strtotime($row['start_time'])) ?>

                            </td>

                            <td>

                                <?php if($row['status']=="Active"){ ?>

                                    <span class="badge bg-danger">

                                        Active

                                    </span>

                                <?php }else{ ?>

                                    <span class="badge bg-success">

                                        Restored

                                    </span>

                                <?php } ?>

                            </td>

                        </tr>

                    <?php } ?>

                <?php }else{ ?>

                    <tr>

                        <td colspan="5" class="text-center text-danger">

                            No outage found in your area.

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>

// Assam Center
var map = L.map('map').setView([26.1445,91.7362],11);

L.tileLayer(
'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
{
    maxZoom:19,
    attribution:'© OpenStreetMap'
}).addTo(map);

// Marker Icons

var redIcon = L.icon({

iconUrl:'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',

shadowUrl:'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',

iconSize:[25,41],

iconAnchor:[12,41],

popupAnchor:[1,-34]

});

var greenIcon = L.icon({

iconUrl:'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-green.png',

shadowUrl:'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',

iconSize:[25,41],

iconAnchor:[12,41],

popupAnchor:[1,-34]

});

<?php foreach($outages as $row){ ?>

var icon =
"<?php echo $row['status']; ?>"=="Pending"
?
redIcon
:
greenIcon;

L.marker(
[
<?php echo $row['latitude']; ?>,
<?php echo $row['longitude']; ?>
],
{
icon:icon
})
.addTo(map)
.bindPopup(

"<div style='min-width:240px'>"+

"<h6><b><?php echo addslashes($row['sub_division']); ?></b></h6>"+

"<b>Feeder :</b> <?php echo addslashes($row['feeder_name']); ?><br>"+

"<b>Transformer :</b> <?php echo addslashes($row['transformer']); ?><br>"+

"<b>Consumers :</b> <?php echo $row['consumers_affected']; ?><br>"+

"<b>Reason :</b> <?php echo addslashes($row['outage_reason']); ?><br>"+

"<b>Started :</b> <?php echo date('d M Y h:i A',strtotime($row['start_time'])); ?><br>"+

"<b>Status :</b> <span style='color:<?php echo ($row['status']=='Pending')?'red':'green'; ?>'><b><?php echo $row['status']; ?></b></span>"+

"</div>"

);

<?php } ?>

// Auto-fit map to markers

var group=[];

<?php foreach($outages as $row){ ?>

group.push(
L.latLng(
<?php echo $row['latitude']; ?>,
<?php echo $row['longitude']; ?>
)
);

<?php } ?>

if(group.length>0){

map.fitBounds(L.latLngBounds(group),{padding:[40,40]});

}

// Refresh every minute

setTimeout(function(){

location.reload();

},60000);

</script>
<footer class="mt-5">

    <div class="card border-0 shadow">

        <div class="card-body text-center">

            <h5 class="text-primary mb-2">
                <i class="bi bi-lightning-charge-fill"></i>
                APDCL Consumer Portal
            </h5>

            <p class="mb-1">
                Assam Power Distribution Company Limited
            </p>

            <small class="text-muted">
                Consumer Outage Monitoring System
            </small>

            <hr>

            <small class="text-secondary">
                &copy; <?= date("Y") ?> APDCL Demo Project | Internship Project
            </small>

        </div>

    </div>

</footer>

</body>

</html>