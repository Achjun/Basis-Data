<?php
include 'koneksi.php';

// Ambil ID pasien dari URL
$id_pasien = $_GET['id'] ?? null;

// Ambil data pasien spesifik
$pasien_terpilih = null;
if ($id_pasien) {
    $stmt = $koneksi->prepare("SELECT * FROM pasien WHERE no_pasien = ?");
    $stmt->bind_param("s", $id_pasien);
    $stmt->execute();
    $result = $stmt->get_result();
    $pasien_terpilih = $result->fetch_assoc();
    $stmt->close();
}

// Ambil data dokter
$dokter_result = $koneksi->query("SELECT * FROM dokter");

// Ambil data poli
$poli_result = $koneksi->query("SELECT * FROM poli");

// Proses simpan kunjungan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $id_pasien = $_POST['no_pasien'];
    $id_dokter = $_POST['no_dokter'];
    $id_poli   = $_POST['id_poli'];
    $tgl       = $_POST['tanggal'];
    $keluhan   = $_POST['keluhan'];

    $sql = "INSERT INTO kunjungan(no_pasien, no_dokter, id_poli, keluhan, tgl_periksa, status)
            VALUES (?, ?, ?, ?, ?, 'Menunggu')";

    $stmt = $koneksi->prepare($sql);
    $status_default = 'Menunggu';
    $stmt->bind_param("ssssss", $id_pasien, $id_dokter, $id_poli, $keluhan, $tgl, $status_default);

    if ($stmt->execute()) {
        header("Location: kunjungan_list.php");
        exit;
    }
    $stmt->close();
}
?>
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
                    $ps_result = $koneksi->query("SELECT * FROM pasien");
                    while($p = $ps_result->fetch_assoc()):
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
                <?php while($d = $dokter_result->fetch_assoc()): ?>
                    <option value="<?= $d['no_dokter'] ?>"><?= $d['nama_dokter'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label>Poli</label>
          <select name="id_poli" class="form-control" required>
            <option value="">-- Pilih Poli --</option>
            <?php while($po = $poli_result->fetch_assoc()): ?>
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