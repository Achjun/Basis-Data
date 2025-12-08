<?php
include 'koneksi.php';
// session_start();

// hanya admin
// if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Admin') {
//     header("Location: login.php");
//     exit;
// }

// Cek apakah id dikirim
if (!isset($_GET['id'])) {
    header("Location: apoteker_users.php");
    exit;
}

$id_user = $_GET['id'];

// Hapus user dari database
$sql = "DELETE FROM users WHERE id_user='$id_user' AND role='apoteker'";
if ($koneksi->query($sql)) {
    // sukses hapus
    header("Location: apoteker_users.php?msg=hapus_sukses");
    exit;
} else {
    // gagal hapus
    header("Location: apoteker_users.php?msg=hapus_gagal");
    exit;
}
?>
