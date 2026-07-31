<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$consumer_no = $_SESSION['consumer'];

/*=========================================
    CHECK BILL ID
=========================================*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: bill_history.php");
    exit();
}

$billId = (int)$_GET['id'];

/*=========================================
    FETCH BILL DETAILS
=========================================*/

$billQuery = mysqli_query($conn,"
SELECT *
FROM bills
WHERE id='$billId'
AND consumer_no='$consumer_no'
LIMIT 1
");

if (!$billQuery || mysqli_num_rows($billQuery) == 0) {
    die("Bill not found.");
}

$bill = mysqli_fetch_assoc($billQuery);

/*=========================================
    FETCH CONSUMER DETAILS
=========================================*/

$userQuery = mysqli_query($conn,"
SELECT *
FROM users
WHERE consumer_no='$consumer_no'
LIMIT 1
");

if (!$userQuery || mysqli_num_rows($userQuery) == 0) {
    die("Consumer not found.");
}

$user = mysqli_fetch_assoc($userQuery);

/*=========================================
    BILL INFORMATION
=========================================*/

$billNo          = $bill['bill_no'];
$billMonth       = $bill['month'];
$billDate        = $bill['bill_date'];
$dueDate         = $bill['due_date'];

$previousReading = (float)$bill['previous_reading'];
$currentReading  = (float)$bill['current_reading'];

$units           = $currentReading - $previousReading;

$energyCharge    = (float)$bill['energy_charge'];
$fixedCharge     = (float)$bill['fixed_charge'];
$electricityDuty = (float)$bill['electricity_duty'];
$fpppaCharge     = (float)$bill['fpppa_charge'];

$outstanding     = (float)$bill['outstanding_amount'];
$adjustment      = (float)$bill['adjustment_amount'];

$govSubsidy      = (float)$bill['government_subsidy'];
$tariffSubsidy   = (float)$bill['tariff_subsidy'];
$solarRebate     = (float)$bill['solar_rebate'];
$otherSubsidy    = (float)$bill['subsidy'];

$totalBill       = (float)$bill['total_bill'];

$payBeforeDue    = (float)$bill['payable_before_due'];
$payAfterDue     = (float)$bill['payable_after_due'];

$paymentStatus   = $bill['status'];
$paymentMode     = $bill['payment_mode'];

$mf              = $bill['mf'];
$powerFactor     = $bill['power_factor'];

/*=========================================
    AMOUNT IN WORDS
=========================================*/

function numberToWords($number)
{
    $ones = [
        "", "One", "Two", "Three", "Four", "Five",
        "Six", "Seven", "Eight", "Nine", "Ten",
        "Eleven", "Twelve", "Thirteen", "Fourteen",
        "Fifteen", "Sixteen", "Seventeen",
        "Eighteen", "Nineteen"
    ];

    $tens = [
        "", "", "Twenty", "Thirty", "Forty",
        "Fifty", "Sixty", "Seventy",
        "Eighty", "Ninety"
    ];

    if ($number == 0) return "Zero";

    if ($number < 20)
        return $ones[$number];

    if ($number < 100)
        return $tens[floor($number/10)] .
            (($number % 10) ? " ".$ones[$number % 10] : "");

    if ($number < 1000)
        return $ones[floor($number/100)] .
            " Hundred" .
            (($number % 100) ? " and ".numberToWords($number % 100) : "");

    if ($number < 100000)
        return numberToWords(floor($number/1000)).
            " Thousand".
            (($number % 1000) ? " ".numberToWords($number % 1000) : "");

    if ($number < 10000000)
        return numberToWords(floor($number/100000)).
            " Lakh".
            (($number % 100000) ? " ".numberToWords($number % 100000) : "");

    return numberToWords(floor($number/10000000)).
        " Crore".
        (($number % 10000000) ? " ".numberToWords($number % 10000000) : "");
}

$amountWords = strtoupper(numberToWords(round($totalBill))) . " RUPEES ONLY";

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>APDCL Electricity Bill</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#e9ecef;
    font-family:'Segoe UI',sans-serif;
}

.bill-container{
    width:1000px;
    margin:30px auto;
    background:#fff;
    border:1px solid #dcdcdc;
    box-shadow:0 10px 30px rgba(0,0,0,.18);
}

.bill-header{
    background:#0056b3;
    color:#fff;
    padding:20px 30px;
}

.bill-header img{
    width:90px;
}

.company-name{
    font-size:30px;
    font-weight:bold;
}

.company-sub{
    font-size:15px;
}

.bill-title{
    background:#f8f9fa;
    border-bottom:3px solid #0056b3;
    color:#0056b3;
    font-size:24px;
    font-weight:bold;
    text-align:center;
    padding:15px;
}

.section-title{
    background:#0056b3;
    color:#fff;
    padding:10px 15px;
    font-size:18px;
    font-weight:bold;
}

.info-table td{
    padding:8px;
    border:1px solid #dee2e6;
}

.table td,
.table th{
    vertical-align:middle;
}

@media print{

body{
    background:#fff;
}

.bill-container{
    width:100%;
    margin:0;
    border:none;
    box-shadow:none;
}

.btn{
    display:none;
}

}

</style>

</head>

<body>

<div class="bill-container">

<!-- ============================
        APDCL HEADER
============================= -->

<div class="bill-header">

<div class="row align-items-center">

<div class="col-md-2 text-center">

<img src="../assets/images/logo-circle.png">

</div>

<div class="col-md-8 text-center">

<div class="company-name">
ASSAM POWER DISTRIBUTION COMPANY LIMITED
</div>

<div class="company-sub">
(A Government of Assam Public Sector Undertaking)
</div>

<div>
Bijulee Bhawan, Paltan Bazar, Guwahati – 781001
</div>

<div>
Consumer Care : 1912 | Website : www.apdcl.org
</div>

</div>

</div>

</div>

<div class="bill-title">

⚡ ELECTRICITY CONSUMPTION BILL

</div>

<!-- ============================
    CONSUMER & BILL DETAILS
============================= -->

<div class="row m-3">

<div class="col-md-6">

<div class="card border-primary">

<div class="card-header bg-primary text-white">

<i class="bi bi-person-fill"></i>

Consumer Details

</div>

<div class="card-body p-0">

<table class="table table-bordered mb-0 info-table">

<tr>
<th width="40%">Consumer No</th>
<td><?= htmlspecialchars($consumer_no) ?></td>
</tr>

<tr>
<th>Name</th>
<td><?= htmlspecialchars($user['name']) ?></td>
</tr>

<tr>
<th>Father Name</th>
<td><?= htmlspecialchars($user['father_name']) ?></td>
</tr>

<tr>
<th>Category</th>
<td><?= htmlspecialchars($user['category']) ?></td>
</tr>

<tr>
<th>Meter No</th>
<td><?= htmlspecialchars($user['meter_no']) ?></td>
</tr>

<tr>
<th>Address</th>
<td><?= htmlspecialchars($user['address']) ?></td>
</tr>

</table>

</div>

</div>

</div>

<div class="col-md-6">

<div class="card border-success">

<div class="card-header bg-success text-white">

<i class="bi bi-receipt"></i>

Bill Details

</div>

<div class="card-body p-0">

<table class="table table-bordered mb-0 info-table">

<tr>
<th width="40%">Bill No</th>
<td><?= htmlspecialchars($billNo) ?></td>
</tr>

<tr>
<th>Bill Month</th>
<td><?= htmlspecialchars($billMonth) ?></td>
</tr>

<tr>
<th>Bill Date</th>
<td><?= date("d M Y",strtotime($billDate)) ?></td>
</tr>

<tr>
<th>Due Date</th>
<td><?= date("d M Y",strtotime($dueDate)) ?></td>
</tr>

<tr>
<th>Payment Status</th>
<td>

<?php
if($paymentStatus=="Paid"){
    echo "<span class='badge bg-success'>PAID</span>";
}else{
    echo "<span class='badge bg-danger'>UNPAID</span>";
}
?>

</td>
</tr>

<tr>
<th>Total Bill</th>
<td class="fw-bold text-primary">

₹ <?= number_format($totalBill,2) ?>

</td>
</tr>

</table>

</div>

</div>

</div>

</div>
<!-- ===========================================
        METER READING DETAILS
=========================================== -->

<div class="section-title">
    <i class="bi bi-speedometer2"></i>
    Meter Reading Details
</div>

<div class="p-3">

<table class="table table-bordered text-center">

<thead class="table-primary">

<tr>

<th>Previous Reading</th>
<th>Current Reading</th>
<th>Units Consumed</th>
<th>MF</th>
<th>Power Factor</th>

</tr>

</thead>

<tbody>

<tr>

<td><?= number_format($previousReading,2) ?></td>

<td><?= number_format($currentReading,2) ?></td>

<td>

<span class="badge bg-primary fs-6">

<?= number_format($units) ?>

Units

</span>

</td>

<td><?= $mf ?></td>

<td><?= $powerFactor ?>%</td>

</tr>

</tbody>

</table>

</div>

<!-- ===========================================
        BILL CHARGES
=========================================== -->

<div class="section-title">
    <i class="bi bi-cash-stack"></i>
    Electricity Bill Charges
</div>

<div class="p-3">

<table class="table table-bordered table-striped">

<thead class="table-primary">

<tr>

<th>Description</th>

<th width="220" class="text-end">

Amount (₹)

</th>

</tr>

</thead>

<tbody>

<tr>

<td>Energy Charge</td>

<td class="text-end">

<?= number_format($energyCharge,2) ?>

</td>

</tr>

<tr>

<td>Fixed Charge</td>

<td class="text-end">

<?= number_format($fixedCharge,2) ?>

</td>

</tr>

<tr>

<td>Electricity Duty</td>

<td class="text-end">

<?= number_format($electricityDuty,2) ?>

</td>

</tr>

<tr>

<td>FPPPA Charge</td>

<td class="text-end">

<?= number_format($fpppaCharge,2) ?>

</td>

</tr>

<tr>

<td>Outstanding Amount</td>

<td class="text-end text-danger">

<?= number_format($outstanding,2) ?>

</td>

</tr>

<tr>

<td>Adjustment Amount</td>

<td class="text-end text-success">

<?= number_format($adjustment,2) ?>

</td>

</tr>

<tr>

<td>Government Subsidy</td>

<td class="text-end text-success">

- <?= number_format($govSubsidy,2) ?>

</td>

</tr>

<tr>

<td>Tariff Subsidy</td>

<td class="text-end text-success">

- <?= number_format($tariffSubsidy,2) ?>

</td>

</tr>

<tr>

<td>Solar Rebate</td>

<td class="text-end text-success">

- <?= number_format($solarRebate,2) ?>

</td>

</tr>

<tr>

<td>Other Subsidy</td>

<td class="text-end text-success">

- <?= number_format($otherSubsidy,2) ?>

</td>

</tr>

<tr class="table-warning">

<th class="fs-5">

TOTAL BILL AMOUNT

</th>

<th class="text-end fs-4 text-primary">

₹ <?= number_format($totalBill,2) ?>

</th>

</tr>

</tbody>

</table>

</div>
<!-- ===========================================
        PAYMENT SUMMARY
=========================================== -->

<div class="section-title">
    <i class="bi bi-credit-card-2-front-fill"></i>
    Payment Summary
</div>

<div class="row p-3">

<div class="col-md-7">

<table class="table table-bordered">

<tr>
<th width="45%">Pay Before Due Date</th>
<td class="fw-bold text-success">
₹ <?= number_format($payBeforeDue,2) ?>
</td>
</tr>

<tr>
<th>Pay After Due Date</th>
<td class="fw-bold text-danger">
₹ <?= number_format($payAfterDue,2) ?>
</td>
</tr>

<tr>
<th>Due Date</th>
<td>
<?= date("d M Y",strtotime($dueDate)) ?>
</td>
</tr>

<tr>
<th>Payment Mode</th>
<td>

<?php
echo empty($paymentMode)
? "Not Paid Yet"
: htmlspecialchars($paymentMode);
?>

</td>
</tr>

<tr>
<th>Status</th>

<td>

<?php

if($paymentStatus=="Paid")
{
    echo "<span class='badge bg-success fs-6'>PAID</span>";
}
else
{
    echo "<span class='badge bg-danger fs-6'>UNPAID</span>";
}

?>

</td>

</tr>

</table>

</div>

<div class="col-md-5">

<div class="card border-success shadow">

<div class="card-header bg-success text-white">

<i class="bi bi-cash-coin"></i>

Amount in Words

</div>

<div class="card-body">

<h5 class="text-success fw-bold">

<?= $amountWords ?>

</h5>

<hr>

<h2 class="text-end text-primary fw-bold">

₹ <?= number_format($totalBill,2) ?>

</h2>

</div>

</div>

</div>

</div>

<!-- ===========================================
        DIGITAL PAYMENT
=========================================== -->

<div class="section-title">

<i class="bi bi-qr-code-scan"></i>

Digital Payment

</div>

<div class="row p-4 align-items-center">

<div class="col-md-3 text-center">

<img src="../assets/images/qr-placeholder.png"
class="img-fluid"
style="max-width:180px;">

</div>

<div class="col-md-9">

<h4 class="text-primary">

Scan & Pay

</h4>

<p>

Pay instantly using

<strong>BHIM UPI</strong>,
<strong>PhonePe</strong>,
<strong>Google Pay</strong>,
<strong>Paytm</strong> or
<strong>any UPI App</strong>.

</p>

<table class="table table-borderless">

<tr>

<th width="180">

Consumer No

</th>

<td>

<?= htmlspecialchars($consumer_no) ?>

</td>

</tr>

<tr>

<th>

Bill Amount

</th>

<td class="fw-bold text-success">

₹ <?= number_format($totalBill,2) ?>

</td>

</tr>

</table>

</div>

</div>

<!-- ===========================================
        IMPORTANT INSTRUCTIONS
=========================================== -->

<div class="section-title">

<i class="bi bi-exclamation-triangle-fill"></i>

Important Instructions

</div>

<div class="p-4">

<div class="alert alert-warning mb-0">

<ul class="mb-0">

<li>Pay your bill before the due date to avoid late payment surcharge.</li>

<li>Always quote your Consumer Number while making payment.</li>

<li>Keep this electricity bill safely for future reference.</li>

<li>For power failure or emergencies, contact APDCL Consumer Care (1912).</li>

<li>This bill is generated electronically and is valid without signature.</li>

</ul>

</div>

</div>
<!-- ===========================================
        DECLARATION
=========================================== -->

<div class="section-title">
    <i class="bi bi-file-earmark-text"></i>
    Declaration
</div>

<div class="p-4">

<p class="mb-2">
This electricity bill has been generated electronically by the
<strong>Assam Power Distribution Company Limited (APDCL)</strong>.
No physical signature is required.
</p>

<p class="mb-0">
Consumers are requested to verify all bill particulars and report any discrepancy
to the nearest APDCL office within seven (7) days.
</p>

</div>

<!-- ===========================================
        SIGNATURE
=========================================== -->

<div class="row px-4 pb-4">

<div class="col-md-6">

<h6 class="fw-bold">Customer Signature</h6>

<br><br>

<hr>

</div>

<div class="col-md-6 text-end">

<h6 class="fw-bold">Authorized Signatory</h6>

<br>

<strong>APDCL</strong>

<hr>

</div>

</div>

<!-- ===========================================
        ACTION BUTTONS
=========================================== -->

<div class="text-center mb-4">

<button onclick="window.print()" class="btn btn-primary btn-lg me-2">

<i class="bi bi-printer-fill"></i>

Print Bill

</button>

<a href="dashboard.php" class="btn btn-success btn-lg me-2">

<i class="bi bi-house-door-fill"></i>

Dashboard

</a>

<a href="bill_history.php" class="btn btn-secondary btn-lg">

<i class="bi bi-arrow-left-circle-fill"></i>

Bill History

</a>

</div>

<!-- ===========================================
        FOOTER
=========================================== -->

<div style="background:#0056b3;color:white;padding:20px;text-align:center;">

<h5 class="mb-2">

ASSAM POWER DISTRIBUTION COMPANY LIMITED

</h5>

<p class="mb-1">

Bijulee Bhawan, Paltan Bazar, Guwahati – 781001

</p>

<p class="mb-1">

Customer Care : <strong>1912</strong>

</p>

<p class="mb-0">

© <?= date("Y") ?> APDCL Consumer Portal |
Internship Demo Project

</p>

</div>

</div>

</body>

</html>