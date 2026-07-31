<?php
session_start();

/*=========================================
    ADMIN LOGIN CHECK
=========================================*/
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================
    GET ADMIN DETAILS
=========================================*/
$admin_username = $_SESSION['admin'];

$stmt = mysqli_prepare($conn,"
SELECT id, username, name
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

$admin = mysqli_fetch_assoc($result);

/*=========================================
    VALIDATE OUTAGE ID
=========================================*/
if(
    !isset($_GET['id']) ||
    !is_numeric($_GET['id'])
){
    die("Invalid Outage ID.");
}

$id = (int)$_GET['id'];

/*=========================================
    LOAD ONLY PENDING OUTAGE
=========================================*/
$stmt = mysqli_prepare($conn,"
SELECT *
FROM outages
WHERE id=?
AND status='Pending'
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"i",$id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){

    $_SESSION['error']="This outage has already been restored.";

    header("Location: manage_outages.php");
    exit();
}

$outage = mysqli_fetch_assoc($result);

/*=========================================
    VARIABLES
=========================================*/

$error = "";

/*=========================================
    RESTORE OUTAGE
=========================================*/

if(isset($_POST['resolve_outage'])){

    $resolution_note = trim($_POST['resolution_note']);

    $status = "Restored";

    $resolved_at = date("Y-m-d H:i:s");

    $resolved_by = !empty($admin['name'])
                    ? $admin['name']
                    : $admin['username'];

    $stmt = mysqli_prepare($conn,"
    UPDATE outages
    SET
        status=?,
        resolved_by=?,
        resolved_at=?,
        resolution_note=?
    WHERE id=?
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "ssssi",
        $status,
        $resolved_by,
        $resolved_at,
        $resolution_note,
        $id
    );

    if(mysqli_stmt_execute($stmt)){

        $_SESSION['success']="Outage restored successfully.";

        header("Location: resolved_outage_details.php?id=".$id);
        exit();

    }else{

        $error = mysqli_error($conn);

    }

}
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Resolve Outage | APDCL Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">

<style>

body{
    background:#eef3f8;
    font-family:'Segoe UI',sans-serif;
}

.container{
    max-width:1300px;
}

.page-header{
    background:#fff;
    border-radius:20px;
    padding:25px;
    margin-bottom:30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.logo{
    width:80px;
    height:80px;
    object-fit:contain;
}

.page-title{
    font-size:32px;
    font-weight:700;
    color:#198754;
    margin-bottom:5px;
}

.page-subtitle{
    color:#6c757d;
    margin:0;
}

.card{
    border:none;
    border-radius:18px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}

.card-header{
    font-size:20px;
    font-weight:600;
}

.form-control{
    border-radius:10px;
}

.btn{
    border-radius:10px;
}

</style>

</head>

<body>

<div class="container py-4">

<div class="page-header">

<div class="d-flex align-items-center">

<img src="../assets/images/logo-circle.png"
class="logo me-3"
alt="APDCL Logo">

<div>

<div class="page-title">

<i class="fa-solid fa-bolt text-warning"></i>

Resolve Power Outage

</div>

<p class="page-subtitle">

Assam Power Distribution Company Limited (APDCL)

</p>

</div>

</div>

<div>

<a href="dashboard.php"
class="btn btn-dark">

<i class="fa fa-home"></i>

Dashboard

</a>

<a href="manage_outages.php"
class="btn btn-secondary">

<i class="fa fa-arrow-left"></i>

Back

</a>

</div>

</div>

<?php if($error!=""){ ?>

<div class="alert alert-danger">

    <i class="fa fa-times-circle"></i>

    <?= $error ?>

</div>

<?php } ?>

<!-- ==========================================
OUTAGE DETAILS
========================================== -->

<div class="card mb-4">

    <div class="card-header bg-primary text-white">

        <i class="fa-solid fa-bolt"></i>

        Pending Outage Details

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-4 mb-3">

                <label class="fw-bold">District</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($outage['district']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Zone</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($outage['zone']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Circle</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($outage['circle']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Sub Division</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($outage['sub_division']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Feeder Name</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($outage['feeder_name']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Transformer</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($outage['transformer']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Consumers Affected</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= number_format($outage['consumers_affected']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Outage Started</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= date("d M Y h:i A",strtotime($outage['start_time'])) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Current Status</label>

                <input
                    type="text"
                    class="form-control fw-bold text-warning"
                    value="Pending"
                    readonly>

            </div>

            <div class="col-12">

                <label class="fw-bold">Outage Reason</label>

                <textarea
                    class="form-control"
                    rows="4"
                    readonly><?= htmlspecialchars($outage['outage_reason']) ?></textarea>

            </div>

        </div>

    </div>

</div>

<!-- ==========================================
RESTORE FORM
========================================== -->

<div class="card">

    <div class="card-header bg-success text-white">

        <i class="fa-solid fa-circle-check"></i>

        Restore Power Supply

    </div>

    <div class="card-body">

        <form method="POST">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="fw-bold">

                        Restored By

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars(!empty($admin['name']) ? $admin['name'] : $admin['username']) ?>"
                        readonly>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="fw-bold">

                        Restore Date & Time

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= date("d M Y h:i A") ?>"
                        readonly>

                </div>

            </div>

            <div class="mb-4">

                <label class="fw-bold">

                    Resolution Note

                </label>

                <textarea
                    name="resolution_note"
                    class="form-control"
                    rows="5"
                    placeholder="Enter restoration details..."
                    required></textarea>

            </div>

            <div class="alert alert-warning">

                <i class="fa-solid fa-circle-info"></i>

                After restoring this outage, it will automatically move from
                <strong>Pending</strong> to
                <strong>Restored</strong>.

            </div>

            <div class="d-flex justify-content-between">

                <a
                    href="manage_outages.php"
                    class="btn btn-secondary btn-lg">

                    <i class="fa fa-arrow-left"></i>

                    Cancel

                </a>

                <button
                    type="submit"
                    name="resolve_outage"
                    class="btn btn-success btn-lg">

                    <i class="fa-solid fa-check-circle"></i>

                    Restore Outage

                </button>

            </div>

        </form>

    </div>

</div>

<?php if($error!=""){ ?>

<div class="alert alert-danger">

    <i class="fa fa-times-circle"></i>

    <?= $error ?>

</div>

<?php } ?>

<!-- ==========================================
OUTAGE DETAILS
========================================== -->

<div class="card mb-4">

    <div class="card-header bg-primary text-white">

        <i class="fa-solid fa-bolt"></i>

        Pending Outage Details

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-4 mb-3">

                <label class="fw-bold">District</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($outage['district']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Zone</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($outage['zone']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Circle</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($outage['circle']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Sub Division</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($outage['sub_division']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Feeder Name</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($outage['feeder_name']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Transformer</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= htmlspecialchars($outage['transformer']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Consumers Affected</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= number_format($outage['consumers_affected']) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Outage Started</label>

                <input
                    type="text"
                    class="form-control"
                    value="<?= date("d M Y h:i A",strtotime($outage['start_time'])) ?>"
                    readonly>

            </div>

            <div class="col-md-4 mb-3">

                <label class="fw-bold">Current Status</label>

                <input
                    type="text"
                    class="form-control fw-bold text-warning"
                    value="Pending"
                    readonly>

            </div>

            <div class="col-12">

                <label class="fw-bold">Outage Reason</label>

                <textarea
                    class="form-control"
                    rows="4"
                    readonly><?= htmlspecialchars($outage['outage_reason']) ?></textarea>

            </div>

        </div>

    </div>

</div>

<!-- ==========================================
RESTORE FORM
========================================== -->

<div class="card">

    <div class="card-header bg-success text-white">

        <i class="fa-solid fa-circle-check"></i>

        Restore Power Supply

    </div>

    <div class="card-body">

        <form method="POST">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <label class="fw-bold">

                        Restored By

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= htmlspecialchars(!empty($admin['name']) ? $admin['name'] : $admin['username']) ?>"
                        readonly>

                </div>

                <div class="col-md-6 mb-3">

                    <label class="fw-bold">

                        Restore Date & Time

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="<?= date("d M Y h:i A") ?>"
                        readonly>

                </div>

            </div>

            <div class="mb-4">

                <label class="fw-bold">

                    Resolution Note

                </label>

                <textarea
                    name="resolution_note"
                    class="form-control"
                    rows="5"
                    placeholder="Enter restoration details..."
                    required></textarea>

            </div>

            <div class="alert alert-warning">

                <i class="fa-solid fa-circle-info"></i>

                After restoring this outage, it will automatically move from
                <strong>Pending</strong> to
                <strong>Restored</strong>.

            </div>

            <div class="d-flex justify-content-between">

                <a
                    href="manage_outages.php"
                    class="btn btn-secondary btn-lg">

                    <i class="fa fa-arrow-left"></i>

                    Cancel

                </a>

                <button
                    type="submit"
                    name="resolve_outage"
                    class="btn btn-success btn-lg">

                    <i class="fa-solid fa-check-circle"></i>

                    Restore Outage

                </button>

            </div>

        </form>

    </div>

</div>

<?php if($error != ""){ ?>

<div class="alert alert-danger mt-4">

    <i class="fa-solid fa-circle-exclamation"></i>

    <?= htmlspecialchars($error); ?>

</div>

<?php } ?>

</div>

<!-- ==========================================
APDCL FOOTER
========================================== -->

<footer class="mt-5">

    <div class="card border-0 shadow">

        <div class="card-body text-center">

            <img
                src="../assets/images/logo-circle.png"
                alt="APDCL Logo"
                style="height:70px;"
                class="mb-3">

            <h5 class="fw-bold text-primary">

                Assam Power Distribution Company Limited

            </h5>

            <p class="text-muted mb-1">

                Outage Restoration Management System

            </p>

            <small class="text-secondary">

                © <?= date("Y"); ?> APDCL. All Rights Reserved.

            </small>

        </div>

    </div>

</footer>

</div>

<!-- ==========================================
BOOTSTRAP
========================================== -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- ==========================================
CARD ANIMATION
========================================== -->

<script>

document.querySelectorAll(".card").forEach(function(card){

    card.addEventListener("mouseenter",function(){

        this.style.transition=".25s";
        this.style.transform="translateY(-3px)";

    });

    card.addEventListener("mouseleave",function(){

        this.style.transform="translateY(0px)";

    });

});

</script>

<!-- ==========================================
AUTO HIDE ALERT
========================================== -->

<script>

setTimeout(function(){

    let alert=document.querySelector(".alert");

    if(alert){

        alert.style.transition="0.5s";
        alert.style.opacity="0";

        setTimeout(function(){

            alert.remove();

        },500);

    }

},4000);

</script>

</body>

</html>