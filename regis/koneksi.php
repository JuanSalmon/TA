<?php
$host = "localhost"; // Host online database
$username = "root"; // Username database
$password = ""; // Password database
$database = "mqtt"; // Nama database

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>