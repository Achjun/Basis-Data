<?php
include 'koneksi.php';

// Ambil ID pasien dari URL (jika ada)
$id_pasien = $_GET['id'] ?? null;

// --- UBAH KE MYSQLI: Ambil data pasien spesifik ---
$pasien_terpilih = null;
if ($id_pasien) {
    // Siapkan query
    $query = "SELECT * FROM pasien WHERE no_pasien = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    
    // Bind parameter (s = string)
    mysqli_stmt_bind_param($stmt, "s", $id_pasien);
    
    // Eksekusi
    mysqli_stmt_execute($stmt);
    
    // Ambil hasil
    $result = mysqli_stmt_get_result($stmt);
    $pasien_terpilih = mysqli_fetch_assoc($result);
}

// --- UBAH KE MYSQLI: Ambil data dokter & poli ---
$dokter = mysqli_query($koneksi, "SELECT * FROM dokter");
$poli   = mysqli_query($koneksi, "SELECT * FROM poli");

// Proses simpan kunjungan
// Proses simpan kunjungan
// Proses simpan kunjungan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Validasi input
    if (empty($_POST['no_pasien']) || empty($_POST['no_dokter']) || empty($_POST['tanggal'])) {
        echo "<script>alert('Harap lengkapi data Pasien, Dokter, dan Tanggal!');</script>";
    } else {
        $id_pasien = $_POST['no_pasien'];
        $id_dokter = $_POST['no_dokter'];
        // $id_poli = $_POST['id_poli']; // KITA ABAIKAN INI KARENA TIDAK DISIMPAN KE DB
        $tgl       = $_POST['tanggal']; // Format dari form biasanya YYYY-MM-DD
        $keluhan   = $_POST['keluhan'];

        // Tambahkan jam otomatis agar format sesuai datetime (Opsional, tapi disarankan)
        // Jika input form hanya tanggal, kita set jam ke sekarang atau 08:00
        $tgl_lengkap = $tgl . " " . date("H:i:s"); 

        // Cek koneksi
        if (!$koneksi) {
            die("Koneksi database gagal: " . mysqli_connect_error());
        }

        // --- QUERY INSERT YANG BENAR (SESUAI FILE SQL ANDA) ---
        // Kita HAPUS 'id_poli' dari sini karena tabel kunjungan tidak punya kolom itu.
        $sql = "INSERT INTO kunjungan (no_pasien, no_dokter, tgl_periksa, keluhan, status) 
                VALUES (?, ?, ?, ?, 'Menunggu')";

        $stmt = mysqli_prepare($koneksi, $sql);

        if ($stmt) {
            // Bind parameter: 
            // s (string) -> no_pasien
            // s (string) -> no_dokter
            // s (string) -> tgl_periksa
            // s (string) -> keluhan
            mysqli_stmt_bind_param($stmt, "ssss", $id_pasien, $id_dokter, $tgl_lengkap, $keluhan);

            if (mysqli_stmt_execute($stmt)) {
                echo "<script>alert('Data Berhasil Disimpan!'); window.location='kunjungan_list.php';</script>";
                exit;
            } else {
                echo "<div class='alert alert-danger'>Gagal Menyimpan: " . mysqli_stmt_error($stmt) . "</div>";
            }
            mysqli_stmt_close($stmt);
        } else {
            echo "<div class='alert alert-danger'>Query Error: " . mysqli_error($koneksi) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buat Kunjungan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="wrapper" class="d-flex">

    <div class="bg-dark border-right" id="sidebar-wrapper">
        <div class="sidebar-heading text-white p-3 fs-4">Resepsionis</div>
        <div class="list-group list-group-flush">
            <a href="dashboard_resepsionis.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fa fa-users"></i> Dashboard</a>
            <a href="pasien_list.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fa fa-users"></i> Data Pasien</a>
            <a href="pasien_tambah.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fa fa-user-plus"></i> Tambah Pasien</a>
            <a href="kunjungan_tambah.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fa fa-calendar-plus"></i> Jadwal Kunjungan</a>
            <a href="kunjungan_list.php" class="list-group-item list-group-item-action bg-dark text-white"><i class="fa fa-calendar"></i> Data Kunjungan</a>
            
            <a href="login.php?logout=true" class="list-group-item list-group-item-action bg-dark text-white"><i class="fa fa-right-from-bracket"></i> Logout</a>
        </div>
    </div>

<div class="container mt-4">
    <h3>Buat Jadwal Kunjungan</h3>

    <form method="POST" class="row g-3">

        <div class="col-md-6">
            <label>Pasien</label>

            <?php if ($pasien_terpilih): ?>
                <input type="text" class="form-control" value="<?= $pasien_terpilih['nama_pasien'] ?>" readonly>
                <input type="hidden" name="no_pasien" value="<?= $pasien_terpilih['no_pasien'] ?>">
            
            <?php else: ?>
                <select name="no_pasien" class="form-control" required>
                    <option value="">-- Pilih Pasien --</option>
                    <?php
                    // --- UBAH KE MYSQLI: Loop Pasien ---
                    $ps = mysqli_query($koneksi, "SELECT * FROM pasien");
                    while($p = mysqli_fetch_assoc($ps)):
                    ?>
                        <option value="<?= $p['no_pasien'] ?>"><?= $p['nama_pasien'] ?></option>
                    <?php endwhile; ?>
                </select>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label>Dokter</label>
            <select name="no_dokter" class="form-control" required>
                <option value="">-- Pilih Dokter --</option>
                <?php 
                // --- UBAH KE MYSQLI: Loop Dokter ---
                while($d = mysqli_fetch_assoc($dokter)): 
                ?>
                    <option value="<?= $d['no_dokter'] ?>"><?= $d['nama_dokter'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label>Poli</label>
            <select name="id_poli" class="form-control" required>
                <option value="">-- Pilih Poli --</option>
                <?php 
                // --- UBAH KE MYSQLI: Loop Poli ---
                while($po = mysqli_fetch_assoc($poli)): 
                ?>
                    <option value="<?= $po['id_poli'] ?>"><?= $po['nama_poli'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label>Tanggal Kunjungan</label>
            <input type="date" name="tanggal" class="form-control" required>
        </div>

        <div class="col-md-12">
            <label>Keluhan</label>
            <textarea name="keluhan" class="form-control"></textarea>
        </div>

        <div class="col-md-3">
            <button class="btn btn-success w-100 mt-3">Simpan</button>
        </div>

    </form>
</div>
</body>
</html>