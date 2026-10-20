<?php
namespace Jubox\Web;

use PDO;
use PDOException;
use InvalidArgumentException;

class Database {
    private $connection;
    private $config;

    public function __construct(array $config) {
        // Logging untuk memverifikasi input dengan timestamp
        error_log(" Config diterima di Database constructor: " . print_r($config, true));

        // Handle jika config dibungkus dalam array result
        if (isset($config['success']) && isset($config['config']) && is_array($config['config'])) {
            error_log(" Detected wrapped config, extracting...");
            $config = $config['config'];
            error_log(" Extracted config: " . print_r($config, true));
        }

        // Validasi kunci yang diperlukan
        $requiredKeys = ['host', 'dbname', 'username'];
        foreach ($requiredKeys as $key) {
            if (!isset($config[$key]) || empty($config[$key])) {
                error_log(" Validasi gagal untuk key '$key' dengan nilai: " . var_export($config[$key] ?? null, true));
                throw new InvalidArgumentException("Invalid database configuration: Missing or empty '$key'");
            }
        }
        
        // Password boleh kosong
        $config['password'] = $config['password'] ?? '';
        $this->config = $config;
        $this->connect();
    }

    private function connect() {
        try {
            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['dbname']};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            error_log("[" . date('Y-m-d H:i:s') . "] Koneksi PDO berhasil dengan config: " . print_r($this->config, true));
        } catch (PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Koneksi gagal: " . $e->getMessage());
            throw new PDOException("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }
}
?>