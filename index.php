<?php
// Load Composer autoload (untuk library tambahan)
require __DIR__ . '/vendor/autoload.php';

// Load konfigurasi aplikasi (misalnya database)
require __DIR__ . '/config/config.php';

require __DIR__ . '/function.php';
// Tangkap URL untuk routing
$page = isset($_GET['url']) ? $_GET['url'] : 'landing';

// Daftar halaman yang diizinkan
$allowed_pages = ['landing', 'login', 'regis', 'dashboard'];
if (!in_array($page, $allowed_pages)) {
    http_response_code(404);
    echo "404 - Halaman tidak ditemukan!";
    exit;
}

// Cek apakah file yang diminta ada
$page_path = "$page.php";
if (file_exists($page_path)) {
    require $page_path;
} else {
    echo "404 - Halaman tidak ditemukan!";
}
?>
