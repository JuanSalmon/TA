<?php
// Mulai session hanya jika belum aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include file konfigurasi database
$config = include __DIR__ . '/config.php';

// Include class Database
require_once __DIR__ . '/database.php';

// Include fungsi autentikasi
require_once __DIR__ . '/auth_function.php';

try {
    // Buat objek Database
    $database = new \Jubox\Web\Database($config);
    // Dapatkan koneksi PDO
    $koneksi = $database->getConnection();
    
    // Test koneksi
    $test = $koneksi->query("SELECT 1");
    error_log("Debug - Database connection test: " . ($test ? "success" : "failed"));
    
} catch (\PDOException $e) {
    error_log("Koneksi database gagal: " . $e->getMessage());
    $_SESSION['error'] = "Terjadi kesalahan. Silakan coba lagi.";
    header("Location: ../login/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Debugging: Cek data POST yang diterima
    error_log("Data POST diterima: username=$username, password=$password");

    try {
        if (empty($username) || empty($password)) {
            error_log("Login gagal: Username atau password kosong.");
            $_SESSION['error'] = "Username dan password harus diisi.";
            header("Location: ../login/index.php");
            exit;
        }

        // Panggil fungsi autentikasi
        $authResult = authenticateUser($koneksi, $username, $password);

        // Set sesi berdasarkan hasil autentikasi
        if ($authResult['success']) {
            // Set session data
            foreach ($authResult['session_data'] as $key => $value) {
                $_SESSION[$key] = $value;
            }

            // Update status online hanya untuk user biasa
            if (strpos($authResult['redirect'], 'user') !== false) {
                $stmt = $koneksi->prepare("UPDATE users SET is_online = 1, last_activity = NOW() WHERE id = :id");
                $stmt->execute(['id' => $_SESSION['id']]);
            }
            // Admin tidak perlu update status online

            header("Location: " . $authResult['redirect']);
            exit;
        } else {
            $_SESSION['error'] = $authResult['session_data']['error'];
            header("Location: ../login/index.php");
            exit;
        }

    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = "Terjadi kesalahan. Silakan coba lagi.";
        header("Location: ../login/index.php");
        exit;
    }
} else {
    // Jika bukan POST, redirect ke login
    header("Location: ../login/index.php");
    exit;
}
?>