<?php
include 'koneksi.php';

// Ambil semua data kunjungan dengan join ke tabel pasien, dokter, dan poli
$sql = "SELECT k.no_kunjungan, 
               p.nama_pasien, 
               d.nama_dokter, 
               po.nama_poli, 
               k.keluhan, 
               k.tgl_periksa, 
               k.status
        FROM kunjungan k
        JOIN pasien p ON k.no_pasien = p.no_pasien
        JOIN dokter d ON k.no_dokter = d.no_dokter
        JOIN poli po ON k.id_poli = po.id_poli
        ORDER BY k.tgl_periksa DESC";

// Menggunakan MySQLi
$result = $koneksi->query($sql);
$kunjungan = $result->fetch_all(MYSQLI_ASSOC); // Ambil semua hasil

// ... sisa kode HTML (tidak berubah)
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Kunjungan</title>
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

            <a href="../login.php?logout=true" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>
<div class="container mt-4">
    <h3>Daftar Kunjungan</h3>
    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Pasien</th>
                <th>Dokter</th>
                <th>Poli</th>
                <th>Keluhan</th>
                <th>Tanggal</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; foreach($kunjungan as $k): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= $k['nama_pasien'] ?></td>
                <td><?= $k['nama_dokter'] ?></td>
                <td><?= $k['nama_poli'] ?></td>
                <td><?= $k['keluhan'] ?></td>
                <td><?= $k['tgl_periksa'] ?></td>
                <td><?= $k['status'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>