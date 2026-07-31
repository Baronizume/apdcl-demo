<?php
session_start();

/*=========================================================
    ADMIN LOGIN CHECK
=========================================================*/
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================================
    ADMIN DETAILS
=========================================================*/
$admin_username = $_SESSION['admin'];

$adminQuery = mysqli_query($conn,"
SELECT *
FROM admin
WHERE username='$admin_username'
LIMIT 1
");

$admin = mysqli_fetch_assoc($adminQuery);

/*=========================================================
    DATA FILTER (SUPER ADMIN / SDE)
=========================================================*/

$isSuperAdmin = ($admin['role'] == 'Super Admin');

$filter = "";

if (!$isSuperAdmin) {
    $subDivision = mysqli_real_escape_string($conn, $admin['sub_division']);
    $filter = " WHERE sub_division='$subDivision' ";
}


/*=========================================================
    DELETE OUTAGE
=========================================================*/
if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    mysqli_query($conn,"
    DELETE FROM outages
    WHERE id='$id'
    ");

    $_SESSION['success'] = "Outage deleted successfully.";

    header("Location: manage_outages.php");
    exit();
}

/*=========================================================
    DASHBOARD STATISTICS
=========================================================*/

// Total Outages
$result = mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
$filter
");
$totalOutages = mysqli_fetch_assoc($result)['total'];

// Pending Outages
if($isSuperAdmin){

$result=mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
WHERE status='Pending'
");

}else{

$result=mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
WHERE sub_division='$subDivision'
AND status='Pending'
");

}
$pendingOutages = mysqli_fetch_assoc($result)['total'];

// Restored Outages
if($isSuperAdmin){

$result=mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
WHERE status='Restored'
");

}else{

$result=mysqli_query($conn,"
SELECT COUNT(*) total
FROM outages
WHERE sub_division='$subDivision'
AND status='Restored'
");

}
$restoredOutages = mysqli_fetch_assoc($result)['total'];

/*=========================================================
    SEARCH FILTER
=========================================================*/

$search = "";
$status = "";

if($isSuperAdmin){

    $where=" WHERE 1=1 ";

}else{

    $where=" WHERE sub_division='$subDivision' ";

}

if (!empty($_GET['search'])) {

    $search = mysqli_real_escape_string($conn, $_GET['search']);

    $where .= "
    AND (
        zone LIKE '%$search%'
        OR circle LIKE '%$search%'
        OR sub_division LIKE '%$search%'
        OR feeder_name LIKE '%$search%'
        OR transformer LIKE '%$search%'
        OR outage_reason LIKE '%$search%'
    )";
}

if (!empty($_GET['status'])) {

    $status = mysqli_real_escape_string($conn,$_GET['status']);

    $where .= " AND status='$status'";
}

/*=========================================================
    FETCH OUTAGES
=========================================================*/

$query = mysqli_query($conn,"
SELECT *
FROM outages
$where
ORDER BY start_time DESC
");

if(!$query){
    die(mysqli_error($conn));
}

$totalRecords = mysqli_num_rows($query);

/*=========================================================
    STATUS BADGE
=========================================================*/

function outageBadge($status){

    if($status=="Pending"){

        return "<span class='badge bg-warning text-dark'>Pending</span>";

    }

    if($status=="Restored"){

        return "<span class='badge bg-success'>Restored</span>";

    }

    return "<span class='badge bg-secondary'>$status</span>";

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Manage Outages | APDCL Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#eef3f9;
    font-family:'Segoe UI',sans-serif;
}

.container-fluid{
    padding:30px;
}

.page-title{
    font-size:30px;
    font-weight:700;
    color:#d32f2f;
}

.page-subtitle{
    color:#6c757d;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
    transition:.25s;
}

.card:hover{
    transform:translateY(-4px);
}

.stat-card{
    color:#fff;
    min-height:140px;
}

.stat-card h2{
    font-size:34px;
    font-weight:bold;
}

.stat-icon{
    font-size:42px;
    opacity:.85;
}

.form-control,
.form-select{
    height:48px;
    border-radius:10px;
}

.btn{
    border-radius:10px;
}

.table th{
    background:#1565c0;
    color:#fff;
    text-align:center;
    vertical-align:middle;
}

.table td{
    vertical-align:middle;
}

.badge{
    font-size:13px;
    padding:7px 12px;
}

footer h5{
    color:#d32f2f;
}

</style>

</head>

<body>

<div class="container-fluid">

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">

<div>

<h2 class="page-title">
<i class="fa-solid fa-map-location-dot"></i>
Manage Outages
</h2>

<p class="page-subtitle">
Manage pending and restored power outages across APDCL.
</p>

</div>

<div>

<a href="dashboard.php" class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Back to Dashboard

</a>

</div>

</div>

<?php if(isset($_SESSION['success'])){ ?>

<div class="alert alert-success alert-dismissible fade show">

<?= $_SESSION['success']; ?>

<button class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php unset($_SESSION['success']); } ?>

<?php
if (isset($_SESSION['success'])) {
?>
<div class="alert alert-success alert-dismissible fade show">
    <?= $_SESSION['success']; ?>
    <button class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php
unset($_SESSION['success']);
}
?>

<!-- ===================== DASHBOARD CARDS ===================== -->

<div class="row g-4 mb-4">

    <div class="col-lg-4">
        <div class="card stat-card bg-primary">
            <div class="card-body text-center">
                <i class="fa-solid fa-list stat-icon"></i>
                <h6>Total Outages</h6>
                <h2><?= number_format($totalOutages); ?></h2>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card stat-card bg-warning text-dark">
            <div class="card-body text-center">
                <i class="fa-solid fa-bolt stat-icon"></i>
                <h6>Pending Outages</h6>
                <h2><?= number_format($pendingOutages); ?></h2>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card stat-card bg-success">
            <div class="card-body text-center">
                <i class="fa-solid fa-circle-check stat-icon"></i>
                <h6>Restored Outages</h6>
                <h2><?= number_format($restoredOutages); ?></h2>
            </div>
        </div>
    </div>

</div>

<!-- ===================== SEARCH ===================== -->

<div class="card mb-4">

    <div class="card-header bg-white">
        <h5 class="mb-0">
            <i class="fa fa-search text-primary"></i>
            Search Outages
        </h5>
    </div>

    <div class="card-body">

        <form method="GET">

            <div class="row g-3">

                <div class="col-lg-5">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Zone, Circle, Feeder, Transformer..."
                        value="<?= htmlspecialchars($search); ?>">
                </div>

                <div class="col-lg-3">

                    <select name="status" class="form-select">

                        <option value="">All Status</option>

                        <option value="Pending"
                            <?= ($status=="Pending") ? "selected" : ""; ?>>
                            Pending
                        </option>

                        <option value="Restored"
                            <?= ($status=="Restored") ? "selected" : ""; ?>>
                            Restored
                        </option>

                    </select>

                </div>

                <div class="col-lg-2">
                    <button class="btn btn-primary w-100">
                        <i class="fa fa-search"></i>
                        Search
                    </button>
                </div>

                <div class="col-lg-2">
                    <a href="manage_outages.php" class="btn btn-secondary w-100">
                        Reset
                    </a>
                </div>

            </div>

        </form>

    </div>

</div>

<!-- ===================== OUTAGE TABLE ===================== -->

<div class="card">

    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">

        <h5 class="mb-0">
            <i class="fa-solid fa-list-check"></i>
            Outage List
        </h5>

        <a href="add_outage.php" class="btn btn-success">
            <i class="fa fa-plus"></i>
            Add Outage
        </a>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Location</th>
                        <th>Feeder</th>
                        <th>Consumers</th>
                        <th>Reason</th>
                        <th>Start Time</th>
                        <th>Status</th>
                        <th width="220">Action</th>
                    </tr>

                </thead>

                <tbody>
                    <!-- ===================== OUTAGE LIST ===================== -->

<div class="card">

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover table-bordered align-middle mb-0">

                <tbody>

                <?php if(mysqli_num_rows($query) > 0){ ?>

                    <?php while($row = mysqli_fetch_assoc($query)){ ?>

                    <tr>

                        <td class="text-center">
                            <strong>#<?= $row['id']; ?></strong>
                        </td>

                        <td>

                            <strong><?= htmlspecialchars($row['zone']); ?></strong><br>

                            <small class="text-muted">
                                <?= htmlspecialchars($row['circle']); ?>
                            </small><br>

                            <small>
                                <?= htmlspecialchars($row['sub_division']); ?>
                            </small>

                        </td>

                        <td>

                            <?= htmlspecialchars($row['feeder_name']); ?>

                            <?php if(!empty($row['transformer'])){ ?>

                                <br>

                                <small class="text-muted">

                                    Transformer:
                                    <?= htmlspecialchars($row['transformer']); ?>

                                </small>

                            <?php } ?>

                        </td>

                        <td class="text-center">

                            <?= number_format($row['consumers_affected']); ?>

                        </td>

                        <td>

                            <?= nl2br(htmlspecialchars($row['outage_reason'])); ?>

                        </td>

                        <td class="text-center">

                            <?= date("d M Y", strtotime($row['start_time'])); ?>

                            <br>

                            <small class="text-muted">

                                <?= date("h:i A", strtotime($row['start_time'])); ?>

                            </small>

                        </td>

                        <td class="text-center">

                            <?= outageBadge($row['status']); ?>

                        </td>

                        <td>

                            <div class="d-flex justify-content-center gap-2">

                                <?php if($row['status'] == 'Pending'){ ?>

                                <a href="outage_details.php?id=<?= $row['id']; ?>"
                                class="btn btn-info btn-sm"
                                title="View Pending Outage">

                                    <i class="fa fa-eye"></i>

                                </a>

                                <?php }else{ ?>

                                <a href="resolved_outage_details.php?id=<?= $row['id']; ?>"
                                class="btn btn-info btn-sm"
                                title="View Restored Outage">

                                    <i class="fa fa-eye"></i>

                                </a>

                                <?php } ?>

                                <a href="edit_outage.php?id=<?= $row['id']; ?>"
                                   class="btn btn-warning btn-sm"
                                   title="Edit">

                                    <i class="fa fa-edit"></i>

                                </a>

                                <?php if($row['status'] == "Pending"){ ?>

                                <a href="resolve_outage.php?id=<?= $row['id']; ?>"
                                   class="btn btn-success btn-sm"
                                   title="Restore">

                                    <i class="fa fa-check-circle"></i>

                                </a>

                                <?php } ?>

                                <a href="?delete=<?= $row['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Delete this outage?');"
                                   title="Delete">

                                    <i class="fa fa-trash"></i>

                                </a>

                            </div>

                        </td>

                    </tr>

                    <?php } ?>

                <?php } else { ?>

                    <tr>

                        <td colspan="8" class="text-center py-5">

                            <i class="fa-solid fa-map-location-dot fa-3x text-secondary mb-3"></i>

                            <h5>No Outages Found</h5>

                            <p class="text-muted">

                                There are currently no outage records available.

                            </p>

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!-- ===================== FOOTER BUTTONS ===================== -->

<div class="mt-4 d-flex justify-content-between flex-wrap">

    <a href="dashboard.php" class="btn btn-secondary">

        <i class="fa fa-arrow-left"></i>

        Back to Dashboard

    </a>

    <div>

        <button onclick="window.print();" class="btn btn-primary me-2">

            <i class="fa fa-print"></i>

            Print

        </button>

        <button onclick="exportTableToCSV();" class="btn btn-success">

            <i class="fa fa-file-csv"></i>

            Export CSV

        </button>

    </div>

</div>

<!-- ===================== APDCL FOOTER ===================== -->

<footer class="mt-5">

    <div class="card border-0 shadow-sm">

        <div class="card-body text-center">

            <h5 class="fw-bold text-danger">

                ⚡ Assam Power Distribution Company Limited (APDCL)

            </h5>

            <p class="text-muted mb-1">

                Outage Management System

            </p>

            <small class="text-secondary">

                © <?= date("Y"); ?> APDCL. All Rights Reserved.

            </small>

        </div>

    </div>

</footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

function exportTableToCSV(){

    let csv = [];

    let rows = document.querySelectorAll("table tr");

    rows.forEach(function(row){

        let cols = row.querySelectorAll("th,td");

        let data = [];

        cols.forEach(function(col){

            data.push('"' + col.innerText.replace(/"/g,'""') + '"');

        });

        csv.push(data.join(","));

    });

    let csvFile = new Blob([csv.join("\n")], {type:"text/csv"});

    let download = document.createElement("a");

    download.download = "outages.csv";

    download.href = window.URL.createObjectURL(csvFile);

    download.click();

}

document.querySelectorAll("tbody tr").forEach(function(row){

    row.addEventListener("mouseenter",function(){

        this.style.transition=".2s";

        this.style.transform="scale(1.01)";

    });

    row.addEventListener("mouseleave",function(){

        this.style.transform="scale(1)";

    });

});

setTimeout(function(){

    let alert=document.querySelector(".alert-success");

    if(alert){

        alert.style.opacity="0";

        alert.style.transition=".5s";

        setTimeout(function(){

            alert.remove();

        },500);

    }

},4000);

</script>

</body>
</html>