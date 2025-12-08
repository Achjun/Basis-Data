<?php
include 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $koneksi->real_escape_string($_POST['username']);
    $password = $koneksi->real_escape_string($_POST['password']); // CATATAN: Dalam produksi, gunakan hashing!

    // Query untuk mengambil data pengguna
    $sql = "SELECT id_user, username, nama_lengkap, role, password FROM users WHERE username = '$username'";
    $result = $koneksi->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
            if ($password === $user['password']) {
            $_SESSION['logged_in'] = true;
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];

            // Arahkan ke dashboard sesuai role
            $role = strtolower($user['role']);
            
            if ($role === 'kasir') {
                 // Resepsionis dalam permintaan Anda adalah Kasir dalam DB
                header("Location: dashboard_resepsionis.php"); 
                exit;
            } elseif ($role === 'apoteker') {
                 // Apoteker tidak diminta, jadi arahkan ke dashboard default jika ada
                header("Location: dashboard.php"); 
                exit;
            } else {
                 // Admin, Dokter, dan role lain
                header("Location: dashboard_" . $role . ".php");
                exit;
            }
        } else {
            $_SESSION['login_error'] = 'Password salah.';
        }
    } else {
        $_SESSION['login_error'] = 'Username tidak ditemukan.';
    }

    // Kembali ke halaman login jika gagal
    header("Location: login.php");
    exit;
}
?>