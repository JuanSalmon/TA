<?php

// Mencegah akses langsung jika tidak dipanggil melalui permintaan yang benar
if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 403 Forbidden');
    exit('Akses langsung tidak diizinkan');
}

// Memulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    // Memuat konfigurasi dan database
    $config = include __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/database.php';

    // Membuat koneksi database
    $database = new \Jubox\Web\Database($config);
    $koneksi = $database->getConnection();
    if (!$koneksi) {
        throw new Exception('Koneksi database tidak tersedia');
    }

    // Membersihkan dan memvalidasi input
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    // Validasi field yang diperlukan
    if (!$user_id || $user_id <= 0) {
        throw new Exception('ID pengguna tidak valid');
    }

    if (!in_array($action, ['allow', 'disallow'])) {
        throw new Exception('Tindakan tidak valid');
    }

    // Menetapkan status berdasarkan tindakan
    $allowed = ($action === 'allow') ? 1 : 0;
    
    // Memeriksa apakah pengguna ada sebelum melakukan update
    $checkStmt = $koneksi->prepare("SELECT id FROM users WHERE id = ?");
    if (!$checkStmt) {
        throw new Exception('Gagal menyiapkan query pengecekan');
    }
    $checkStmt->execute([$user_id]);
    
    if ($checkStmt->rowCount() === 0) {
        throw new Exception('Pengguna tidak ditemukan');
    }

    // Memperbarui status pengguna
    $stmt = $koneksi->prepare("UPDATE users SET allowed = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Gagal menyiapkan query');
    }
    
    $stmt->execute([$allowed, $user_id]);
    
    // Menetapkan pesan sukses
    $statusText = $allowed ? 'diizinkan' : 'ditolak';
    $_SESSION['success'] = "Status pengguna berhasil diubah menjadi $statusText";
    
    // Menutup statement dan koneksi
    $stmt = null;
    $koneksi = null;
    
    // Mengalihkan kembali ke halaman sebelumnya atau halaman pengguna
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'users.php';
    header("Location: $redirect");
    exit;
    
} catch (Exception $e) {
    // Mencatat kesalahan
    error_log('Kesalahan di update_status.php: ' . $e->getMessage());
    
    // Menetapkan pesan kesalahan
    $_SESSION['error'] = $e->getMessage();
    
    // Mengalihkan kembali ke halaman sebelumnya atau halaman pengguna
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'users.php';
    header("Location: $redirect");
    exit;
}
?>