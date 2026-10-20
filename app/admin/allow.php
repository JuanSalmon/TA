<?php
namespace Jubox\Web;

use PDO;

session_start();
error_log("[" . date('Y-m-d H:i:s') . "] Memuat allow.php, path: " . __FILE__ . ", sesi: " . print_r($_SESSION, true));

// Cek sesi admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    error_log("[" . date('Y-m-d H:i:s') . "] Akses ditolak untuk allow.php, sesi: " . print_r($_SESSION, true));
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Akses hanya untuk admin']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
    exit();
}

header('Content-Type: application/json; charset=utf-8');
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();

try {
    // Include konfigurasi
    $configPath = __DIR__ . '/../config/config.php';
    if (!file_exists($configPath)) {
        throw new Exception('File konfigurasi tidak ditemukan: ' . $configPath);
    }
    $configResult = include $configPath;
    if (!is_array($configResult) || !isset($configResult['success']) || !$configResult['success']) {
        throw new Exception('Konfigurasi tidak valid: ' . print_r($configResult, true));
    }
    error_log("[" . date('Y-m-d H:i:s') . "] allow.php: Config loaded");

    // Include Database class
    $dbPath = __DIR__ . '/../config/database.php';
    if (!file_exists($dbPath)) {
        throw new Exception('File database.php tidak ditemukan: ' . $dbPath);
    }
    require_once $dbPath;
    error_log("[" . date('Y-m-d H:i:s') . "] allow.php: Database class loaded");

    // Buat koneksi
    $database = new Database($configResult['config']);
    $koneksi = $database->getConnection();
    if (!$koneksi) {
        throw new Exception('Koneksi database tidak tersedia');
    }
    error_log("[" . date('Y-m-d H:i:s') . "] allow.php: Database connected");

    // Ambil data dari POST
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : null;
    $allowed = isset($_POST['allowed']) ? intval($_POST['allowed']) : null;
    error_log("[" . date('Y-m-d H:i:s') . "] allow.php: Received user_id: $user_id, allowed: $allowed");

    if ($user_id === null || $user_id <= 0 || !in_array($allowed, [0, 1])) {
        throw new Exception('Parameter user_id atau allowed tidak valid');
    }

    // Update status allowed
    $stmt = $koneksi->prepare("UPDATE user SET allowed = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception('Gagal menyiapkan query update');
    }
    $stmt->execute([$allowed, $user_id]);
    $affectedRows = $stmt->rowCount();
    error_log("[" . date('Y-m-d H:i:s') . "] allow.php: Update executed, affected rows: $affectedRows");

    if ($affectedRows === 0) {
        throw new Exception('Pengguna tidak ditemukan atau status tidak berubah');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Status pengguna berhasil diperbarui'
    ]);

} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] allow.php: Error: " . $e->getMessage() . ", Trace: " . $e->getTraceAsString());
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()]);
}

ob_end_flush();
exit;
?>