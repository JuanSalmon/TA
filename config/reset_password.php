<?php
use Dotenv\Dotenv;

// Set zona waktu PHP ke WITA
date_default_timezone_set('Asia/Makassar');

require '../vendor/autoload.php';

// Muat file .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Validasi variabel lingkungan
$requiredEnvVars = ['DB_HOST', 'DB_NAME', 'DB_USER'];
$missingVars = array_filter($requiredEnvVars, fn($var) => !isset($_ENV[$var]) || empty($_ENV[$var]));
// Izinkan DB_PASS kosong untuk lingkungan XAMPP
if (!isset($_ENV['DB_PASS'])) {
    $_ENV['DB_PASS'] = ''; // Set ke string kosong jika tidak ada
}
if (!empty($missingVars)) {
    $errorMsg = 'Variabel lingkungan tidak lengkap: ' . implode(', ', $missingVars);
    error_log("[" . date('Y-m-d H:i:s') . "] $errorMsg");
    session_start();
    $_SESSION['error'] = $errorMsg;
    header('Location: /reset_password.php');
    exit;
}

// Koneksi ke database
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set zona waktu MySQL ke WITA
    $pdo->exec("SET time_zone = '+08:00'");
    // Log waktu saat ini di PHP dan MySQL
    $phpTime = date('Y-m-d H:i:s');
    $mysqlTime = $pdo->query("SELECT NOW() as now")->fetchColumn();
    error_log("[" . date('Y-m-d H:i:s') . "] Koneksi database berhasil: {$_ENV['DB_NAME']}, Waktu PHP: $phpTime, Waktu MySQL: $mysqlTime");
} catch (PDOException $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] Koneksi database gagal: " . $e->getMessage());
    session_start();
    $_SESSION['error'] = 'Koneksi database gagal. Silakan hubungi administrator.';
    header('Location: /reset_password.php');
    exit;
}

// Mulai sesi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];

    // Validasi token
    try {
        $stmt = $pdo->prepare("SELECT id, email, reset_token_expires_at FROM user WHERE reset_token_hash = ? AND reset_token_expires_at > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $mysqlTime = $pdo->query("SELECT NOW() as now")->fetchColumn();
        error_log("[" . date('Y-m-d H:i:s') . "] Validasi token: $token, ditemukan: " . ($user ? 'Ya' : 'Tidak') . ", Waktu MySQL: $mysqlTime, Expires: " . ($user['reset_token_expires_at'] ?? 'N/A'));
    } catch (PDOException $e) {
        error_log("[" . date('Y-m-d H:i:s') . "] Error query SELECT: " . $e->getMessage());
        $_SESSION['error'] = 'Terjadi kesalahan database. Silakan hubungi administrator.';
        header('Location: /reset_password.php?token=' . urlencode($token));
        exit;
    }

    if ($user) {
        // Hash kata sandi baru
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Perbarui kata sandi dan hapus token
        try {
            $stmt = $pdo->prepare("UPDATE user SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?");
            $stmt->execute([$hashed_password, $user['id']]);
            error_log("[" . date('Y-m-d H:i:s') . "] Kata sandi direset untuk email: {$user['email']}, id: {$user['id']}");
        } catch (PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Error query UPDATE: " . $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan database. Silakan hubungi administrator.';
            header('Location: /reset_password.php?token=' . urlencode($token));
            exit;
        }

        $_SESSION['message'] = 'Kata sandi berhasil direset. Silakan login.';
        header('Location: /login/index.php');
    } else {
        $_SESSION['error'] = 'Token tidak valid atau telah kedaluwarsa.';
        error_log("[" . date('Y-m-d H:i:s') . "] Token tidak valid: $token");
        header('Location: /reset_password.php?token=' . urlencode($token));
    }
    exit;
}
?>