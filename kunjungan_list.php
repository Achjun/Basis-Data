<?php
include 'koneksi.php';

// --- UPDATE QUERY ---
// Tambahkan LEFT JOIN ke tabel pembayaran untuk mengambil no_bayar (agar bisa cetak struk)
$sql = "SELECT k.no_kunjungan, 
               p.nama_pasien, 
               d.nama_dokter, 
               po.nama_poli, 
               k.keluhan, 
               k.tgl_periksa, 
               k.status,
               pay.no_bayar  -- Ambil no_bayar jika ada
        FROM kunjungan k
        JOIN pasien p ON k.no_pasien = p.no_pasien
        JOIN dokter d ON k.no_dokter = d.no_dokter
        JOIN poli po ON d.id_poli = po.id_poli
        LEFT JOIN pembayaran pay ON k.no_kunjungan = pay.no_kunjungan 
        ORDER BY k.tgl_periksa DESC";

$result = mysqli_query($koneksi, $sql);

if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}
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

    <div class="bg-dark border-right" id="sidebar-wrapper" style="min-height: 100vh;">
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
            <a href="kunjungan_list.php" class="list-group-item list-group-item-action bg-dark text-white active">
                <i class="fa fa-calendar"></i> Data Kunjungan
            </a>
            
            <a href="login.php?logout=true" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fa fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </div>
    
    <div class="container-fluid mt-4 px-4">
        <h3>Daftar Kunjungan & Pembayaran</h3>
        
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Pasien</th>
                        <th>Dokter / Poli</th>
                        <th>Keluhan</th>
                        <th>Status</th>
                        <th width="15%">Aksi</th> </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while ($k = mysqli_fetch_assoc($result)): 
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($k['tgl_periksa'])) ?></td>
                        <td><?= $k['nama_pasien'] ?></td>
                        <td>
                            <strong><?= $k['nama_dokter'] ?></strong><br>
                            <small class="text-muted"><?= $k['nama_poli'] ?></small>
                        </td> 
                        <td><?= $k['keluhan'] ?></td>
                        <td>
                            <?php if($k['status'] == 'Selesai'): ?>
                                <span class="badge bg-success">Selesai</span>
                            <?php elseif($k['status'] == 'Batal'): ?>
                                <span class="badge bg-danger">Batal</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><?= $k['status'] ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($k['status'] == 'Selesai'): ?>
                                <?php if($k['no_bayar']): ?>
                                    <a href="struk.php?id=<?= $k['no_bayar'] ?>" target="_blank" class="btn btn-secondary btn-sm" title="Cetak Struk">
                                        <i class="fa fa-print"></i> Struk
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted text-small">Lunas</span>
                                <?php endif; ?>

                            <?php elseif ($k['status'] == 'Batal'): ?>
                                <span class="text-muted">-</span>
                            
                            <?php else: ?>
                                <a href="pembayaran.php?id=<?= $k['no_kunjungan'] ?>" class="btn btn-primary btn-sm">
                                    <i class="fa fa-money-bill"></i> Bayar
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>