<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_url = $_SERVER['REQUEST_URI'] ?? '';

// Debug log untuk session dan URL
error_log("auth.php: Current URL = " . $current_url);
error_log("auth.php: Current session data: " . print_r($_SESSION, true));

// Get current script name
$current_file = basename($_SERVER['PHP_SELF']);
error_log("auth.php: Current file: " . $current_file);

// Include file konfigurasi database
$config = include __DIR__ . '/../config/config.php';

// Include class Database
require_once __DIR__ . '/../config/database.php';

try {
    $database = new \Jubox\Web\Database($config);
    $koneksi = $database->getConnection();
} catch (\PDOException $e) {
    error_log("Koneksi database gagal: " . $e->getMessage());
    $_SESSION['error'] = "Koneksi database gagal";
    header("Location: /login/index.php");
    exit();
}

// Check if logged in
if (!isset($_SESSION['name']) || !isset($_SESSION['id'])) {
    error_log("No valid session found - redirecting to login");
    session_destroy();
    header("Location: /login/index.php");
    exit();
}

// If admin and not already in admin area, redirect to admin page
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1 && strpos($current_url, '/admin/') === false && !isset($_GET['redirected'])) {
    error_log("Admin detected - redirecting to admin dashboard");
    header("Location: /admin/index.php?redirected=1");
    exit();
}

// Only proceed with user checks if not admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    try {
        $stmt = $koneksi->prepare("SELECT id FROM users WHERE name = :name AND allowed = 1 LIMIT 1");
        $stmt->execute(['name' => $_SESSION['name']]);
        
        if ($stmt->rowCount() == 0) {
            error_log("User tidak ditemukan atau belum diijinkan: " . $_SESSION['name']);
            session_destroy();
            header("Location: /login/index.php?error=not_allowed");
            exit();
        }

        // Update user activity only if query successful
        $stmt = $koneksi->prepare("UPDATE users SET last_activity = NOW(), is_online = 1 WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['id']]);

        //activate user
        $stmt = $koneksi->prepare("SELECT id, name FROM users WHERE is_online = 1");
        $stmt->execute();
        $activeUser = $stmt->fetchALL(PDO::FETCH_ASSOC);
        $_SESSION['activeUser'] = $activeUser;
        error_log("User activity updated for: " . print_r($_SESSION['activeUser'], true));

    } catch (\PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        session_destroy();
        header("Location: /login/index.php?error=db_error");
        exit();
    }
    
}
?>