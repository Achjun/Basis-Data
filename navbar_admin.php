<?php
// if (!isset($_SESSION)) {
//     session_start();
// }
$user_name = $_SESSION['nama_lengkap'] ?? 'User';
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom shadow-sm sticky-top">
    <div class="container-fluid">
        <button class="btn btn-outline-primary" id="sidebarToggle">
            <i class="fas fa-bars"></i> Menu
        </button>

        <span class="navbar-text me-3 d-none d-md-inline">
            Selamat Datang, Admin <?= htmlspecialchars($user_name); ?>
        </span>

        <a href="login.php" class="btn btn-danger btn-sm">
            <i class="fas fa-sign-out-alt"></i> Keluar
        </a>
    </div>
</nav>
