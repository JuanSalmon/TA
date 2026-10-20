<?php
namespace Jubox\Web;

// Mulai output buffering untuk menangkap output tak diinginkan
ob_start();

// Set header JSON
header('Content-Type: application/json');

// Sertakan kelas Database dan file enkripsi
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/enkrip.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Konfigurasi database
$config = include __DIR__ . '/../config/config.php';

// Set zona waktu WITA
date_default_timezone_set('Asia/Makassar');

// Muat kunci enkripsi dari .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();
$key = base64_decode($_ENV['ENCRYPTION_KEY'] ?: $config['config']['aes_key']);

try {
    // Inisialisasi koneksi database
    $db = new Database($config);
    $pdo = $db->getConnection();

    // Periksa apakah form disubmit
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Metode request tidak valid']);
        ob_end_flush();
        exit;
    }

    // Validasi CSRF token
    session_start();
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid']);
        ob_end_flush();
        exit;
    }

    // Ambil data dari form
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validasi input
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Username, email, dan password tidak boleh kosong']);
        ob_end_flush();
        exit;
    }

    // Validasi username (huruf, angka, underscore, max 50)
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username) || strlen($username) > 50) {
        echo json_encode(['success' => false, 'message' => 'Username hanya boleh berisi huruf, angka, atau underscore dan maksimum 50 karakter']);
        ob_end_flush();
        exit;
    }

    // Validasi email (max 100)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        echo json_encode(['success' => false, 'message' => 'Format email tidak valid atau terlalu panjang (maksimum 100 karakter)']);
        ob_end_flush();
        exit;
    }

    // Validasi password (min 8 karakter, huruf atau angka)
    if (!preg_match('/^[A-Za-z0-9]{8,}$/', $password)) {
        echo json_encode(['success' => false, 'message' => 'Password harus minimal 8 karakter huruf atau angka']);
        ob_end_flush();
        exit;
    }

    // Cek apakah username sudah ada
    $sql = "SELECT COUNT(*) FROM user WHERE name = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Username sudah terdaftar']);
        ob_end_flush();
        exit;
    }

    // Cek apakah email sudah ada
    $sql = "SELECT COUNT(*) FROM user WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'Email sudah terdaftar']);
        ob_end_flush();
        exit;
    }

    // Hash password untuk autentikasi
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Enkripsi password untuk kredensial database
    $encrypted_password = encrypt($password, $key);

    // Set nilai default untuk kolom lainnya
    $allowed = '0';
    $first_login = 1; // Tambahkan first_login
    $tanggal_dibuat = date('Y-m-d H:i:s');
    $is_online = '0';
    $reset_token_hash = null;
    $reset_token_expires_at = null;
    $last_activity = $tanggal_dibuat;

    // Query untuk menambahkan user
    $sql = "INSERT INTO user (name, email, password, encrypted_password, allowed, first_login, tanggal_dibuat, last_activity, is_online, reset_token_hash, reset_token_expires_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $email, $hashed_password, $encrypted_password, $allowed, $first_login, $tanggal_dibuat, $last_activity, $is_online, $reset_token_hash, $reset_token_expires_at]);

    // Catat ke audit_log (opsional, uncomment jika diperlukan)
    // $sql = "INSERT INTO audit_log (action, username) VALUES (?, ?)";
    // $stmt = $pdo->prepare($sql);
    // $stmt->execute(['Register user', $username]);

    // Buat pengguna database (opsional, uncomment jika diperlukan)
    // createDatabaseUser($username, $password, $pdo);

    // Kirim respons sukses
    echo json_encode(['success' => true, 'message' => 'Registrasi berhasil! Silakan login']);
    error_log("[" . date('Y-m-d H:i:s') . "] Registrasi berhasil: username=$username, email=$email, first_login=$first_login");
} catch (\PDOException $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Error database: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error database: ' . $e->getMessage()]);
} catch (\Exception $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Terjadi kesalahan: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}

// Bersihkan buffer dan kirim respons
ob_end_flush();
exit;
?>