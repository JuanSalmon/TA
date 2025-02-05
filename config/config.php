<?php
$host = "localhost"; // Host online database
$username = "root"; // Username database
$password = ""; // Password database
$database = "mqtt"; // Nama database

// Membuat koneksi
$koneksi = new mysqli($host, $username, $password, $database);

// Periksa koneksi
if ($koneksi->connect_error) {
    die("Connection failed: " . $koneksi->connect_error);
}

?>