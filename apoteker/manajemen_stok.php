<?php
// PATH FIX: include harus pakai ../
include '../koneksi.php';

// Cek autentikasi dan role
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Apoteker') {
    header("Location: ../login.php");
    exit;
}

$user_name = $_SESSION['nama_lengkap'];

// Query untuk menampilkan semua obat, diurutkan berdasarkan stok terendah
$query_semua_stok = "
    SELECT 
        kode_obat, 
        nama_obat, 
        stok, 
        harga_satuan, 
        satuan
    FROM obat
    ORDER BY stok ASC;
";
$result_semua_stok = $koneksi->query($query_semua_stok);

$stok_minimum = 20; // Batas stok yang dianggap rendah
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klinik Sehat | Manajemen Stok</title>
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
            
            <a href="pemrosesan_resep.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-prescription-bottle-alt me-2"></i> Pemrosesan Resep</a>
            
            <a href="manajemen_stok.php" class="list-group-item list-group-item-action bg-dark text-white active"><i class="fas fa-boxes me-2"></i> Manajemen Stok</a>
            
            <a href="#" class="list-group-item list-group-item-action bg-dark text-white"><i class="fas fa-history me-2"></i> Riwayat Pengeluaran</a>
        </div>
    </div>
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom shadow-sm sticky-top">
            <div class="container-fluid">
                <button class="btn btn-outline-primary" id="sidebarToggle"><i class="fas fa-bars"></i> Menu</button>
                <span class="navbar-text me-3 d-none d-md-inline">Selamat Datang, **<?php echo $user_name; ?>** (Apoteker)!</span>
                <a href="../login.php?logout=true" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Keluar</a>
            </div>
        </nav>

        <div class="container-fluid py-4">
            <h1 class="mb-4 text-primary"><i class="fas fa-boxes"></i> Manajemen Stok Obat</h1>

            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white fw-bold">
                    Daftar Semua Stok Obat
                </div>
                <div class="card-body">
                    <?php if ($result_semua_stok && $result_semua_stok->num_rows > 0): ?>
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Kode Obat</th>
                                    <th>Nama Obat</th>
                                    <th>Harga Satuan</th>
                                    <th>Stok</th>
                                    <th>Satuan</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result_semua_stok->fetch_assoc()): ?>
                                <tr class="<?php echo ($row['stok'] <= $stok_minimum) ? 'table-warning' : ''; ?>">
                                    <td><?php echo $row['kode_obat']; ?></td>
                                    <td><?php echo $row['nama_obat']; ?></td>
                                    <td>Rp<?php echo number_format($row['harga_satuan'], 0, ',', '.'); ?></td>
                                    <td><?php echo $row['stok']; ?></td>
                                    <td><?php echo $row['satuan']; ?></td>
                                    <td>
                                        <?php if ($row['stok'] <= $stok_minimum): ?>
                                            <span class="badge bg-danger">Stok Rendah</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Aman</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" title="Edit Data"><i class="fas fa-edit"></i></button>
                                        <button class="btn btn-sm btn-secondary" title="Tambah Stok"><i class="fas fa-box-open"></i></button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert alert-warning">Tidak ada data obat dalam database.</div>
                    <?php endif; ?>
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
    document.getElementById("sidebarToggle").addEventListener("click", () => {
        document.getElementById("wrapper").classList.toggle("toggled");
    });
</script>
</body>
</html>