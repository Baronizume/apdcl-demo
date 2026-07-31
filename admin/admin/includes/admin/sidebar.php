<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<div class="sidebar bg-primary text-white">

    <div class="text-center py-4">
        <h3>⚡ APDCL</h3>
        <small>Admin Panel</small>
    </div>

    <hr class="text-white">

    <ul class="nav flex-column">

        <li class="nav-item">
            <a href="dashboard.php"
               class="nav-link text-white <?= ($currentPage=='dashboard.php') ? 'active bg-light text-primary' : '' ?>">
                🏠 Dashboard
            </a>
        </li>

        <li class="nav-item">
            <a href="manage_consumers.php"
               class="nav-link text-white <?= ($currentPage=='manage_consumers.php') ? 'active bg-light text-primary' : '' ?>">
                👥 Consumers
            </a>
        </li>

        <li class="nav-item">
            <a href="generate_bill.php"
               class="nav-link text-white <?= ($currentPage=='generate_bill.php') ? 'active bg-light text-primary' : '' ?>">
                ⚡ Generate Bill
            </a>
        </li>

        <li class="nav-item">
            <a href="manage_complaints.php"
               class="nav-link text-white <?= ($currentPage=='manage_complaints.php') ? 'active bg-light text-primary' : '' ?>">
                📝 Complaints
            </a>
        </li>

        <li class="nav-item">
            <a href="manage_notices.php"
               class="nav-link text-white <?= ($currentPage=='manage_notices.php') ? 'active bg-light text-primary' : '' ?>">
                📢 Notices
            </a>
        </li>

        <li class="nav-item">
            <a href="reports.php"
               class="nav-link text-white <?= ($currentPage=='reports.php') ? 'active bg-light text-primary' : '' ?>">
                📊 Reports
            </a>
        </li>

        <li class="nav-item mt-auto">
            <a href="../logout.php" class="nav-link text-warning">
                🚪 Logout
            </a>
        </li>

    </ul>

</div>