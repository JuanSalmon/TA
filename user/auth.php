<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '../../config/config.php';
// Cek apakah user sudah login atau belum
if (!isset($_SESSION['username'])) {
    header("Location: ../login/index.php"); // Redirect ke login jika belum login
    exit();
}

// Ambil username dari session
$username = $_SESSION['username'];

// Cek apakah username ada di tabel `admins`
$query = "SELECT username FROM user_db WHERE username = '$username'";
$result = $koneksi->query($query);

if ($result->num_rows == 0) {
    // Jika username tidak ditemukan di `admins`, logout dan redirect ke login
    session_destroy();
    header("Location: ../login/index.php");
    exit();
}
?>
