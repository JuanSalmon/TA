<?php
session_start();

// Include file konfigurasi database
$config = include 'config.php'; // Pastikan path ke config.php benar

// Include class Database
require_once 'database.php'; // Sesuaikan path ke file Database.php

try {
    // Buat objek Database
    $database = new \Jubox\Web\Database($config);

    // Dapatkan koneksi PDO
    $koneksi = $database->getConnection();
} catch (\PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

if (isset($_POST['login'])) {
    $username = trim($_POST['nama'] ?? '');
    $password = trim($_POST['password'] ?? '');
    echo "Username: $username, Password: $password<br>";
    try {
        // Query untuk tabel users
        $query_user = "SELECT * FROM users WHERE name = :username AND password = :password";
        echo "Query user: $query_user<br>"; // Debugging query
        $stmt_user = $koneksi->prepare($query_user);
        $stmt_user->execute(['username' => $username, 'password' => $password]);
        $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

        // Mengecek hasil query User
        if ($stmt_user->rowCount() > 0) {
            $_SESSION['username'] = $username;
            header("Location: ../../../user/index.php"); // Redirect ke dashboard user
            exit;
        }
    } catch (\PDOException $e) {
        die("Query user gagal: " . $e->getMessage());
    }

    try {
        // Query untuk tabel admin
        $query_admin = "SELECT * FROM admin WHERE username = :username AND password = :password";
        $stmt_admin = $koneksi->prepare($query_admin);
        $stmt_admin->execute(['username' => $username, 'password' => $password]);

        // Mengecek hasil query Admin
        if ($stmt_admin->rowCount() > 0) {
            $_SESSION['username'] = $username;
            header("Location: ../../../admin/index.php"); // Redirect ke dashboard admin
            exit;
        }
    } catch (\PDOException $e) {
        die("Query admin gagal: " . $e->getMessage());
    }

    // Jika username tidak ditemukan di kedua tabel
    echo "<script>alert('Username atau password salah!');</script>";
    echo "<script>window.location.href='../login/index.php';</script>";
}
?>