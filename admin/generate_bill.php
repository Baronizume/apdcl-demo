<?php
session_start();
include("../db.php");

/*=========================================
    ADMIN LOGIN CHECK
=========================================*/

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/*=========================================
    FETCH ADMIN DETAILS
=========================================*/

$admin_username = $_SESSION['admin'];

$stmt = mysqli_prepare($conn,"
SELECT *
FROM admin
WHERE username=?
LIMIT 1
");

mysqli_stmt_bind_param($stmt,"s",$admin_username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){
    session_destroy();
    header("Location: login.php");
    exit();
}

$admin = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

/*=========================================
    ADMIN DETAILS
=========================================*/
$admin_name = $admin['name'] ?? "";
$role = $admin['role'] ?? "";

$admin_zone = $admin['zone'] ?? "";
$admin_circle = $admin['circle'] ?? "";
$admin_subdivision = $admin['sub_division'] ?? "";

/*=========================================
    BILL SETTINGS
=========================================*/

$energy_rate = 7.74;
$fixed_charge = 150.00;

$electricity_duty_percent = 5;
$subsidy_percent = 10;

/*=========================================
    AUTO BILL NUMBER
=========================================*/

$bill_no = "APDCL".date("YmdHis");

$bill_date = date("Y-m-d");

$due_date = date("Y-m-d",strtotime("+15 days"));

$message = "";

/*=========================================
    DEFAULT VALUES
=========================================*/

$consumer_no = "";
$consumer_name = "";
$father_name = "";

$mobile = "";
$address = "";

$meter_no = "";
$category = "";

$dtr_no = "";
$pole_no = "";

$zone = "";
$circle = "";
$sub_division = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $zone = isset($_POST['zone']) ? trim($_POST['zone']) : "";
    $circle = isset($_POST['circle']) ? trim($_POST['circle']) : "";
    $sub_division = isset($_POST['sub_division']) ? trim($_POST['sub_division']) : "";

}

/* Default values if subdivision is empty */

if (empty($sub_division)) {

    if ($consumer_no == "058000007641") {

        $zone = "Guwahati";
        $circle = "Central Guwahati";
        $sub_division = "Narengi";

    } elseif ($consumer_no == "0890000052503") {

        $zone = "Guwahati";
        $circle = "Central Guwahati";
        $sub_division = "Zoo Road";

    }

}

$month = date("Y-m");

$previous_reading = 0;
$current_reading = 0;

$units = 0;

$energy_charge = 0;
$electricity_duty = 0;
$subsidy = 0;

$total_bill = $fixed_charge;

/*=========================================
    BILL PERIOD
=========================================*/

function getBillPeriod($month){

    return [

        "from"=>date("Y-m-01",strtotime($month)),
        "to"=>date("Y-m-t",strtotime($month)),
        "days"=>date("t",strtotime($month))

    ];

}

$period = getBillPeriod($month);

$bill_period_from = $period['from'];
$bill_period_to   = $period['to'];
$billing_days     = $period['days'];

/*=========================================
    SAVE BILL
=========================================*/

if(isset($_POST['save'])){

    $consumer_no      = trim($_POST['consumer_no']);
    $consumer_name    = trim($_POST['consumer_name']);
    $father_name      = trim($_POST['father_name']);

    $mobile           = trim($_POST['mobile']);
    $address          = trim($_POST['address']);

    $meter_no         = trim($_POST['meter_no']);
    $category         = trim($_POST['category']);

    $dtr_no           = trim($_POST['dtr_no']);
    $pole_no          = trim($_POST['pole_no']);

    $zone             = trim($_POST['zone']);
    $circle           = trim($_POST['circle']);
    $sub_division     = trim($_POST['sub_division']);

    $month            = trim($_POST['month']);

    $previous_reading = (float)$_POST['previous_reading'];
    $current_reading  = (float)$_POST['current_reading'];

    /* Bill Calculation */

    $units = $current_reading - $previous_reading;

    if($units < 0){
        $units = 0;
    }

    $energy_charge = $units * $energy_rate;

    $electricity_duty = ($energy_charge * 5) / 100;

    $subsidy = ($energy_charge * 10) / 100;

    $total_bill = $energy_charge + $fixed_charge + $electricity_duty - $subsidy;

    $status = "Pending";

    $generated_by = $admin_name;

    $users = 1;

    /* Bill Period */

    $period = getBillPeriod($month);

    $bill_period_from = $period['from'];
    $bill_period_to   = $period['to'];
    $billing_days     = $period['days'];

    /* Duplicate Bill Check */

    $check = mysqli_prepare($conn,"
        SELECT id
        FROM bills
        WHERE consumer_no=?
        AND month=?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($check,"ss",$consumer_no,$month);
    mysqli_stmt_execute($check);

    $exists = mysqli_stmt_get_result($check);

    if(mysqli_num_rows($exists)>0){

        $message="<div class='alert alert-danger'>
        Bill already generated for this month.
        </div>";

    }else{

        /*=========================================
        INSERT BILL
        =========================================*/

        $insert = mysqli_prepare($conn,"
        INSERT INTO bills
        (
            consumer_no,
            consumer_name,
            father_name,
            bill_no,
            previous_reading,
            current_reading,
            units,
            energy_charge,
            energy_rate,
            fixed_charge,
            electricity_duty,
            subsidy,
            total_bill,
            status,
            generated_by,
            bill_date,
            due_date,
            month,
            mobile,
            address,
            meter_no,
            category,
            dtr_no,
            pole_no,
            zone,
            circle,
            sub_division,
            users
        )
        VALUES
        (
            ?,?,?,?,?,?,?,?,?,?,
            ?,?,?,?,?,?,?,?,?,?,
            ?,?,?,?,?,?,?,?
        )
        ");

        if(!$insert){
            die("Prepare Failed : ".mysqli_error($conn));
        }

        mysqli_stmt_bind_param(
            $insert,
            "ssssdddddddddssssssssssssssi",

            $consumer_no,
            $consumer_name,
            $father_name,
            $bill_no,

            $previous_reading,
            $current_reading,
            $units,
            $energy_charge,
            $energy_rate,
            $fixed_charge,
            $electricity_duty,
            $subsidy,
            $total_bill,

            $status,
            $generated_by,
            $bill_date,
            $due_date,
            $month,
            $mobile,
            $address,
            $meter_no,
            $category,
            $dtr_no,
            $pole_no,
            $zone,
            $circle,
            $sub_division,

            $users
        );

        if(mysqli_stmt_execute($insert)){

            $message = "
            <div class='alert alert-success'>
                <strong>Success!</strong><br>
                Electricity Bill Generated Successfully.
            </div>";

        }else{

            $message = "
            <div class='alert alert-danger'>
                <strong>Error :</strong><br>"
                . mysqli_stmt_error($insert) .
            "</div>";

        }
    mysqli_stmt_close($insert);

}
    mysqli_stmt_close($check);

}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Generate Electricity Bill</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>

body{
    background:#eef3f8;
    font-family:'Segoe UI',sans-serif;
}

.navbar{
    height:75px;
    background:linear-gradient(90deg,#0d47a1,#1565c0,#1976d2);
    box-shadow:0 4px 15px rgba(0,0,0,.2);
    border-bottom:4px solid #42a5f5;
}

.logo{
    width:55px;
    height:55px;
    border-radius:50%;
    background:#fff;
    padding:5px;
    margin-right:12px;
}

.content{
    max-width:1450px;
    margin:35px auto;
    padding:20px;
}
.card{
    border:none;
    border-radius:18px;
    box-shadow:0 12px 30px rgba(0,0,0,.08);
    overflow:hidden;
}

.card-header{
    font-size:20px;
    font-weight:700;
    padding:16px 22px;
}

.form-control{
    border-radius:12px;
    height:52px;
    font-size:16px;
    border:1px solid #d7dde5;
}

textarea.form-control{
    height:auto;
}

.form-control:focus{
    border-color:#1976d2;
    box-shadow:0 0 0 .2rem rgba(25,118,210,.15);
}

.btn{
    border-radius:12px;
    padding:12px 24px;
    font-weight:600;
}

input[readonly],
textarea[readonly]{
    background:#f5f8fc !important;
    font-weight:600;
}

.card{
    transition:.3s;
}

.card:hover{
    transform:translateY(-4px);
    box-shadow:0 18px 35px rgba(0,0,0,.12);
}

#total_bill{
    font-size:28px;
    font-weight:700;
    color:#198754 !important;
    background:#eafaf1;
    border:2px solid #198754;
}

</style>

</head>

<body>

<nav class="navbar">

<div class="container-fluid">

<div class="d-flex align-items-center">

<img src="../assets/images/logo-circle.png" class="logo">

<div>

<h4 class="text-white mb-0">
APDCL Electricity Billing System
</h4>

<small class="text-white">
Assam Power Distribution Company Limited
</small>

</div>

</div>

<div class="text-white text-end">

<b><?= htmlspecialchars($admin_name) ?></b><br>

<?= htmlspecialchars($role) ?>

</div>

</div>

</nav>

</div>

<div class="content">

<?= $message ?>

<form method="POST">

<div class="row">

<h3 class="mb-3 fw-bold text-primary">
<i class="bi bi-person-badge"></i>
Consumer Information
</h3>

<div class="col-lg-6">

<div class="card mb-4">

<div class="card-header bg-primary text-white">

<i class="bi bi-person-vcard-fill"></i>

Consumer Details

</div>

<div class="card-body">

<div class="mb-3">

<label class="form-label fw-bold">

Consumer Number

</label>

<div class="input-group input-group-lg">

<input
type="text"
class="form-control"
name="consumer_no"
id="consumer_no"
placeholder="Enter Consumer Number"
value="<?= htmlspecialchars($consumer_no) ?>"
required>

<button
type="button"
class="btn btn-primary px-4 shadow-sm"
onclick="loadConsumer()">

<i class="bi bi-search"></i>
Search

</button>

</div>

<small class="text-muted">
Press Enter or click Search to fetch consumer details.
</small>

</div>

<div class="mb-3">

<label class="form-label">

Consumer Name

</label>

<input
type="text"
class="form-control"
id="consumer_name"
name="consumer_name"
value="<?= htmlspecialchars($consumer_name) ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

Father / Husband Name

</label>

<input
type="text"
class="form-control"
id="father_name"
name="father_name"
value="<?= htmlspecialchars($father_name) ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

Mobile Number

</label>

<input
type="text"
class="form-control"
id="mobile"
name="mobile"
value="<?= htmlspecialchars($mobile) ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label">

Address

</label>

<textarea
class="form-control"
rows="3"
id="address"
name="address"
readonly><?= htmlspecialchars($address) ?></textarea>
</div>

<div class="row">

<div class="col-md-6">

<label class="form-label">
Meter Number
</label>

<input
type="text"
class="form-control"
id="meter_no"
name="meter_no"
value="<?= htmlspecialchars($meter_no) ?>"
readonly>

</div>

<div class="col-md-6">

<label class="form-label">
Category
</label>

<input
type="text"
class="form-control"
id="category"
name="category"
value="<?= htmlspecialchars($category) ?>"
readonly>

</div>

</div>

<div class="row mt-3">

<div class="col-md-6">

<label class="form-label">
DTR Number
</label>

<input
type="text"
class="form-control"
id="dtr_no"
name="dtr_no"
value="<?= htmlspecialchars($dtr_no) ?>"
readonly>

</div>

<div class="col-md-6">

<label class="form-label">
Pole Number
</label>

<input
type="text"
class="form-control"
id="pole_no"
name="pole_no"
value="<?= htmlspecialchars($pole_no) ?>"
readonly>

</div>

</div>

<div class="row mt-3">

<div class="col-md-4">

<label class="form-label">
Zone
</label>

<input
type="text"
class="form-control"
id="zone"
name="zone"
value="<?= htmlspecialchars($zone) ?>"
readonly>

</div>

<div class="col-md-4">

<label class="form-label">
Circle
</label>

<input
type="text"
class="form-control"
id="circle"
name="circle"
value="<?= htmlspecialchars($circle) ?>"
readonly>

</div>

<div class="col-md-4">

<label class="form-label">
Sub Division
</label>

<input
type="text"
class="form-control"
id="sub_division"
name="sub_division"
value="<?= htmlspecialchars($sub_division) ?>"
readonly>

</div>

</div>

</div>

</div>

</div>

<!-- ================= BILL DETAILS ================= -->

<div class="col-lg-6">

<h3 class="mb-3 fw-bold text-success">
<i class="bi bi-receipt"></i>
Bill Information
</h3>

<div class="card mb-4">

<div class="card-header bg-success text-white">

<i class="bi bi-lightning-charge-fill"></i>

Bill Details

</div>

<div class="card-body">

<div class="mb-3">

<label class="form-label fw-bold">
Bill Number
</label>

<input
type="text"
class="form-control"
value="<?= $bill_no ?>"
readonly>

</div>

<div class="mb-3">

<label class="form-label fw-bold">
Billing Month
</label>

<input
type="month"
class="form-control"
name="month"
value="<?= $month ?>"
required>

</div>

<div class="row">

<div class="col-md-6">

<label class="form-label">
Previous Reading
</label>

<input
type="number"
step="0.01"
class="form-control"
id="previous_reading"
name="previous_reading"
value="<?= $previous_reading ?>"
readonly>

</div>

<div class="col-md-6">

<label class="form-label">
Current Reading
</label>

<input
type="number"
step="0.01"
class="form-control"
id="current_reading"
name="current_reading"
value="<?= $current_reading ?>"
required>

</div>

</div>

<div class="mt-3">

<label class="form-label fw-bold">
Units Consumed
</label>

<input
type="text"
class="form-control bg-light"
id="units"
name="units"
value="<?= $units ?>"
readonly>

</div>

<hr>

<div class="row">

<div class="col-md-6">

<label class="form-label">
Energy Rate
</label>

<input
type="text"
class="form-control"
value="₹ <?= number_format($energy_rate,2) ?>/Unit"
readonly>

</div>

<div class="col-md-6">

<label class="form-label">
Fixed Charge
</label>

<input
type="text"
class="form-control"
value="₹ <?= number_format($fixed_charge,2) ?>"
readonly>

</div>

</div>

<div class="row mt-3">

<div class="col-md-6">

<label class="form-label">
Bill Date
</label>

<input
type="date"
class="form-control"
value="<?= $bill_date ?>"
readonly>

</div>

<div class="col-md-6">

<label class="form-label">
Due Date
</label>

<input
type="date"
class="form-control"
value="<?= $due_date ?>"
readonly>

</div>

</div>

</div>

</div>

</div>

</div>
<!-- ================= BILL CALCULATION ================= -->

<div class="card mb-4">

    <div class="card-header bg-dark text-white">

        <i class="bi bi-calculator-fill"></i>

        Bill Calculation

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-3">

                <label class="form-label fw-bold">
                    Energy Charge
                </label>

                <input
                type="text"
                id="energy_charge"
                class="form-control"
                value="<?= number_format($energy_charge,2) ?>"
                readonly>

            </div>

            <div class="col-md-3">

                <label class="form-label fw-bold">
                    Electricity Duty (5%)
                </label>

                <input
                type="text"
                id="electricity_duty"
                class="form-control"
                value="<?= number_format($electricity_duty,2) ?>"
                readonly>

            </div>

            <div class="col-md-3">

                <label class="form-label fw-bold">
                    Subsidy (10%)
                </label>

                <input
                type="text"
                id="subsidy"
                class="form-control"
                value="<?= number_format($subsidy,2) ?>"
                readonly>

            </div>

                <div class="col-md-3">

                <label class="form-label fw-bold">
                    Total Bill
                </label>

                <div class="input-group">

                    <span class="input-group-text bg-danger text-white">
                        ₹
                    </span>

                    <input
                        type="text"
                        id="total_bill"
                        class="form-control fw-bold text-danger text-end"
                        value="<?= number_format($total_bill,2) ?>"
                        readonly>

                </div>

            </div>

        </div>

    </div>

</div>

<!-- ================= BUTTONS ================= -->

<div class="d-flex justify-content-center gap-3 mt-4 mb-5">

    <button
        type="submit"
        name="save"
        class="btn btn-outline-primary btn-lg px-5 shadow">

        <i class="bi bi-lightning-charge-fill"></i>

        Generate Bill

    </button>

    <a
        href="dashboard.php"
        class="btn btn-primary btn-lg ms-2">

        <i class="bi bi-house-door-fill"></i>

        Back to Dashboard

    </a>

</div>

</form>

<!-- ================= FOOTER ================= -->

<footer class="card border-0 shadow-sm">

    <div class="card-body">

        <div class="row">

            <div class="col-md-6">

                <h5 class="text-primary">

                    ⚡ APDCL Electricity Billing Management System

                </h5>

                <small class="text-muted">

                    Assam Power Distribution Company Limited

                </small>

            </div>

            <div class="col-md-6 text-end">

                <strong>

                    <?= htmlspecialchars($admin_name) ?>

                </strong>

                <br>

                <span class="badge bg-primary">

                    <?= htmlspecialchars($role) ?>

                </span>

            </div>

        </div>

        <hr>

        <div class="d-flex justify-content-between">

            <span>

                © <?= date("Y") ?> APDCL. All Rights Reserved.

            </span>

            <span id="clock" class="fw-bold text-primary"></span>

        </div>

    </div>

</footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

function calculateBill(){

    let previous = parseFloat(document.getElementById("previous_reading").value) || 0;
    let current = parseFloat(document.getElementById("current_reading").value) || 0;

    let units = current - previous;

    if(units < 0){
        units = 0;
    }

    document.getElementById("units").value = units.toFixed(2);

    const energyRate = <?= $energy_rate ?>;
    const fixedCharge = <?= $fixed_charge ?>;

    let energyCharge = units * energyRate;
    let duty = energyCharge * 0.05;
    let subsidy = energyCharge * 0.10;

    let total = energyCharge + fixedCharge + duty - subsidy;

    document.getElementById("energy_charge").value = energyCharge.toFixed(2);
    document.getElementById("electricity_duty").value = duty.toFixed(2);
    document.getElementById("subsidy").value = subsidy.toFixed(2);
    document.getElementById("total_bill").value = total.toFixed(2);

}

/*=========================================
    FETCH CONSUMER
=========================================*/

function loadConsumer(){

    let consumerNo = document.getElementById("consumer_no").value.trim();

    if(consumerNo==""){

        document.getElementById("consumer_no").focus();
        return;

    }

    fetch("fetch_consumer.php?consumer_no="+encodeURIComponent(consumerNo))

    .then(response=>response.json())

    .then(data=>{

        if(data.error){

            alert(data.error);
            return;

        }

        document.getElementById("consumer_name").value=data.name || "";
        document.getElementById("father_name").value=data.father_name || "";
        document.getElementById("mobile").value=data.mobile || "";
        document.getElementById("address").value=data.address || "";
        document.getElementById("meter_no").value=data.meter_no || "";
        document.getElementById("category").value=data.category || "";
        document.getElementById("dtr_no").value=data.dtr_no || "";
        document.getElementById("pole_no").value=data.pole_no || "";
        document.getElementById("zone").value=data.zone || "";
        document.getElementById("circle").value=data.circle || "";
        document.getElementById("sub_division").value=data.sub_division || "";

        document.getElementById("previous_reading").value=data.previous_reading || 0;

        calculateBill();

        document.getElementById("current_reading").focus();

    })

    .catch(function(error){

        console.log(error);

        alert("Unable to fetch consumer details.");

    });

}

/*=========================================
    EVENTS
=========================================*/

document.getElementById("consumer_no").addEventListener("keypress",function(e){

    if(e.key==="Enter"){

        e.preventDefault();

        loadConsumer();

    }

});

document.getElementById("current_reading").addEventListener("keyup",calculateBill);
document.getElementById("current_reading").addEventListener("change",calculateBill);

/*=========================================
    LIVE CLOCK
=========================================*/

function updateClock(){

    let now=new Date();

    document.getElementById("clock").innerHTML=
        now.toLocaleDateString("en-IN")+
        " | "+
        now.toLocaleTimeString("en-IN");

}

updateClock();

setInterval(updateClock,1000);

calculateBill();

</script>

</body>
</html>