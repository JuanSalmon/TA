<?php
session_start();
error_log("[" . date('Y-m-d H:i:s') . "] Memuat delete_topik.php, sesi: " . print_r($_SESSION, true));

// Muat konfigurasi
$configResult = include __DIR__ . '/../config/config.php';
if (!is_array($configResult) || !isset($configResult['success']) || !$configResult['success']) {
    $_SESSION['error'] = 'Konfigurasi sistem tidak valid';
    header("Location: /login/index.php?error=config_error");
    exit();
}
$config = $configResult['config'];

// Muat kelas Database
require_once __DIR__ . '/../config/database.php';

try {
    $database = new \Jubox\Web\Database($config);
    $koneksi = $database->getConnection();

    // Verifikasi user berdasarkan sesi yang digunakan (name, bukan username)
    if (!isset($_SESSION['name'])) {
        $_SESSION['error'] = 'Sesi tidak valid, silakan login kembali';
        header("Location: /login/index.php?error=not_logged_in");
        exit();
    }
    $stmt = $koneksi->prepare("SELECT id FROM user WHERE name = :name LIMIT 1");
    $stmt->execute(['name' => $_SESSION['name']]);
    $user = $stmt->fetch(\PDO::FETCH_ASSOC);
    if (!$user) {
        $_SESSION['error'] = 'Pengguna tidak ditemukan';
        header("Location: /login/index.php?error=user_not_found");
        exit();
    }
    $user_id = $user['id'];

    // Proses penghapusan topik
    $success_message = '';
    $error_message = '';
    if (isset($_GET['id_topik']) && is_numeric($_GET['id_topik'])) {
        $id_topik = (int)$_GET['id_topik'];
        $query = "DELETE FROM user_topik WHERE id_topik = :id_topik AND user_id = :user_id";
        $stmt = $koneksi->prepare($query);
        $stmt->execute(['id_topik' => $id_topik, 'user_id' => $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $success_message = 'Topik berhasil dihapus!';
        } else {
            $error_message = 'Topik tidak ditemukan atau tidak dapat dihapus.';
        }
    } else {
        $error_message = 'ID topik tidak valid.';
    }

    // Simpan pesan ke sesi untuk ditampilkan di halaman tujuan
    if ($success_message) {
        $_SESSION['success_message'] = $success_message;
    }
    if ($error_message) {
        $_SESSION['error_message'] = $error_message;
    }

    // Redirect kembali ke halaman topik
    header("Location: index2.php");
    exit();

} catch (\PDOException $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Error menghapus topik: " . $e->getMessage());
    $_SESSION['error_message'] = 'Gagal menghapus topik: Terjadi kesalahan server.';
    header("Location: index2.php");
    exit();
}
?>