<?php
// if (!isset($_SESSION)) {
//     session_start();
// }
?>

<div class="bg-dark text-white border-end sidebar-wrapper" id="sidebar-wrapper">
    <div class="sidebar-heading p-4 text-center border-bottom text-primary fw-bold fs-5">
        <i class="fas fa-user-shield me-2"></i> ADMIN PANEL
    </div>

    <div class="list-group list-group-flush">

        <a href="dashboard_admin.php"
            class="list-group-item list-group-item-action bg-dark text-white <?= basename($_SERVER['PHP_SELF']) == 'dashboard_admin.php' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
        </a>

        <a href="admin_users.php"
            class="list-group-item list-group-item-action bg-dark text-white <?= basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : '' ?>">
            <i class="fas fa-users-cog me-2"></i> Role Admin
        </a>

        <a href="dokter_users.php"
            class="list-group-item list-group-item-action bg-dark text-white <?= basename($_SERVER['PHP_SELF']) == 'dokter_users.php' ? 'active' : '' ?>">
            <i class="fas fa-user-md me-2"></i> Role Dokter
        </a>

        <a href="kasir_users.php"
            class="list-group-item list-group-item-action bg-dark text-white <?= basename($_SERVER['PHP_SELF']) == 'kasir_users.php' ? 'active' : '' ?>">
            <i class="fas fa-cash-register me-2"></i> Role Kasir
        </a>

        <a href="apoteker_users.php"
            class="list-group-item list-group-item-action bg-dark text-white <?= basename($_SERVER['PHP_SELF']) == 'apoteker_users.php' ? 'active' : '' ?>">
            <i class="fas fa-pills me-2"></i> Role Apoteker
        </a>

    </div>
</div>