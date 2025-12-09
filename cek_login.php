<?php
// cek_login.php
include 'koneksi.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Memastikan koneksi berhasil sebelum digunakan
    if ($koneksi->connect_error) {
        $_SESSION['login_error'] = 'Gagal terhubung ke database.';
        header("Location: login.php");
        exit;
    }

    $username = $koneksi->real_escape_string($_POST['username'] ?? '');
    $password = $koneksi->real_escape_string($_POST['password'] ?? '');

    // Query untuk mengambil data pengguna
    $sql = "SELECT id_user, username, nama_lengkap, role, password FROM users WHERE username = '$username'";
    $result = $koneksi->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        if ($password === $user['password']) {
            
            // Set session data
            $_SESSION['logged_in'] = true;
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];

            // Arahkan ke dashboard sesuai role
            $role = strtolower($user['role']);
            
            // --- LOGIKA PENGARAHAN YANG SUDAH KOREK ---
            
            if ($role === 'kasir') {
<<<<<<< HEAD
                header("Location: dashboard_resepsionis.php"); 
            } elseif ($role === 'apoteker') {
                // PATH SUDAH BENAR: Langsung masuk ke folder apoteker
                header("Location: /BASIS-DATA/apoteker/dashboard_apoteker.php");
            } elseif ($role === 'manajer') {
                header("Location: dashboard_manajer.php");
            } else {
                header("Location: dashboard_" . $role . ".php");
            }

            // ----------------------------------------

            exit;

=======
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
>>>>>>> 928d8940cd9a0bd0cc5792c5edb92d1cfbe2b6d6
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