<?php
session_start();
include 'config.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // Debugging koneksi
    if (!$koneksi) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }

    // Query User
    $query_user = "SELECT * FROM users WHERE name = '$username' AND password = '$password'";
    $result_user = mysqli_query($koneksi, $query_user);

    if (!$result_user) {
        die("Query user_db gagal: " . mysqli_error($koneksi));
    }

    // Query Admin
    $query_admin = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    $result_admin = mysqli_query($koneksi, $query_admin);

    if (!$result_admin) {
        die("Query admin gagal: " . mysqli_error($koneksi));
    }

    // Mengecek hasil query User
    if (mysqli_num_rows($result_user) > 0) {
        session_start();
        $_SESSION['username'] = $username;
        header("Location: ../../../user/index.php"); // Redirect ke dashboard user
        exit;
    }

    // Mengecek hasil query Admin
    if (mysqli_num_rows($result_admin) > 0) {
        session_start();
        $_SESSION['username'] = $username;
        header("Location: ../../../admin/index.php"); // Redirect ke dashboard admin
        exit;
    }

    // Jika username tidak ditemukan di kedua tabel
    echo "<script>alert('Username atau password salah!');</script>";
    echo "<script>window.location.href='login.html';</script>";
}
?>
