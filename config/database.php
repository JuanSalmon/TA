<?php
namespace Jubox\Web;

class Database {
    private $pdo;

    public function __construct($config) {
        try {
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
            $this->pdo = new \PDO($dsn, $config['username'], $config['password']);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}