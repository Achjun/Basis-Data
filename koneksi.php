<?php

$host = "localhost"; 
$user = "root";   
$pass = ""; 
$db   = "db_klinik_pro";
$koneksi = new mysqli($host, $user, $pass, $db);
if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>