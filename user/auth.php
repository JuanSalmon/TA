<?php
// Mulai session
session_start();

// Include file konfigurasi database
require_once __DIR__ . '/../config/config.php';

// Fungsi untuk memeriksa apakah pengguna sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Cek apakah pengguna sudah login
if (!isLoggedIn()) {
    // Jika belum login, redirect ke halaman login
    header("Location: ../login/index.php");
    exit();
}

// Ambil username dari session (pastikan session username sudah diset saat login)
if (!isset($_SESSION['username'])) {
    // Jika session username tidak ada, logout dan redirect ke login
    session_destroy();
    header("Location: ../login/index.php");
    exit();
}

$username = $_SESSION['username'];

// Verifikasi apakah username ada di tabel `user_db`
try {
    global $pdo; // Pastikan koneksi PDO sudah diinisialisasi di config.php

    $stmt = $pdo->prepare("SELECT id FROM user_db WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Jika username tidak ditemukan di tabel `user_db`, logout dan redirect ke login
        session_destroy();
        header("Location: ../login/index.php");
        exit();
    }
} catch (Exception $e) {
    // Tangani error database
    error_log("Database error: " . $e->getMessage());
    session_destroy();
    header("Location: ../login/index.php");
    exit();
}
?>