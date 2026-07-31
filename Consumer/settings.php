<?php
session_start();
require_once("../db.php");

if (!isset($_SESSION['consumer'])) {
    header("Location: login.php");
    exit();
}

$consumer_no = $_SESSION['consumer'];

$stmt = mysqli_prepare($conn,
"SELECT * FROM users WHERE consumer_no=? LIMIT 1");
mysqli_stmt_bind_param($stmt,"s",$consumer_no);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result)==0){
    session_destroy();
    header("Location: login.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

$success = "";
$error   = "";

/* ==========================
   UPDATE PROFILE
========================== */

if(isset($_POST['update_profile'])){

    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $mobile  = trim($_POST['mobile']);
    $address = trim($_POST['address']);

    $stmt = mysqli_prepare($conn,"
        UPDATE users
        SET
        name=?,
        email=?,
        mobile=?,
        address=?
        WHERE consumer_no=?
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $name,
        $email,
        $mobile,
        $address,
        $consumer_no
    );

    if(mysqli_stmt_execute($stmt)){

        $success = "Profile updated successfully.";

        $stmt = mysqli_prepare($conn,
        "SELECT * FROM users WHERE consumer_no=?");

        mysqli_stmt_bind_param($stmt,"s",$consumer_no);
        mysqli_stmt_execute($stmt);

        $user = mysqli_fetch_assoc(
            mysqli_stmt_get_result($stmt)
        );

    }else{

        $error = "Unable to update profile.";

    }

}

/* ==========================
   CHANGE PASSWORD
========================== */

if(isset($_POST['change_password'])){

    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if($current != $user['password']){

        $error = "Current password is incorrect.";

    }elseif($new != $confirm){

        $error = "New passwords do not match.";

    }else{

        $stmt = mysqli_prepare($conn,
        "UPDATE users SET password=? WHERE consumer_no=?");

        mysqli_stmt_bind_param(
            $stmt,
            "ss",
            $new,
            $consumer_no
        );

        if(mysqli_stmt_execute($stmt)){

            $success = "Password changed successfully.";

        }else{

            $error = "Unable to change password.";

        }

    }

}

date_default_timezone_set("Asia/Kolkata");
$currentDate = date("l, d F Y");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1">

<title>Consumer Settings | APDCL</title>

<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<link
href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
rel="stylesheet">

<link
href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
rel="stylesheet">

<style>

:root{

--primary:#005BAC;
--secondary:#003B8F;
--light:#f4f7fb;
--white:#ffffff;

}

*{

margin:0;
padding:0;
box-sizing:border-box;
font-family:'Poppins',sans-serif;

}

body{

background:var(--light);
overflow-x:hidden;

}

.wrapper{

display:flex;
min-height:100vh;

}

.sidebar{

width:280px;
background:linear-gradient(180deg,var(--primary),var(--secondary));
position:fixed;
left:0;
top:0;
height:100vh;
color:#fff;

}

.main{

margin-left:280px;
width:calc(100% - 280px);

}

.topbar{

height:75px;
background:#fff;
display:flex;
justify-content:space-between;
align-items:center;
padding:0 30px;
box-shadow:0 5px 20px rgba(0,0,0,.08);

}

.page-content{

padding:30px;

}

.card{

border:none;
border-radius:18px;
box-shadow:0 10px 25px rgba(0,0,0,.08);

}

.btn{

border-radius:10px;

}

</style>

</head>

<body>

<div class="wrapper">
<!-- ================= Sidebar ================= -->

<aside class="sidebar">

    <div class="text-center py-4 border-bottom border-light">

        <img src="../assets/images/logo-circle.png"
             width="75"
             class="bg-white rounded-circle p-2 shadow">

        <h3 class="mt-3 fw-bold">APDCL</h3>

        <p class="small mb-0">
            Consumer Portal
        </p>

    </div>

    <!-- Consumer -->

    <div class="text-center py-4">

        <div class="bg-white rounded-circle mx-auto d-flex align-items-center justify-content-center"
             style="width:85px;height:85px;">

            <i class="bi bi-person-fill fs-1 text-primary"></i>

        </div>

        <h5 class="mt-3">
            <?= htmlspecialchars($user['name']); ?>
        </h5>

        <small>Consumer No</small>

        <br>

        <strong><?= htmlspecialchars($user['consumer_no']); ?></strong>

    </div>

    <!-- Menu -->

    <ul class="nav flex-column px-3">

        <li class="nav-item mb-2">
            <a href="dashboard.php" class="nav-link text-white">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="current_bill.php" class="nav-link text-white">
                <i class="bi bi-lightning-charge-fill me-2"></i>
                Current Bill
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="bill_history.php" class="nav-link text-white">
                <i class="bi bi-receipt me-2"></i>
                Bill History
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="pay_bill.php" class="nav-link text-white">
                <i class="bi bi-credit-card-fill me-2"></i>
                Pay Bill
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="payment_history.php" class="nav-link text-white">
                <i class="bi bi-wallet-fill me-2"></i>
                Payments
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="complaint_history.php" class="nav-link text-white">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                Complaints
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="complaint.php" class="nav-link text-white">
                <i class="bi bi-pencil-square me-2"></i>
                Register Complaint
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="notices.php" class="nav-link text-white">
                <i class="bi bi-megaphone-fill me-2"></i>
                Notices
            </a>
        </li>

        <li class="nav-item mb-2">
            <a href="outage_map.php" class="nav-link text-white">
                <i class="bi bi-geo-alt-fill me-2"></i>
                Outage Map
            </a>
        </li>

        <!-- Active Menu -->
        <li class="nav-item mb-2">
            <a href="settings.php"
               class="nav-link bg-white text-primary rounded fw-bold">
                <i class="bi bi-gear-fill me-2"></i>
                Settings
            </a>
        </li>

        <li class="nav-item mt-4">
            <a href="logout.php"
               class="nav-link bg-danger text-white rounded">
                <i class="bi bi-box-arrow-right me-2"></i>
                Logout
            </a>
        </li>

    </ul>

</aside>

<!-- ================= Main ================= -->

<div class="main">

    <!-- Topbar -->

    <nav class="topbar">

        <div>

            <h4 class="fw-bold mb-0">

                Consumer Settings

            </h4>

            <small class="text-muted">

                <?= $currentDate ?>

            </small>

        </div>

        <div class="dropdown">

            <a href="#"
               class="text-decoration-none dropdown-toggle"
               data-bs-toggle="dropdown">

                <i class="bi bi-person-circle fs-3 text-primary"></i>

            </a>

            <ul class="dropdown-menu dropdown-menu-end">

                <li>
                    <a class="dropdown-item"
                       href="profile.php">

                        <i class="bi bi-person-circle"></i>

                        My Profile

                    </a>
                </li>

                <li>
                    <a class="dropdown-item"
                       href="settings.php">

                        <i class="bi bi-gear"></i>

                        Settings

                    </a>
                </li>

                <li><hr></li>

                <li>
                    <a class="dropdown-item text-danger"
                       href="logout.php">

                        <i class="bi bi-box-arrow-right"></i>

                        Logout

                    </a>
                </li>

            </ul>

        </div>

    </nav>

    <!-- Page -->

    <div class="page-content">

        <div class="container-fluid">

<?php if($success!=""){ ?>

<div class="alert alert-success">
    <?= $success ?>
</div>

<?php } ?>

<?php if($error!=""){ ?>

<div class="alert alert-danger">
    <?= $error ?>
</div>

<?php } ?>
<!-- ================= PROFILE SETTINGS ================= -->

<div class="row">

    <!-- Profile Information -->
    <div class="col-lg-8 mb-4">

        <div class="card">

            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-person-circle"></i>
                    Profile Information
                </h5>
            </div>

            <div class="card-body">

                <form method="POST">

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Consumer Number</label>
                            <input type="text"
                                   class="form-control"
                                   value="<?= htmlspecialchars($user['consumer_no']); ?>"
                                   readonly>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="<?= htmlspecialchars($user['name']); ?>"
                                   required>
                        </div>

                    </div>

                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label>Email Address</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="<?= htmlspecialchars($user['email']); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label>Mobile Number</label>
                            <input type="text"
                                   name="mobile"
                                   class="form-control"
                                   value="<?= htmlspecialchars($user['mobile']); ?>">
                        </div>

                    </div>

                    <div class="mb-3">

                        <label>Address</label>

                        <textarea
                            name="address"
                            class="form-control"
                            rows="4"><?= htmlspecialchars($user['address']); ?></textarea>

                    </div>

                    <button
                        class="btn btn-primary"
                        name="update_profile">

                        <i class="bi bi-save"></i>

                        Update Profile

                    </button>

                </form>

            </div>

        </div>

    </div>

    <!-- Account Information -->

    <div class="col-lg-4 mb-4">

        <div class="card">

            <div class="card-header bg-success text-white">

                <h5 class="mb-0">

                    <i class="bi bi-person-badge"></i>

                    Account Information

                </h5>

            </div>

            <div class="card-body">

                <table class="table table-borderless">

                    <tr>
                        <th>Consumer No</th>
                        <td><?= htmlspecialchars($user['consumer_no']); ?></td>
                    </tr>

                    <tr>
                        <th>Category</th>
                        <td><?= htmlspecialchars($user['category']); ?></td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge bg-success">
                                Active
                            </span>
                        </td>
                    </tr>

                    <tr>
                        <th>Email</th>
                        <td><?= htmlspecialchars($user['email']); ?></td>
                    </tr>

                    <tr>
                        <th>Mobile</th>
                        <td><?= htmlspecialchars($user['mobile']); ?></td>
                    </tr>

                </table>

            </div>

        </div>

    </div>

</div>

<!-- ================= CHANGE PASSWORD ================= -->

<div class="card mt-4">

    <div class="card-header bg-warning">

        <h5 class="mb-0">

            <i class="bi bi-shield-lock-fill"></i>

            Change Password

        </h5>

    </div>

    <div class="card-body">

        <form method="POST">

            <div class="row">

                <div class="col-md-4 mb-3">

                    <label>Current Password</label>

                    <input
                        type="password"
                        name="current_password"
                        class="form-control"
                        required>

                </div>

                <div class="col-md-4 mb-3">

                    <label>New Password</label>

                    <input
                        type="password"
                        name="new_password"
                        class="form-control"
                        required>

                </div>

                <div class="col-md-4 mb-3">

                    <label>Confirm Password</label>

                    <input
                        type="password"
                        name="confirm_password"
                        class="form-control"
                        required>

                </div>

            </div>

            <button
                class="btn btn-success"
                name="change_password">

                <i class="bi bi-key-fill"></i>

                Change Password

            </button>

        </form>

    </div>

</div>

<!-- ================= FOOTER ================= -->

<footer class="text-center py-4 mt-5 bg-white border-top">

    <div class="container">

        <h6 class="text-primary fw-bold">
            APDCL Consumer Portal
        </h6>

        <p class="mb-1">
            © 2026 Assam Power Distribution Company Limited
        </p>

        <small class="text-muted">
            Internship Demo Project
        </small>

    </div>

</footer>

</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>