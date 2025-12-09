<?php
include 'koneksi.php';

// 1. Cek Keamanan
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Dokter') {
    header("Location: login.php");
    exit;
}

// 2. Ambil ID Kunjungan dari URL
if (!isset($_GET['no_kunjungan'])) {
    die("Error: No Kunjungan tidak dipilih.");
}
$no_kunjungan = $_GET['no_kunjungan'];

// 3. Ambil Data Pasien Terkait
$query = "SELECT k.*, p.nama_pasien, p.jenis_kelamin, p.tgl_lahir 
          FROM kunjungan k 
          JOIN pasien p ON k.no_pasien = p.no_pasien 
          WHERE k.no_kunjungan = '$no_kunjungan'";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data kunjungan tidak ditemukan.");
}

// 4. PROSES SIMPAN (Saat tombol 'Simpan Pemeriksaan' ditekan)
if (isset($_POST['simpan_diagnosa'])) {
    $diagnosa = mysqli_real_escape_string($koneksi, $_POST['diagnosa']);
    
    // Ambil Array Data Obat
    $obat_dipilih = isset($_POST['obat']) ? $_POST['obat'] : []; // Array ID obat (Checkbox)
    $jumlah_obat  = $_POST['jumlah'];       // Array Jumlah obat
    $aturan_pakai = $_POST['aturan_pakai']; // Array Aturan Pakai (PERBAIKAN DISINI)

    // A. Update Tabel Kunjungan (Diagnosa & Status Selesai)
    // Status diubah jadi 'Selesai' (asumsi setelah dokter periksa, pasien lanjut ke kasir/apotek)
    // Jika alurnya ke Apotek dulu, bisa set status 'Resep' atau 'Menunggu Obat' sesuai kebutuhan sistem Anda.
    $update_kunjungan = "UPDATE kunjungan SET diagnosa = '$diagnosa', status = 'Menunggu' WHERE no_kunjungan = '$no_kunjungan'";
    mysqli_query($koneksi, $update_kunjungan);

    // B. Buat Header Resep Baru
    // Cek apakah ada obat yang dipilih sebelum membuat resep
    if (!empty($obat_dipilih)) {
        
        $no_resep = "R-" . time(); 
        $tgl_resep = date('Y-m-d');
        
        $insert_resep = "INSERT INTO resep (no_resep, no_kunjungan, tgl_resep, status_resep) 
                         VALUES ('$no_resep', '$no_kunjungan', '$tgl_resep', 'Diproses')";
        
        if (mysqli_query($koneksi, $insert_resep)) {
            
            // C. Simpan Detail Obat (Looping array obat yang dicentang)
            foreach ($obat_dipilih as $kode_obat) {
                $qty = $jumlah_obat[$kode_obat];
                $aturan = mysqli_real_escape_string($koneksi, $aturan_pakai[$kode_obat]); // Ambil aturan pakai spesifik obat ini
                
                // Pastikan quantity valid
                if ($qty > 0) {
                    // 1. Masukkan ke detail_resep dengan Aturan Pakai yang diinput dokter
                    $insert_detail = "INSERT INTO detail_resep (no_resep, kode_obat, jumlah, aturan_pakai) 
                                      VALUES ('$no_resep', '$kode_obat', '$qty', '$aturan')";
                    
                    if(!mysqli_query($koneksi, $insert_detail)){
                        echo "Error Detail: " . mysqli_error($koneksi);
                    }

                    // 2. Kurangi Stok di Tabel Obat
                    $update_stok = "UPDATE obat SET stok = stok - $qty WHERE kode_obat = '$kode_obat'";
                    mysqli_query($koneksi, $update_stok);
                }
            }
            
            echo "<script>alert('Pemeriksaan Selesai & Resep Terkirim!'); window.location='dashboard_dokter.php';</script>";
        } else {
            echo "Gagal membuat resep: " . mysqli_error($koneksi);
        }
    } else {
        // Jika tidak ada obat, hanya simpan diagnosa
        echo "<script>alert('Pemeriksaan Selesai (Tanpa Resep)!'); window.location='dashboard_dokter.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pemeriksaan Pasien</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-light">

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4><i class="fas fa-stethoscope me-2"></i> Form Pemeriksaan Medis</h4>
                </div>
                <div class="card-body">
                    
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4 border-end">
                                <h5 class="text-secondary mb-3">Data Pasien</h5>
                                <div class="mb-3">
                                    <label class="fw-bold">Nama Pasien</label>
                                    <input type="text" class="form-control bg-light" value="<?= $data['nama_pasien']; ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Jenis Kelamin</label>
                                    <input type="text" class="form-control bg-light" value="<?= ($data['jenis_kelamin']=='L')?'Laki-laki':'Perempuan'; ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="fw-bold">Keluhan Utama</label>
                                    <textarea class="form-control bg-warning-subtle" rows="3" readonly><?= $data['keluhan']; ?></textarea>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <h5 class="text-primary mb-3">Hasil Pemeriksaan</h5>
                                
                                <div class="mb-4">
                                    <label class="form-label fw-bold">Diagnosa Dokter</label>
                                    <textarea name="diagnosa" class="form-control" rows="3" placeholder="Tulis hasil diagnosa penyakit pasien disini..." required></textarea>
                                </div>

                                <hr>

                                <h5 class="text-primary mb-3">Resep Obat</h5>
                                <div class="alert alert-info py-2"><small><i class="fas fa-info-circle"></i> Centang obat, isi jumlah, dan aturan pakai.</small></div>
                                
                                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                    <table class="table table-bordered table-sm align-middle">
                                        <thead class="table-dark sticky-top">
                                            <tr>
                                                <th width="5%" class="text-center">Pilih</th>
                                                <th>Nama Obat</th>
                                                <th width="15%">Stok</th>
                                                <th width="15%">Jumlah</th>
                                                <th width="30%">Aturan Pakai</th> </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $q_obat = mysqli_query($koneksi, "SELECT * FROM obat WHERE stok > 0 ORDER BY nama_obat ASC");
                                            while($obat = mysqli_fetch_assoc($q_obat)) {
                                                $id = $obat['kode_obat'];
                                            ?>
                                            <tr>
                                                <td class="text-center">
                                                    <input type="checkbox" name="obat[]" value="<?= $id; ?>" class="form-check-input border-secondary">
                                                </td>
                                                <td>
                                                    <strong><?= $obat['nama_obat']; ?></strong><br>
                                                    <small class="text-muted"><?= $obat['satuan']; ?></small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary"><?= $obat['stok']; ?></span>
                                                </td>
                                                <td>
                                                    <input type="number" name="jumlah[<?= $id; ?>]" class="form-control form-control-sm" min="1" max="<?= $obat['stok']; ?>" value="1">
                                                </td>
                                                <td>
                                                    <input type="text" name="aturan_pakai[<?= $id; ?>]" class="form-control form-control-sm" placeholder="Cth: 3x1 Sesudah Makan">
                                                </td>
                                            </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="dashboard_dokter.php" class="btn btn-secondary me-2">Kembali</a>
                            <button type="submit" name="simpan_diagnosa" class="btn btn-primary btn-lg" onclick="return confirm('Simpan pemeriksaan ini?');">
                                <i class="fas fa-save me-2"></i> Simpan Pemeriksaan
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>