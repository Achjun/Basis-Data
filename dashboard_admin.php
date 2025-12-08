<?php
include 'koneksi.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}
$user_name = $_SESSION['nama_lengkap'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="d-flex" id="wrapper">
        <?php include 'sidebar_admin.php'; ?>
        <div id="page-content-wrapper">
            <?php include 'navbar_admin.php'; ?>
            </nav>
            <div class="container-fluid py-4">
                <h1 class="mb-4 text-primary"><i class="fas fa-chart-area"></i> Ringkasan Sistem</h1>
                <p class="lead">Kontrol penuh atas data users.</p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>document.getElementById("sidebarToggle").addEventListener("click", () => { document.getElementById("wrapper").classList.toggle("toggled"); });</script>
</body>

</html>