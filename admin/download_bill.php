<?php
session_start();
include("../db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Invalid Bill ID");
}

$id = (int)$_GET['id'];

$billQuery = mysqli_query($conn, "SELECT * FROM bills WHERE id='$id'");

if (mysqli_num_rows($billQuery) == 0) {
    die("Bill Not Found");
}

$bill = mysqli_fetch_assoc($billQuery);

$userQuery = mysqli_query($conn, "SELECT * FROM users WHERE consumer_no='".$bill['consumer_no']."'");
$user = mysqli_fetch_assoc($userQuery);
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>APDCL Electricity Bill</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#eceff3;
    font-family:Arial,Helvetica,sans-serif;
}

.bill{

    width:850px;

    margin:30px auto;

    background:#fff;

    padding:30px;

    border-radius:10px;

    box-shadow:0 5px 15px rgba(0,0,0,.2);

}

.logo{

    width:90px;

}

h2{

    color:#0056b3;

    font-weight:bold;

}

.table td,
.table th{

    vertical-align:middle;

}

.footer{

    text-align:center;

    margin-top:40px;

    font-size:14px;

}

@media print{

body{

background:#fff;

}

.bill{

width:100%;

margin:0;

box-shadow:none;

}

.no-print{

display:none;

}

}

</style>

</head>

<body>

<div class="bill">

    <div class="text-center">

        <img src="../assets/images/logo.png" class="logo" alt="APDCL Logo">

        <h2 class="mt-2">ASSAM POWER DISTRIBUTION COMPANY LIMITED</h2>

        <h5>(APDCL)</h5>

        <h4 class="text-primary">ELECTRICITY BILL</h4>

        <hr>

    </div>

    <div class="row">

        <!-- Consumer Details -->
        <div class="col-md-6">
    
        </div>

        <!-- Bill Details -->
        <div class="col-md-6">

    <table class="table table-bordered">

        <tr>
            <th width="40%">Bill Number</th>
            <td>APDCL<?= str_pad($bill['id'],6,"0",STR_PAD_LEFT); ?></td>
        </tr>

        <tr>
            <th>Consumer Number</th>
            <td><?= htmlspecialchars($bill['consumer_no']); ?></td>
         </tr>

        <tr>
            <th>Consumer Name</th>
            <td><?= htmlspecialchars($user['name'] ?? 'N/A'); ?></td>
        </tr>

        <tr>
             <th>Email</th>
            <td><?= htmlspecialchars($user['email'] ?? 'N/A'); ?></td>
        </tr>

         <tr>
             <th>Mobile Number</th>
             <td><?= htmlspecialchars($user['mobile'] ?? 'N/A'); ?></td>
         </tr>

         <tr>
            <th>Address</th>
            <td><?= nl2br(htmlspecialchars($user['address'] ?? 'N/A')); ?></td>
        </tr>

    </table>

        </div>

    </div>

    <h4 class="text-primary mt-4">Tariff Details</h4>

    <table class="table table-bordered">

        <thead class="table-primary">

            <tr>
                <th>Description</th>
                <th class="text-end">Amount (₹)</th>
            </tr>

        </thead>

        <tbody>

            <tr>
                <td>Tariff Rate</td>
                <td class="text-end">₹ 7.50 / Unit</td>
            </tr>

            <tr>
                <td>Energy Charges</td>
                <td class="text-end">₹ <?= number_format($bill['energy_charge'],2); ?></td>
            </tr>

            <tr>
                <td>Fixed Charges</td>
                <td class="text-end">₹ <?= number_format($bill['fixed_charge'],2); ?></td>
            </tr>

            <tr>
                <td>Electricity Duty</td>
                <td class="text-end">₹ <?= number_format($bill['electricity_duty'],2); ?></td>
            </tr>

            <tr>
                <td>Government Subsidy</td>
                <td class="text-end text-success">- ₹ <?= number_format($bill['subsidy'],2); ?></td>
            </tr>

            <tr class="table-warning">

                <th>Total Bill Amount</th>

                <th class="text-end">

                    ₹ <?= number_format($bill['total_bill'],2); ?>

                </th>

            </tr>

        </tbody>

    </table>

    <div class="alert alert-info">

        <strong>Important:</strong><br>

        • This is a computer-generated electricity bill.<br>

        • No signature is required.<br>

        • Customer Care: <strong>1912</strong>

    </div>

    <div class="text-center mt-4 no-print">

        <button onclick="window.print()" class="btn btn-primary">

            🖨 Print / Save PDF

        </button>

        <a href="manage_bills.php" class="btn btn-secondary">

            Back to Manage Bills

        </a>

    </div>

    <div class="footer text-center mt-4">

        <hr>

        <strong>Assam Power Distribution Company Limited</strong><br>

        Bijulee Bhawan, Paltan Bazar, Guwahati - 781001, Assam<br>

        Customer Care: 1912 | Website: www.apdcl.org<br><br>

        © <?= date("Y"); ?> APDCL. All Rights Reserved.

    </div>

</div>

<script>
window.onload = function () {
    window.print();
};
</script>