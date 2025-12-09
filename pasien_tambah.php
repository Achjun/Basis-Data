<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $no_pasien = $_POST['no_pasien'];
    $nama = $_POST['nama'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = $_POST['alamat'];
    $telp = $_POST['no_telp'];
    $tgl_lahir = $_POST['tgl_lahir'];

    // Menggunakan Prepared Statement MySQLi
    $sql = "INSERT INTO pasien (no_pasien, nama_pasien, jenis_kelamin, alamat, no_telepon, tgl_lahir)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ssssss", $no_pasien, $nama, $jenis_kelamin, $alamat, $telp, $tgl_lahir);

    if ($stmt->execute()) {
        header("Location: pasien_list.php");
        exit;
    } else {
        echo "Gagal menambah data! Error: " . $stmt->error;
    }
    $stmt->close();
}
// ... sisa kode HTML (tidak berubah)
?>