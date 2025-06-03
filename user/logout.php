<?php
session_start();

// Sertakan konfigurasi database
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    // Inisialisasi koneksi database
    $config = include __DIR__ . '/../config/config.php';
    $database = new \Jubox\Web\Database($config);
    $koneksi = $database->getConnection();

    // Perbarui is_online menjadi 0 untuk pengguna saat ini
    if (isset($_SESSION['id'])) {
        $stmt = $koneksi->prepare("UPDATE users SET is_online = 0 WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['id']]);
        error_log("Pengguna ID {$_SESSION['id']} diatur ke offline");
    }

    // Hancurkan sesi
    session_unset();
    session_destroy();

    // Arahkan ke halaman login
    header("Location: /login/index.php");
    exit();
} catch (\PDOException $e) {
    error_log("Kesalahan logout: " . $e->getMessage());
    header("Location: /login/index.php?error=db_error");
    exit();
}
?>