<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

// Cek login dan role
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Manajer') {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['nama_lengkap'];

// Ambil pasien yang paling sering berkunjung
$pasien_query = $koneksi->query("
    SELECT p.nama_pasien, COUNT(k.no_kunjungan) AS kunjungan
    FROM kunjungan k
    JOIN pasien p ON k.no_pasien = p.no_pasien
    GROUP BY k.no_pasien
    ORDER BY kunjungan DESC
    LIMIT 5
");

// Ambil dokter yang paling banyak menangani pasien
$dokter_query = $koneksi->query("
    SELECT d.nama_dokter, COUNT(k.no_kunjungan) AS pasien_terlayani
    FROM kunjungan k
    JOIN dokter d ON k.no_dokter = d.no_dokter
    GROUP BY k.no_dokter
    ORDER BY pasien_terlayani DESC
    LIMIT 5
");

// Ambil transaksi dengan biaya terbesar
$biaya_query = $koneksi->query("
    SELECT p.nama_pasien, b.total_akhir
    FROM pembayaran b
    JOIN kunjungan k ON b.no_kunjungan = k.no_kunjungan
    JOIN pasien p ON k.no_pasien = p.no_pasien
    ORDER BY b.total_akhir DESC
    LIMIT 5
");

// Data grafik kunjungan bulanan
$chart_query = $koneksi->query("
    SELECT MONTH(tgl_periksa) AS bulan, COUNT(*) AS jumlah
    FROM kunjungan
    GROUP BY MONTH(tgl_periksa)
    ORDER BY MONTH(tgl_periksa)
");

$chart_labels = [];
$chart_data = [];
while ($row = $chart_query->fetch_assoc()) {
    $chart_labels[] = "Bulan " . $row['bulan'];
    $chart_data[] = $row['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Manajer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="d-flex" id="wrapper">
    <!-- Sidebar -->
    <div class="bg-dark text-white border-end" id="sidebar-wrapper">
        <div class="sidebar-heading p-4 text-center text-primary fw-bold fs-5">
            <i class="fas fa-chart-pie me-2"></i> MANAJER
        </div>
        <div class="list-group list-group-flush">
            <a href="#" class="list-group-item list-group-item-action bg-dark text-white active">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
            <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-users me-2"></i> Laporan SDM
            </a>
            <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-money-bill-wave me-2"></i> Laporan Keuangan
            </a>
            <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-chart-bar me-2"></i> Statistik Kunjungan
            </a>
            <a href="#" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-box me-2"></i> Stok Obat & Logistik
            </a>
        </div>
    </div>

    <!-- Page Content -->
    <div id="page-content-wrapper">
        <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom shadow-sm sticky-top">
            <div class="container-fluid">
                <button class="btn btn-outline-primary" id="sidebarToggle">
                    <i class="fas fa-bars"></i> Menu
                </button>
                <span class="navbar-text ms-3">
                    Selamat Datang, <strong><?php echo $user_name; ?></strong> (Manajer)
                </span>
                <a href="login.php?logout=true" class="btn btn-danger btn-sm ms-auto">
                    <i class="fas fa-sign-out-alt"></i> Keluar
                </a>
            </div>
        </nav>

        <div class="container-fluid py-4">
            <h1 class="mb-4 text-primary"><i class="fas fa-analytics"></i> Analisis Kinerja Klinik</h1>

            <!-- Grafik Kunjungan Bulanan -->
            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-chart-line me-2"></i> Kunjungan Pasien Bulanan</div>
                <div class="card-body">
                    <canvas id="kunjunganChart" height="100"></canvas>
                </div>
            </div>

            <div class="row">
                <!-- Pasien yang sering berkunjung -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-user-friends me-2"></i> Pasien Paling Sering Berkunjung
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php while ($row = $pasien_query->fetch_assoc()): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $row['nama_pasien']; ?>
                                        <span class="badge bg-primary rounded-pill"><?php echo $row['kunjungan']; ?>x</span>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Dokter yang paling banyak menangani pasien -->
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-warning text-white">
                            <i class="fas fa-user-md me-2"></i> Dokter Paling Aktif
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php while ($row = $dokter_query->fetch_assoc()): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $row['nama_dokter']; ?>
                                        <span class="badge bg-primary rounded-pill"><?php echo $row['pasien_terlayani']; ?> pasien</span>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Biaya terbesar -->
                <div class="col-md-12 mb-4">
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <i class="fas fa-money-bill-wave me-2"></i> Transaksi dengan Biaya Terbesar
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php while ($row = $biaya_query->fetch_assoc()): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $row['nama_pasien']; ?>
                                        <span class="badge bg-primary rounded-pill">Rp <?php echo number_format($row['total_akhir'],0,",","."); ?></span>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- End Page Content -->
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById("sidebarToggle").addEventListener("click", () => {
    document.getElementById("wrapper").classList.toggle("toggled");
});

// Grafik Kunjungan Bulanan
const ctx = document.getElementById('kunjunganChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($chart_labels); ?>,
        datasets: [{
            label: 'Jumlah Kunjungan',
            data: <?php echo json_encode($chart_data); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>
</body>
</html>
