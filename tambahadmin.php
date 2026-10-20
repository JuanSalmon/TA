<?php
$config = include __DIR__ . '/config/config.php';
$database = new \Jubox\Web\Database($config);
$koneksi = $database->getConnection();

try {
    // Data admin awal
    $adminName = 'ok';
    $adminPassword = '1'; // Ganti dengan password yang diinginkan
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

    // Masukkan data ke tabel admin
    $query = "INSERT INTO admin (name, password) VALUES (:name, :password)";
    $stmt = $koneksi->prepare($query);
    $stmt->execute(['name' => $adminName, 'password' => $hashedPassword]);

    echo "Admin $adminName telah ditambahkan dengan password di-hash.";
} catch (\PDOException $e) {
    error_log("Gagal menambahkan admin: " . $e->getMessage());
    die("Terjadi kesalahan.");
}
?>