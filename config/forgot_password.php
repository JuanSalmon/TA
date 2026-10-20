<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

// Set zona waktu PHP ke WITA
date_default_timezone_set('Asia/Makassar');

require '../vendor/autoload.php';

// Muat file .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Validasi variabel lingkungan
$requiredEnvVars = ['DB_HOST', 'DB_NAME', 'DB_USER', 'SMTP_HOST', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'SMTP_PORT', 'SMTP_FROM_EMAIL', 'SMTP_FROM_NAME', 'APP_URL'];
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
    header('Location: /forgot_password.php');
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
    header('Location: /forgot_password.php');
    exit;
}

// Mulai sesi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proses form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    error_log("[" . date('Y-m-d H:i:s') . "] Menerima permintaan reset untuk email: $email");

    // Cek apakah email ada di database
    try {
        $stmt = $pdo->prepare("SELECT id, email FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("[" . date('Y-m-d H:i:s') . "] Query SELECT untuk email: $email, ditemukan: " . ($user ? 'Ya' : 'Tidak') . ", ID: " . ($user['id'] ?? 'N/A'));
    } catch (PDOException $e) {
        error_log("[" . date('Y-m-d H:i:s') . "] Error query SELECT: " . $e->getMessage());
        $_SESSION['error'] = 'Terjadi kesalahan database. Silakan hubungi administrator.';
        header('Location: /forgot_password.php');
        exit;
    }

    if ($user) {
        // Buat token reset
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+5 minutes'));

        // Simpan token dan waktu kedaluwarsa
        try {
            $stmt = $pdo->prepare("UPDATE user SET reset_token_hash = ?, reset_token_expires_at = ? WHERE id = ?");
            $stmt->execute([$token, $expires, $user['id']]);
            error_log("[" . date('Y-m-d H:i:s') . "] Token reset disimpan untuk email: $email, id: {$user['id']}, token: $token, expires: $expires");
        } catch (PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Error query UPDATE: " . $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan database. Silakan hubungi administrator.';
            header('Location: /forgot_password.php');
            exit;
        }

        // Konfigurasi PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USERNAME'];
            $mail->Password = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $_ENV['SMTP_PORT'];

            $mail->setFrom($_ENV['SMTP_FROM_EMAIL'], $_ENV['SMTP_FROM_NAME']);
            $mail->addAddress($email);
            $mail->Subject = 'Reset Kata Sandi';
            $resetLink = $_ENV['APP_URL'] . "/reset_password.php?token=$token";
            $mail->Body = "Klik tautan berikut untuk mereset kata sandi Anda: $resetLink\nTautan ini berlaku selama 5 menit.";

            $mail->send();
            $_SESSION['message'] = 'Link reset kata sandi telah dikirim ke email Anda.';
            error_log("[" . date('Y-m-d H:i:s') . "] Email reset dikirim ke: $email");
        } catch (Exception $e) {
            $_SESSION['error'] = "Gagal mengirim email: {$e->getMessage()}";
            error_log("[" . date('Y-m-d H:i:s') . "] Gagal mengirim email: {$e->getMessage()}");
            header('Location: /forgot_password.php');
            exit;
        }
    } else {
        $_SESSION['error'] = 'Email tidak ditemukan.';
        error_log("[" . date('Y-m-d H:i:s') . "] Email tidak ditemukan: $email");
    }
    header('Location: /forgot_password.php');
    exit;
}
?>