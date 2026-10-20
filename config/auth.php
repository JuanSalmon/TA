<?php
namespace Jubox\Web;

// Mulai sesi hanya jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_log("[" . date('Y-m-d H:i:s') . "] Memuat auth.php, path: " . __FILE__ . ", sesi awal: " . print_r($_SESSION, true));
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

function authenticate($username, $password) {
    error_log("[" . date('Y-m-d H:i:s') . "] Mencoba autentikasi untuk: " . $username);
    $configResult = include __DIR__ . '/config.php';
    if (!is_array($configResult) || !isset($configResult['success']) || !$configResult['success']) {
        error_log("[" . date('Y-m-d H:i:s') . "] Konfigurasi tidak valid: " . print_r($configResult, true));
        return ['success' => false, 'error' => 'config_error'];
    }
    $config = $configResult['config'];

    try {
        $database = new Database($config);
        $koneksi = $database->getConnection();

        // Cek admin
        $stmt = $koneksi->prepare("SELECT name, password FROM admin WHERE name = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            $admin = $stmt->fetch(\PDO::FETCH_ASSOC);
            error_log("[" . date('Y-m-d H:i:s') . "] Admin ditemukan: " . $admin['name']);
            if (password_verify($password, $admin['password'])) {
                $_SESSION['name'] = $admin['name'];
                $_SESSION['is_admin'] = true;
                $_SESSION['allowed'] = true;
                error_log("[" . date('Y-m-d H:i:s') . "] Login admin berhasil: " . $admin['name'] . ", Sesi: " . print_r($_SESSION, true));
                return ['success' => true, 'is_admin' => true];
            }
            error_log("[" . date('Y-m-d H:i:s') . "] Kata sandi admin salah untuk: " . $username);
            return ['success' => false, 'error' => 'password_mismatch'];
        }

        // Cek user
        $stmt = $koneksi->prepare("SELECT name, password, allowed FROM user WHERE name = ? OR email = ?");
        $stmt->execute([$username, $username]);
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            error_log("[" . date('Y-m-d H:i:s') . "] User ditemukan: " . $user['name']);
            if (password_verify($password, $user['password'])) {
                if (!$user['allowed']) {
                    error_log("[" . date('Y-m-d H:i:s') . "] User tidak diizinkan: " . $user['name']);
                    return ['success' => false, 'error' => 'not_allowed'];
                }
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email']; // Tambahkan email ke sesi
                $_SESSION['is_admin'] = false;
                $_SESSION['allowed'] = $user['allowed'];
                error_log("[" . date('Y-m-d H:i:s') . "] Login user berhasil: " . $user['name'] . ", Sesi: " . print_r($_SESSION, true));
                return ['success' => true, 'is_admin' => false];
            }
            error_log("[" . date('Y-m-d H:i:s') . "] Kata sandi user salah untuk: " . $username);
            return ['success' => false, 'error' => 'password_mismatch'];
        }

        error_log("[" . date('Y-m-d H:i:s') . "] User tidak ditemukan: " . $username);
        return ['success' => false, 'error' => 'user_not_found'];
    } catch (\PDOException $e) {
        error_log("[" . date('Y-m-d H:i:s') . "] Database error in auth: " . $e->getMessage());
        return ['success' => false, 'error' => 'db_error'];
    }
}

// Proses login dari form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    error_log("[" . date('Y-m-d H:i:s') . "] Menerima input: username=$username, password=[HIDDEN]");
    if (empty($username) || empty($password)) {
        error_log("[" . date('Y-m-d H:i:s') . "] Input kosong: username=$username");
        $_SESSION['error'] = 'Username dan kata sandi harus diisi';
        header("Location: /login/index.php?error=empty_input");
        exit();
    }

    $result = authenticate($username, $password);
    error_log("[" . date('Y-m-d H:i:s') . "] Hasil autentikasi untuk $username: " . print_r($result, true));
    if ($result['success']) {
        error_log("[" . date('Y-m-d H:i:s') . "] Redirect ke: " . ($result['is_admin'] ? '/admin/index.php' : '/user/index.php'));
        header("Location: " . ($result['is_admin'] ? '/admin/index.php' : '/user/index.php'));
        exit();
    } else {
        $_SESSION['error'] = match ($result['error']) {
            'user_not_found' => 'Username atau email tidak ditemukan',
            'password_mismatch' => 'Kata sandi salah',
            'not_allowed' => 'Akun Anda tidak diizinkan',
            'db_error' => 'Koneksi database gagal. Silakan hubungi administrator.',
            'config_error' => 'Konfigurasi sistem tidak valid',
            default => 'Terjadi kesalahan saat login'
        };
        error_log("[" . date('Y-m-d H:i:s') . "] Error login: " . $_SESSION['error']);
        header("Location: /login/index.php?error=" . $result['error']);
        exit();
    }
}

?>