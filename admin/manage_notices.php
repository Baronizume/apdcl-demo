<?php
session_start();

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("../db.php");

$result = mysqli_query($conn, "SELECT * FROM notices ORDER BY id DESC");
$totalNotices = mysqli_num_rows($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Manage Notices | APDCL</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>

body{
    background:#f4f7fc;
    font-family:'Segoe UI',sans-serif;
}

.page-header{
    background:linear-gradient(135deg,#0d6efd,#0047ab);
    color:#fff;
    border-radius:18px;
    padding:25px 30px;
    margin-bottom:25px;
    box-shadow:0 10px 25px rgba(0,0,0,.12);
}

.card{
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
    transition:.2s;
}

.btn{
    border-radius:10px;
}

.notice-count{
    font-size:15px;
    background:#fff;
    color:#0d6efd;
    padding:6px 15px;
    border-radius:30px;
    font-weight:600;
}

.search-box{
    max-width:300px;
}

.action-btn{
    min-width:75px;
}

</style>

</head>

<body>

<div class="container-fluid p-4">

    <!-- Header -->

    <div class="page-header d-flex justify-content-between align-items-center flex-wrap">

        <div>

            <h2 class="fw-bold mb-1">
                <i class="bi bi-megaphone-fill"></i>
                Manage Notices
            </h2>

            <small>
                Create, edit and manage APDCL notices.
            </small>

        </div>

        <div class="notice-count mt-3 mt-md-0">

            Total Notices :
            <?= $totalNotices ?>

        </div>

    </div>

    <!-- Top Buttons -->

    <div class="card mb-4">

        <div class="card-body">

            <div class="row align-items-center">

                <div class="col-md-6 mb-3 mb-md-0">

                    <a href="dashboard.php" class="btn btn-secondary">

                        <i class="bi bi-arrow-left-circle"></i>
                        Dashboard

                    </a>

                    <a href="add_notice.php" class="btn btn-success">

                        <i class="bi bi-plus-circle"></i>
                        Add Notice

                    </a>

                </div>

                <div class="col-md-6 text-md-end">

                    <input
                        type="text"
                        id="searchNotice"
                        class="form-control search-box d-inline-block"
                        placeholder="Search Notice...">

                </div>

            </div>

        </div>

    </div>

    <!-- Table -->

    <div class="card">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0" id="noticeTable">

                    <thead>

                        <tr>

                            <th width="80">ID</th>

                            <th>Title</th>

                            <th width="180">Notice Date</th>

                            <th width="220" class="text-center">

                                Action

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if(mysqli_num_rows($result)>0){ ?>

                        <?php while($row=mysqli_fetch_assoc($result)){ ?>

                        <tr>

                            <td>

                                <strong>

                                    #<?= $row['id']; ?>

                                </strong>

                            </td>

                            <td>

                                <?= htmlspecialchars($row['title']); ?>

                            </td>

                            <td>

                                <?= date("d M Y",strtotime($row['notice_date'])); ?>

                            </td>

                            <td class="text-center">

                                <a
                                href="edit_notice.php?id=<?= $row['id']; ?>"
                                class="btn btn-warning btn-sm action-btn">

                                    <i class="bi bi-pencil-square"></i>
                                    Edit

                                </a>

                                <a
                                href="delete_notice.php?id=<?= $row['id']; ?>"
                                class="btn btn-danger btn-sm action-btn"
                                onclick="return confirm('Delete this notice?');">

                                    <i class="bi bi-trash-fill"></i>
                                    Delete

                                </a>

                            </td>

                        </tr>

                        <?php } ?>

                    <?php } else { ?>

                        <tr>

                            <td colspan="4" class="text-center py-5 text-muted">

                                <i class="bi bi-folder-x display-5 d-block mb-3"></i>

                                No Notices Found

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

<script>

document.getElementById("searchNotice").addEventListener("keyup",function(){

    let value=this.value.toLowerCase();

    let rows=document.querySelectorAll("#noticeTable tbody tr");

    rows.forEach(function(row){

        row.style.display=row.innerText.toLowerCase().includes(value)
        ? ""
        : "none";

    });

});

</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>