<?php
use Dotenv\Dotenv;

error_log("Memulai config.php");

// Mulai dengan array default
$result = ['success' => true, 'config' => []];

try {
    // Cek autoload
    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        error_log("vendor/autoload.php tidak ditemukan di " . __DIR__ . '/../vendor/autoload.php');
        throw new Exception('Autoload tidak ditemukan');
    }
    
    require __DIR__ . '/../vendor/autoload.php';
    error_log("Autoload dimuat");

    // Load .env file
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    
    // Validasi apakah .env berhasil dimuat
    if (empty($_ENV)) {
        error_log("File .env dimuat tetapi $_ENV kosong");
        throw new Exception('Variabel lingkungan tidak dimuat');
    }
    
    // error_log("File .env dimuat: " . print_r($_ENV, true));
    
    // Validasi required variables
    $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'SMTP_HOST', 'SMTP_PORT', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'APP_URL'])->notEmpty();
    
    // Set default untuk DB_PASS jika tidak ada
    if (!isset($_ENV['DB_PASS'])) {
        $_ENV['DB_PASS'] = '';
    }
    
    error_log("Variabel wajib divalidasi");
    
    // Buat konfigurasi database HANYA jika .env berhasil dimuat
    $dbConfig = [
        'host' => $_ENV['DB_HOST'],
        'dbname' => $_ENV['DB_NAME'],
        'username' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASS']
    ];
    
    // Double check konfigurasi database
    if (empty($dbConfig['host']) || empty($dbConfig['dbname']) || empty($dbConfig['username'])) {
        throw new Exception('Konfigurasi database tidak lengkap: ' . print_r($dbConfig, true));
    }
    
    $result['config'] = $dbConfig;
    error_log("Config berhasil dibuat: " . print_r($dbConfig, true));
    
    // Definisikan konstanta SMTP
    $constants = [
        'SMTP_HOST' => $_ENV['SMTP_HOST'],
        'SMTP_PORT' => $_ENV['SMTP_PORT'],
        'SMTP_USERNAME' => $_ENV['SMTP_USERNAME'],
        'SMTP_PASSWORD' => $_ENV['SMTP_PASSWORD'],
        'APP_URL' => $_ENV['APP_URL']
    ];

    foreach ($constants as $name => $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }
    
    error_log("Konstanta SMTP berhasil didefinisikan");
    
} catch (Exception $e) {
    error_log("Error di config.php: " . $e->getMessage());
    $result = ['success' => false, 'error' => 'Gagal memuat konfigurasi: ' . $e->getMessage()];
}

error_log("Mengembalikan result: " . print_r($result, true));
return $result;
?>