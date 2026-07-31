<?php
session_start();

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

if (!isset($_GET['id'])) {
    die("Invalid Bill ID.");
}

$bill_id = (int)$_GET['id'];
$consumer_no = $_SESSION['consumer'];

$sql = "SELECT b.*, u.name, u.address, u.mobile, u.email, u.meter_no, u.category
        FROM bills b
        INNER JOIN users u ON b.consumer_no = u.consumer_no
        WHERE b.id = $bill_id
        AND b.consumer_no = '$consumer_no'
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) == 0) {
    die("Bill not found.");
}

$bill = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Print Bill</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
body{
    background:#f2f2f2;
    font-family:Arial,sans-serif;
}

.bill{
    width:900px;
    margin:20px auto;
    background:#fff;
    border:2px solid #000;
    padding:30px;
}

.header{
    text-align:center;
    border-bottom:2px solid #000;
    padding-bottom:15px;
    margin-bottom:20px;
}

.header h2{
    margin:0;
    color:#0d6efd;
}

table{
    width:100%;
}

th{
    background:#0d6efd;
    color:#fff;
}

td,th{
    padding:8px;
    border:1px solid #ccc;
}

.total{
    font-size:22px;
    font-weight:bold;
    color:#d32f2f;
}

.footer{
    margin-top:40px;
    display:flex;
    justify-content:space-between;
}

@media print{

body{
    background:#fff;
}

.bill{
    border:none;
    width:100%;
    margin:0;
}

.no-print{
    display:none;
}

}
</style>

</head>

<body>

<div class="bill">

<div class="header">
    <h2>ASSAM POWER DISTRIBUTION COMPANY LIMITED</h2>
    <h4>Electricity Bill</h4>
</div>

<h5>Consumer Details</h5>

<table class="table table-bordered">

<tr>
<td><strong>Consumer No</strong></td>
<td><?= $bill['consumer_no']; ?></td>

<td><strong>Meter No</strong></td>
<td><?= $bill['meter_no']; ?></td>
</tr>

<tr>
<td><strong>Name</strong></td>
<td><?= $bill['name']; ?></td>

<td><strong>Category</strong></td>
<td><?= $bill['category']; ?></td>
</tr>

<tr>
<td><strong>Mobile</strong></td>
<td><?= $bill['mobile']; ?></td>

<td><strong>Email</strong></td>
<td><?= $bill['email']; ?></td>
</tr>

<tr>
<td><strong>Address</strong></td>
<td colspan="3"><?= $bill['address']; ?></td>
</tr>

</table>

<h5>Bill Details</h5>

<table class="table table-bordered">

<tr>
<th>Month</th>
<th>Units</th>
<th>Energy Charge</th>
<th>Fixed Charge</th>
<th>Electricity Duty</th>
<th>Total Bill</th>
</tr>

<tr>

<td><?= $bill['month']; ?></td>

<td><?= $bill['units']; ?></td>

<td>₹<?= number_format($bill['energy_charge'],2); ?></td>

<td>₹<?= number_format($bill['fixed_charge'],2); ?></td>

<td>₹<?= number_format($bill['electricity_duty'],2); ?></td>

<td class="total">
₹<?= number_format($bill['total_bill'],2); ?>
</td>

</tr>

</table>

<h5>Status :
<?php
if($bill['status']=="Paid"){
    echo "<span style='color:green;'>PAID</span>";
}else{
    echo "<span style='color:red;'>UNPAID</span>";
}
?>
</h5>

<div class="footer">

<div>
Generated on:
<br>
<?= date("d-m-Y h:i A"); ?>
</div>

<div style="text-align:right">
_____________________
<br>
Authorized Signature
</div>

</div>

<div class="text-center mt-4 no-print">

<button onclick="window.print()" class="btn btn-primary">
Print Bill
</button>

<a href="bill.php" class="btn btn-secondary">
Back
</a>

</div>

</div>

<script>
window.onload = function(){
    window.print();
};
</script>

</body>
</html>