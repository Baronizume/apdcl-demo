<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

if (isset($_GET['id']) && is_numeric($_GET['id'])) {

    $id = (int)$_GET['id'];

    $stmt = mysqli_prepare($conn,"
        SELECT *
        FROM bills
        WHERE id=?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($stmt,"i",$id);

} elseif (isset($_GET['bill_no'])) {

    $bill_no = trim($_GET['bill_no']);

    $stmt = mysqli_prepare($conn,"
        SELECT *
        FROM bills
        WHERE bill_no=?
        LIMIT 1
    ");

    mysqli_stmt_bind_param($stmt,"s",$bill_no);

} else {

    die("Invalid Bill ID.");

}

mysqli_stmt_execute($stmt);

$billQuery = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($billQuery)==0){
    die("Bill not found.");
}

$bill = mysqli_fetch_assoc($billQuery);

$user = $bill;

$defaults = [
    'previous_reading' => 0,
    'current_reading' => 0,
    'units' => 0,
    'connected_load' => 0,
    'energy_rate' => 7.74,
    'fixed_rate' => 70,
    'energy_charge' => 0,
    'fixed_charge' => 0,
    'fpppa_charge' => 0,
    'electricity_duty' => 0,
    'tariff_subsidy' => 0,
    'current_demand' => 0,
    'outstanding_amount' => 0,
    'adjustment_amount' => 0,
    'government_subsidy' => 0,
    'solar_rebate' => 0,
    'area_principal' => 0,
    'area_surcharge' => 0,
    'current_surcharge' => 0,
    'payable_before_due' => 0,
    'payable_after_due' => 0,
    'recorded_demand' => 'N/A',
    'maximum_demand' => 'N/A',
    'meter_status' => 'N/A',
    'billing_status' => 'N/A',
    'solar_adjusted' => 0,
    'power_factor' => 94,
    'receipt_no' => 'N/A',
    'payment_mode' => 'Pending'
];

$bill = array_merge($defaults, $bill);

mysqli_stmt_close($stmt);

function numberToWords($number)
{
    $ones = array(
        0=>"Zero",1=>"One",2=>"Two",3=>"Three",4=>"Four",
        5=>"Five",6=>"Six",7=>"Seven",8=>"Eight",9=>"Nine",
        10=>"Ten",11=>"Eleven",12=>"Twelve",13=>"Thirteen",
        14=>"Fourteen",15=>"Fifteen",16=>"Sixteen",
        17=>"Seventeen",18=>"Eighteen",19=>"Nineteen"
    );

    $tens = array(
        2=>"Twenty",3=>"Thirty",4=>"Forty",
        5=>"Fifty",6=>"Sixty",7=>"Seventy",
        8=>"Eighty",9=>"Ninety"
    );

    if($number < 20)
        return $ones[$number];

    if($number < 100){
        return $tens[floor($number/10)] .
        (($number%10)? " ".$ones[$number%10]:"");
    }

    if($number < 1000){
        return $ones[floor($number/100)] . " Hundred" .
        (($number%100)? " ".numberToWords($number%100):"");
    }

    if($number < 100000){
        return numberToWords(floor($number/1000)) . " Thousand" .
        (($number%1000)? " ".numberToWords($number%1000):"");
    }

    if($number < 10000000){
        return numberToWords(floor($number/100000)) . " Lakh" .
        (($number%100000)? " ".numberToWords($number%100000):"");
    }

    return numberToWords(floor($number/10000000)) . " Crore" .
    (($number%10000000)? " ".numberToWords($number%10000000):"");
}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Electricity Bill</title>

<meta name="viewport"
content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{

background:#eceff1;

font-family:Arial,Helvetica,sans-serif;

}

.bill{

width:210mm;

min-height:297mm;

margin:20px auto;

background:#fff;

padding:30px;

border:1px solid #bbb;

box-shadow:0 5px 15px rgba(0,0,0,.15);

}

.header{

display:flex;

align-items:center;

border-bottom:3px solid #0d47a1;

padding-bottom:15px;

margin-bottom:20px;

}

.logo{

width:90px;

margin-right:20px;

}

.company{

flex:1;

text-align:center;

}

.company h2{

font-size:28px;

font-weight:bold;

color:#0d47a1;

margin:0;

}

.company h5{

margin:5px 0;

font-size:18px;

}

.company h4{

margin-top:10px;

font-weight:bold;

text-decoration:underline;

}

.section-title{

background:#0d47a1;

color:#fff;

padding:8px 15px;

font-size:18px;

font-weight:bold;

margin-top:20px;

}

.table{

margin-bottom:0;

}

.table td{

padding:8px;

font-size:15px;

}

.table-bordered{

border:1px solid #000;

}

.table-bordered td,
.table-bordered th{

border:1px solid #000;

}

@media print{

body{

background:#fff;

}

.bill{

box-shadow:none;

margin:0;

border:none;

width:100%;

}

.no-print{

display:none;

}

}

</style>

</head>

<body>

<div class="text-center mb-3 no-print">

<button
onclick="window.print()"
class="btn btn-primary">

🖨 Print Bill

</button>

<a href="manage_bills.php"
class="btn btn-secondary">

← Back

</a>

</div>

<div class="bill">

<div class="header">

<img
src="../assets/images/logo-circle.png"
class="logo">

<div class="company">

<h2>

Assam Power Distribution Company Limited

</h2>

<h5>

ELECTRICAL SUB-DIVISION / IRCA : ULUBARI

</h5>

<?php

$billTitle = "ELECTRICITY BILL";

if (isset($bill['meter_type']) && $bill['meter_type'] == "Smart Prepaid") {

    $billTitle = "SMART PREPAID BILL";

}

?>

<h4 style="font-weight:bold;text-decoration:underline;">

<?= $billTitle; ?>

</h4>

</div>

</div>

<!-- CONSUMER DETAILS -->

<div class="section-title">

Consumer Details

</div>

<table class="table table-bordered">

<tr>

<td width="25%"><b>Consumer Name</b></td>

<td width="25%">
<?= htmlspecialchars($bill['consumer_name'] ?? 'N/A'); ?>
</td>

<td width="25%"><b>Consumer No</b></td>

<td>
<?= htmlspecialchars($bill['consumer_no'] ?? 'N/A'); ?>
</td>

</tr>

<tr>

<td><b>Meter No</b></td>

<td>
<?= htmlspecialchars($bill['meter_no'] ?? 'N/A'); ?>
</td>

<td><b>DTR Number</b></td>

<td>
<?= htmlspecialchars($bill['dtr_no'] ?? 'N/A'); ?>
</td>

</tr>

<tr>
    <td><b>Address</b></td>
    <td colspan="3">
        <?= htmlspecialchars($bill['address'] ?? 'N/A'); ?>
    </td>
</tr>

<tr>

<td><b>Bill Period</b></td>

<td>

<?= htmlspecialchars($bill['bill_period_from'] ?? 'N/A'); ?>

<td><b>Number of Days</b></td>

<td>

<?= htmlspecialchars($bill['billing_days'] ?? 'N/A'); ?>

</td>

</tr>

<tr>

<td><b>Tariff Category</b></td>

<td>

<?= htmlspecialchars($bill['tariff_category'] ?? 'N/A'); ?>

</td>

<td><b>Supply Voltage</b></td>

<td>

<?= htmlspecialchars($bill['supply_voltage'] ?? 'N/A'); ?>

</td>

</tr>

<tr>

<td><b>Connected Load</b></td>

<td>

<?= htmlspecialchars($bill['connected_load'] ?? 'N/A'); ?>

KW

</td>

<td><b>Contract Demand</b></td>

<td>

<?= htmlspecialchars($bill['contract_demand'] ?? 'N/A'); ?>

KVA

</td>

</tr>

<tr>

<td><b>Meter Status</b></td>

<td>

<?= htmlspecialchars($bill['meter_status'] ?? 'N/A'); ?>

</td>

<td><b>Billing Status</b></td>

<td>

<?= htmlspecialchars($bill['billing_status'] ?? 'N/A'); ?>

</td>

</tr>

</table>

<!-- ===========================================
            METER READING
=========================================== -->

<div class="section-title">

Meter Reading

</div>

<table class="table table-bordered text-center">

<thead>

<tr class="table-secondary">

<th>Reading Type</th>

<th>MF</th>

<th>Previous Reading (kWh)</th>

<th>Current Reading (kWh)</th>

<th>Difference</th>

</tr>

</thead>

<tbody>

<tr>

<td>KWh (Normal)</td>

<td>

<?= htmlspecialchars($bill['mf'] ?? '1'); ?>

</td>

<td>

<?= number_format((float)($bill['previous_reading'] ?? 0),2); ?>

</td>

<td>

<?= number_format((float)($bill['current_reading'] ?? 0),2); ?>

</td>

<td>

<?= number_format($bill['current_reading']-$bill['previous_reading'],2); ?>

</td>

</tr>

</tbody>

</table>

<br>

<table class="table table-bordered text-center">

<tr class="table-secondary">

<th>Units Consumed</th>

<th>Billable Units</th>

<th>Recorded Demand (KVA)</th>

<th>Maximum Demand (KVA)</th>

</tr>

<tr>

<td>

<?= number_format((float)($bill['units'] ?? 0),2); ?>

</td>

<td>

<?= number_format($bill['units'],2); ?>

</td>

<td>

<?= htmlspecialchars($bill['recorded_demand'] ?? 'N/A'); ?>

</td>

<td>

<?= htmlspecialchars($bill['maximum_demand'] ?? 'N/A'); ?>

</td>

</tr>

</table>

<br>

<table class="table table-bordered text-center">

<tr class="table-secondary">

<th>Solar Units Adjusted</th>

<th>Average Power Factor (%)</th>

<th>Meter Status</th>

<th>Billing Status</th>

</tr>

<tr>

<td>

<?= htmlspecialchars($bill['solar_adjusted'] ?? '0'); ?>

</td>

<td>

<?= htmlspecialchars($bill['power_factor'] ?? '94'); ?>

</td>

<td>

<?= htmlspecialchars($bill['meter_status']); ?>

</td>

<td>

<?= htmlspecialchars($bill['billing_status']); ?>

</td>

</tr>

</table>

<!-- ===========================================
          BILL SUMMARY
=========================================== -->

<div class="section-title">

Billing Summary

</div>

<table class="table table-bordered">

<tr>

<th width="25%">

Current Demand

</th>

<td>

₹ <?= number_format($bill['current_demand'],2); ?>

</td>

<th width="25%">

Outstanding Amount

</th>

<td>

₹ <?= number_format($bill['outstanding_amount'],2); ?>

</td>

</tr>

<tr>

<th>

Adjustment Amount

</th>

<td>

₹ <?= number_format($bill['adjustment_amount'],2); ?>

</td>

<th>

Government Subsidy

</th>

<td>

₹ <?= number_format($bill['government_subsidy'],2); ?>

</td>

</tr>

<tr>

<th>

Solar Rebate

</th>

<td>

₹ <?= number_format($bill['solar_rebate'],2); ?>

</td>

<th>

Net Bill Amount

</th>

<td>

<strong style="font-size:20px;color:#d32f2f;">

₹ <?= number_format($bill['total_bill'],2); ?>

</strong>

</td>

</tr>

<tr>

<th>

Amount In Words

</th>

<td colspan="3">

<strong>

Rupees

<?= numberToWords(round($bill['total_bill'])); ?>

Only

</strong>

</td>

</tr>

</table>

<!-- ===========================================
            BILLING DETAILS
=========================================== -->

<div class="section-title">

Billing Details

</div>

<table class="table table-bordered">

<thead>

<tr class="table-secondary text-center">

<th width="45%">Particulars</th>

<th width="15%">Units</th>

<th width="15%">Rate</th>

<th width="25%">Amount (₹)</th>

</tr>

</thead>

<tbody>

<tr>

<td>Energy Charge</td>

<td><?= number_format($bill['units'],2); ?></td>

<td><?= number_format($bill['energy_rate'] ?? 7.74,2); ?></td>

<td><?= number_format((float)($bill['energy_charge'] ?? 0),2); ?></td>

</tr>

<tr>

<td>Demand / Fixed Charge</td>

<td><?= number_format($bill['connected_load'],2); ?></td>

<td><?= number_format($bill['fixed_rate'] ?? 70,2); ?></td>

<td><?= number_format($bill['fixed_charge'],2); ?></td>

</tr>

<tr>

<td>FPPPA Charge</td>

<td colspan="2">

<?= number_format($bill['fpppa_percent'] ?? 2.34,2); ?> %

</td>

<td>

<?= number_format($bill['fpppa_charge'],2); ?>

</td>

</tr>

<tr>

<td>Electricity Duty</td>

<td colspan="2">

5.00 %

</td>

<td>

<?= number_format($bill['electricity_duty'],2); ?>

</td>

</tr>

<tr>

<td>Tariff Subsidy</td>

<td colspan="2">Government</td>

<td>

- <?= number_format($bill['tariff_subsidy'] ?? 0,2); ?>

</td>

</tr>

<tr>

<td>Area Principal</td>

<td colspan="2">-</td>

<td>

<?= number_format($bill['area_principal'],2); ?>

</td>

</tr>

<tr>

<td>Area Surcharge</td>

<td colspan="2">-</td>

<td>

<?= number_format($bill['area_surcharge'],2); ?>

</td>

</tr>

<tr>

<td>Current Surcharge</td>

<td colspan="2">-</td>

<td>

<?= number_format($bill['current_surcharge'],2); ?>

</td>

</tr>

<tr class="table-warning">

<th colspan="3">

Total Energy Charge

</th>

<th>

₹ <?= number_format($bill['energy_charge'],2); ?>

</th>

</tr>

<tr class="table-success">

<th colspan="3">

Payable Amount Before Due Date

</th>

<th>

₹ <?= number_format($bill['payable_before_due'],2); ?>

</th>

</tr>

<tr class="table-danger">

<th colspan="3">

Payable Amount After Due Date

</th>

<th>

₹ <?= number_format($bill['payable_after_due'],2); ?>

</th>

</tr>

</tbody>

</table>

<!-- ===========================================
            PAYMENT INFORMATION
=========================================== -->

<div class="section-title">

Payment Information

</div>

<table class="table table-bordered">

<tr>

<th width="25%">Bill Month</th>

<td><?= date('F Y', strtotime($bill['bill_date'])); ?></td>

<th width="25%">Bill Date</th>

<td><?= date("d-M-Y",strtotime($bill['bill_date'])); ?></td>

</tr>

<tr>

<th>Due Date</th>

<td>

<?= !empty($bill['due_date']) ? date("d-M-Y",strtotime($bill['due_date'])) : 'N/A'; ?>

</td>

<th>Status</th>

<td>

<?php

if($bill['status']=="Paid"){

echo "<span style='color:green;font-weight:bold;'>PAID</span>";

}else{

echo "<span style='color:red;font-weight:bold;'>UNPAID</span>";

}

?>

</td>

</tr>

<tr>

<th>Payment Mode</th>

<td>

<?= htmlspecialchars($bill['payment_mode'] ?? 'Pending'); ?>

</td>

<th>Receipt No.</th>

<td>

<?= htmlspecialchars($bill['receipt_no'] ?? 'N/A'); ?>

</td>

</tr>

</table>

<br>

<!-- ===========================================
            IMPORTANT NOTICE
=========================================== -->

<div class="section-title">

Important Information

</div>

<div style="border:1px solid #000;padding:15px;font-size:14px;line-height:1.8;">

<ul>

<li>

Please pay your electricity bill before the due date to avoid surcharge.

</li>

<li>

Late payment may result in disconnection of electricity supply.

</li>

<li>

Keep this bill safely for future reference.

</li>

<li>

This bill is generated based on the recorded meter reading.

</li>

<li>

For any billing disputes, contact APDCL SDE Ulubari Office.

</li>

<li>

Payment can be made through APDCL Portal, UPI, Net Banking or Authorized Collection Centres.

</li>

</ul>

</div>

<br>

<!-- ===========================================
            FOOTER
=========================================== -->

<hr>

<table width="100%" style="margin-top:30px;">

<tr>

<!-- Left Side -->

<td width="50%" valign="top">

<h5 style="margin-bottom:10px;">Office Address</h5>

Assam Power Distribution Company Limited<br>

Electrical Sub-Division, Ulubari<br>

Guwahati, Assam<br>

Phone : 1912<br>

Email : support@apdcl.org

</td>

<!-- Right Side -->

<td width="50%" align="right" valign="bottom">

<div style="margin-top:80px;">

_________________________<br>

<b>Sub-Divisional Engineer</b><br>

APDCL, Ulubari

</div>

</td>

</tr>

</table>

<hr>

<div style="text-align:center;">

<h5 style="margin-bottom:5px;color:#0d47a1;">

Assam Power Distribution Company Limited

</h5>

<p style="margin:0;">

This is a Computer Generated Electricity Bill.

</p>

<p style="margin:0;">

No Signature Required.

</p>

<p style="margin-top:10px;font-size:12px;color:#555;">

© <?= date("Y"); ?> Assam Power Distribution Company Limited | All Rights Reserved

</p>

</div>

</div>

</body>

</html>