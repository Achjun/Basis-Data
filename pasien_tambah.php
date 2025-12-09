<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $no_pasien = $_POST['no_pasien'];
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = $_POST['alamat'];
    $telp = $_POST['no_telp'];
    $tgl_lahir = $_POST['tgl_lahir'];

    $sql = "INSERT INTO pasien (no_pasien, nama_pasien, jenis_kelamin, alamat, no_telepon, tgl_lahir)
            VALUES ('$no_pasien','$nama', '$jenis_kelamin', '$alamat', '$telp', '$tgl_lahir')";

    if ($conn->query($sql)) {
        header("Location: pasien_list.php");
        exit;
    } else {
        echo "Gagal menambah data!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Tambah Pasien</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div id="wrapper" class="d-flex">

        <div class="bg-dark border-right" id="sidebar-wrapper">
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

                <a href="kunjungan_list.php" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa fa-calendar"></i> Data Kunjungan
                </a>
                

                <a href="login.php?logout=true" class="list-group-item list-group-item-action bg-dark text-white">
                    <i class="fa fa-right-from-bracket"></i> Logout
                </a>
            </div>
        </div>

        <div class="container mt-4">

            <h3>Tambah Pasien</h3>

            <form method="POST" class="row g-3">

                <div class="col-md-6">
                    <label>No Pasien</label>
                    <input type="text" name="no_pasien" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label>Nama Pasien</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label>Alamat</label>
                    <input type="text" name="alamat" class="form-control" required>
                </div>

                <div class="col-md-12">
                    <label>tgl_lahir</label>
                    <input type="date" name="tgl_lahir" class="form-control" required></input>
                </div>

                <div class="col-md-6">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control" required>
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>No Telepon</label>
                    <input type="text" name="no_telp" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <button class="btn btn-success w-100 mt-3">Simpan</button>
                </div>

            </form>

        </div>

</body>

</html>