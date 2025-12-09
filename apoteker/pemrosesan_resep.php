<?php
// PATH FIX: include harus pakai ../
include '../koneksi.php';

// Cek autentikasi dan role
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Apoteker') {
    header("Location: ../login.php");
    exit;
}

$user_name = $_SESSION['nama_lengkap'];
$no_resep = $_GET['no_resep'] ?? ''; 

// Tentukan mode tampilan: 'daftar' untuk sidebar, 'detail' untuk tombol Proses
$mode = empty($no_resep) ? 'daftar' : 'detail';

if ($mode === 'daftar') {
    // MODE DAFTAR (untuk link Sidebar)
    
    $query_daftar_resep = "
        SELECT 
            r.no_resep, r.tgl_resep, p.nama_pasien, r.status_resep
        FROM resep r
        JOIN kunjungan k ON r.no_kunjungan = k.no_kunjungan
        JOIN pasien p ON k.no_pasien = p.no_pasien
        WHERE r.status_resep IN ('Antre', 'Diproses')
        ORDER BY r.tgl_resep ASC;
    ";
    $result_daftar = $koneksi->query($query_daftar_resep);
    
} else {
    // MODE DETAIL (untuk tombol Proses dari dashboard atau dari mode daftar)
    
    // 1. Ambil Data Dasar Resep dan Pasien (FIX SQL alias dr.nama_dokter)
    $query_resep_utama = "
        SELECT 
            r.no_resep, r.tgl_resep, r.status_resep,
            p.nama_pasien, p.no_pasien,
            dr.nama_dokter
        FROM resep r
        JOIN kunjungan k ON r.no_kunjungan = k.no_kunjungan
        JOIN pasien p ON k.no_pasien = p.no_pasien
        JOIN dokter dr ON k.no_dokter = dr.no_dokter
        WHERE r.no_resep = '$no_resep';
    ";
    $result_utama = $koneksi->query($query_resep_utama);
    $data_resep = $result_utama->fetch_assoc();
    
    if (!$data_resep) {
        $mode = 'daftar';
    } else {
        // 2. Ambil Detail Obat dalam Resep (Ambil juga kode_obat untuk update stok)
        $query_detail = "
            SELECT dr.jumlah, dr.aturan_pakai, o.nama_obat, o.satuan, o.stok, dr.kode_obat
            FROM detail_resep dr
            JOIN obat o ON dr.kode_obat = o.kode_obat
            WHERE dr.no_resep = '$no_resep';
        ";
        $result_detail = $koneksi->query($query_detail);

        // --- LOGIKA UPDATE STATUS RESEP & STOK ---
        if (isset($_POST['update_status']) && $_POST['update_status'] == 'Selesai') {
            
            // FIX FATAL ERROR: Hapus 'tgl_selesai' dari query
            $update_query = "UPDATE resep SET status_resep = 'Diambil' WHERE no_resep = '$no_resep'";
            
            $stok_aman = true;
            $items_to_update = [];
            
            // Cek stok sebelum update
            $result_check = $koneksi->query($query_detail); 
            
            if ($result_check && $result_check->num_rows > 0) {
                while($item = $result_check->fetch_assoc()) {
                    if ($item['stok'] < $item['jumlah']) {
                        $stok_aman = false;
                        break;
                    }
                    $items_to_update[] = $item;
                }
            }
            
            if ($stok_aman && $koneksi->query($update_query)) {
                
                // Lakukan pengurangan stok
                foreach ($items_to_update as $item) {
                    $new_stok = $item['stok'] - $item['jumlah'];
                    $kode_obat_update = $item['kode_obat'];
                    $koneksi->query("UPDATE obat SET stok = $new_stok WHERE kode_obat = '$kode_obat_update'");
                }
                
                $_SESSION['success'] = "Resep $no_resep berhasil diproses dan status diubah menjadi Diambil.";
                header("Location: dashboard_apoteker.php");
                exit;
            } else {
                $_SESSION['error'] = "Gagal memproses resep. Pastikan semua obat memiliki stok yang cukup.";
                header("Location: pemrosesan_resep.php?no_resep=$no_resep");
                exit;
            }
        }
        // --- END LOGIKA UPDATE STATUS RESEP ---
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik Sehat | Pemrosesan Resep</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../style.css"> 
</head>
<body>
<div class="d-flex" id="wrapper">
    <div class="bg-dark text-white border-end sidebar-wrapper" id="sidebar-wrapper">
        <div class="sidebar-heading p-4 text-center border-bottom text-primary fw-bold fs-5"><i class="fas fa-pills me-2"></i> APOTEK FARMA</div>
        <div class="list-group list-group-flush">
            <a href="dashboard_apoteker.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
            
            <a href="pemrosesan_resep.php" class="list-group-item list-group-item-action bg-dark text-white active"><i class="fas fa-prescription-bottle-alt me-2"></i> Pemrosesan Resep</a>
            
            <a href="manajemen_stok.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-boxes me-2"></i> Manajemen Stok</a>
            <a href="#" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-history me-2"></i> Riwayat Pengeluaran</a>
        </div>
    </div>
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom shadow-sm sticky-top">
            <div class="container-fluid">
                <button class="btn btn-outline-primary" id="sidebarToggle"><i class="fas fa-bars"></i> Menu</button>
                <div class="d-flex align-items-center">
                    <span class="navbar-text me-3 d-none d-md-inline">Selamat Datang, **<?php echo $user_name; ?>** (Apoteker)!</span>
                    <a href="../login.php?logout=true" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                </div>
            </nav>

        <div class="container-fluid py-4">
            
            <?php if ($mode === 'detail'): ?>
            <h1 class="mb-4 text-primary"><i class="fas fa-file-prescription"></i> Detail Resep #<?php echo $data_resep['no_resep']; ?></h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header bg-secondary text-white fw-bold">Data Pasien & Dokter</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>No. Pasien:</strong> <?php echo $data_resep['no_pasien']; ?></p>
                            <p><strong>Nama Pasien:</strong> <?php echo $data_resep['nama_pasien']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Tanggal Resep:</strong> <?php echo date('d M Y', strtotime($data_resep['tgl_resep'])); ?></p>
                            <p><strong>Dokter:</strong> <?php echo $data_resep['nama_dokter']; ?></p>
                            <p><strong>Status Resep:</strong> <span class="badge bg-info fs-6"><?php echo $data_resep['status_resep']; ?></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center">
                    Daftar Obat Resep
                    <?php if ($data_resep['status_resep'] != 'Diambil'): ?>
                    <form method="POST" onsubmit="return confirm('Apakah Anda yakin semua obat sudah disiapkan dan ingin menyelesaikan resep ini? Stok akan dikurangi.');">
                        <input type="hidden" name="update_status" value="Selesai">
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-check-circle"></i> Selesaikan Resep</button>
                    </form>
                    <?php else: ?>
                    <span class="badge bg-success fs-6"><i class="fas fa-check"></i> Sudah Diambil</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Nama Obat</th>
                                <th>Jumlah</th>
                                <th>Satuan</th>
                                <th>Aturan Pakai</th>
                                <th>Stok Tersedia</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result_detail->fetch_assoc()): ?>
                            <tr class="<?php echo ($row['stok'] < $row['jumlah']) ? 'table-danger' : ''; ?>">
                                <td><?php echo $row['nama_obat']; ?></td>
                                <td><?php echo $row['jumlah']; ?></td>
                                <td><?php echo $row['satuan']; ?></td>
                                <td><strong><?php echo $row['aturan_pakai']; ?></strong></td>
                                <td>
                                    <?php echo $row['stok']; ?>
                                    <?php if ($row['stok'] < $row['jumlah']): ?>
                                        <span class="badge bg-danger ms-2">Stok Kurang!</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php elseif ($mode === 'daftar'): ?>
            <h1 class="mb-4 text-primary"><i class="fas fa-list-alt"></i> Daftar Resep Menunggu Proses</h1>
            <p class="lead">Pilih resep di bawah untuk mulai pemrosesan obat.</p>
            
            <div class="card shadow">
                <div class="card-header bg-primary text-white fw-bold">
                    Daftar Antrian Resep (<?php echo $result_daftar->num_rows ?? 0; ?>)
                </div>
                <div class="card-body">
                    <?php if ($result_daftar && $result_daftar->num_rows > 0): ?>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr><th>No. Resep</th><th>Tgl Resep</th><th>Pasien</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            <?php while($row = $result_daftar->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['no_resep']; ?></td>
                                <td><?php echo date('d M Y', strtotime($row['tgl_resep'])); ?></td>
                                <td><?php echo $row['nama_pasien']; ?></td>
                                <td><span class="badge bg-<?php echo ($row['status_resep'] == 'Antre' ? 'warning' : 'info'); ?>"><?php echo $row['status_resep']; ?></span></td>
                                <td>
                                    <a href="pemrosesan_resep.php?no_resep=<?php echo $row['no_resep']; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-eye"></i> Proses
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                        <div class="alert alert-info">Tidak ada resep dalam antrian.</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php endif; ?>
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
    document.getElementById("sidebarToggle").addEventListener("click", () => {
        document.getElementById("wrapper").classList.toggle("toggled");
    });
</script>
</body>
</html>