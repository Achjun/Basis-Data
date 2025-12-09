<?php

include 'koneksi.php';

// Jika ingin cek apakah user sudah login:
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Kasir') {
    header("Location: login.php");
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Resepsionis</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div id="wrapper" class="d-flex">

    <div class="bg-dark border-right" id="sidebar-wrapper">
        <div class="sidebar-heading text-white p-3 fs-4">Resepsionis</div>

        <div class="list-group list-group-flush">
            <a href="dashboard_resepsionis.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-users"></i> Dashboard
            </a>
            <a href="pasien_list.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-users"></i> Data Pasien
            </a>
        

            <a href="pasien_tambah.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-user-plus"></i> Tambah Pasien
            </a>

            <a href="kunjungan_tambah.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-calendar-plus"></i> Jadwal Kunjungan
            </a>

            <a href="kunjungan_list.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-calendar"></i> Data Kunjungan
            </a>

            <a href="login.php?logout=true" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>

    <div id="page-content-wrapper" class="w-100">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
            <div class="container-fluid">
                <span class="navbar-brand">Dashboard Resepsionis</span>
            </div>
        </nav>

        <div class="container-fluid mt-4">
            <h3>Selamat Datang di Dashboard Resepsionis</h3>
            <p>Pilih menu di samping untuk mengelola pasien & kunjungan.</p>

            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-primary text-white p-3">
                        <h4>Data Pasien</h4>
                        <p>Kelola dan cari data pasien.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-success text-white p-3">
                        <h4>Tambah Pasien</h4>
                        <p>Daftarkan pasien baru.</p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-info text-white p-3">
                        <h4>Jadwal Kunjungan</h4>
                        <p>Buat jadwal kunjungan pasien.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

</body>
</html>
