<?php
namespace Jubox\Web;

// Mencegah akses langsung jika tidak dipanggil melalui permintaan yang benar
if (php_sapi_name() !== 'cli' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 403 Forbidden');
    error_log("[" . date('Y-m-d H:i:s') . "] Akses langsung ke update_user.php ditolak, IP: " . $_SERVER['REMOTE_ADDR']);
    exit('Akses langsung tidak diizinkan');
}

// Memulai sesi jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

    // Ambil data dari POST
    $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Validasi input
    if ($edit_id <= 0) {
        throw new Exception('ID pengguna tidak valid');
    }
    if (empty($name)) {
        throw new Exception('Username wajib diisi');
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Email tidak valid');
    }
    if (!empty($password) && strlen($password) < 8) {
        throw new Exception('Password minimal 8 karakter');
    }

    // Periksa duplikasi username dan email
    $stmt = $koneksi->prepare("SELECT id FROM user WHERE (name = ? OR email = ?) AND id != ?");
    $stmt->execute([$name, $email, $edit_id]);
    if ($stmt->fetch()) {
        throw new Exception('Username atau email sudah digunakan');
    }

    // Log data yang akan diupdate
    error_log("[" . date('Y-m-d H:i:s') . "] update_user.php: Memperbarui pengguna, ID: $edit_id, Name: $name, Email: $email, Password: " . (empty($password) ? 'tidak diubah' : 'diubah'));

    // Update database
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare("UPDATE user SET name = ?, email = ?, password = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan query');
        }
        $stmt->execute([$name, $email, $hashed_password, $edit_id]);
    } else {
        $stmt = $koneksi->prepare("UPDATE user SET name = ?, email = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Gagal menyiapkan query');
        }
        $stmt->execute([$name, $email, $edit_id]);
    }

    // Periksa apakah update berhasil
    $affected_rows = $stmt->rowCount();
    if ($affected_rows === 0) {
        throw new Exception('Tidak ada data yang diperbarui, ID tidak ditemukan');
    }

    // Log keberhasilan
    error_log("[" . date('Y-m-d H:i:s') . "] update_user.php: Update berhasil, affected rows: $affected_rows");

    echo json_encode(['success' => true, 'message' => 'Data pengguna berhasil diperbarui']);
} catch (Exception $e) {
    // Log error
    error_log("[" . date('Y-m-d H:i:s') . "] update_user.php: Error - " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

ob_end_flush();
?>