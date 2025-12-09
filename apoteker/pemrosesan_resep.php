<?php
// ==========================================
// FILE: pemrosesan_resep.php
// FUNGSI: Menangani antrian resep & proses pengambilan obat
// ==========================================

include '../koneksi.php';

// 1. CEK KEAMANAN AKSES
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Apoteker') {
    header("Location: ../login.php");
    exit;
}

$user_name = $_SESSION['nama_lengkap'];
$no_resep = $_GET['no_resep'] ?? ''; 

// Tentukan Mode: Jika ada ?no_resep=... di URL berarti mode DETAIL, jika tidak berarti DAFTAR
$mode = empty($no_resep) ? 'daftar' : 'detail';

// ==========================================
// LOGIKA PHP: MODE DAFTAR ANTRIAN
// ==========================================
if ($mode === 'daftar') {
    $query_daftar = "
        SELECT 
            r.no_resep, 
            r.tgl_resep, 
            p.nama_pasien, 
            r.status_resep
        FROM resep r
        JOIN kunjungan k ON r.no_kunjungan = k.no_kunjungan
        JOIN pasien p ON k.no_pasien = p.no_pasien
        WHERE r.status_resep IN ('Antre', 'Diproses')
        ORDER BY r.tgl_resep ASC
    ";
    $result_daftar = mysqli_query($koneksi, $query_daftar);
} 

// ==========================================
// LOGIKA PHP: MODE DETAIL & PROSES
// ==========================================
else {
    // A. Ambil Data Header (Info Pasien & Dokter)
    $query_header = "
        SELECT 
            r.no_resep, r.tgl_resep, r.status_resep,
            p.nama_pasien, p.no_pasien,
            d.nama_dokter,
            poli.nama_poli
        FROM resep r
        JOIN kunjungan k ON r.no_kunjungan = k.no_kunjungan
        JOIN pasien p ON k.no_pasien = p.no_pasien
        JOIN dokter d ON k.no_dokter = d.no_dokter
        JOIN poli ON d.id_poli = poli.id_poli
        WHERE r.no_resep = '$no_resep'
    ";
    $result_header = mysqli_query($koneksi, $query_header);
    $data_resep = mysqli_fetch_assoc($result_header);

    if (!$data_resep) {
        echo "<script>alert('Resep tidak ditemukan!'); window.location='pemrosesan_resep.php';</script>";
        exit;
    }

    // B. Ambil Detail Obat (JOIN TABEL OBAT UNTUK DAPAT NAMA)
    // Di sini kita ambil kode_obat dari detail_resep, lalu ambil nama_obat dari tabel obat
    $query_obat = "
        SELECT 
            dr.kode_obat, 
            dr.jumlah AS jumlah_minta, 
            dr.aturan_pakai, 
            o.nama_obat,      -- Ambil Nama Obat
            o.satuan,         -- Ambil Satuan
            o.stok AS stok_tersedia
        FROM detail_resep dr
        JOIN obat o ON dr.kode_obat = o.kode_obat
        WHERE dr.no_resep = '$no_resep'
    ";
    $result_obat = mysqli_query($koneksi, $query_obat);

    // C. Validasi Stok (Looping ke Array dulu)
    $list_obat = [];
    $stok_aman = true;

    while ($item = mysqli_fetch_assoc($result_obat)) {
        // Cek apakah stok cukup
        if ($item['stok_tersedia'] < $item['jumlah_minta']) {
            $stok_aman = false;
        }
        $list_obat[] = $item;
    }

    // D. PROSES JIKA TOMBOL 'SELESAIKAN' DITEKAN
    if (isset($_POST['proses_selesai'])) {
        if (!$stok_aman) {
            echo "<script>alert('GAGAL: Stok obat tidak mencukupi!');</script>";
        } else {
            // 1. Update Status Resep jadi 'Diambil'
            mysqli_query($koneksi, "UPDATE resep SET status_resep = 'Diambil' WHERE no_resep = '$no_resep'");

            // 2. Kurangi Stok Obat di Database
            foreach ($list_obat as $o) {
                $kd = $o['kode_obat'];
                $jml = $o['jumlah_minta'];
                // Query pengurangan stok
                mysqli_query($koneksi, "UPDATE obat SET stok = stok - $jml WHERE kode_obat = '$kd'");
            }

            // 3. Redirect kembali
            $_SESSION['success'] = "Resep $no_resep berhasil diselesaikan! Stok telah dikurangi.";
            header("Location: pemrosesan_resep.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Apotek - Pemrosesan Resep</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="d-flex" id="wrapper">
    <div class="bg-dark text-white border-end" style="width: 250px; min-height: 100vh;">
        <div class="p-4 text-center fw-bold fs-4 border-bottom">APOTEK</div>
        <div class="list-group list-group-flush">
            <a href="dashboard_apoteker.php" class="list-group-item list-group-item-action bg-dark text-white">Dashboard</a>
            <a href="pemrosesan_resep.php" class="list-group-item list-group-item-action bg-secondary text-white">Proses Resep</a>
            <a href="manajemen_stok.php" class="list-group-item list-group-item-action bg-dark text-white">Stok Obat</a>
            <a href="../login.php?logout=true" class="list-group-item list-group-item-action bg-dark text-danger mt-3">Logout</a>
        </div>
    </div>

    <div class="container-fluid p-4">
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($mode === 'daftar'): ?>
            <h2 class="mb-4"><i class="fa fa-clipboard-list"></i> Daftar Antrian Resep</h2>
            <div class="card shadow">
                <div class="card-body">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-primary">
                            <tr>
                                <th>No. Resep</th>
                                <th>Tanggal</th>
                                <th>Nama Pasien</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($result_daftar) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result_daftar)): ?>
                                <tr>
                                    <td><strong><?= $row['no_resep'] ?></strong></td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['tgl_resep'])) ?></td>
                                    <td><?= $row['nama_pasien'] ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark"><?= $row['status_resep'] ?></span>
                                    </td>
                                    <td>
                                        <a href="pemrosesan_resep.php?no_resep=<?= $row['no_resep'] ?>" class="btn btn-primary btn-sm">
                                            <i class="fa fa-pills"></i> Proses Obat
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Belum ada antrian resep masuk.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2><i class="fa fa-file-prescription"></i> Detail Resep: <?= $data_resep['no_resep'] ?></h2>
                <a href="pemrosesan_resep.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</a>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow h-100">
                        <div class="card-header bg-info text-white fw-bold">Data Pasien</div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm mb-0">
                                <tr><td width="130">Nama Pasien</td><td>: <strong><?= $data_resep['nama_pasien'] ?></strong></td></tr>
                                <tr><td>No. Pasien</td><td>: <?= $data_resep['no_pasien'] ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow h-100">
                        <div class="card-header bg-success text-white fw-bold">Data Dokter</div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm mb-0">
                                <tr><td width="130">Nama Dokter</td><td>: <?= $data_resep['nama_dokter'] ?></td></tr>
                                <tr><td>Poli</td><td>: <?= $data_resep['nama_poli'] ?></td></tr>
                                <tr><td>Tanggal</td><td>: <?= date('d M Y', strtotime($data_resep['tgl_resep'])) ?></td></tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Daftar Obat yang Diminta</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($list_obat)): ?>
                        <div class="alert alert-danger text-center">
                            <strong>Data Kosong!</strong><br>
                            Tidak ada obat dalam resep ini.
                        </div>
                    <?php else: ?>
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Obat</th>
                                    <th class="text-center">Jumlah</th>
                                    <th>Aturan Pakai</th>
                                    <th class="text-center">Stok Gudang</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($list_obat as $obat): ?>
                                <tr class="<?= ($obat['stok_tersedia'] < $obat['jumlah_minta']) ? 'table-danger' : '' ?>">
                                    <td>
                                        <strong><?= $obat['nama_obat'] ?></strong><br>
                                        <small class="text-muted">Kode: <?= $obat['kode_obat'] ?></small>
                                    </td>
                                    <td class="text-center fw-bold fs-5">
                                        <?= $obat['jumlah_minta'] ?> <small style="font-size:12px"><?= $obat['satuan'] ?></small>
                                    </td>
                                    <td><?= $obat['aturan_pakai'] ?></td>
                                    <td class="text-center">
                                        <?= $obat['stok_tersedia'] ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($obat['stok_tersedia'] >= $obat['jumlah_minta']): ?>
                                            <span class="badge bg-success">Tersedia</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Stok Kurang!</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <div class="card-footer bg-light p-3 text-end">
                    <?php if ($data_resep['status_resep'] == 'Diambil'): ?>
                        <button class="btn btn-secondary btn-lg" disabled>Resep Sudah Selesai</button>
                    
                    <?php elseif (!empty($list_obat) && $stok_aman): ?>
                        <form method="POST" onsubmit="return confirm('Yakin ingin menyelesaikan resep ini? Stok akan dikurangi otomatis.');">
                            <input type="hidden" name="proses_selesai" value="true">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa fa-check-circle"></i> Selesaikan & Serahkan Obat
                            </button>
                        </form>
                    
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg" disabled>Tidak Dapat Diproses</button>
                        <div class="text-danger mt-2 small">
                            *Tombol non-aktif karena stok obat kurang atau data obat kosong.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>