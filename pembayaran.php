<?php
include 'koneksi.php';

// Ambil ID Kunjungan dari URL
$no_kunjungan = $_GET['id'] ?? null;

if (!$no_kunjungan) {
    echo "<script>alert('Pilih Pasien Terlebih Dahulu!'); window.location='kunjungan_list.php';</script>";
    exit;
}

// 1. AMBIL DATA PASIEN & KUNJUNGAN
$query_pasien = "SELECT k.*, p.nama_pasien, d.nama_dokter 
                 FROM kunjungan k 
                 JOIN pasien p ON k.no_pasien = p.no_pasien 
                 JOIN dokter d ON k.no_dokter = d.no_dokter 
                 WHERE k.no_kunjungan = ?";
$stmt = mysqli_prepare($koneksi, $query_pasien);
mysqli_stmt_bind_param($stmt, "i", $no_kunjungan);
mysqli_stmt_execute($stmt);
$data_kunjungan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// 2. HITUNG OTOMATIS BIAYA OBAT (Dari Resep)
// Logika: Cek apakah ada resep untuk kunjungan ini, lalu hitung total harganya
$query_obat = "SELECT SUM(dr.jumlah * o.harga_satuan) as total_obat
               FROM resep r
               JOIN detail_resep dr ON r.no_resep = dr.no_resep
               JOIN obat o ON dr.kode_obat = o.kode_obat
               WHERE r.no_kunjungan = ?";
$stmt_obat = mysqli_prepare($koneksi, $query_obat);
mysqli_stmt_bind_param($stmt_obat, "i", $no_kunjungan);
mysqli_stmt_execute($stmt_obat);
$hasil_obat = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_obat));

// Jika tidak ada resep, set 0
$biaya_obat = $hasil_obat['total_obat'] ?? 0;

// 3. AMBIL DAFTAR TINDAKAN (Untuk Checkbox)
$tindakan_list = mysqli_query($koneksi, "SELECT * FROM tindakan ORDER BY nama_tindakan ASC");

// --- PROSES SIMPAN PEMBAYARAN ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // A. Generate No Bayar Otomatis (Format: BYR-001)
    $q_no = mysqli_query($koneksi, "SELECT MAX(no_bayar) as max_no FROM pembayaran");
    $row_no = mysqli_fetch_assoc($q_no);
    $last_no = $row_no['max_no']; // Contoh: BYR-005
    
    if ($last_no) {
        $urut = (int) substr($last_no, 4, 3);
        $urut++;
        $no_bayar = "BYR-" . sprintf("%03s", $urut);
    } else {
        $no_bayar = "BYR-001";
    }

    // B. Hitung Total Tindakan dari Checkbox yang dipilih
    $total_tindakan = 0;
    if (isset($_POST['tindakan'])) {
        foreach ($_POST['tindakan'] as $kode_tindakan) {
            // Ambil harga dari DB agar aman (jangan ambil dari value HTML)
            $q_harga = mysqli_query($koneksi, "SELECT tarif FROM tindakan WHERE kode_tindakan = '$kode_tindakan'");
            $h = mysqli_fetch_assoc($q_harga);
            $total_tindakan += $h['tarif'];
        }
    }

    $total_akhir = $total_tindakan + $biaya_obat;
    $tgl_bayar   = date("Y-m-d H:i:s");
    $id_petugas  = 1; // Default ID Admin (Ganti dengan $_SESSION['id_user'] jika sudah login)

    // C. Insert ke Tabel Pembayaran
    $sql_simpan = "INSERT INTO pembayaran (no_bayar, no_kunjungan, total_biaya_tindakan, total_biaya_obat, total_akhir, tgl_bayar, id_petugas)
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_save = mysqli_prepare($koneksi, $sql_simpan);
    mysqli_stmt_bind_param($stmt_save, "siddssi", $no_bayar, $no_kunjungan, $total_tindakan, $biaya_obat, $total_akhir, $tgl_bayar, $id_petugas);

    if (mysqli_stmt_execute($stmt_save)) {
        // D. Update Status Kunjungan jadi 'Selesai'
        mysqli_query($koneksi, "UPDATE kunjungan SET status = 'Selesai' WHERE no_kunjungan = '$no_kunjungan'");

        // Redirect ke Struk
        echo "<script>
                alert('Pembayaran Berhasil!'); 
                window.open('struk.php?id=$no_bayar', '_blank'); 
                window.location='kunjungan_list.php';
              </script>";
    } else {
        echo "Error: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Proses Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Kasir Pembayaran</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 border-end">
                        <h5 class="text-secondary mb-3">Data Pasien</h5>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th width="150">No. Kunjungan</th>
                                <td>: #<?= $data_kunjungan['no_kunjungan'] ?></td>
                            </tr>
                            <tr>
                                <th>Nama Pasien</th>
                                <td>: <strong><?= $data_kunjungan['nama_pasien'] ?></strong></td>
                            </tr>
                            <tr>
                                <th>Dokter</th>
                                <td>: <?= $data_kunjungan['nama_dokter'] ?></td>
                            </tr>
                            <tr>
                                <th>Keluhan</th>
                                <td>: <?= $data_kunjungan['keluhan'] ?></td>
                            </tr>
                        </table>
                        
                        <hr>
                        
                        <div class="alert alert-info">
                            <h6 class="alert-heading fw-bold">Biaya Obat (Otomatis)</h6>
                            <p class="mb-0">Berdasarkan resep dokter.</p>
                            <h3 class="mt-2">Rp <?= number_format($biaya_obat, 0, ',', '.') ?></h3>
                            <input type="hidden" id="biaya_obat" value="<?= $biaya_obat ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="text-secondary mb-3">Pilih Tindakan / Layanan</h5>
                        <div class="card p-3 mb-3" style="max-height: 300px; overflow-y: auto;">
                            <?php while($t = mysqli_fetch_assoc($tindakan_list)): ?>
                                <div class="form-check">
                                    <input class="form-check-input chk-tindakan" type="checkbox" 
                                           name="tindakan[]" 
                                           value="<?= $t['kode_tindakan'] ?>" 
                                           data-tarif="<?= $t['tarif'] ?>"
                                           id="t_<?= $t['kode_tindakan'] ?>">
                                    <label class="form-check-label d-flex justify-content-between" for="t_<?= $t['kode_tindakan'] ?>">
                                        <span><?= $t['nama_tindakan'] ?></span>
                                        <b>Rp <?= number_format($t['tarif'], 0, ',', '.') ?></b>
                                    </label>
                                </div>
                            <?php endwhile; ?>
                        </div>

                        <div class="card bg-dark text-white p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span>TOTAL YANG HARUS DIBAYAR:</span>
                                <h2 class="mb-0" id="display_total">Rp <?= number_format($biaya_obat, 0, ',', '.') ?></h2>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-success btn-lg">PROSES BAYAR & CETAK</button>
                            <a href="kunjungan_list.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Ambil elemen
    const checkboxes = document.querySelectorAll('.chk-tindakan');
    const displayTotal = document.getElementById('display_total');
    const biayaObat = parseFloat(document.getElementById('biaya_obat').value);

    // Fungsi format Rupiah
    const formatter = new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    });

    // Event Listener setiap checkbox diklik
    checkboxes.forEach(chk => {
        chk.addEventListener('change', hitungTotal);
    });

    function hitungTotal() {
        let totalTindakan = 0;
        
        // Loop semua checkbox, jika diceklis tambahkan tarifnya
        checkboxes.forEach(chk => {
            if (chk.checked) {
                totalTindakan += parseFloat(chk.getAttribute('data-tarif'));
            }
        });

        // Total Akhir = Obat + Tindakan
        let totalAkhir = biayaObat + totalTindakan;

        // Update tampilan
        displayTotal.innerText = formatter.format(totalAkhir);
    }
</script>

</body>
</html>