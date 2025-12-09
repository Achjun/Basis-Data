<?php
// cek_login.php
include 'koneksi.php'; 

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Query menggunakan Prepared Statement MySQLi
    $stmt = $koneksi->prepare("SELECT id_user, username, nama_lengkap, role, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Periksa password (asumsi password plain text di database)
        if ($password === $user['password']) {
            
            // Set session data
            $_SESSION['logged_in'] = true;
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];

            // Arahkan ke dashboard sesuai role
            $role = strtolower($user['role']);
            
            if ($role === 'kasir') {
                header("Location: dashboard_resepsionis.php"); 
            } elseif ($role === 'apoteker') {
                header("Location: apoteker/dashboard_apoteker.php");
            } elseif ($role === 'manajer') {
                header("Location: manajer/dashboard_manajer.php");
            } elseif ($role === 'dokter') {
                header("Location: dokter/dashboard_dokter.php");
            } else {
                header("Location: dashboard_" . $role . ".php");
            }

            exit;

        } else {
            $_SESSION['login_error'] = 'Password salah.';
        }
    } else {
        $_SESSION['login_error'] = 'Username tidak ditemukan.';
    }

    $stmt->close();
    
    // Kembali ke halaman login jika gagal
    header("Location: login.php");
    exit;
}
?>