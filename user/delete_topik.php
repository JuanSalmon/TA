<?php
session_start();

$config = include __DIR__ . '/../config/config.php';

require_once __DIR__ . '/../config/database.php';

try {
    $database = new \Jubox\Web\Database($config);
    $koneksi = $database->getConnection();

    // Verifikasi user
    $query = "SELECT id FROM users WHERE name = :username";
    $stmt = $koneksi->prepare($query);
    $stmt->execute(['username' => $_SESSION['username']]);
    if ($stmt->rowCount() == 0) {
        header("Location: ../login/login.php");
        exit;
    }
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $user['id'];

    // Hapus topik jika id_topik valid dan milik user
    if (isset($_GET['id_topik'])) {
        $query = "DELETE FROM user_topik WHERE id_topik = :id_topik AND id_user = :id_user";
        $stmt = $koneksi->prepare($query);
        $stmt->execute(['id_topik' => $_GET['id_topik'], 'id_user' => $user_id]);
        
        // Set session untuk notifikasi sukses
        $_SESSION['success_message'] = 'Topik berhasil dihapus!';
    }

    // Redirect kembali ke halaman topik
    header("Location: index2.php");
    exit;

} catch (\PDOException $e) {
    die("Gagal menghapus topik: " . $e->getMessage());
}
?>