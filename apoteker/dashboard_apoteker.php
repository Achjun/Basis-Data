<?php
// FIX 1: Menggunakan '../' karena koneksi.php ada di satu tingkat di atas folder 'apoteker'
include '../koneksi.php';

// Pastikan sesi sudah dimulai dan role adalah Apoteker
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Apoteker') {
    // FIX 2: Menggunakan '../' untuk kembali ke login.php di root folder
    header("Location: ../login.php");
    exit;
}
$user_name = $_SESSION['nama_lengkap'];

// --- 1. Query Resep yang Perlu Diproses ---
$query_resep = "
    SELECT 
        r.no_resep, 
        r.tgl_resep, 
        k.no_kunjungan,
        p.nama_pasien, 
        r.status_resep
    FROM resep r
    JOIN kunjungan k ON r.no_kunjungan = k.no_kunjungan
    JOIN pasien p ON k.no_pasien = p.no_pasien
    WHERE r.status_resep IN ('Antre', 'Diproses')
    ORDER BY r.tgl_resep ASC;
";
$result_resep = $koneksi->query($query_resep);


// --- 2. Query Obat dengan Stok Rendah (Misal: Stok < 20) ---
$query_stok_rendah = "
    SELECT 
        kode_obat, 
        nama_obat, 
        stok, 
        satuan
    FROM obat
    WHERE stok <= 20
    ORDER BY stok ASC;
";
$result_stok_rendah = $koneksi->query($query_stok_rendah);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik Sehat | Dashboard Apoteker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../style.css"> 
</head>
<body>
<div class="d-flex" id="wrapper">
    <div class="bg-dark text-white border-end sidebar-wrapper" id="sidebar-wrapper">
        <div class="sidebar-heading p-4 text-center border-bottom text-primary fw-bold fs-5"><i class="fas fa-pills me-2"></i> APOTEK FARMA</div>
        <div class="list-group list-group-flush">
            <a href="dashboard_apoteker.php" class="list-group-item list-group-item-action bg-dark text-white active"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            
            <a href="pemrosesan_resep.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-prescription-bottle-alt me-2"></i> Pemrosesan Resep</a>
            
            <a href="manajemen_stok.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-boxes me-2"></i> Manajemen Stok</a>
        
        </div>
    </div>
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom shadow-sm sticky-top">
            <div class="container-fluid">
                <button class="btn btn-primary d-md-none" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <span class="navbar-text me-3 d-none d-md-inline">Selamat Datang, **<?php echo $user_name; ?>** (Apoteker)!</span>
                <a href="../login.php?logout=true" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </div>
        </nav>

        <div class="container-fluid py-4">
            <h1 class="mb-4 text-primary"><i class="fas fa-tachometer-alt"></i> Dashboard Apoteker</h1>
            
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white fw-bold">
                            Antrian Resep yang Belum Selesai (<?php echo $result_resep->num_rows ?? 0; ?>)
                        </div>
                        <div class="card-body">
                            <?php if ($result_resep && $result_resep->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>No. Resep</th>
                                            <th>Tgl Resep</th>
                                            <th>Pasien</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $result_resep->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['no_resep']; ?></td>
                                            <td><?php echo date('d M Y', strtotime($row['tgl_resep'])); ?></td>
                                            <td><?php echo $row['nama_pasien']; ?></td>
                                            <td><span class="badge bg-<?php echo ($row['status_resep'] == 'Antre' ? 'warning' : 'info'); ?>"><?php echo $row['status_resep']; ?></span></td>
                                            <td>
                                                <a href="pemrosesan_resep.php?no_resep=<?php echo $row['no_resep']; ?>" class="btn btn-sm btn-success" title="Lihat Detail Resep"><i class="fas fa-eye"></i> Proses</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                                <div class="alert alert-info">Tidak ada resep dalam antrian.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-danger text-white fw-bold">
                            <i class="fas fa-exclamation-triangle"></i> Stok Obat Rendah (<= 20)
                        </div>
                        <div class="card-body">
                            <?php if ($result_stok_rendah && $result_stok_rendah->num_rows > 0): ?>
                                <ul class="list-group list-group-flush">
                                    <?php while($row = $result_stok_rendah->fetch_assoc()): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $row['nama_obat']; ?>
                                        <span class="badge bg-danger rounded-pill"><?php echo $row['stok'] . ' ' . $row['satuan']; ?></span>
                                    </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <div class="alert alert-success">Semua stok obat dalam batas aman (> 20).</div>
                            <?php endif; ?>
                            <a href="manajemen_stok.php" class="btn btn-sm btn-outline-danger mt-3 d-block">Lihat Semua Stok</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <footer class="footer mt-auto py-3 bg-light border-top">
            <div class="container-fluid">
                <span class="text-muted">© <?php echo date("Y"); ?> Apotek Klinik.</span>
            </div>
        </footer>

    </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle Sidebar Script
    document.getElementById("sidebarToggle").addEventListener("click", () => {
        document.getElementById("wrapper").classList.toggle("toggled");
    });
</script>
</body>
</html>