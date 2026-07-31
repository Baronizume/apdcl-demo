<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

/*=========================================
GET LOGGED-IN ADMIN SUB-DIVISION
=========================================*/

$admin_username = $_SESSION['admin'];

$adminQuery = mysqli_query($conn,"
SELECT *
FROM admin
WHERE username='$admin_username'
LIMIT 1
");

$admin = mysqli_fetch_assoc($adminQuery);

$subDivision = mysqli_real_escape_string($conn,$admin['sub_division']);

/*=========================================
DELETE PAYMENT
=========================================*/

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    mysqli_query($conn,"
    DELETE FROM payments
    WHERE id='$id'
    AND sub_division='$subDivision'
    ");

    header("Location: manage_payments.php");
    exit();
}

/*=========================================
SEARCH
=========================================*/

$search = "";

$sql = "
SELECT *
FROM payments
WHERE sub_division='$subDivision'
";

if(!empty($_GET['search'])){

    $search = mysqli_real_escape_string($conn,$_GET['search']);

    $sql .= "
    AND(
        consumer_no LIKE '%$search%'
        OR consumer_name LIKE '%$search%'
        OR bill_no LIKE '%$search%'
        OR payment_method LIKE '%$search%'
        OR payment_id LIKE '%$search%'
        OR transaction_id LIKE '%$search%'
    )
    ";

}

$sql .= " ORDER BY payment_date DESC";

$result = mysqli_query($conn,$sql);

/*=========================================
STATISTICS
=========================================*/

$totalPayments = mysqli_fetch_assoc(
    mysqli_query($conn,
    "SELECT COUNT(*) total
    FROM payments
    WHERE sub_division='$subDivision'")
)['total'];

$totalRevenue = mysqli_fetch_assoc(
    mysqli_query($conn,
    "SELECT SUM(amount) revenue
    FROM payments
    WHERE sub_division='$subDivision'")
)['revenue'];

$totalRevenue = $totalRevenue ?: 0;

$todayPayments = mysqli_fetch_assoc(
    mysqli_query($conn,
    "SELECT COUNT(*) total
    FROM payments
    WHERE sub_division='$subDivision'
    AND DATE(payment_date)=CURDATE()")
)['total'];

$paidConsumers = mysqli_fetch_assoc(
    mysqli_query($conn,
    "SELECT COUNT(DISTINCT consumer_no) total
    FROM payments
    WHERE sub_division='$subDivision'")
)['total'];

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1">

<title>Manage Payments | APDCL</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{

    background:#f4f7fb;

    font-family:'Segoe UI',sans-serif;

}

/*==================================
HEADER
==================================*/

.page-header{

    background:linear-gradient(135deg,#0d6efd,#003c8f);

    color:#fff;

    padding:25px 30px;

    border-radius:18px;

    margin:30px auto;

    box-shadow:0 10px 25px rgba(0,0,0,.12);

}

.page-header img{

    width:75px;

    height:75px;

    background:#fff;

    border-radius:50%;

    padding:6px;

    margin-right:20px;

}

.page-header h2{

    font-weight:700;

    margin-bottom:5px;

}

.page-header p{

    margin:0;

    opacity:.9;

}

/*==================================
CARDS
==================================*/

.dashboard-card{

    border:none;

    border-radius:18px;

    box-shadow:0 8px 20px rgba(0,0,0,.08);

    transition:.3s;

}

.dashboard-card:hover{

    transform:translateY(-6px);

    box-shadow:0 15px 30px rgba(0,0,0,.15);

}

.dashboard-card i{

    font-size:42px;

}

/*==================================
SEARCH BAR
==================================*/

.search-card{

    border:none;

    border-radius:18px;

    box-shadow:0 5px 18px rgba(0,0,0,.08);

}

/*==================================
TABLE
==================================*/

.table-card{

    border:none;

    border-radius:18px;

    box-shadow:0 8px 20px rgba(0,0,0,.08);

}

.table thead{

    background:#0d6efd;

    color:#fff;

}

.table tbody tr:hover{

    background:#eef6ff;

}

.btn{

    border-radius:10px;

}

</style>

</head>

<body>

<div class="container-fluid px-4">

<!--==================================
HEADER
===================================-->

<div class="page-header d-flex align-items-center justify-content-between flex-wrap">

    <div class="d-flex align-items-center">

        <img src="../assets/images/logo-circle.png" alt="APDCL">

        <div>

            <h2>
                <i class="bi bi-credit-card-fill"></i>
                Manage Payments
            </h2>

            <p>
                APDCL Electricity Billing Management System
            </p>

        </div>

    </div>

    <div class="text-end">

        <h5 class="mb-1">

            <?= date("d M Y"); ?>

        </h5>

        <small>

            Payment Administration Panel

        </small>

    </div>

</div>

<!--==================================
TOP ACTION BAR
===================================-->

<div class="card search-card mb-4">

    <div class="card-body">

        <div class="row align-items-center">

            <div class="col-lg-3 mb-3 mb-lg-0">

                <a href="dashboard.php" class="btn btn-secondary">

                    <i class="bi bi-arrow-left-circle"></i>

                    Dashboard

                </a>

                <a href="manage_payments.php" class="btn btn-success">

                    <i class="bi bi-arrow-clockwise"></i>

                    Refresh

                </a>

            </div>

            <div class="col-lg-9">

                <form method="GET">

                    <div class="input-group">

                        <span class="input-group-text">

                            <i class="bi bi-search"></i>

                        </span>

                        <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search Consumer Number, Bill ID or Payment Mode..."
                        value="<?= htmlspecialchars($search); ?>">

                        <button class="btn btn-primary">

                            Search

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

<!--==================================
STATISTICS CARDS
===================================-->

<div class="row g-4 mb-4">

    <div class="col-xl-3 col-md-6">

        <div class="card dashboard-card bg-primary text-white">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small>Total Payments</small>

                        <h2 class="fw-bold">

                            <?= number_format($totalPayments); ?>

                        </h2>

                    </div>

                    <i class="bi bi-credit-card-fill"></i>

                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6">

        <div class="card dashboard-card bg-success text-white">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small>Total Revenue</small>

                        <h2 class="fw-bold">

                            ₹<?= number_format($totalRevenue); ?>

                        </h2>

                    </div>

                    <i class="bi bi-cash-stack"></i>

                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6">

        <div class="card dashboard-card bg-warning">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small>Today's Payments</small>

                        <h2 class="fw-bold">

                            <?= number_format($todayPayments); ?>

                        </h2>

                    </div>

                    <i class="bi bi-calendar-check-fill fs-1"></i>

                </div>

            </div>

        </div>

    </div>

    <div class="col-xl-3 col-md-6">

        <div class="card dashboard-card bg-danger text-white">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <small>Paid Consumers</small>

                        <h2 class="fw-bold">

                            <?= number_format($paidConsumers); ?>

                        </h2>

                    </div>

                    <i class="bi bi-people-fill"></i>

                </div>

            </div>

        </div>

    </div>

</div>

<!--==================================
PAYMENTS TABLE
===================================-->

<div class="card table-card mb-4">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">

        <h4 class="mb-0">

            <i class="bi bi-table"></i>

            Payment Records

        </h4>

        <span class="badge bg-primary fs-6">

            <?= mysqli_num_rows($result); ?> Records

        </span>

    </div>

    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover table-bordered align-middle mb-0">

                <thead>

                    <tr class="text-center">

                        <th width="70">ID</th>

                        <th>Consumer No</th>

                        <th>Bill No</th>

                        <th>Amount</th>

                        <th>Payment Mode</th>

                        <th>Payment Date</th>

                        <th width="170">Action</th>

                    </tr>

                </thead>

                <tbody>

                <?php

                if(mysqli_num_rows($result)>0){

                    while($row=mysqli_fetch_assoc($result)){

                ?>

                    <tr>

                        <td class="text-center fw-bold">

                            <?= $row['id']; ?>

                        </td>

                        <td>

                            <span class="fw-semibold text-primary">

                                <?= htmlspecialchars($row['consumer_no']); ?>

                            </span>

                        </td>

                        <td>

                            <?= htmlspecialchars($row['bill_no']); ?>

                        </td>

                        <td>

                            <span class="fw-bold text-success">

                                ₹<?= number_format($row['amount'],2); ?>

                            </span>

                        </td>

                        <td>

                            <?php

                            $mode = strtolower($row['payment_method']);

                            if($mode=="upi"){

                                echo '<span class="badge bg-success">UPI</span>';

                            }

                            elseif($mode=="cash"){

                                echo '<span class="badge bg-warning text-dark">Cash</span>';

                            }

                            elseif($mode=="card"){

                                echo '<span class="badge bg-primary">Card</span>';

                            }

                            elseif($mode=="net banking"){

                                echo '<span class="badge bg-info text-dark">Net Banking</span>';

                            }

                            else{

                                echo '<span class="badge bg-secondary">'.htmlspecialchars($row['payment_method']).'</span>';

                            }

                            ?>

                        </td>

                        <td>

                            <?= date("d M Y",strtotime($row['payment_date'])); ?>

                        </td>

                        <td class="text-center">
                                                        <a href="view_payment.php?id=<?= $row['id']; ?>"
                               class="btn btn-sm btn-info text-white mb-1">

                                <i class="bi bi-eye-fill"></i>

                                View

                            </a>

                            <a href="?delete=<?= $row['id']; ?>"
                               class="btn btn-sm btn-danger mb-1"
                               onclick="return confirm('Are you sure you want to delete this payment record?');">

                                <i class="bi bi-trash-fill"></i>

                                Delete

                            </a>

                        </td>

                    </tr>

                <?php

                    }

                } else {

                ?>

                    <tr>

                        <td colspan="7" class="text-center py-5">

                            <i class="bi bi-credit-card-2-front fs-1 text-muted d-block mb-3"></i>

                            <h5 class="text-muted">

                                No Payment Records Found

                            </h5>

                        </td>

                    </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<!--==================================
PAGE FOOTER
===================================-->

<div class="card shadow-sm border-0 mb-4">

    <div class="card-body d-flex justify-content-between align-items-center flex-wrap">

        <div>

            <small class="text-muted">

                Showing

                <strong><?= mysqli_num_rows($result); ?></strong>

                payment record(s).

            </small>

        </div>

        <div>

            <a href="dashboard.php" class="btn btn-secondary">

                <i class="bi bi-arrow-left-circle"></i>

                Back to Dashboard

            </a>

        </div>

    </div>

</div>

</div>

<!--==================================
BOOTSTRAP JAVASCRIPT
===================================-->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>