<?php
namespace Jubox\Web;
use PDO;

// Mencegah akses langsung jika tidak dipanggil melalui permintaan yang benar
if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 403 Forbidden');
    error_log("[" . date('Y-m-d H:i:s') . "] hapus_user.php: Akses langsung ditolak, IP: " . $_SERVER['REMOTE_ADDR']);
    echo json_encode(['success' => false, 'message' => 'Metode tidak diizinkan']);
    exit;
}

// Memulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validasi sesi admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    error_log("[" . date('Y-m-d H:i:s') . "] hapus_user.php: Akses ditolak, bukan admin, sesi: " . print_r($_SESSION, true));
    echo json_encode(['success' => false, 'message' => 'Akses hanya untuk admin']);
    exit;
}

try {
    // Muat konfigurasi
    $configResult = include __DIR__ . '/../config/config.php';
    if (!is_array($configResult) || !isset($configResult['success']) || !$configResult['success']) {
        throw new Exception('Konfigurasi tidak valid');
    }
    $config = $configResult['config'];

    // Muat kelas Database
    require_once __DIR__ . '/../config/database.php';
    $database = new Database($config);
    $koneksi = $database->getConnection();
    if (!$koneksi) {
        throw new Exception('Koneksi database tidak tersedia');
    }

    // Ambil username dari POST
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';

    // Validasi username
    if (empty($username)) {
        error_log("[" . date('Y-m-d H:i:s') . "] hapus_user.php: Username kosong atau tidak valid");
        echo json_encode(['success' => false, 'message' => 'Username tidak valid']);
        exit;
    }

    // Cek apakah username adalah pengguna yang sedang login
    if ($username === $_SESSION['name']) {
        error_log("[" . date('Y-m-d H:i:s') . "] hapus_user.php: Upaya menghapus pengguna saat ini: '$username'");
        echo json_encode(['success' => false, 'message' => 'Tidak dapat menghapus pengguna yang sedang login']);
        exit;
    }

    // Cek apakah user ada
    $stmt = $koneksi->prepare("SELECT id FROM user WHERE name = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        error_log("[" . date('Y-m-d H:i:s') . "] hapus_user.php: Pengguna tidak ditemukan: '$username'");
        echo json_encode(['success' => false, 'message' => 'Pengguna tidak ditemukan']);
        exit;
    }

    $user_id = $user['id'];

    // Mulai transaksi
    $koneksi->beginTransaction();

    // Hapus relasi di user_topik
    $stmt = $koneksi->prepare("DELETE FROM user_topik WHERE user_id = ?");
    $stmt->execute([$user_id]);
    error_log("[" . date('Y-m-d H:i:s') . "] hapus_user.php: Relasi user_topik dihapus untuk id_user: $user_id");

    // Hapus user dari tabel user
    $stmt = $koneksi->prepare("DELETE FROM user WHERE id = ?");
    $stmt->execute([$user_id]);
    $affected_rows = $stmt->rowCount();

    if ($affected_rows > 0) {
        $koneksi->commit();
        error_log("[" . date('Y-m-d H:i:s') . "] hapus_user.php: Pengguna dihapus berhasil: '$username', id: $user_id");
        echo json_encode(['success' => true, 'message' => 'Pengguna berhasil dihapus']);
    } else {
        $koneksi->rollBack();
        error_log("[" . date('Y-m-d H:i:s') . "] hapus_user.php: Gagal menghapus pengguna: '$username', id: $user_id");
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus pengguna']);
    }

} catch (Exception $e) {
    if (isset($koneksi) && $koneksi->inTransaction()) {
        $koneksi->rollBack();
    }
    error_log("[" . date('Y-m-d H:i:s') . "] hapus_user.php: Error - " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

exit;
?>