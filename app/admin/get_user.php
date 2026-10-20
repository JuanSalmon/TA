<?php
namespace Jubox\Web;

use PDO;

session_start();
error_log("[" . date('Y-m-d H:i:s') . "] Memuat get_user.php, path: " . __FILE__ . ", sesi: " . print_r($_SESSION, true));

// Cek sesi admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    error_log("[" . date('Y-m-d H:i:s') . "] Akses ditolak untuk get_user.php, sesi: " . print_r($_SESSION, true));
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Akses hanya untuk admin']);
    exit();
}

if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 403 Forbidden');
    exit('Akses langsung tidak diizinkan');
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
        throw new Exception('File konfigurasi tidak ditemukan');
    }
    $configResult = include $configPath;
    if (!is_array($configResult) || !isset($configResult['success']) || !$configResult['success']) {
        throw new Exception('Konfigurasi tidak valid');
    }
    error_log("[" . date('Y-m-d H:i:s') . "] get_user.php: Config loaded");

    // Include Database class
    $dbPath = __DIR__ . '/../config/database.php';
    if (!file_exists($dbPath)) {
        throw new Exception('File database.php tidak ditemukan');
    }
    require_once $dbPath;
    error_log("[" . date('Y-m-d H:i:s') . "] get_user.php: Database class loaded");

    // Buat koneksi
    $database = new Database($configResult['config']);
    $koneksi = $database->getConnection();
    if (!$koneksi) {
        throw new Exception('Koneksi database tidak tersedia');
    }
    error_log("[" . date('Y-m-d H:i:s') . "] get_user.php: Database connected");

    // Cek kolom tabel user
    $stmt = $koneksi->query("SHOW COLUMNS FROM user");
    $columns = $stmt->fetchAll(\PDO::FETCH_COLUMN);
    $requiredColumns = ['id', 'name', 'email', 'allowed'];
    foreach ($requiredColumns as $col) {
        if (!in_array($col, $columns)) {
            throw new Exception("Kolom '$col' tidak ditemukan di tabel user");
        }
    }

    // Ambil ID untuk edit
    $id = isset($_GET['id']) ? intval($_GET['id']) : null;
    error_log("[" . date('Y-m-d H:i:s') . "] get_user.php: ID received: " . ($id !== null ? $id : 'none'));

    if ($id !== null && $id > 0) {
        $stmt = $koneksi->prepare("SELECT id, name, email FROM user WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan query untuk single user');
        }
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        error_log("[" . date('Y-m-d H:i:s') . "] get_user.php: Single user query result: " . ($result ? json_encode($result) : 'No result'));

        if ($result) {
            $stmtTopics = $koneksi->prepare("SELECT nama_topik FROM user_topik WHERE id_user = ?");
            $stmtTopics->execute([$id]);
            $result['topics'] = $stmtTopics->fetchAll(\PDO::FETCH_COLUMN) ?: [];
            echo json_encode($result);
        } else {
            echo json_encode(['error' => 'User tidak ditemukan']);
        }
    } else {
        $stmt = $koneksi->prepare("SELECT id, name, email, allowed FROM user");
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan query untuk semua user');
        }
        $stmt->execute();
        $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        error_log("[" . date('Y-m-d H:i:s') . "] get_user.php: All users query executed, count: " . count($users));

        foreach ($users as &$user) {
            $stmtTopics = $koneksi->prepare("SELECT nama_topik FROM user_topik WHERE user_id = ?");
            $stmtTopics->execute([$user['id']]);
            $user['topics'] = $stmtTopics->fetchAll(\PDO::FETCH_COLUMN) ?: [];
            $user['allowed'] = (bool)$user['allowed'];
        }
        unset($user);

        // Validasi data
        $ids = [];
        foreach ($users as $index => $user) {
            if (!isset($user['id']) || $user['id'] <= 0 || !is_numeric($user['id'])) {
                error_log("[" . date('Y-m-d H:i:s') . "] get_user.php: Invalid user at index " . $index . ": " . json_encode($user));
                throw new Exception('Data user tidak valid: ID hilang atau tidak valid pada index ' . $index);
            }
            if (in_array($user['id'], $ids)) {
                error_log("[" . date('Y-m-d H:i:s') . "] get_user.php: Duplicate ID found: " . $user['id']);
                throw new Exception('Data user tidak valid: ID duplikat ditemukan: ' . $user['id']);
            }
            $ids[] = $user['id'];
        }

        error_log("[" . date('Y-m-d H:i:s') . "] get_user.php: JSON response prepared, users: " . count($users));
        echo json_encode(['data' => $users]);
    }

} catch (Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] get_user.php: Error: " . $e->getMessage());
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

ob_end_flush();
exit;
?>